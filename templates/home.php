<?php
$P = fn($k) => page('home', $k);
$featured = array_values(array_filter(courses(), fn($c) => !empty($c['featured'])));
$faq = array_slice(content()['faq'] ?? [], 0, 4);

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
<section class="hero">
  <?= hero_art(true) ?>
  <div class="wrap hero__grid">
    <div class="hero__text">
      <span class="eyebrow reveal"><?= e($P('hero_eyebrow')) ?></span>
      <h1 class="display reveal"><?= e($P('hero_title')) ?></h1>
      <p class="lead reveal"><?= e($P('hero_lead')) ?></p>
      <div class="btn-row reveal">
        <a class="btn btn--red" href="<?= url('termine') ?>"><?= icon('calendar') ?> Kurstermine</a>
        <a class="btn btn--ghost" href="<?= url('unternehmen') ?>">Für Unternehmen</a>
      </div>
    </div>
  </div>
</section>

<section class="finder-wrap">
  <div class="wrap">
    <form class="finder reveal" action="<?= url('termine') ?>" method="get" data-finder>
      <div class="finder__head">
        <h2 class="h5">Kurstermin finden</h2>
        <p class="finder__note"><?= icon('phone') ?> Lieber persönlich? <a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a></p>
      </div>
      <label class="field">
        <span class="sr-only">Welcher Kurs?</span>
        <select name="kurs" aria-label="Kurs wählen">
          <?php foreach (bookable_courses() as $c): ?>
          <option value="<?= e($c['slug']) ?>"><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn btn--red" type="submit">Termine anzeigen <?= icon('arrow') ?></button>
    </form>
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
      <article class="card reveal">
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
      <?= image_slot('home', 'Erste-Hilfe-Ausbildung in einer kleinen Gruppe', 'building') ?>
      <div class="stat"><strong><?= e($P('stat_value')) ?></strong><span><?= e($P('stat_text')) ?></span></div>
    </div>
    <div class="stack reveal">
      <span class="eyebrow"><?= e($P('firma_eyebrow')) ?></span>
      <h2 class="h2"><?= e($P('firma_title')) ?></h2>
      <p class="lead"><?= e($P('firma_text')) ?></p>
      <ul class="checks">
        <?php foreach (lines($P('firma_list')) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?>
      </ul>
      <div class="btn-row"><a class="btn btn--dark" href="<?= url('unternehmen') ?>">Leistungen für Unternehmen <?= icon('arrow') ?></a></div>
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

<?php include __DIR__ . '/partials/cta.php'; ?>
<?php layout_end(); ?>
