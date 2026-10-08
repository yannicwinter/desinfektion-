<?php
declare(strict_types=1);

const MAIL_FILE = DATA_DIR . '/mail.json';
const MAIL_TOKEN_FILE = DATA_DIR . '/mail-token.json';

if (is_file(DATA_DIR . '/config.php')) {
    require_once DATA_DIR . '/config.php';
}

function mail_from_config(): bool
{
    foreach (['GRAPH_TENANT_ID', 'GRAPH_CLIENT_ID', 'GRAPH_CLIENT_SECRET', 'GRAPH_SENDER'] as $k) {
        if (!defined($k) || trim((string) constant($k)) === '' || str_contains((string) constant($k), 'PLATZHALTER')) {
            return false;
        }
    }
    return true;
}

function mail_config(): array
{
    if (mail_from_config()) {
        return ['tenant' => trim((string) GRAPH_TENANT_ID), 'client_id' => trim((string) GRAPH_CLIENT_ID), 'secret' => trim((string) GRAPH_CLIENT_SECRET), 'sender' => trim((string) GRAPH_SENDER), 'source' => 'config'];
    }
    return is_file(MAIL_FILE) ? (json_decode((string) file_get_contents(MAIL_FILE), true) ?: []) : [];
}

function mail_save_config(array $c): bool
{
    $ok = file_put_contents(MAIL_FILE, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
    @chmod(MAIL_FILE, 0600);
    return $ok;
}

function mail_uses_graph(): bool
{
    $c = mail_config();
    return !empty($c['tenant']) && !empty($c['client_id']) && !empty($c['secret']) && !empty($c['sender']);
}

function mail_recipients(string $to): array
{
    return array_values(array_filter(array_map('trim', preg_split('/[,;]/', $to) ?: []), fn($a) => (bool) filter_var($a, FILTER_VALIDATE_EMAIL)));
}

function mail_target(): string
{
    return mail_uses_graph() ? (string) mail_config()['sender'] : (site('form_recipient') ?: site('email'));
}

function mail_test_target(): string
{
    return defined('MAIL_TEST') && filter_var(MAIL_TEST, FILTER_VALIDATE_EMAIL) ? (string) MAIL_TEST : 'y.winter@rotkreuz-verden.de';
}

function send_mail(string $to, string $subject, string $body, string $replyTo = '', string $html = ''): string
{
    $rcpt = mail_recipients($to);
    if (!$rcpt) {
        return 'Kein gültiger Empfänger eingetragen.';
    }
    if (mail_uses_graph()) {
        $err = graph_send($rcpt, $subject, $html !== '' ? $html : $body, $replyTo, $html !== '');
        if ($err !== '') {
            @error_log('Graph-Mailversand fehlgeschlagen: ' . $err);
        }
        return $err;
    }
    $host = preg_replace('/^www\./', '', parse_url(site('url'), PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $headers = [
        'From: ' . mb_encode_mimeheader(site('name')) . ' <noreply@' . $host . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'MIME-Version: 1.0',
    ];
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }
    return @mail(implode(', ', $rcpt), mb_encode_mimeheader($subject), $body, implode("\r\n", $headers)) ? '' : 'mail() des Servers hat den Versand abgelehnt.';
}

function graph_token(bool $fresh = false): array
{
    $c = mail_config();
    $t = is_file(MAIL_TOKEN_FILE) ? (json_decode((string) file_get_contents(MAIL_TOKEN_FILE), true) ?: []) : [];
    $key = hash('sha256', $c['tenant'] . '|' . $c['client_id'] . '|' . $c['secret']);
    if (!$fresh && ($t['key'] ?? '') === $key && !empty($t['token']) && ($t['exp'] ?? 0) > time() + 120) {
        return [(string) $t['token'], ''];
    }
    $r = mail_http('https://login.microsoftonline.com/' . rawurlencode((string) $c['tenant']) . '/oauth2/v2.0/token', http_build_query([
        'client_id' => $c['client_id'],
        'client_secret' => $c['secret'],
        'scope' => 'https://graph.microsoft.com/.default',
        'grant_type' => 'client_credentials',
    ]), ['Content-Type: application/x-www-form-urlencoded']);
    $j = json_decode($r['body'], true) ?: [];
    if (empty($j['access_token'])) {
        $msg = (string) ($j['error_description'] ?? $j['error'] ?? ($r['error'] ?: 'HTTP ' . $r['code']));
        return ['', 'Anmeldung bei Microsoft fehlgeschlagen: ' . strtok($msg, "\r\n")];
    }
    file_put_contents(MAIL_TOKEN_FILE, json_encode(['key' => $key, 'token' => (string) $j['access_token'], 'exp' => time() + (int) ($j['expires_in'] ?? 3600)]), LOCK_EX);
    @chmod(MAIL_TOKEN_FILE, 0600);
    return [(string) $j['access_token'], ''];
}

function graph_send(array $rcpt, string $subject, string $body, string $replyTo = '', bool $isHtml = false): string
{
    [$token, $err] = graph_token();
    if ($token === '') {
        return $err;
    }
    $msg = [
        'subject' => $subject,
        'body' => ['contentType' => $isHtml ? 'HTML' : 'Text', 'content' => $body],
        'toRecipients' => array_map(fn($a) => ['emailAddress' => ['address' => $a]], $rcpt),
    ];
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $msg['replyTo'] = [['emailAddress' => ['address' => $replyTo]]];
    }
    $url = 'https://graph.microsoft.com/v1.0/users/' . rawurlencode((string) mail_config()['sender']) . '/sendMail';
    $payload = json_encode(['message' => $msg, 'saveToSentItems' => false], JSON_UNESCAPED_UNICODE);
    $r = mail_http($url, (string) $payload, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
    if ($r['code'] === 401) {
        [$token, $err] = graph_token(true);
        if ($token === '') {
            return $err;
        }
        $r = mail_http($url, (string) $payload, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
    }
    if ($r['code'] === 202) {
        return '';
    }
    $j = json_decode($r['body'], true) ?: [];
    return 'Microsoft Graph: ' . ($j['error']['message'] ?? ($r['error'] ?: 'HTTP ' . $r['code']));
}

function mail_http(string $url, string $data, array $headers): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $data, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 6,
        ]);
        $body = (string) curl_exec($ch);
        $res = ['code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE), 'body' => $body, 'error' => curl_error($ch)];
        curl_close($ch);
        return $res;
    }
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $data, 'timeout' => 15, 'ignore_errors' => true]]);
    $body = (string) @file_get_contents($url, false, $ctx);
    $code = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int) $m[1] : 0;
    return ['code' => $code, 'body' => $body, 'error' => $code ? '' : 'keine Verbindung'];
}
