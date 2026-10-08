<?php
$P = fn($k) => page('home', $k);
$featured = array_values(array_filter(listed_courses(), fn($c) => !empty($c['featured'])));
$faq = array_values(array_filter(content()['faq'] ?? [], fn($f) => !empty($f['start'])));
if (!$faq) {
    $faq = array_slice(content()['faq'] ?? [], 0, 5);
}
$next = array_values(array_filter(hiorg_dates_all(bookable_courses()), fn($it) => $it['status'] !== 'full' && !empty($it['kid'])));

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => '/',
    'active' => '',
    'schema' => [[
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => site('name'),
        'url' => abs_url('/'),
        'inLanguage' => 'de',
    ]],
]);
?>
<section class="hero hero--home hero--photo hero--overlap">
  <?= hero_media('hero', 'Erste-Hilfe- und Brandschutz-Ausbildung beim DRK in Verden') ?>
  <div class="hero__in">
    <?php if ($P('hero_eyebrow')): ?><span class="badge"><?= icon('shield') ?><?= e($P('hero_eyebrow')) ?></span><?php endif; ?>
    <h1><?= e($P('hero_title')) ?></h1>
    <p class="hero__lead"><?= e($P('hero_lead')) ?></p>
  </div>
</section>

<?php include __DIR__ . '/partials/suche.php'; ?>

<section class="section">
  <div class="wrap">
    <?= shead($P('angebot_eyebrow'), $P('angebot_title'), $P('angebot_lead')) ?>
    <div class="grid3">
      <?= topic_card(photo('erste-hilfe', 'Erste Hilfe') ?: illus_page('erste-hilfe'), 'Erste Hilfe', $P('angebot_eh'), 'Erste-Hilfe-Kurse', url('erste-hilfe')) ?>
      <?= topic_card(photo('brandschutz', 'Brandschutz') ?: illus_page('brandschutz'), 'Brandschutz', $P('angebot_bs'), 'Brandschutz-Angebote', url('brandschutz')) ?>
      <?= topic_card(photo('unternehmen', 'Für Unternehmen') ?: illus_page('unternehmen'), 'Für Unternehmen', $P('angebot_as'), 'Leistungen für Betriebe', url('arbeitssicherheit')) ?>
    </div>
  </div>
</section>

<?php if ($next): ?>
<section class="section section--alt">
  <div class="wrap">
    <?= shead($P('termine_eyebrow'), $P('termine_title'), $P('termine_lead'), 'Alle ' . count($next) . ' Termine', url('termine')) ?>
    <ul class="dates reveal">
      <?= render_dates($next, ['limit' => 4, 'show_course' => true]) ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?= band($P('band_title'), $P('band_text'), 'Angebot für Unternehmen', url('arbeitssicherheit')) ?>

<?php if ($featured): ?>
<section class="section">
  <div class="wrap">
    <?= shead('Kurse', $P('kurse_title'), $P('kurse_lead'), 'Alle Kurse', url('erste-hilfe')) ?>
    <div class="grid3">
      <?php foreach (array_slice($featured, 0, 3) as $c): ?><?= course_card($c) ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($faq): ?>
<section class="section section--alt">
  <div class="wrap faqw">
    <div class="reveal">
      <p class="eyebrow">FAQ</p>
      <h2 class="h2">Häufige Fragen</h2>
      <p class="lead">Kurz und klar beantwortet.</p>
      <div class="helpbox"><b>Deine Frage ist nicht dabei?</b>Ruf uns an: <a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a> · <a href="<?= url('faq') ?>">Alle Fragen</a></div>
    </div>
    <div class="reveal"><?= faq_list($faq) ?></div>
  </div>
</section>
<?php endif; ?>

<?php $insta = instagram_posts(6); if ($insta || site('instagram')): ?>
<section class="section">
  <div class="wrap">
    <?= shead('Instagram', $P('insta_title'), $P('insta_lead'), '@drk_kreisverband_verden', site('instagram')) ?>
    <ul class="insta reveal">
      <?php if ($insta): ?>
      <?php foreach ($insta as $post): ?>
      <li><a href="<?= e($post['link']) ?>" target="_blank" rel="noopener" title="<?= e($post['caption']) ?>"><img src="<?= e($post['img']) ?>" alt="<?= e($post['caption'] ?: 'Instagram-Beitrag DRK Verden') ?>" loading="lazy" decoding="async" width="600" height="600"></a></li>
      <?php endforeach; ?>
      <?php else: ?>
      <?php foreach (['Erste Hilfe', 'Brandschutz', 'Unser Team', 'Kurse', 'Arbeitssicherheit', 'Ehrenamt'] as $label): ?>
      <li><a class="insta__ph" href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener"><?= icon('instagram') ?><span><?= e($label) ?></span></a></li>
      <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?= advice_box(contact_person($P('berater'))) ?>
<?php layout_end(); ?>
