<?php
$c = $current;
$cat = $c['category'] === 'brandschutz' ? 'brandschutz' : 'erste-hilfe';
$catTitle = page($cat, 'title');
$dates = free_dates($c);
$sources = dates_sources($c);
$bookable = !empty($c['hiorg_id']) || $sources;
$srcBy = [];
foreach ($sources as [$sl, $sc]) {
    $srcBy[$sl] = $sc;
}
$facts = pairs($c['facts'] ?? '');
$top = array_slice($facts, 0, 4);
$rest = array_slice($facts, 4);
$icons = ['ausbildung' => 'award', 'fortbildung' => 'shield', 'inhouse' => 'building', 'dauer' => 'clock', 'preis' => 'euro', 'kosten' => 'euro', 'ort' => 'pin', 'format' => 'pin', 'frist' => 'shield', 'auffrischung' => 'shield', 'teilnahme' => 'users', 'für' => 'users', 'gruppe' => 'users', 'welpen' => 'heart', 'übung' => 'check', 'module' => 'clock', 'zeiten' => 'clock'];
$others = array_values(array_filter(listed_courses($cat), fn($o) => $o['slug'] !== $c['slug']));
usort($others, fn($a, $b) => (($b['group'] === $c['group']) <=> ($a['group'] === $c['group'])) ?: ((int) !empty($b['hiorg_id']) <=> (int) !empty($a['hiorg_id'])));

layout_start([
    'title' => $c['title'] . ' in Verden & Achim | DRK',
    'description' => $c['teaser'] . ' ' . ($c['price'] ? 'Preis: ' . $c['price'] . '. ' : '') . 'DRK-Kreisverband Verden – Termine online buchen.',
    'path' => course_path($c),
    'active' => $cat,
    'breadcrumb' => [[$catTitle, $cat], [$c['title'], course_path($c)]],
    'schema' => [course_schema($c, $dates)],
]);
$slot = slot_image('kurs-' . $c['slug']) ? 'kurs-' . $c['slug'] : $cat;
page_head('', $c['title'], $c['teaser'], [[$catTitle, $cat], [$c['title'], course_path($c)]], '', $slot, true);
?>
<div class="wrap">
  <div class="kfacts reveal">
    <?php foreach ($top as [$k, $v]): ?>
    <div><span class="kfacts__ic"><?= icon($icons[mb_strtolower($k)] ?? 'info') ?></span><span><small><?= e($k) ?></small><b><?= e($v) ?></b></span></div>
    <?php endforeach; ?>
  </div>
</div>

