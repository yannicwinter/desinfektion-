<?php
$w = maintenance();
$until = $w['bis'] !== '' ? strtotime($w['bis']) : false;
http_response_code(503);
header('Retry-After: ' . ($until && $until > time() ? max(300, $until - time()) : 3600));
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
$title = $w['titel'] ?: 'Wir sind gleich wieder da';
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($title) ?> · <?= e(site('name')) ?></title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="maint-page">
<main class="maint">
  <div class="maint__box">
    <img class="maint__logo" src="<?= asset('img/logo-drk-mittelweser.png') ?>" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V.">
    <h1><?= e($title) ?></h1>
    <?php foreach (paragraphs($w['text'] ?: 'Wir arbeiten gerade an der Website. Kurse und Termine kannst du in der Zwischenzeit telefonisch oder per E-Mail anfragen.') as $par): ?><p><?= e($par) ?></p><?php endforeach; ?>
    <?php if ($until): ?><p class="maint__when">Voraussichtlich wieder erreichbar:<br><?= e(de_date((new DateTimeImmutable())->setTimestamp($until), 'WWW, D. MMM YYYY') . ', ' . date('H:i', $until)) ?> Uhr</p><?php endif; ?>
    <div class="maint__contact">
      <a class="btn btn--red" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a>
      <a class="btn btn--ghost" href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?> <?= e(site('email')) ?></a>
    </div>
  </div>
  <p class="maint__foot"><?= e(site('org')) ?> · <?= e(site('street')) ?> · <?= e(site('zip')) ?> <?= e(site('city')) ?> · <a href="<?= url('admin') ?>">Admin</a></p>
</main>
</body>
</html>
