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

// Kurzinfo zum Kurs (eine Zeile statt Seitenleiste)
$info = [];
if ($slug !== '') {
    foreach (pairs($current['facts']) as [$k, $v]) {
        if (in_array(mb_strtolower($k), ['dauer', 'preis', 'kosten'], true)) {
            $info[] = $v;
        }
    }
}
$free = count(array_filter($items, fn($it) => $it['status'] !== 'full' && !empty($it['bookable'] ?? true)));
?>
<section class="tpage">
  <div class="wrap tpage__wrap">
    <nav class="crumbs" aria-label="Brotkrumen"><a href="<?= url('/') ?>">Start</a><span aria-hidden="true">/</span><a href="<?= url('termine') ?>">Kurstermine</a><?php if ($slug): ?><span aria-hidden="true">/</span><span><?= e($current['title']) ?></span><?php endif; ?></nav>
    <h1 class="h2 tpage__title"><?= $slug ? e($current['title']) : e($P('title')) ?></h1>
    <?php if ($slug && $info): ?>
    <p class="tpage__info"><?= e(implode(' · ', $info)) ?> · <a href="<?= course_url($current) ?>">Mehr zum Kurs</a></p>
    <?php elseif (!$slug): ?>
    <p class="tpage__info"><?= e($lead) ?></p>
    <?php endif; ?>

    <nav class="tabs" aria-label="Kurs wählen">
      <a class="tab" href="<?= url('termine') ?>"<?= $slug === '' ? ' aria-current="page"' : '' ?>>Alle</a>
      <?php foreach ($all as $c): ?>
      <a class="tab" href="<?= url('termine/' . $c['slug']) ?>"<?= $slug === $c['slug'] ? ' aria-current="page"' : '' ?>><?= e($c['title']) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($items): ?>
    <?php $fo = date_filter_options($items); ?>
    <form class="dates-tools" data-date-filter onsubmit="return false">
      <?php foreach (['ort' => ['Ort', 'Alle Orte'], 'monat' => ['Monat', 'Alle Monate'], 'wtag' => ['Wochentag', 'Alle Tage']] as $key => [$label, $allLabel]): if (count($fo[$key]) < 2 && !($key === 'wtag' && !empty($_GET['wann'])) && !($key === 'ort' && !empty($_GET['ort']))) continue; ?>
      <label class="field field--inline"><span class="sr-only"><?= $label ?></span>
        <?php $pre = $key === 'ort' ? (string) ($_GET['ort'] ?? '') : ($key === 'wtag' ? (string) ($_GET['wann'] ?? '') : ''); ?>
        <select name="<?= $key ?>" data-nice aria-label="<?= $label ?>"><option value=""><?= $allLabel ?></option>
          <?php if ($key === 'wtag'): ?><option value="wk"<?= $pre === 'wk' ? ' selected' : '' ?>>Unter der Woche</option><option value="we"<?= $pre === 'we' ? ' selected' : '' ?>>Am Wochenende</option><?php endif; ?>
          <?php foreach ($fo[$key] as $v => $l): ?><option value="<?= e((string) $v) ?>"<?= $pre !== '' && $pre === (string) $v ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </label>
      <?php endforeach; ?>
      <span class="muted small dates-tools__count" data-date-count><?= $free ?> freie Termine</span>
    </form>
    <ul class="dates dates--list" data-date-list>
      <?= render_dates($items, ['show_course' => $slug === '', 'months' => true]) ?>
    </ul>
    <p class="notice empty" hidden data-date-empty>Keine Termine für diese Auswahl. <a href="#" data-date-reset>Filter zurücksetzen</a></p>
    <?php elseif ($failed): ?>
    <div class="notice">
      <h2 class="h5">Termine gerade nicht erreichbar</h2>
      <p>Unser Buchungssystem antwortet gerade nicht. Bitte versuch es gleich noch einmal oder ruf uns an: <a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a></p>
    </div>
    <?php else: ?>
    <div class="notice">
      <h2 class="h5">Aktuell keine freien Termine</h2>
      <p>Neue Termine kommen laufend dazu. Für Gruppen und Betriebe finden wir auch einen eigenen Termin.</p>
    </div>
    <?php endif; ?>

    <div class="tpage__more">
      <span>Kein passender Termin? Für Gruppen und Betriebe kommen wir auch vor Ort.</span>
      <a class="btn btn--ghost btn--sm" href="<?= url('kontakt' . ($slug ? '?thema=' . $slug : '')) ?>">Inhouse anfragen</a>
      <button class="btn btn--ghost btn--sm" type="button" data-kf-open>Welcher Kurs passt?</button>
    </div>
  </div>
</section>
<?php layout_end(); ?>
