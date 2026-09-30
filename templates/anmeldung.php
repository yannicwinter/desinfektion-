<?php
/** /termine/{kurs}/anmeldung/{kid}: HiOrg-Anmeldeformular direkt in unsere Seite eingebettet */
$it = hiorg_find($current, $kid);
if (!$it || empty($it['bookable'])) {
    // Termin ausgebucht, vorbei oder unbekannt → zurück zur Terminliste
    redirect('termine/' . $current['slug'], 302);
}
$d = $it['date'];
$when = de_date($d, 'WWW, D. MMM YYYY');
if ($it['end']) {
    $when .= ' – ' . de_date($it['end'], 'WW, D. MMM');
}
header('X-Robots-Tag: noindex');

layout_start([
    'title' => 'Anmeldung: ' . $current['title'] . ' am ' . de_date($d, 'D. MMM YYYY') . ' | ' . site('name'),
    'description' => '',
    'path' => 'termine/' . $current['slug'],
    'active' => 'anmeldung',
    'noindex' => true,
]);
?>
<section class="signup">
  <div class="wrap">
    <nav class="crumbs" aria-label="Brotkrumen"><a href="<?= url('/') ?>">Start</a><span aria-hidden="true">/</span><a href="<?= url('termine') ?>">Kurstermine</a><span aria-hidden="true">/</span><a href="<?= url('termine/' . $current['slug']) ?>"><?= e($current['title']) ?></a><span aria-hidden="true">/</span><span>Anmeldung</span></nav>
    <h1 class="h2 signup__title">Anmeldung: <?= e($current['title']) ?></h1>

    <div class="signup__grid">
      <aside class="signup__side">
        <div class="box signup__card">
          <div class="signup__date">
            <time class="date__cal" datetime="<?= $d->format('Y-m-d') ?>"><span><?= de_date($d, 'MMM') ?></span><strong><?= $d->format('j') ?></strong></time>
            <div><strong><?= e($when) ?></strong><?php if ($it['time']): ?><span><?= e($it['time']) ?> Uhr</span><?php endif; ?></div>
          </div>
          <ul class="signup__facts">
            <?php if ($it['details']): ?><li><?= icon('pin') ?><span><?= e($it['details']) ?></span></li><?php endif; ?>
            <?php if ($it['free']): ?><li><?= icon('users') ?><span><?= e($it['free']) ?></span></li><?php endif; ?>
            <?php if ($it['price']): ?><li><?= icon('check') ?><span><?= e($it['price']) ?> · Betriebe: Abrechnung über BG möglich</span></li><?php endif; ?>
          </ul>
          <a class="link-arrow small" href="<?= url('termine/' . $current['slug']) ?>">Anderen Termin wählen <?= icon('arrow') ?></a>
        </div>
        <div class="box box--soft signup__help">
          <h2 class="h6">Fragen zur Anmeldung?</h2>
          <?php $mail = site(($current['category'] ?? '') === 'erste-hilfe' ? 'email_erste_hilfe' : 'email_brandschutz') ?: site('email'); ?>
          <a href="mailto:<?= e($mail) ?>"><?= icon('mail') ?><?= e($mail) ?></a>
          <a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a>
        </div>
      </aside>

      <div class="signup__main">
        <div class="signup__frame">
          <iframe src="<?= e($it['link']) ?>" title="Anmeldeformular <?= e($current['title']) ?>" referrerpolicy="strict-origin-when-cross-origin" data-signup-frame></iframe>
        </div>
        <p class="muted small signup__note">Die Anmeldung und ggf. Zahlung laufen über unser Buchungssystem HiOrg-Server. Funktioniert etwas nicht? <a href="<?= e($it['link']) ?>" target="_blank" rel="noopener">Formular in neuem Tab öffnen</a></p>
        <p class="muted small signup__mhelp">Fragen zur Anmeldung? <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
      </div>
    </div>
  </div>
</section>
<?php layout_end(); ?>