<section class="section">
  <div class="wrap kgrid">
    <div class="reveal">
      <p class="eyebrow">Kursinhalt</p>
      <h2>Darum geht's</h2>
      <div class="kgrid__text"><?php foreach (paragraphs($c['text']) as $par): ?><p><?= e($par) ?></p><?php endforeach; ?></div>
      <?php if ($parts = lines($c['parts'] ?? '')): ?>
      <div class="parts">
        <?php foreach ($parts as $pl): [$pt, $px, $pm] = array_map('trim', array_pad(explode('|', $pl), 3, '')); ?>
        <div class="part">
          <h3><?= e($pt) ?></h3>
          <p><?= e($px) ?></p>
          <?php if ($pm): ?><p class="part__meta"><?= icon('clock') ?><?= e($pm) ?></p><?php endif; ?>
          <?php if (isset($srcBy[$pt])): ?><a class="alink" href="<?= course_url($srcBy[$pt]) ?>"><?= e($srcBy[$pt]['title']) ?> <?= icon('arrow') ?></a>
          <?php elseif ($bookable): ?><a class="alink" href="<?= url('termine/' . $c['slug']) ?>?art=<?= rawurlencode($pt) ?>">Termine <?= e($pt) ?> <?= icon('arrow') ?></a><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ($learn = lines($c['learn'])): ?>
      <h2><?= e(($c['learn_title'] ?? '') ?: ($cat === 'brandschutz' ? 'Inhalte' : 'Das lernst du')) ?></h2>
      <ul class="checks checks--2 checks--plain"><?php foreach ($learn as $l): ?><li><?= icon('check') ?><?= e($l) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if (course_bg($c)): ?><?= bg_box() ?><?php endif; ?>
      <?php if ($rest): ?>
      <div class="note"><?= icon('info') ?><div><?php foreach ($rest as $n => [$k, $v]): ?><?= $n ? '<br>' : '' ?><b><?= e($k) ?>:</b> <?= e($v) ?><?php endforeach; ?></div></div>
      <?php endif; ?>
      <?php
      $cmpData = page('erste-hilfe', 'vergleich');
      $cmpSlugs = array_map(fn($t) => slugify(trim($t)), array_slice(explode('|', lines($cmpData)[0] ?? ''), 1));
      if (in_array($c['slug'], $cmpSlugs, true) || array_intersect(array_map(fn($s) => $s[1]['slug'], $sources), $cmpSlugs)): ?>
      <?= compare_table($cmpData, page('erste-hilfe', 'vergleich_hinweis')) ?>
      <?php endif; ?>
    </div>

    <aside class="sidebox reveal" aria-label="Termine">
      <?php if ($bookable && $dates): ?>
      <div class="sidebox__head"><h3>Nächste freie Termine</h3><p>Direkt online anmelden</p></div>
      <ul class="dates"><?= render_dates($dates, ['limit' => 4]) ?></ul>
      <?php if ($pn = place_notice(array_slice($dates, 0, 4))): ?><div class="sidebox__body sidebox__body--notice"><?= $pn ?></div><?php endif; ?>
      <?php if ($sources): ?>
      <div class="sidebox__foot sidebox__foot--multi"><?php foreach ($sources as [$sl, $sc]): ?><a class="alink" href="<?= url('termine/' . $sc['slug']) ?>">Alle Termine <?= e($sl) ?> <?= icon('arrow') ?></a><?php endforeach; ?></div>
      <?php else: ?>
      <div class="sidebox__foot"><a class="alink" href="<?= url('termine/' . $c['slug']) ?>">Alle <?= count($dates) ?> Termine <?= icon('arrow') ?></a></div>
      <?php endif; ?>
      <?php elseif ($bookable): ?>
      <div class="sidebox__head"><h3>Termine</h3><p>Aktuell keine freien Plätze</p></div>
      <div class="sidebox__body"><p>Neue Termine kommen laufend dazu. Für Gruppen und Betriebe finden wir auch einen eigenen Termin.</p>
        <a class="btn btn--red" href="<?= url('termine/' . $c['slug']) ?>"><?= icon('calendar') ?> Alle Termine</a>
        <a class="btn btn--ghost" href="<?= url('kontakt?thema=' . $c['slug']) ?>#formular">Termin anfragen</a></div>
      <?php else: ?>
      <div class="sidebox__head"><h3>Termin anfragen</h3><p>Nach Absprache – bei uns oder direkt vor Ort</p></div>
      <div class="sidebox__body"><p>Wir stimmen Termin, Ort und Gruppengröße gemeinsam ab und melden uns schnellstmöglich.</p>
        <a class="btn btn--red" href="<?= url('kontakt?thema=' . $c['slug']) ?>#formular"><?= icon('mail') ?> Unverbindlich anfragen</a>
        <a class="btn btn--ghost" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> <?= e(site('phone')) ?></a></div>
      <?php endif; ?>
      <div class="sidebox__person">
        <?= person_card(course_contact($c), 'Deine Ansprechperson') ?>
        <?php if (($pay = contact_by_role('Kosten')) && $pay['name'] !== (course_contact($c)['name'] ?? '')): ?>
        <p class="pcard__more"><b>Kosten &amp; Abrechnung:</b> <?= e($pay['name']) ?> · <a href="mailto:<?= e($pay['email']) ?>"><?= e($pay['email']) ?></a></p>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</section>

<?php if ($others): ?>
<section class="section">
  <div class="wrap">
    <?= shead('Weitere Kurse', $cat === 'brandschutz' ? 'Weitere Angebote' : 'Das könnte dich auch interessieren', '', 'Alle Kurse ansehen', url($cat)) ?>
    <div class="grid3"><?php foreach (array_slice($others, 0, 3) as $o): ?><?= course_card($o) ?><?php endforeach; ?></div>
  </div>
</section>
<?php endif; ?>
<?php layout_end(); ?>
