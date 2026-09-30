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
<section class="hero2">
  <?= photo('hero', 'Erste-Hilfe-Kurs beim DRK: Teilnehmende üben die Herzdruckmassage', 'hero2__bg', false) ?>
  <div class="hero2__shade" aria-hidden="true"></div>
  <div class="wrap hero2__in">
    <?php if ($P('hero_badge')): ?><span class="hero2__badge"><?= icon('shield') ?><?= e($P('hero_badge')) ?></span><?php endif; ?>
    <h1 class="display"><?= e($P('hero_title')) ?></h1>
    <p class="lead"><?= e($P('hero_lead')) ?></p>
    <?php include __DIR__ . '/partials/suche.php'; ?>
    <ul class="hero2__trust">
      <?php foreach (lines($P('hero_trust')) as $t): ?><li><?= icon('check') ?><?= e($t) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="tiles">
  <div class="wrap tiles__grid">
    <?php foreach ([['erste-hilfe', 'Erste Hilfe', 'Führerschein · Betrieb · Familie'], ['brandschutz', 'Brandschutz', 'Helfer · Löschtraining · Beratung'], ['arbeitssicherheit', 'Arbeitssicherheit', 'Fachkraft · Beratung · Dokumente']] as [$slug, $t, $sub]): ?>
    <a class="tile reveal" href="<?= url($slug) ?>">
      <?= photo($slug === 'arbeitssicherheit' ? 'unternehmen' : $slug, $t, 'tile__img') ?>
      <span class="tile__txt"><strong><?= e($t) ?></strong><span><?= e($sub) ?></span></span>
      <span class="tile__go"><?= icon('arrow') ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<?php
$next = array_values(array_filter(hiorg_dates_all(bookable_courses()), fn($it) => $it['status'] !== 'full' && !empty($it['kid'])));
?>
<?php if ($next): ?>
<section class="section section--tight">
  <div class="wrap nextd">
    <div class="section-head reveal">
      <div><h2 class="h2">Nächste freie Termine</h2><p class="lead">Platz sichern und direkt online anmelden.</p></div>
      <a class="link-arrow" href="<?= url('termine') ?>">Alle <?= count($next) ?> Termine <?= icon('arrow') ?></a>
    </div>
    <ul class="dates dates--list reveal">
      <?= render_dates($next, ['limit' => 5, 'show_course' => true]) ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<section class="figures">
  <div class="wrap figures__grid">
    <div class="figure"><strong><?= count($next) ?></strong><span>freie Termine</span></div>
    <div class="figure"><strong><?= count($orte) ?></strong><span>Kursorte in der Region</span></div>
    <div class="figure"><strong><?= count(courses()) ?></strong><span>Kurse &amp; Leistungen</span></div>
    <div class="figure"><strong>1 Tag</strong><span>Erste-Hilfe-Kurs – für Führerschein und Betrieb</span></div>
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
        <div class="card__media"><?= course_media($c) ?></div>
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
