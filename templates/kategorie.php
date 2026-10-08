<?php
/** Bereichsseite /erste-hilfe bzw. /brandschutz: Foto-Kopf, Terminsuche, Wegweiser, Einleitung, alle Kurse als Karten, „Gut zu wissen“. */
$P = fn($k) => page($category, $k);
$list = listed_courses($category);
$groups = [];
foreach ($list as $c) {
    $groups[$c['group'] ?: 'Kurse'][] = $c;
}
$isFire = $category === 'brandschutz';
$faqGroups = $isFire ? ['Brandschutzhelfer'] : ['Erste-Hilfe-Kurse', 'Buchung'];
$faq = array_slice(array_values(array_filter(content()['faq'] ?? [], fn($f) => in_array($f['group'] ?? '', $faqGroups, true))), 0, 5);
$mail = site($isFire ? 'email_brandschutz' : 'email_erste_hilfe') ?: site('email');

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => $category,
    'active' => $category,
    'breadcrumb' => [[$P('title'), $category]],
    'schema' => array_map(fn($c) => course_schema($c), $list),
]);
// Brandschutz nutzt das Foto der Startseite
page_head('', $P('title'), $P('lead'), [[$P('title'), $category]], '', $isFire ? 'hero' : $category, true);
$searchCategory = $category;
include __DIR__ . '/partials/suche.php';
?>

<?php if ($wege = lines($P('wege'))): ?>
<section class="section wege">
  <div class="wrap">
    <?= shead($P('wege_eyebrow') ?: 'Wegweiser', $P('wege_title'), $P('wege_lead')) ?>
    <div class="<?= count($wege) === 4 ? 'grid4' : 'grid3' ?>">
      <?php foreach ($wege as $w):
          [$t, $txt, $href, $slot] = array_map('trim', array_pad(explode('|', $w), 4, ''));
          $img = ($slot ? photo($slot, $t) : '') ?: photo($category, $t); ?>
      <?= topic_card($img, $t, $txt, 'Passende Kurse', url($href)) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($P('intro_title')): ?>
<section class="section section--alt">
  <div class="wrap intro">
    <div class="reveal">
      <p class="eyebrow"><?= e($P('intro_eyebrow')) ?></p>
      <h2 class="h2"><?= e($P('intro_title')) ?></h2>
      <?php foreach (paragraphs($P('intro_text')) as $par): ?><p class="intro__p"><?= e($par) ?></p><?php endforeach; ?>
    </div>
    <?php if ($il = lines($P('intro_list'))): ?>
    <div class="intro__box reveal">
      <h3><?= e($P('intro_list_title')) ?></h3>
      <ul class="checks checks--plain"><?php foreach ($il as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="wrap">
    <?= shead('Alle Kurse', $P('kurse_title') ?: $P('title') . ' im Überblick', $P('kurse_lead') ?: 'Preis, Dauer und nächster freier Termin auf einen Blick.') ?>
    <?php foreach ($groups as $g => $items): ?>
    <h3 class="group-title" id="<?= e(slugify($g)) ?>"><?= e($g) ?></h3>
    <div class="grid3">
      <?php foreach ($items as $c): ?><?= course_card($c) ?><?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$isFire): ?><?= compare_table($P('vergleich'), $P('vergleich_hinweis')) ?><?php endif; ?>
  </div>
</section>

<?php if ($know = lines($P('wissen'))): ?>
<section class="section section--alt">
  <div class="wrap">
    <?= shead('Auf einen Blick', $P('wissen_title') ?: 'Gut zu wissen', $P('wissen_lead')) ?>
    <div class="know">
      <?php foreach ($know as $k): [$kt, $kx, $ki] = array_map('trim', array_pad(explode('|', $k), 3, '')); ?>
      <div class="know__item reveal"><span class="know__ic"><?= icon($ki ?: 'info') ?></span><h3><?= e($kt) ?></h3><p><?= e($kx) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?= band($P('band_title'), $P('band_text'), 'Mehr für Unternehmen', url('arbeitssicherheit')) ?>

<?php if ($faq): ?>
<section class="section">
  <div class="wrap faqw">
    <div class="reveal">
      <p class="eyebrow">FAQ</p>
      <h2 class="h2"><?= e($P('faq_title') ?: 'Häufige Fragen') ?></h2>
      <div class="helpbox"><b>Fragen zur Anmeldung?</b><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a><br><a href="<?= url('faq') ?>">Alle Fragen ansehen</a></div>
    </div>
    <div class="reveal"><?= faq_list($faq) ?></div>
  </div>
</section>
<?php endif; ?>

<?= advice_box(contact_person($P('berater'))) ?>
<?php layout_end(); ?>
