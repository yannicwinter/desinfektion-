<?php
$it = hiorg_find($current, $kid);
if (!$it || empty($it['bookable'])) {
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
<section class="phead">
  <div class="wrap">
    <?= crumbs_html([['Termine', 'termine'], [$current['title'], 'termine/' . $current['slug']], ['Anmeldung', 'termine/' . $current['slug']]]) ?>
    <h1 class="h1">Anmeldung: <?= e($current['title']) ?></h1>
  </div>
</section>
<section class="section section--tight signup">
  <div class="wrap">
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
            <?php if ($it['price']): ?><li><?= icon('check') ?><span><?= e($it['price']) ?><?= course_bg($current) ? ' · Betriebe: Abrechnung über BG möglich' : '' ?></span></li><?php endif; ?>
          </ul>
          <?= place_notice([$it]) ?>
          <?php if (($bg = doc_url('bg-formular')) && course_bg($current)): ?><p class="small docs-line"><?= icon('info') ?> <span>Über den Arbeitgeber? <a href="<?= e($bg) ?>" target="_blank" rel="noopener">Abrechnungsformular (PDF)</a> ausgefüllt zum Kurs mitbringen.<?php if ($ex = site('bg_ausnahme')): ?> <?= inline($ex) ?><?php endif; ?></span></p><?php endif; ?>
          <a class="alink small" href="<?= url('termine/' . $current['slug']) ?>">Anderen Termin wählen <?= icon('arrow') ?></a>
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
        <p class="muted small signup__note">Die Anmeldung und ggf. Zahlung laufen über unser Buchungssystem HiOrg-Server.<?php if ($agb = doc_url('agb')): ?> Es gelten unsere <a href="<?= e($agb) ?>" target="_blank" rel="noopener">AGB</a>.<?php endif; ?> Funktioniert etwas nicht? <a href="<?= e($it['link']) ?>" target="_blank" rel="noopener">Formular in neuem Tab öffnen</a></p>
        <p class="muted small signup__mhelp">Fragen zur Anmeldung? <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
      </div>
    </div>
  </div>
</section>
<?php layout_end(); ?>
