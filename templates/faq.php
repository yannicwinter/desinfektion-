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
<section class="section section--tight">
  <div class="wrap layout-aside">
    <div class="layout-aside__main">
      <?php foreach ($groups as $g => $list): ?>
      <h2 class="group-title reveal"><?= e($g) ?></h2>
      <div class="acc acc--plain reveal">
        <?php foreach ($list as $f): ?>
        <details class="acc__item"><summary class="acc__sum"><span class="acc__title"><?= e($f['q']) ?></span><?= icon('chevron', 'i acc__chev') ?></summary><div class="acc__body"><div class="prose"><?= rich($f['a']) ?></div></div></details>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <aside class="layout-aside__side">
      <div class="box box--rose">
        <h2 class="h5">Deine Frage ist nicht dabei?</h2>
        <p>Ruf uns an oder schreib uns.</p>
        <ul class="contact-list">
          <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
          <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
        </ul>
      </div>
    </aside>
  </div>
</section>
<?php layout_end(); ?>
