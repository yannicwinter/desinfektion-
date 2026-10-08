<?php
$searchCourses = bookable_courses();
if (!empty($searchCategory)) {
    $searchCourses = array_values(array_filter($searchCourses, fn($c) => $c['category'] === $searchCategory));
}
$suche = ['kurse' => [], 'termine' => [], 'base' => url('termine'), 'cat' => (string) ($searchCategory ?? '')];
foreach ($searchCourses as $c) {
    foreach (free_dates($c) as $it) {
        $suche['termine'][] = ['k' => $c['slug'], 'o' => hiorg_town($it['details']), 'w' => (int) $it['date']->format('N')];
    }
    $suche['kurse'][] = ['slug' => $c['slug'], 'title' => $c['title']];
}
$orte = array_values(array_unique(array_filter(array_column($suche['termine'], 'o'))));
sort($orte);
$allLabel = ['erste-hilfe' => 'Alle Erste-Hilfe-Kurse', 'brandschutz' => 'Alle Brandschutz-Kurse'][$searchCategory ?? ''] ?? 'Alle Kurse';
$popular = [];
foreach (lines(page('home', 'beliebt')) as $l) {
    [$label, $href] = array_map('trim', array_pad(explode('|', $l, 2), 2, ''));
    if ($label !== '' && $href !== '') {
        $popular[] = [$label, $href];
    }
}
$trustIcons = ['award', 'check', 'card', 'pin'];
?>
<div class="hsearch">
  <div class="wrap">
    <div class="sbox reveal" data-search='<?= e(json_encode($suche, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
      <form class="sbar" action="<?= url('termine') ?>" method="get" data-search-form>
        <div class="sbar__f">
          <?= icon('heart') ?>
          <div><small>Kurs</small>
            <select name="kurs" data-f="kurs" data-nice aria-label="Kurs">
              <option value=""><?= e($allLabel) ?></option>
              <?php foreach ($suche['kurse'] as $k): ?><option value="<?= e($k['slug']) ?>"><?= e($k['title']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="sbar__f">
          <?= icon('pin') ?>
          <div><small>Ort</small>
            <select name="ort" data-f="ort" data-nice aria-label="Ort">
              <option value="">Alle Orte</option>
              <?php foreach ($orte as $o): ?><option value="<?= e($o) ?>"><?= e($o) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="sbar__f">
          <?= icon('calendar') ?>
          <div><small>Wann</small>
            <select name="wann" data-f="wann" data-nice aria-label="Wann">
              <option value="">Egal</option>
              <option value="wk">Unter der Woche</option>
              <option value="we">Am Wochenende</option>
            </select>
          </div>
        </div>
        <button class="btn btn--red sbar__go" type="submit"><?= icon('search') ?><span data-count-label>Termine finden</span></button>
      </form>
      <?php if ($popular && empty($searchCategory)): ?>
      <div class="sbox__pop"><span>Beliebt:</span><?php foreach ($popular as [$label, $href]): ?><a href="<?= e(url($href)) ?>"><?= e($label) ?></a><?php endforeach; ?></div>
      <?php endif; ?>
    </div>
    <?php if (($searchCategory ?? '') !== 'brandschutz' && ($trust = lines(page('home', 'trust')))): ?>
    <ul class="trust reveal">
      <?php foreach ($trust as $n => $t): ?><li><?= icon($trustIcons[$n % 4]) ?><?= e($t) ?></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
</div>
<script src="<?= asset('js/suche.js') ?>" defer></script>
