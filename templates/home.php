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
<?php
$next = array_values(array_filter(hiorg_dates_all(bookable_courses()), fn($it) => $it['status'] !== 'full' && !empty($it['kid'])));
$first = $next[0] ?? null;
$ehPrice = (course('erste-hilfe-ausbildung') ?? [])['price'] ?? '';
$orteAll = array_unique(array_filter(array_map(fn($it) => hiorg_town($it['details']), $next)));
?>
<section class="ahero">
  <div class="wrap ahero__grid">
    <div class="ahero__text">
      <?php if ($P('hero_eyebrow')): ?><p class="kicker"><?= e($P('hero_eyebrow')) ?></p><?php endif; ?>
      <h1 class="ahero__title"><?= e($P('hero_title')) ?></h1>
      <p class="ahero__lead"><?= e($P('hero_lead')) ?></p>
      <div class="ahero__acts">
        <a class="btn btn--red" href="#termine">Kurstermin finden <?= icon('arrow') ?></a>
        <a class="ulink" href="<?= url('arbeitssicherheit') ?>">Angebot für Unternehmen</a>
      </div>
      <dl class="ahero__facts">
        <?php if ($next): ?><div><dt><?= count($next) ?></dt><dd>freie Termine</dd></div><?php endif; ?>
        <?php if ($orteAll): ?><div><dt><?= count($orteAll) ?></dt><dd>Kursorte</dd></div><?php endif; ?>
        <?php if ($ehPrice): ?><div><dt><?= e($ehPrice) ?></dt><dd>Erste-Hilfe-Kurs</dd></div><?php endif; ?>
      </dl>
    </div>
    <div class="ahero__pic">
      <?= photo('hero', 'Erste-Hilfe-Kurs: Herzdruckmassage an der Übungspuppe', 'ahero__img', false) ?>
      <?php if ($first): $fc = $first['course']; $book = site('hiorg_booking') !== 'tab' ? url('termine/' . $fc['slug'] . '/anmeldung/' . $first['kid']) : $first['link']; ?>
      <div class="nextcard">
        <small>Nächster freier Termin</small>
        <p><?= e($fc['title']) ?></p>
        <span><?= e(de_date($first['date'], 'WW, D. MMM')) ?><?= $first['time'] ? ' · ' . e(explode('–', $first['time'])[0]) . ' Uhr' : '' ?><?= hiorg_town($first['details']) ? ' · ' . e(hiorg_town($first['details'])) : '' ?></span>
        <div><em><?= e($first['free']) ?></em><a href="<?= e($book) ?>">Buchen <?= icon('arrow') ?></a></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="asec asec--grey">
  <div class="wrap">
    <h2 class="asec__title"><?= e($P('angebot_title')) ?></h2>
    <div class="offer">
      <?php foreach ([['erste-hilfe', 'Erste Hilfe', 'angebot_eh', 'Alle Kurse'], ['brandschutz', 'Brandschutz', 'angebot_bs', 'Mehr erfahren'], ['arbeitssicherheit', 'Arbeitssicherheit', 'angebot_as', 'Für Unternehmen']] as [$slug, $t, $k, $more]): ?>
      <a class="offer__item" href="<?= url($slug) ?>">
        <h3><?= e($t) ?></h3>
        <p><?= e($P($k)) ?></p>
        <span class="alink"><?= e($more) ?> <?= icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="asec" id="termine">
  <div class="wrap">
    <div class="asec__head">
      <div><h2 class="asec__title"><?= e($P('termine_title')) ?></h2><p class="asec__lead"><?= e($P('termine_lead')) ?></p></div>
    </div>
    <?php include __DIR__ . '/partials/suche.php'; ?>
    <?php if ($next): ?>
    <ul class="dates dates--list anext">
      <?= render_dates($next, ['limit' => 5, 'show_course' => true]) ?>
    </ul>
    <p class="anext__more"><a class="alink" href="<?= url('termine') ?>">Alle <?= count($next) ?> freien Termine <?= icon('arrow') ?></a></p>
    <?php endif; ?>
  </div>
</section>

<section class="asec asec--grey">
  <div class="wrap asplit">
    <figure class="asplit__pic"><?= photo('home', 'Arbeitssicherheit im Betrieb', 'asplit__img') ?></figure>
    <div class="asplit__text">
      <p class="kicker"><?= e($P('firma_eyebrow')) ?></p>
      <h2 class="asec__title"><?= e($P('firma_title')) ?></h2>
      <p class="asec__lead"><?= e($P('firma_text')) ?></p>
      <ul class="alist2">
        <?php foreach (lines($P('firma_list')) as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?>
      </ul>
      <a class="btn btn--red" href="<?= url('arbeitssicherheit') ?>">Leistungen für Unternehmen <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<?php if ($faq): ?>
<section class="asec">
  <div class="wrap afaq">
    <div>
      <h2 class="asec__title">Häufige Fragen</h2>
      <a class="alink" href="<?= url('faq') ?>">Alle Fragen <?= icon('arrow') ?></a>
    </div>
    <div class="acc acc--plain">
      <?php foreach ($faq as $f): ?>
      <details class="acc__item"><summary class="acc__sum"><span class="acc__title"><?= e($f['q']) ?></span><?= icon('chevron', 'i acc__chev') ?></summary><div class="acc__body"><div class="prose"><?= rich($f['a']) ?></div></div></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php $insta = instagram_posts(6); if ($insta): ?>
<section class="asec asec--grey insta">
  <div class="wrap">
    <div class="section-head reveal">
      <div><h2 class="asec__title"><?= e($P('insta_title')) ?></h2></div>
      <a class="alink" href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener">@drk_kreisverband_verden folgen <?= icon('arrow') ?></a>
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
