<?php
require_once __DIR__ . '/../inc/form.php';
[$err, $old] = form_handle('arbeitssicherheit');
$P = fn($k) => page('unternehmen', $k);
// Angebote aus den Kursdaten (Titel/Teaser im Admin gepflegt)
$pick = function (array $slugs): array {
    $out = [];
    foreach ($slugs as $slug => $label) {
        if ($c = course($slug)) {
            $out[] = ['title' => $label ?: $c['title'], 'teaser' => $c['teaser'], 'url' => course_url($c), 'c' => $c];
        }
    }
    return $out;
};
$ausbildung = $pick(['erste-hilfe-ausbildung' => 'Ersthelfer im Betrieb', 'erste-hilfe-fortbildung' => 'Ersthelfer-Fortbildung', 'brandschutzhelfer' => '', 'feuerloeschertraining' => '', 'evakuierungsuebung' => '']);
$dienst = $pick(['brandschutzordnung-rettungsplaene' => '', 'brandschutzbeauftragter' => '']);
$dienst[] = ['title' => page('unternehmen', 'asi_title'), 'teaser' => 'Wir übernehmen Arbeitssicherheitspflichten – Betreuung nach DGUV Vorschrift 2.', 'url' => '#fachkraft', 'c' => ['slug' => 'brandschutzbeauftragter', 'category' => 'brandschutz']];

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
page_head('', $P('title'), $P('lead'), [['Arbeitssicherheit', 'arbeitssicherheit']],
    '<div class="btn-row"><a class="btn btn--red" href="#formular">Unverbindlich anfragen</a><a class="btn btn--ghost" href="tel:' . e(site('phone_link')) . '">' . icon('phone') . ' ' . e(site('phone')) . '</a></div>');
?>
<section class="section section--tight">
  <div class="wrap apage">
    <div class="apage__cols">
      <?php foreach ([[$P('ausbildung_title'), $ausbildung], [$P('dienst_title'), $dienst]] as [$head, $list]): ?>
      <div class="apage__col reveal">
        <h2 class="group-title"><?= e($head) ?></h2>
        <ul class="alist">
          <?php foreach ($list as $it): ?>
          <li><a href="<?= e(str_starts_with($it['url'], '#') ? $it['url'] : $it['url']) ?>">
            <span class="acc__ico" aria-hidden="true"><?= illus_course($it['c']) ?></span>
            <span class="alist__txt"><strong><?= e($it['title']) ?></strong><span><?= e($it['teaser']) ?></span></span>
            <?= icon('arrow', 'i alist__go') ?>
          </a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($kosten = pairs($P('kosten'))): ?>
    <ul class="apage__kosten reveal"><?php foreach ($kosten as [$k, $v]): ?><li><?= icon('check') ?><span><strong><?= e($k) ?>:</strong> <?= e($v) ?></span></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
</section>

<section class="section section--soft" id="fachkraft">
  <div class="wrap apage">
    <h2 class="h2 reveal"><?= e($P('asi_title')) ?></h2>
    <p class="lead reveal"><?= e($P('asi_text')) ?></p>
    <div class="apage__three reveal">
      <?php foreach ([['Leistungen', 'asi_list'], ['Nutzen', 'asi_benefits'], ['Angebot', 'asi_offer']] as [$h, $k]): ?>
      <div><h3 class="h6"><?= $h ?></h3><ul class="checks checks--sm"><?php foreach (lines($P($k)) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" id="inhouse">
  <div class="wrap apage">
    <h2 class="h2 reveal"><?= e($P('inhouse_title')) ?></h2>
    <ol class="asteps reveal">
      <?php foreach (pairs($P('inhouse_steps'), '|') as $i => [$t, $d]): ?>
      <li><span class="asteps__n"><?= $i + 1 ?></span><strong><?= e($t) ?></strong><span><?= e($d) ?></span></li>
      <?php endforeach; ?>
    </ol>
    <p class="apage__needs reveal"><strong>Voraussetzungen:</strong> <?= e(implode(' · ', lines($P('inhouse_needs')))) ?></p>
  </div>
</section>

<section class="section section--soft" id="formular">
  <div class="wrap apage">
    <div class="form-wrap form-wrap--narrow">
      <div class="stack">
        <h2 class="h2"><?= e($P('form_title')) ?></h2>
        <p class="lead"><?= e($P('form_text')) ?></p>
        <ul class="contact-list">
          <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
          <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
        </ul>
      </div>
      <div class="panel"><?php form_render($err, $old, (string) ($_GET['thema'] ?? 'erste-hilfe-ausbildung')); ?></div>
    </div>
  </div>
</section>
<?php layout_end(); ?>
