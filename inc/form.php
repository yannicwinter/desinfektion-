<?php
declare(strict_types=1);

function form_topics(): array
{
    $t = [];
    foreach (courses() as $c) {
        $t[$c['slug']] = $c['title'];
    }
    $t['arbeitssicherheit'] = 'Fachkraft für Arbeitssicherheit';
    $t['sonstiges'] = 'Sonstiges';
    return $t;
}

function form_handle(string $returnPath): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return [[], []];
    }
    $in = [];
    foreach (['firma', 'name', 'email', 'telefon', 'thema', 'teilnehmer', 'nachricht'] as $k) {
        $in[$k] = trim(str_replace(["\r", "\0"], '', (string) ($_POST[$k] ?? '')));
    }
    $err = [];

    $started = (int) ($_POST['t'] ?? 0);
    if (!empty($_POST['website']) || $started === 0 || time() - $started < 3) {
        redirect($returnPath . '?gesendet=1#formular');
    }
    if (!hash_equals(hash_hmac('sha256', 'form' . $started, app_secret()), (string) ($_POST['sig'] ?? '')) || time() - $started > 86400) {
        $err[] = 'Das Formular ist abgelaufen – bitte noch einmal absenden.';
    }
    if ($in['name'] === '') {
        $err[] = 'Bitte einen Namen angeben.';
    }
    if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
        $err[] = 'Bitte eine gültige E-Mail-Adresse angeben.';
    }
    if (mb_strlen($in['nachricht']) > 5000) {
        $err[] = 'Die Nachricht ist zu lang.';
    }
    if (empty($_POST['datenschutz'])) {
        $err[] = 'Bitte der Datenschutzerklärung zustimmen.';
    }
    foreach (glob(CACHE_DIR . '/form-*.cnt') ?: [] as $old) {
        if (filemtime($old) < time() - 7200) {
            @unlink($old);
        }
    }
    $ipFile = CACHE_DIR . '/form-' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . date('YmdH')) . '.cnt';
    $count = is_file($ipFile) ? (int) file_get_contents($ipFile) : 0;
    if ($count >= 5) {
        $err[] = 'Zu viele Anfragen – bitte später noch einmal versuchen oder anrufen.';
    }
    if ($err) {
        return [$err, $in];
    }

    $topics = form_topics();
    $topic = $topics[$in['thema']] ?? 'Allgemeine Anfrage';
    $body = "Neue Anfrage über die Website\n\n"
        . "Thema:        {$topic}\n"
        . "Unternehmen:  {$in['firma']}\n"
        . "Name:         {$in['name']}\n"
        . "E-Mail:       {$in['email']}\n"
        . "Telefon:      {$in['telefon']}\n"
        . "Teilnehmende: {$in['teilnehmer']}\n\n"
        . "Nachricht:\n{$in['nachricht']}\n";

    $rows = ['Thema' => $topic, 'Unternehmen' => $in['firma'], 'Name' => $in['name'], 'E-Mail' => $in['email'], 'Telefon' => $in['telefon'], 'Teilnehmende' => $in['teilnehmer']];
    $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#14161B;max-width:640px">'
        . '<div style="border-top:4px solid #E60005;padding:16px 0 8px"><b style="font-size:18px">Neue Anfrage über die Website</b></div><table cellpadding="6" style="border-collapse:collapse;width:100%">';
    foreach ($rows as $k => $v) {
        if ($v !== '') {
            $val = $k === 'E-Mail' ? '<a href="mailto:' . e($v) . '">' . e($v) . '</a>' : ($k === 'Telefon' ? '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', $v)) . '">' . e($v) . '</a>' : e($v));
            $html .= '<tr><td style="color:#6B7080;width:130px;border-bottom:1px solid #E3E5E9">' . $k . '</td><td style="border-bottom:1px solid #E3E5E9">' . $val . '</td></tr>';
        }
    }
    $html .= '</table><p style="margin:18px 0 6px;color:#6B7080">Nachricht</p><div style="white-space:pre-wrap;background:#F5F6F8;padding:12px;border-radius:6px">' . e($in['nachricht'] ?: '–') . '</div>'
        . '<p style="font-size:13px;color:#6B7080;margin-top:18px">„Antworten“ schreibt direkt an ' . e($in['email']) . '.</p></div>';
    $sent = send_mail(mail_target(), 'Website-Anfrage: ' . $topic . ' – ' . $in['name'], $body, $in['email'], $html) === '';
    @file_put_contents($ipFile, (string) ($count + 1));
    if (!$sent) {
        return [['Die Nachricht konnte leider nicht versendet werden. Bitte direkt an ' . site('email') . ' schreiben.'], $in];
    }
    redirect($returnPath . '?gesendet=1#formular');
    return [[], []];
}

function form_render(array $err, array $old, string $preset = ''): void
{
    $topics = form_topics();
    $sel = $old['thema'] ?? $preset;
    $pre = fn($k) => in_array($k, ['teilnehmer', 'nachricht', 'firma'], true) ? mb_substr(trim((string) ($_GET[$k] ?? '')), 0, 500) : '';
    $v = fn($k) => e($old[$k] ?? $pre($k));
    if (!empty($_GET['gesendet'])): ?>
<div class="notice notice--ok" role="status"><h3 class="h5">Vielen Dank!</h3><p>Die Anfrage ist angekommen. Wir melden uns schnellstmöglich.</p></div>
<?php return; endif; ?>
<form class="form" method="post" action="#formular" novalidate>
  <?php if ($err): ?><div class="notice notice--err" role="alert"><ul><?php foreach ($err as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php $t = time(); ?>
  <input type="hidden" name="t" value="<?= $t ?>">
  <input type="hidden" name="sig" value="<?= hash_hmac('sha256', 'form' . $t, app_secret()) ?>">
  <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="form__grid">
    <label class="field"><span>Name *</span><input name="name" required autocomplete="name" value="<?= $v('name') ?>"></label>
    <label class="field"><span>Unternehmen</span><input name="firma" autocomplete="organization" value="<?= $v('firma') ?>"></label>
    <label class="field"><span>E-Mail *</span><input type="email" name="email" required autocomplete="email" value="<?= $v('email') ?>"></label>
    <label class="field"><span>Telefon</span><input type="tel" name="telefon" autocomplete="tel" value="<?= $v('telefon') ?>"></label>
    <label class="field"><span>Thema</span>
      <select name="thema">
        <?php foreach ($topics as $k => $t): ?><option value="<?= e($k) ?>"<?= $sel === $k ? ' selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="field"><span>Teilnehmende (ca.)</span><input name="teilnehmer" inputmode="numeric" value="<?= $v('teilnehmer') ?>"></label>
    <label class="field field--full"><span>Nachricht</span><textarea name="nachricht" rows="5"><?= $v('nachricht') ?></textarea></label>
  </div>
  <label class="check"><input type="checkbox" name="datenschutz" value="1" required><span>Ich habe die <a href="<?= url('datenschutz') ?>">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben einverstanden. *</span></label>
  <button class="btn btn--red" type="submit">Anfrage senden <?= icon('arrow') ?></button>
</form>
<?php
}
