<?php
declare(strict_types=1);

const IG_DIR = UPLOAD_DIR . '/instagram';
const IG_TOKEN_FILE = DATA_DIR . '/instagram.json';
const IG_API = 'https://graph.instagram.com';

function instagram_posts(int $limit = 6): array
{
    $posts = instagram_api_posts();
    return array_slice($posts ?: instagram_manual_posts(), 0, $limit);
}

function instagram_state(): array
{
    return is_file(IG_TOKEN_FILE) ? (json_decode((string) file_get_contents(IG_TOKEN_FILE), true) ?: []) : [];
}

function instagram_save_state(array $s): void
{
    file_put_contents(IG_TOKEN_FILE, json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod(IG_TOKEN_FILE, 0600);
}

function instagram_set_token(string $token): string
{
    $token = trim($token);
    if ($token === '') {
        instagram_save_state([]);
        @unlink(IG_DIR . '/feed.json');
        return '';
    }
    $me = json_decode(instagram_get(IG_API . '/me?fields=user_id,username&access_token=' . rawurlencode($token)), true);
    if (empty($me['username'])) {
        return 'Der Schlüssel wurde nicht akzeptiert: ' . ($me['error']['message'] ?? 'keine Antwort von Instagram') . '.';
    }
    instagram_save_state(['token' => $token, 'username' => $me['username'], 'refreshed' => time(), 'expires' => time() + 60 * 86400, 'error' => '']);
    @unlink(IG_DIR . '/feed.json');
    return '';
}

function instagram_token(): string
{
    $s = instagram_state();
    $token = (string) ($s['token'] ?? '');
    if ($token === '') {
        return '';
    }
    if (($s['refreshed'] ?? 0) < time() - 7 * 86400) {
        $r = json_decode(instagram_get(IG_API . '/refresh_access_token?grant_type=ig_refresh_token&access_token=' . rawurlencode($token)), true);
        if (!empty($r['access_token'])) {
            $s['token'] = $token = (string) $r['access_token'];
            $s['expires'] = time() + (int) ($r['expires_in'] ?? 60 * 86400);
            $s['error'] = '';
        } else {
            $s['error'] = 'Verlängerung fehlgeschlagen: ' . ($r['error']['message'] ?? 'keine Antwort');
        }
        $s['refreshed'] = $s['error'] ? time() - 6 * 86400 : time();
        instagram_save_state($s);
    }
    return $token;
}

function instagram_api_posts(): array
{
    if (!is_file(IG_TOKEN_FILE)) {
        return [];
    }
    if (!is_dir(IG_DIR)) {
        @mkdir(IG_DIR, 0755, true);
    }
    $cacheFile = IG_DIR . '/feed.json';
    $cached = is_file($cacheFile) ? (json_decode((string) file_get_contents($cacheFile), true) ?: []) : [];
    if (is_file($cacheFile) && filemtime($cacheFile) > time() - 3 * 3600) {
        return $cached;
    }
    $token = instagram_token();
    if ($token === '') {
        return [];
    }
    $json = json_decode(instagram_get(IG_API . '/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&limit=12&access_token=' . rawurlencode($token)), true);
    $s = instagram_state();
    if (!isset($json['data'])) {
        $s['error'] = 'Abruf fehlgeschlagen: ' . ($json['error']['message'] ?? 'keine Antwort von Instagram');
        instagram_save_state($s);
        @touch($cacheFile);
        return $cached;
    }

    $posts = [];
    foreach ($json['data'] as $m) {
        $src = (string) (($m['media_type'] ?? '') === 'VIDEO' ? ($m['thumbnail_url'] ?? '') : ($m['media_url'] ?? ''));
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($m['id'] ?? '')) ?: md5($src);
        if ($src === '') {
            continue;
        }
        $file = IG_DIR . '/' . $id . '.jpg';
        if (!is_file($file) && !instagram_store_image($src, $file)) {
            continue;
        }
        $posts[] = [
            'img' => url('uploads/instagram/' . $id . '.jpg'),
            'link' => (string) ($m['permalink'] ?? '') ?: site('instagram'),
            'caption' => mb_substr(trim((string) preg_replace('/\s+/u', ' ', (string) ($m['caption'] ?? ''))), 0, 140),
            'date' => substr((string) ($m['timestamp'] ?? ''), 0, 10),
        ];
    }
    file_put_contents($cacheFile, json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    $s['error'] = '';
    $s['fetched'] = time();
    instagram_save_state($s);
    $keep = array_map(fn($p) => basename($p['img']), $posts);
    foreach (glob(IG_DIR . '/*.jpg') ?: [] as $f) {
        if (!in_array(basename($f), $keep, true)) {
            @unlink($f);
        }
    }
    return $posts;
}

function instagram_manual_posts(): array
{
    $links = lines(site('instagram_links'));
    $posts = [];
    for ($i = 1; $i <= 6; $i++) {
        if ($img = slot_image('insta-' . $i)) {
            $posts[] = ['img' => $img, 'link' => $links[$i - 1] ?? site('instagram'), 'caption' => '', 'date' => ''];
        }
    }
    return $posts;
}

function instagram_get(string $url): string
{
    if (!function_exists('curl_init')) {
        return (string) @file_get_contents($url);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_FOLLOWLOCATION => true]);
    $r = (string) curl_exec($ch);
    curl_close($ch);
    return $r;
}

function instagram_store_image(string $src, string $file): bool
{
    $raw = instagram_get($src);
    if ($raw === '' || !function_exists('imagecreatefromstring') || !($im = @imagecreatefromstring($raw))) {
        return false;
    }
    $w = imagesx($im);
    $h = imagesy($im);
    $s = min($w, $h);
    $out = imagecreatetruecolor(600, 600);
    imagecopyresampled($out, $im, 0, 0, (int) (($w - $s) / 2), (int) (($h - $s) / 2), 600, 600, $s, $s);
    imageinterlace($out, true);
    return imagejpeg($out, $file, 78);
}
