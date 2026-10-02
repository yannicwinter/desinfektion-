<?php
/** Bereichsseite /erste-hilfe bzw. /brandschutz: Foto-Kopf, Terminsuche, Wegweiser, alle Kurse als Karten. */
$P = fn($k) => page($category, $k);
$list = courses($category);
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
page_head('', $P('title'), $P('lead'), [[$P('title'), $category]], '', $category, true);
$searchCategory = $category;
include __DIR__ . '/partials/suche.php';
?>

<?php if ($wege = lines($P('wege'))): ?>
<section class="section wege">
  <div class="wrap">
    <?= shead($P('wege_eyebrow') ?: 'Wegweiser', $P('wege_title'), $P('wege_lead')) ?>
    <div class="grid3">
      <?php foreach ($wege as $w):
          [$t, $txt, $href, $slot] = array_map('trim', array_pad(explode('|', $w), 4, ''));
          $img = ($slot ? photo($slot, $t) : '') ?: photo($category, $t); ?>
      <?= topic_card($img, $t, $txt, 'Passende Kurse', url($href)) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--alt">
  <div class="wrap">
    <?= shead('Alle Kurse', $P('kurse_title') ?: $P('title') . ' im Überblick', $P('kurse_lead') ?: 'Preis, Dauer und nächster freier Termin auf einen Blick.') ?>
    <?php foreach ($groups as $g => $items): ?>
    <h3 class="group-title" id="<?= e(slugify($g)) ?>"><?= e($g) ?></h3>
    <div class="grid3">
      <?php foreach ($items as $c): ?><?= course_card($c) ?><?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$isFire && ($cmp = compare_table($P('vergleich'), true))): ?>
    <details class="cmp reveal">
      <summary><?= icon('info') ?> Welcher Kurs passt zu mir? Führerschein · Selbstzahler · BG<?= icon('plus', 'i acc__chev') ?></summary>
      <?= $cmp ?>
    </details>
    <?php endif; ?>
  </div>
</section>

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
