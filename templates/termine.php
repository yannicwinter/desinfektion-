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
      <?php $fo = date_filter_options($items); ?>
      <form class="dates-tools" data-date-filter onsubmit="return false">
        <?php foreach (['ort' => ['Ort', 'Alle Orte'], 'monat' => ['Monat', 'Alle Monate'], 'wtag' => ['Wochentag', 'Alle Tage']] as $key => [$label, $all]): if (count($fo[$key]) < 2 && !($key === 'wtag' && !empty($_GET['wann'])) && !($key === 'ort' && !empty($_GET['ort']))) continue; ?>
        <label class="field field--inline"><span class="sr-only"><?= $label ?></span>
          <?php $pre = $key === 'ort' ? (string) ($_GET['ort'] ?? '') : ($key === 'wtag' ? (string) ($_GET['wann'] ?? '') : ''); ?>
          <select name="<?= $key ?>"><option value=""><?= $all ?></option>
            <?php if ($key === 'wtag'): ?><option value="wk"<?= $pre === 'wk' ? ' selected' : '' ?>>Unter der Woche</option><option value="we"<?= $pre === 'we' ? ' selected' : '' ?>>Am Wochenende</option><?php endif; ?>
            <?php foreach ($fo[$key] as $v => $l): ?><option value="<?= e((string) $v) ?>"<?= $pre !== '' && $pre === (string) $v ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
          </select>
        </label>
        <?php endforeach; ?>
        <span class="muted small dates-tools__count" data-date-count><?= count($items) ?> Termine</span>
      </form>
      <ul class="dates" data-date-list>
        <?= render_dates($items, ['show_course' => $slug === '']) ?>
      </ul>
      <p class="notice empty" hidden data-date-empty>Keine Termine für diese Auswahl. <a href="#" data-date-reset>Filter zurücksetzen</a></p>
      <?php elseif ($failed): ?>
      <div class="notice">
        <h2 class="h5">Termine gerade nicht erreichbar</h2>
        <p>Unser Buchungssystem antwortet gerade nicht. Die Termine gibt es direkt dort:</p>
        <div class="btn-row">
          <?php foreach ($slug ? [$current] : $all as $c): ?>
          <a class="btn btn--ghost btn--sm" href="<?= e(hiorg_list_url((string) $c['hiorg_id'])) ?>" target="_blank" rel="noopener"><?= e($c['title']) ?> <?= icon('external') ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="notice">
        <h2 class="h5">Aktuell keine freien Termine</h2>
        <p>Neue Termine kommen laufend dazu. Für Gruppen und Betriebe finden wir auch einen eigenen Termin.</p>
        <div class="btn-row"><a class="btn btn--red btn--sm" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a></div>
      </div>
      <?php endif; ?>
    </div>

    <aside class="layout-aside__side">
      <?php if ($slug === '' || in_array($slug, ['erste-hilfe-ausbildung', 'erste-hilfe-fortbildung', 'erste-hilfe-am-kind'], true)): ?>
      <div class="box">
        <?= compare_table(page('erste-hilfe', 'vergleich'), true) ?>
        <p class="small">Unsicher? Der Kursfinder hilft in 3 Fragen.</p>
        <button class="btn btn--ghost btn--sm" type="button" data-kf-open>Kursfinder fragen</button>
      </div>
      <?php endif; ?>
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
        <p>Für Gruppen und Betriebe kommen wir auch vor Ort.</p>
        <a class="btn btn--red btn--block" href="<?= url('kontakt' . ($slug ? '?thema=' . $slug : '')) ?>">Inhouse anfragen</a>
      </div>
    </aside>
  </div>
</section>
<?php layout_end(); ?>
