<?php
/** Kursübersicht als Akkordeon: /erste-hilfe und /brandschutz */
$P = fn($k) => page($category, $k);
$list = courses($category);
$groups = [];
foreach ($list as $c) {
    $groups[$c['group'] ?: 'Kurse'][] = $c;
}
$isFire = $category === 'brandschutz';

layout_start([
    'title' => $P('seo_title'),
    'description' => $P('seo_description'),
    'path' => $category,
    'active' => $category,
    'breadcrumb' => [[$P('title'), $category]],
    'schema' => array_map(fn($c) => course_schema($c), $list),
]);
page_head($P('eyebrow'), $P('title'), $P('lead'), [[$P('title'), $category]], '', $category);
?>
<section class="section section--tight">
  <div class="wrap cpage">
    <?php if ($category === 'erste-hilfe' && lines(page('erste-hilfe', 'vergleich'))): ?>
    <details class="cpage__compare reveal">
      <summary><?= icon('search') ?> Welcher Kurs passt zu mir? <span>Übersicht Führerschein · Selbstzahler · BG</span><?= icon('chevron', 'i cpage__chev') ?></summary>
      <?= compare_table(page('erste-hilfe', 'vergleich'), true) ?>
    </details>
    <?php endif; ?>

    <?php foreach ($groups as $g => $items): ?>
    <h2 class="group-title reveal"><?= e($g) ?></h2>
    <div class="acc acc--courses reveal">
      <?php foreach ($items as $c):
          $facts = array_filter(pairs($c['facts']), fn($f) => mb_strtolower($f[0]) !== 'preis');
          $learn = lines($c['learn']);
          $bookable = !empty($c['hiorg_id']);
      ?>
      <details class="acc__item" id="<?= e($c['slug']) ?>">
        <summary class="acc__sum">
          <span class="acc__ico" aria-hidden="true"><?= course_media($c, 'acc__photo') ?></span>
          <span class="acc__head">
            <span class="acc__title"><?= e($c['title']) ?></span>
            <span class="acc__teaser"><?= e($c['teaser']) ?></span>
          </span>
          <span class="acc__price"><?= $c['price'] ? e($c['price']) : 'auf Anfrage' ?></span>
          <?= icon('chevron', 'i acc__chev') ?>
        </summary>
        <div class="acc__body">
          <p class="acc__text"><?= e($c['text']) ?></p>
          <?php if ($facts): ?>
          <ul class="acc__facts"><?php foreach ($facts as [$k, $v]): ?><li><span><?= e($k) ?></span> <?= e($v) ?></li><?php endforeach; ?></ul>
          <?php endif; ?>
          <?php if ($learn): ?>
          <ul class="checks checks--sm checks--2 acc__learn"><?php foreach ($learn as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
          <?php endif; ?>
          <?php if ($bookable): ?>
          <div class="acc__dates" data-dates="<?= url('api/termine/' . $c['slug']) ?>?limit=3">
            <div class="acc__dates-list"><p class="muted small">Termine werden geladen …</p></div>
          </div>
          <?php endif; ?>
          <div class="btn-row">
            <?php if ($bookable): ?>
            <a class="btn btn--red" href="<?= url('termine/' . $c['slug']) ?>"><?= icon('calendar') ?> Alle Termine</a>
            <a class="btn btn--ghost" href="<?= url('kontakt?thema=' . $c['slug']) ?>">Inhouse anfragen</a>
            <?php else: ?>
            <a class="btn btn--red" href="<?= url('kontakt?thema=' . $c['slug']) ?>"><?= icon('mail') ?> Anfragen</a>
            <a class="btn btn--ghost" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> Anrufen</a>
            <?php endif; ?>
          </div>
        </div>
      </details>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <p class="cpage__help reveal">
      <?= $isFire ? e($P('contact_title')) . ': ' . e($P('contact_text')) : e($P('outro_title')) ?>
      · <a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a>
      · <a href="#kursfinder" data-kf-open>Kursfinder fragen</a>
    </p>
  </div>
</section>
<?php include __DIR__ . '/partials/cta.php'; ?>
<?php layout_end(); ?>
