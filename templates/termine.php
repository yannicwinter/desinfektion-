<?php
/** /termine (alle Kurse) und /termine/{kurs} */
$P = fn($k) => page('termine', $k);

// Formular ohne JavaScript: ?kurs=slug → /termine/slug
if ($slug === '' && !empty($_GET['kurs']) && ($k = course((string) $_GET['kurs'])) && !empty($k['hiorg_id'])) {
    redirect('termine/' . $k['slug'], 302);
}

$all = bookable_courses();
$bereich = in_array($_GET['bereich'] ?? '', ['erste-hilfe', 'brandschutz'], true) ? $_GET['bereich'] : '';
if ($slug !== '') {
    $res = hiorg_dates($current);
    $items = $res['items'];
    $failed = !$res['ok'];
    $title = $current['title'];
    $seoTitle = $current['title'] . ' – Termine & Anmeldung | DRK Verden';
    $seoDesc = $current['teaser'] . ' Aktuelle Termine beim DRK-Kreisverband Verden – freie Plätze sehen und online buchen.';
    $schema = [course_schema($current, $items)];
} else {
    $list = $bereich ? array_values(array_filter($all, fn($c) => $c['category'] === $bereich)) : $all;
    $items = hiorg_dates_all($list);
    $failed = !$items && !array_filter(array_map(fn($c) => is_file(hiorg_cache_file(hiorg_first_id($c))), $list));
    $title = $P('title');
    $seoTitle = $P('seo_title');
    $seoDesc = $P('seo_description');
    $schema = [];
}

$crumbs = [['Termine', 'termine']];
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

$free = count(array_filter($items, fn($it) => $it['status'] !== 'full' && !empty($it['bookable'] ?? true)));
if ($slug !== '') {
    $info = array_filter([course_duration($current), $current['price']]);
    $lead = implode(' · ', $info);
} else {
    $lead = $P('lead');
}
?>
<section class="phead">
  <div class="wrap">
    <?= crumbs_html($crumbs) ?>
    <h1 class="h1"><?= e($slug ? 'Termine: ' . $title : $title) ?></h1>
    <?php if ($slug): ?>
    <p class="phead__info"><?= e($lead) ?><?= $lead ? ' · ' : '' ?><a href="<?= course_url($current) ?>">Alles zum Kurs</a></p>
    <?php elseif ($lead): ?>
    <p class="lead"><?= e($lead) ?></p>
    <?php endif; ?>
  </div>
</section>

<section class="section section--tight">
  <div class="wrap">
    <nav class="tabs" aria-label="Kurs wählen">
      <a class="tab" href="<?= url('termine') ?>"<?= $slug === '' && !$bereich ? ' aria-current="page"' : '' ?>>Alle</a>
      <?php foreach ($all as $c): ?>
      <a class="tab" href="<?= url('termine/' . $c['slug']) ?>"<?= $slug === $c['slug'] ? ' aria-current="page"' : '' ?>><?= e($c['title']) ?></a>
      <?php endforeach; ?>
    </nav>

    <?php if ($items): ?>
    <?php $fo = date_filter_options($items); ?>
    <form class="dates-tools" data-date-filter onsubmit="return false">
      <?php foreach (['art' => ['Art', 'Ausbildung & Fortbildung'], 'ort' => ['Ort', 'Alle Orte'], 'monat' => ['Monat', 'Alle Monate'], 'wtag' => ['Wochentag', 'Alle Tage']] as $key => [$label, $allLabel]): if (count($fo[$key]) < 2 && !($key === 'wtag' && !empty($_GET['wann'])) && !($key === 'ort' && !empty($_GET['ort']))) continue; ?>
      <label class="field field--inline"><span class="sr-only"><?= $label ?></span>
        <?php $pre = (string) ($_GET[['ort' => 'ort', 'wtag' => 'wann', 'art' => 'art'][$key] ?? ''] ?? ''); ?>
        <select name="<?= $key ?>" data-nice aria-label="<?= $label ?>"><option value=""><?= $allLabel ?></option>
          <?php if ($key === 'wtag'): ?><option value="wk"<?= $pre === 'wk' ? ' selected' : '' ?>>Unter der Woche</option><option value="we"<?= $pre === 'we' ? ' selected' : '' ?>>Am Wochenende</option><?php endif; ?>
          <?php foreach ($fo[$key] as $v => $l): ?><option value="<?= e((string) $v) ?>"<?= $pre !== '' && $pre === (string) $v ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </label>
      <?php endforeach; ?>
      <span class="dates-tools__count" data-date-count><?= $free ?> freie Termine</span>
    </form>
    <?= place_notice($items) ?>
    <?php if ($slug === '' || ($current['category'] ?? '') === 'erste-hilfe'): ?>
    <p class="muted small dates-hint"><?= icon('info') ?> <span><b>Anmeldung über den Arbeitgeber:</b> Bitte bei der Anmeldung „Arbeitgeber / UVT / BG“ wählen und alle Angaben zur Berufsgenossenschaft machen (z. B. Name der BG und Unternehmensnummer).<?php if ($bg = doc_url('bg-formular')): ?> <a href="<?= e($bg) ?>" target="_blank" rel="noopener">Abrechnungsformular BG (PDF)</a><?php endif; ?></span></p>
    <?php endif; ?>
    <ul class="dates" data-date-list>
      <?= render_dates($items, ['show_course' => $slug === '', 'show_price' => true, 'months' => true]) ?>
    </ul>
    <p class="notice" hidden data-date-empty>Keine Termine für diese Auswahl. <a href="#" data-date-reset>Filter zurücksetzen</a></p>
    <?php elseif ($failed): ?>
    <div class="notice">
      <h2 class="h5">Termine gerade nicht erreichbar</h2>
      <p>Unser Buchungssystem antwortet gerade nicht. Bitte gleich noch einmal versuchen oder anrufen: <a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a></p>
    </div>
    <?php else: ?>
    <div class="notice">
      <h2 class="h5">Aktuell keine freien Termine</h2>
      <p>Neue Termine kommen laufend dazu. Für Gruppen und Betriebe finden wir auch einen eigenen Termin.</p>
    </div>
    <?php endif; ?>

    <div class="tmore">
      <span>Kein passender Termin? Für Gruppen und Betriebe kommen wir auch vor Ort.</span>
      <a class="btn btn--ghost btn--sm" href="<?= url('kontakt' . ($slug ? '?thema=' . $slug : '')) ?>#formular">Inhouse anfragen</a>
      <button class="btn btn--ghost btn--sm" type="button" data-kf-open>Welcher Kurs passt?</button>
    </div>
  </div>
</section>
<?php layout_end(); ?>
