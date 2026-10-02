<?php
$P = fn($k) => page('faq', $k);
$items = content()['faq'] ?? [];
$groups = [];
foreach ($items as $f) {
    $groups[$f['group'] ?: 'Allgemein'][] = $f;
}
layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => 'faq',
    'active' => 'faq',
    'breadcrumb' => [['FAQ', 'faq']],
    'schema' => [[
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn($f) => [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags(rich($f['a']))],
        ], $items),
    ]],
]);
page_head($P('eyebrow'), $P('title'), $P('lead'), [['FAQ', 'faq']]);
?>
<section class="section">
  <div class="wrap faqw">
    <aside class="reveal">
      <p class="eyebrow">Kontakt</p>
      <h2 class="h2">Deine Frage ist nicht dabei?</h2>
      <p class="lead">Ruf uns an oder schreib uns – wir helfen gern weiter.</p>
      <ul class="contact-list">
        <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
        <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
      </ul>
    </aside>
    <div>
      <?php foreach ($groups as $g => $list): ?>
      <h2 class="group-title reveal"><?= e($g) ?></h2>
      <div class="reveal"><?= faq_list($list) ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php layout_end(); ?>
