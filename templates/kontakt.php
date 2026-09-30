<?php
require_once __DIR__ . '/../inc/form.php';
[$err, $old] = form_handle('kontakt');
$P = fn($k) => page('kontakt', $k);
layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => 'kontakt',
    'active' => 'kontakt',
    'breadcrumb' => [['Kontakt', 'kontakt']],
]);
page_head($P('eyebrow'), $P('title'), $P('lead'), [['Kontakt', 'kontakt']]);
$map = 'https://www.openstreetmap.org/search?query=' . rawurlencode(site('street') . ', ' . site('zip') . ' ' . site('city'));
?>
<section class="section section--tight" id="formular">
  <div class="wrap form-wrap">
    <div class="stack">
      <div class="box">
        <h2 class="h5">Zentrale</h2>
        <ul class="contact-list">
          <li><a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a></li>
          <li><a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a></li>
          <li><a href="<?= e($map) ?>" target="_blank" rel="noopener"><?= icon('pin') ?><?= e(site('street')) ?>, <?= e(site('zip')) ?> <?= e(site('city')) ?></a></li>
        </ul>
      </div>
      <div class="box">
        <h2 class="h5">Kursorte</h2>
        <ul class="checks checks--sm"><?php foreach (lines($P('orte')) as $o): ?><li><?= icon('pin') ?><?= e($o) ?></li><?php endforeach; ?></ul>
      </div>
    </div>
    <div class="panel"><h2 class="h4">Schreiben Sie uns</h2><?php form_render($err, $old, (string) ($_GET['thema'] ?? 'sonstiges')); ?></div>
  </div>
</section>

<section class="section section--soft">
  <div class="wrap">
    <div class="section-head"><div><span class="eyebrow">Ansprechpersonen</span><h2 class="h2">Direkt zur richtigen Person</h2></div></div>
    <div class="people">
      <?php foreach (content()['contacts'] ?? [] as $p):
          $ini = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', $p['name']), 0, 2))); ?>
      <div class="person reveal">
        <span class="person__ini"><?= e($ini) ?></span>
        <div><span class="person__role"><?= e($p['role']) ?></span><strong><?= e($p['name']) ?></strong><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php layout_end(); ?>
