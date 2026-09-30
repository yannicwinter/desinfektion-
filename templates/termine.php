<?php
/** /termine (alle Kurse) und /termine/{kurs} */
$P = fn($k) => page('termine', $k);

// Formular der Startseite ohne JavaScript: ?kurs=slug → /termine/slug
if ($slug === '' && !empty($_GET['kurs']) && ($k = course((string) $_GET['kurs'])) && !empty($k['hiorg_id'])) {
    redirect('termine/' . $k['slug'], 302);
}

$all = bookable_courses();
if ($slug !== '') {
    $res = hiorg_dates($current);
    $items = $res['items'];
    $failed = !$res['ok'];
    $title = 'Termine: ' . $current['title'];
    $seoTitle = $current['title'] . ' – Termine & Anmeldung | DRK Verden';
    $seoDesc = $current['teaser'] . ' Aktuelle Termine beim DRK-Kreisverband Verden – freie Plätze sehen und online buchen.';
    $lead = $current['teaser'];
    $schema = [course_schema($current, $items)];
} else {
    $items = hiorg_dates_all($all);
    $failed = !$items && !array_filter(array_map(fn($c) => is_file(hiorg_cache_file((string) $c['hiorg_id'])), $all));
    $title = $P('title');
    $seoTitle = $P('seo_title');
    $seoDesc = $P('seo_description');
    $lead = $P('lead');
    $schema = [];
}

$crumbs = [['Kurstermine', 'termine']];
if ($slug !== '') {
    $crumbs[] = [$current['title'], 'termine/' . $slug];
}

layout_start([
    'title' => $seoTitle,
    'description' => $seoDesc,
    'path' => $slug ? 'termine/' . $slug : 'termine',
    'active' => 'termine',
    'breadcrumb' => $crumbs,
    'schema' => $schema,
]);

ob_start(); ?>
<nav class="chips" aria-label="Kurs wählen">
  <a class="chip" href="<?= url('termine') ?>"<?= $slug === '' ? ' aria-current="page"' : '' ?>>Alle Kurse</a>
  <?php foreach ($all as $c): ?>
  <a class="chip" href="<?= url('termine/' . $c['slug']) ?>"<?= $slug === $c['slug'] ? ' aria-current="page"' : '' ?>><?= e($c['title']) ?></a>
  <?php endforeach; ?>
</nav>
<?php
$chips = ob_get_clean();
page_head($P('eyebrow'), $title, $lead, $crumbs, $chips);
?>
<section class="section section--tight">
  <div class="wrap layout-aside">
    <div class="layout-aside__main">
      <?php if ($items): ?>
      <div class="dates-tools">
        <label class="search">
          <?= icon('search') ?>
          <span class="sr-only">Termine filtern</span>
          <input type="search" placeholder="Ort, Monat oder Wochentag …" data-date-filter>
        </label>
        <span class="muted small" data-date-count><?= count($items) ?> Termine</span>
      </div>
      <ul class="dates" data-date-list>
        <?= render_dates($items, ['show_course' => $slug === '']) ?>
      </ul>
      <p class="muted small empty" hidden data-date-empty>Keine Termine für diese Suche.</p>
      <?php elseif ($failed): ?>
      <div class="notice">
        <h2 class="h5">Termine gerade nicht erreichbar</h2>
        <p>Unser Buchungssystem antwortet im Moment nicht. Sie können die Termine direkt dort aufrufen:</p>
        <div class="btn-row">
          <?php foreach ($slug ? [$current] : $all as $c): ?>
          <a class="btn btn--ghost btn--sm" href="<?= e(hiorg_list_url((string) $c['hiorg_id'])) ?>" target="_blank" rel="noopener"><?= e($c['title']) ?> <?= icon('external') ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="notice">
        <h2 class="h5">Aktuell keine freien Termine</h2>
        <p>Neue Termine werden laufend eingestellt. Rufen Sie uns gern an – für Gruppen und Betriebe finden wir auch einen eigenen Termin.</p>
        <div class="btn-row"><a class="btn btn--red btn--sm" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a></div>
      </div>
      <?php endif; ?>
      <p class="muted small hint"><?= e($P('hint')) ?>
        <?php if ($slug): ?><a href="<?= e(hiorg_list_url((string) $current['hiorg_id'])) ?>" target="_blank" rel="noopener">Liste bei HiOrg-Server öffnen</a><?php endif; ?></p>
    </div>

    <aside class="layout-aside__side">
      <?php if ($slug): $facts = pairs($current['facts']); ?>
      <div class="box">
        <h2 class="h5"><?= e($current['title']) ?></h2>
        <div class="prose small"><?= rich($current['text']) ?></div>
        <?php if ($facts): ?>
        <dl class="facts facts--compact"><?php foreach ($facts as [$k, $v]): ?><div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div><?php endforeach; ?></dl>
        <?php endif; ?>
        <a class="link-arrow" href="<?= course_url($current) ?>">Mehr zum Kurs <?= icon('arrow') ?></a>
      </div>
      <?php endif; ?>
      <div class="box box--rose">
        <h2 class="h5">Kein passender Termin?</h2>
        <p>Für Gruppen und Betriebe kommen wir auch zu Ihnen.</p>
        <a class="btn btn--red btn--block" href="<?= url('kontakt' . ($slug ? '?thema=' . $slug : '')) ?>">Inhouse anfragen</a>
      </div>
    </aside>
  </div>
</section>
<?php layout_end(); ?>
