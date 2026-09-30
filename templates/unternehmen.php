<?php
require_once __DIR__ . '/../inc/form.php';
[$err, $old] = form_handle('arbeitssicherheit');
$P = fn($k) => page('unternehmen', $k);
$services = [
    ['users', 'Ersthelfer ausbilden', 'Aus- und Fortbildung nach DGUV – Abrechnung über die BG möglich.', 'erste-hilfe#erste-hilfe-ausbildung'],
    ['flame', 'Brandschutzhelfer', 'Pflicht für mind. 5 % der Belegschaft – mit Löschübung.', 'brandschutz#brandschutzhelfer'],
    ['shield', 'Vorbeugender Brandschutz', 'Brandschutzordnung, Rettungspläne, externer Brandschutzbeauftragter.', 'brandschutz#brandschutzordnung-rettungsplaene'],
];

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => 'arbeitssicherheit',
    'active' => 'arbeitssicherheit',
    'breadcrumb' => [['Arbeitssicherheit', 'arbeitssicherheit']],
    'schema' => [[
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $P('asi_title'),
        'serviceType' => 'Arbeitssicherheit',
        'description' => $P('asi_text'),
        'provider' => ['@id' => abs_url('/') . '#org'],
        'areaServed' => 'Landkreis Verden',
    ]],
]);
page_head($P('eyebrow'), $P('title'), $P('lead'), [['Arbeitssicherheit', 'arbeitssicherheit']],
    '<div class="btn-row"><a class="btn btn--red" href="#formular">Unverbindlich anfragen</a><a class="btn btn--ghost" href="tel:' . e(site('phone_link')) . '">' . icon('phone') . ' ' . e(site('phone')) . '</a></div>');
?>
<section class="section section--tight">
  <div class="wrap cards cards--3">
    <?php foreach ($services as [$ic, $t, $d, $href]): ?>
    <article class="card reveal">
      <span class="card__icon"><?= icon($ic) ?></span>
      <h2 class="h4"><a class="card__link" href="<?= url($href) ?>"><?= e($t) ?></a></h2>
      <p><?= e($d) ?></p>
      <div class="card__foot"><span></span><span class="card__cta">Mehr <?= icon('arrow') ?></span></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section--soft">
  <div class="wrap split">
    <div class="stack reveal">
      <span class="eyebrow">Arbeitssicherheit</span>
      <h2 class="h2"><?= e($P('asi_title')) ?></h2>
      <p class="lead"><?= e($P('asi_text')) ?></p>
      <ul class="checks checks--2"><?php foreach (lines($P('asi_list')) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
      <?php if ($ben = lines($P('asi_benefits'))): ?>
      <div class="needs"><strong>Der Nutzen:</strong><?php foreach ($ben as $b): ?><span class="pill pill--red"><?= e($b) ?></span><?php endforeach; ?></div>
      <?php endif; ?>
      <div class="btn-row"><a class="btn btn--dark" href="#formular">Kostenlose Erstberatung</a></div>
    </div>
    <div class="split__media reveal"><?= slot_image('unternehmen') ? image_slot('unternehmen', 'Sicherheitsfachkraft bei einer Betriebsbegehung', 'building') : illus_page('unternehmen') ?></div>
  </div>
</section>

<section class="section" id="inhouse">
  <div class="wrap">
    <div class="section-head reveal"><div><span class="eyebrow">Inhouse</span><h2 class="h2"><?= e($P('inhouse_title')) ?></h2></div></div>
    <ol class="steps">
      <?php foreach (pairs($P('inhouse_steps'), '|') as $i => [$t, $d]): ?>
      <li class="step reveal"><span class="step__n"><?= $i + 1 ?></span><h3 class="h5"><?= e($t) ?></h3><p><?= e($d) ?></p></li>
      <?php endforeach; ?>
    </ol>
    <div class="needs reveal">
      <strong>Voraussetzungen:</strong>
      <?php foreach (lines($P('inhouse_needs')) as $n): ?><span class="pill"><?= icon('check') ?><?= e($n) ?></span><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--soft" id="formular">
  <div class="wrap form-wrap">
    <div class="stack">
      <span class="eyebrow">Anfrage</span>
      <h2 class="h2"><?= e($P('form_title')) ?></h2>
      <p class="lead"><?= e($P('form_text')) ?></p>
      <ul class="contact-list">
        <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
        <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
      </ul>
    </div>
    <div class="panel"><?php form_render($err, $old, (string) ($_GET['thema'] ?? 'erste-hilfe-ausbildung')); ?></div>
  </div>
</section>
<?php layout_end(); ?>
