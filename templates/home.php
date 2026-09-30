<?php
$P = fn($k) => page('home', $k);
$featured = array_values(array_filter(courses(), fn($c) => !empty($c['featured'])));
$faq = array_values(array_filter(content()['faq'] ?? [], fn($f) => !empty($f['start'])));
if (!$faq) {
    $faq = array_slice(content()['faq'] ?? [], 0, 4);
}

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
<section class="hero hero--search">
  <div class="wrap hs">
    <h1 class="display reveal"><?= e($P('hero_title')) ?></h1>
    <p class="lead reveal"><?= e($P('hero_lead')) ?></p>
    <?php include __DIR__ . '/partials/suche.php'; ?>
    <p class="hs__help reveal">Unsicher, welcher Kurs passt? <a href="#kursfinder" data-kf-open>Kursfinder fragen</a></p>
  </div>
</section>

<section class="themes">
  <div class="wrap themes__grid">
    <a class="theme reveal" href="<?= url('erste-hilfe') ?>">
      <span class="theme__art theme__art--red"><?= illus_heart('theme__heart', 'rgba(255,255,255,.2)', '#fff') ?></span>
      <span class="theme__txt"><strong>Erste Hilfe</strong><span>Führerschein · Betrieb · Familie</span></span>
      <?= icon('arrow', 'i theme__go') ?>
    </a>
    <a class="theme reveal" href="<?= url('brandschutz') ?>">
      <span class="theme__art theme__art--rose"><?= illus_extinguisher('theme__ext') ?></span>
      <span class="theme__txt"><strong>Brandschutz</strong><span>Helfer · Übungen · Beratung</span></span>
      <?= icon('arrow', 'i theme__go') ?>
    </a>
    <a class="theme reveal" href="<?= url('arbeitssicherheit') ?>">
      <span class="theme__art theme__art--soft"><svg class="theme__shield" viewBox="0 0 120 140" aria-hidden="true"><path d="M60 4 112 22v44c0 36-24 58-52 70C32 124 8 102 8 66V22z" fill="#E60005"/><path d="M36 70l16 16 32-34" fill="none" stroke="#fff" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
      <span class="theme__txt"><strong>Arbeitssicherheit</strong><span>Fachkraft · Beratung · Dokumente</span></span>
      <?= icon('arrow', 'i theme__go') ?>
    </a>
  </div>
</section>

<section class="usps">
  <div class="wrap usps__grid">
    <?php foreach ([['shield', 1], ['hand', 2], ['pin', 3]] as [$ic, $n]): ?>
    <div class="usp reveal">
      <span class="usp__icon"><?= icon($ic) ?></span>
      <div><h2 class="h5"><?= e($P("usp{$n}_title")) ?></h2><p><?= e($P("usp{$n}_text")) ?></p></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <div><h2 class="h2"><?= e($P('kurse_title')) ?></h2><p class="lead"><?= e($P('kurse_lead')) ?></p></div>
      <a class="link-arrow" href="<?= url('erste-hilfe') ?>">Alle Kurse <?= icon('arrow') ?></a>
    </div>
    <div class="cards">
      <?php foreach ($featured as $c): ?>
      <article class="card card--img reveal">
        <?= illus_course($c) ?>
        <span class="card__tag"><?= $c['category'] === 'brandschutz' ? 'Brandschutz' : e($c['group']) ?></span>
        <h3 class="h4"><a href="<?= course_url($c) ?>" class="card__link"><?= e($c['title']) ?></a></h3>
        <p><?= e($c['teaser']) ?></p>
        <div class="card__foot">
          <span class="card__price"><?= e($c['price'] ?: '') ?></span>
          <?php if (!empty($c['hiorg_id'])): ?>
          <a class="card__cta" href="<?= url('termine/' . $c['slug']) ?>">Termine <?= icon('arrow') ?></a>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--soft">
  <div class="wrap split">
    <div class="split__media reveal">
      <?php if ($img = slot_image('home')): ?>
      <?= image_slot('home', 'Arbeitssicherheit beim DRK', 'building') ?>
      <div class="stat"><strong><?= e($P('stat_value')) ?></strong><span><?= e($P('stat_text')) ?></span></div>
      <?php else: ?>
      <?= illus_safety() ?>
      <?php endif; ?>
    </div>
    <div class="stack reveal">
      <span class="eyebrow"><?= e($P('firma_eyebrow')) ?></span>
      <h2 class="h2"><?= e($P('firma_title')) ?></h2>
      <p class="lead"><?= e($P('firma_text')) ?></p>
      <ul class="checks">
        <?php foreach (lines($P('firma_list')) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?>
      </ul>
      <div class="btn-row"><a class="btn btn--dark" href="<?= url('arbeitssicherheit') ?>">Leistungen für Unternehmen <?= icon('arrow') ?></a></div>
    </div>
  </div>
</section>

<?php if ($faq): ?>
<section class="section">
  <div class="wrap faq-teaser">
    <div class="reveal">
      <span class="eyebrow">Häufige Fragen</span>
      <h2 class="h2">Kurz gefragt.</h2>
      <a class="link-arrow" href="<?= url('faq') ?>">Alle Fragen <?= icon('arrow') ?></a>
    </div>
    <div class="acc acc--plain reveal">
      <?php foreach ($faq as $f): ?>
      <details class="acc__item"><summary class="acc__sum"><span class="acc__title"><?= e($f['q']) ?></span><?= icon('chevron', 'i acc__chev') ?></summary><div class="acc__body"><div class="prose"><?= rich($f['a']) ?></div></div></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $insta = instagram_posts(6); if ($insta || site('instagram')): ?>
<section class="section section--tight insta">
  <div class="wrap">
    <div class="section-head reveal">
      <div><span class="eyebrow">Instagram</span><h2 class="h2"><?= e($P('insta_title')) ?></h2></div>
      <a class="link-arrow" href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener">@drk_kreisverband_verden folgen <?= icon('arrow') ?></a>
    </div>
    <?php if (!$insta): ?>
    <ul class="insta__row insta__row--ph">
      <?php foreach ([['heart', 'Erste Hilfe'], ['flame', 'Brandschutz'], ['users', 'Unser Team'], ['calendar', 'Kurse'], ['shield', 'Arbeitssicherheit'], ['hand', 'Ehrenamt']] as $i => [$ic, $label]): ?>
      <li><a href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener" class="insta__ph insta__ph--<?= $i % 3 ?>"><?= icon($ic) ?><span><?= e($label) ?></span></a></li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <ul class="insta__row">
      <?php foreach ($insta as $post): ?>
      <li><a href="<?= e($post['link']) ?>" target="_blank" rel="noopener" title="<?= e($post['caption']) ?>"><img src="<?= e($post['img']) ?>" alt="<?= e($post['caption'] ?: 'Instagram-Beitrag DRK Verden') ?>" loading="lazy" decoding="async" width="600" height="600"></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/partials/cta.php'; ?>
<?php layout_end(); ?>
