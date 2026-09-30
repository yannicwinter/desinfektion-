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
<section class="hero">
  <div class="wrap hero__grid">
    <div class="hero__text">
      <span class="eyebrow reveal"><?= e($P('hero_eyebrow')) ?></span>
      <h1 class="display reveal"><?= e($P('hero_title')) ?></h1>
      <p class="lead reveal"><?= e($P('hero_lead')) ?></p>
      <div class="btn-row reveal">
        <a class="btn btn--red" href="<?= url('termine') ?>"><?= icon('calendar') ?> Kurstermine</a>
        <a class="btn btn--ghost" href="<?= url('arbeitssicherheit') ?>">Für Unternehmen</a>
      </div>
    </div>
    <?php if ($hero = slot_image('hero')): ?>
    <figure class="hero__media"><img src="<?= e($hero) ?>" alt="Erste-Hilfe- und Brandschutz-Training beim DRK" width="2000" height="1125" fetchpriority="high"></figure>
    <?php else:
        $next = null;
        foreach (hiorg_dates_all(bookable_courses()) as $it) {
            if ($it['status'] !== 'full' && !empty($it['kid'])) { $next = $it; break; }
        } ?>
    <div class="bento">
      <a class="bento__tile bento__eh" href="<?= url('erste-hilfe') ?>">
        <?= cross_svg('bento__cross', '#fff') ?>
        <?= illus_heart('bento__heart', 'rgba(255,255,255,.16)', '#fff') ?>
        <span class="bento__label"><strong>Erste Hilfe</strong><span>Führerschein · Betrieb · Familie</span></span>
      </a>
      <a class="bento__tile bento__bs" href="<?= url('brandschutz') ?>">
        <?= illus_extinguisher('bento__ext') ?>
        <span class="bento__label"><strong>Brandschutz</strong><span>Helfer · Übungen · Beratung</span></span>
      </a>
      <?php if ($next): ?>
      <a class="bento__tile bento__next" href="<?= url('termine/' . $next['course']['slug'] . '/anmeldung/' . $next['kid']) ?>">
        <span class="bento__kicker">Nächster freier Kurs</span>
        <strong class="bento__date"><?= e(de_date($next['date'], 'WW, D. MMM')) ?></strong>
        <span><?= e($next['course']['title']) ?><?= hiorg_town($next['details']) ? ' · ' . e(hiorg_town($next['details'])) : '' ?></span>
        <span class="bento__go"><?= $next['free'] ? e($next['free']) : 'Jetzt anmelden' ?> <?= icon('arrow') ?></span>
      </a>
      <?php else: ?>
      <a class="bento__tile bento__next" href="<?= url('termine') ?>">
        <span class="bento__kicker">Kurstermine</span>
        <strong class="bento__date">Alle Termine</strong>
        <span class="bento__go">Jetzt ansehen <?= icon('arrow') ?></span>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="finder-wrap">
  <div class="wrap">
    <form class="finder reveal" action="<?= url('termine') ?>" method="get" data-finder>
      <div class="finder__head">
        <h2 class="h5">Kurstermin finden</h2>
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

<?php $insta = instagram_posts(6); if ($insta): ?>
<section class="section section--tight insta">
  <div class="wrap">
    <div class="section-head reveal">
      <div><span class="eyebrow">Instagram</span><h2 class="h2"><?= e($P('insta_title')) ?></h2></div>
      <a class="link-arrow" href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener">@drk_kreisverband_verden folgen <?= icon('arrow') ?></a>
    </div>
    <?php if ($insta): ?>
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
