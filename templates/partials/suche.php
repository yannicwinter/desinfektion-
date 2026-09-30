<?php
/**
 * Terminsuche der Startseite: Suchleiste (Desktop) + Such-Menü von unten (Handy).
 * Die freien Termine werden als kleines JSON mitgeliefert, damit die Anzahl live mitzählt.
 */
$suche = ['kurse' => [], 'termine' => []];
foreach (bookable_courses() as $c) {
    $n = 0;
    foreach (hiorg_dates($c)['items'] as $it) {
        if ($it['status'] === 'full' || empty($it['kid'])) {
            continue;
        }
        $suche['termine'][] = ['k' => $c['slug'], 'o' => hiorg_town($it['details']), 'w' => (int) $it['date']->format('N')];
        $n++;
    }
    $suche['kurse'][] = ['slug' => $c['slug'], 'title' => $c['title'], 'teaser' => $c['teaser'], 'n' => $n];
}
$orte = array_values(array_unique(array_filter(array_column($suche['termine'], 'o'))));
sort($orte);
$suche['orte'] = $orte;
$suche['base'] = url('termine');
$first = $suche['kurse'][0]['slug'] ?? '';
?>
<div class="hsearch" data-search='<?= e(json_encode($suche, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'>
  <form class="sbar" action="<?= url('termine') ?>" method="get" data-search-form>
    <div class="sbar__f sbar__f--kurs">
      <small>Kurs</small>
      <select name="kurs" data-f="kurs" data-nice aria-label="Kurs">
        <option value="">Alle Kurse</option>
        <?php foreach ($suche['kurse'] as $k): ?>
        <option value="<?= e($k['slug']) ?>"><?= e($k['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="sbar__f">
      <small>Ort</small>
      <select name="ort" data-f="ort" data-nice aria-label="Ort">
        <option value="">Alle Orte</option>
        <?php foreach ($orte as $o): ?><option value="<?= e($o) ?>"><?= e($o) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="sbar__f">
      <small>Wann</small>
      <select name="wann" data-f="wann" data-nice aria-label="Wann">
        <option value="">Egal</option>
        <option value="wk">Unter der Woche</option>
        <option value="we">Am Wochenende</option>
      </select>
    </div>
    <button class="btn btn--red sbar__go" type="submit"><?= icon('search') ?><span data-count-label>Termine finden</span></button>
  </form>

  <button class="spill" type="button" data-sheet-open aria-haspopup="dialog" aria-controls="suche-sheet">
    <span class="spill__icon"><?= icon('search') ?></span>
    <span class="spill__text"><strong>Kurstermin finden</strong><span data-spill-sub>Kurs · Ort · Wann</span></span>
    <span class="spill__go"><?= icon('arrow') ?></span>
  </button>

  <div class="ssheet" id="suche-sheet" role="dialog" aria-modal="true" aria-labelledby="ssheet-title" hidden>
    <div class="ssheet__backdrop" data-sheet-close></div>
    <div class="ssheet__panel">
      <header class="ssheet__head">
        <span class="ssheet__grip" aria-hidden="true"></span>
        <strong id="ssheet-title">Kurstermin finden</strong>
        <button type="button" class="ssheet__x" data-sheet-close aria-label="Schließen"><svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
      </header>
      <div class="ssheet__body">
        <p class="ssheet__step">1 · Welcher Kurs?</p>
        <div class="ssheet__kurse" data-chips="kurs">
          <button type="button" class="kchip kchip--all is-on" data-v="" aria-pressed="true"><strong>Alle Kurse</strong><span class="kchip__n" data-n></span></button>
          <?php foreach ($suche['kurse'] as $i => $k): ?>
          <button type="button" class="kchip" data-v="<?= e($k['slug']) ?>" aria-pressed="false">
            <strong><?= e($k['title']) ?></strong>
            <span class="kchip__n<?= $k['n'] ? '' : ' is-zero' ?>" data-n></span>
          </button>
          <?php endforeach; ?>
        </div>
        <p class="ssheet__step">2 · Wo?</p>
        <div class="ssheet__chips" data-chips="ort">
          <button type="button" class="schip is-on" data-v="" aria-pressed="true">Egal</button>
          <?php foreach ($orte as $o): ?><button type="button" class="schip" data-v="<?= e($o) ?>" aria-pressed="false"><?= e($o) ?></button><?php endforeach; ?>
        </div>
        <p class="ssheet__step">3 · Wann?</p>
        <div class="ssheet__chips" data-chips="wann">
          <button type="button" class="schip is-on" data-v="" aria-pressed="true">Egal</button>
          <button type="button" class="schip" data-v="wk" aria-pressed="false">Unter der Woche</button>
          <button type="button" class="schip" data-v="we" aria-pressed="false">Wochenende</button>
        </div>
        <p class="ssheet__help">Unsicher, welcher Kurs passt? <a href="#kursfinder" data-kf-open data-sheet-close>Kursfinder fragen</a></p>
      </div>
      <footer class="ssheet__foot">
        <button type="button" class="btn btn--red btn--block ssheet__go" data-sheet-go><?= icon('calendar') ?><span data-count-label>Termine anzeigen</span></button>
      </footer>
    </div>
  </div>
</div>
<script src="<?= asset('js/suche.js') ?>" defer></script>
