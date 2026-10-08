<?php
/** /arbeitssicherheit – Für Unternehmen: Pflichten, Leistungen, Fachkraft, Schulung im Betrieb, Anfrage. */
require_once __DIR__ . '/../inc/form.php';
[$err, $old] = form_handle('arbeitssicherheit');
$P = fn($k) => page('unternehmen', $k);

// Leistungen aus den Kursdaten (Titel/Teaser im Admin gepflegt)
$cards = [];
foreach (['erste-hilfe-im-betrieb' => '', 'aed-reanimationstraining' => '', 'fresh-up-arztpraxen' => 'Fresh Up für Arztpraxen', 'brandschutzhelfer' => '', 'feuerloeschertraining' => '', 'brandschutzordnung-rettungsplaene' => ''] as $slug => $label) {
    if ($c = course($slug)) {
        $img = photo('kurs-' . $slug, $c['title']) ?: illus_course($c);
        $cards[] = topic_card($img, $label ?: $c['title'], $c['teaser'], 'Mehr erfahren', course_url($c));
    }
}
$cards[] = topic_card(photo('fachkraft', $P('asi_title')) ?: illus_safety(), $P('asi_title'), 'Betreuung nach DGUV Vorschrift 2 – wir übernehmen Arbeitssicherheitspflichten.', 'Mehr erfahren', '#fachkraft');
$need = [];
foreach (lines($P('pflichten')) as $l) {
    $need[] = array_map('trim', array_pad(explode('|', $l), 4, ''));
}
$person = contact_person($P('berater'));
$pimg = person_photo($person);

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => 'arbeitssicherheit',
    'active' => 'arbeitssicherheit',
    'breadcrumb' => [['Für Unternehmen', 'arbeitssicherheit']],
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
page_head('', $P('title'), $P('lead'), [['Für Unternehmen', 'arbeitssicherheit']],
    '<a class="btn btn--red" href="#formular">Unverbindlich anfragen ' . icon('arrow') . '</a><a class="btn btn--line" href="tel:' . e(site('phone_link')) . '">' . icon('phone') . e(site('phone')) . '</a>',
    'hero');
?>

<?php if ($need): ?>
<section class="section">
  <div class="wrap">
    <?= shead('Pflichten im Überblick', $P('pflichten_title'), $P('pflichten_lead')) ?>
    <div class="need">
      <?php foreach ($need as [$num, $t, $txt, $slug]): $nc = $slug ? course($slug) : null; ?>
      <div class="need__item reveal">
        <span class="need__num"><?= e($num) ?></span>
        <h3><?= e($t) ?></h3>
        <p><?= e($txt) ?></p>
        <?php if ($nc): ?><a class="alink" href="<?= course_url($nc) ?>">Zur Ausbildung <?= icon('arrow') ?></a><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--alt">
  <div class="wrap">
    <?= shead('Leistungen', $P('leistungen_title'), $P('leistungen_lead')) ?>
    <div class="grid4"><?= implode('', $cards) ?></div>
    <?php if ($kosten = pairs($P('kosten'))): ?>
    <ul class="kosten reveal"><?php foreach ($kosten as [$k, $v]): ?><li><?= icon('info') ?><span><strong><?= e($k) ?>:</strong> <?= e($v) ?></span></li><?php endforeach; ?>
</ul>
    <?php endif; ?>
    <?= bg_box('reveal') ?>
  </div>
</section>

<section class="section" id="fachkraft">
  <div class="wrap">
    <div class="three" style="margin-top:0">
      <?php foreach ([['Nutzen', 'asi_benefits'], ['Angebot', 'asi_offer']] as [$h, $k]): if (!lines($P($k))) continue; ?>
      <div class="reveal"><h3><?= $h ?></h3><ul class="checks"><?php foreach (lines($P($k)) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul></div>
      <?php endforeach; ?>
      <div class="reveal"><h3>Kontakt</h3><p>Fragen zur Betreuung oder ein individuelles Angebot? Wir melden uns schnellstmöglich.</p>
        <ul class="contact-list"><li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li><li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li></ul></div>
    </div>
  </div>
</section>

<section class="section section--alt" id="inhouse">
  <div class="wrap">
    <?= shead('Ablauf', $P('inhouse_title'), $P('inhouse_text')) ?>
    <div class="steps">
      <?php foreach (pairs($P('inhouse_steps'), '|') as $i => [$t, $d]): ?>
      <div class="step reveal"><span class="step__n"><?= $i + 1 ?></span><b><?= e($t) ?></b><span><?= e($d) ?></span></div>
      <?php endforeach; ?>
    </div>
    <?php if ($needs = lines($P('inhouse_needs'))): ?><p class="needs reveal"><strong>Voraussetzungen:</strong> <?= e(implode(' · ', $needs)) ?></p><?php endif; ?>
  </div>
</section>

<?= band($P('band_title'), $P('band_text'), 'Jetzt anfragen', '#formular') ?>

<section class="section" id="formular">
  <div class="wrap fw">
    <div class="reveal">
      <p class="eyebrow">Kontakt</p>
      <h2 class="h2"><?= e($P('form_title')) ?></h2>
      <p class="lead"><?= e($P('form_text')) ?></p>
      <?php if ($person): ?>
      <div class="person-mini"><?php if ($pimg): ?><img src="<?= e($pimg) ?>" alt="<?= e($person['name']) ?>" loading="lazy" width="64" height="64"><?php endif; ?>
        <div><b><?= e($person['name']) ?></b><span><?= e($person['role']) ?></span><a href="mailto:<?= e($person['email'] ?: site('email')) ?>"><?= e($person['email'] ?: site('email')) ?></a></div></div>
      <?php endif; ?>
    </div>
    <div class="panel reveal"><?php form_render($err, $old, (string) ($_GET['thema'] ?? 'erste-hilfe-im-betrieb')); ?></div>
  </div>
</section>
<?php layout_end(); ?>
