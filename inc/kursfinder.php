<?php
/**
 * Kursfinder: geführter Assistent (ohne KI-Dienst), der per Rückfragen den passenden Kurs
 * ermittelt und die nächsten freien HiOrg-Termine mit Link zur Anmeldung zeigt.
 * Läuft komplett auf dem eigenen Server – keine Daten an Dritte.
 */
declare(strict_types=1);

/** JSON für /api/kursfinder?kurs={slug}: freie Termine eines Kurses. */
function kursfinder_api(string $slug): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    header('X-Robots-Tag: noindex');
    $c = course($slug);
    if (!$c || empty($c['hiorg_id'])) {
        http_response_code(404);
        echo json_encode(['error' => 'unbekannter Kurs']);
        return;
    }
    $res = hiorg_dates($c);
    $dates = [];
    foreach ($res['items'] as $it) {
        if ($it['status'] === 'full' || empty($it['kid'])) {
            continue;
        }
        $d = $it['date'];
        $dates[] = [
            'day' => $d->format('j'),
            'month' => de_date($d, 'MMM'),
            'label' => de_date($d, 'WWW, D. MMM'),
            'wd' => (int) $d->format('N'),
            'time' => $it['time'],
            'ort' => hiorg_town($it['details']),
            'place' => $it['details'],
            'free' => $it['free'],
            'few' => $it['status'] === 'few',
            'price' => $it['price'],
            'book' => url('termine/' . $c['slug'] . '/anmeldung/' . $it['kid']),
        ];
    }
    echo json_encode([
        'ok' => $res['ok'],
        'title' => $c['title'],
        'all' => url('termine/' . $c['slug']),
        'info' => course_url($c),
        'inhouse' => url('kontakt?thema=' . $c['slug']),
        'dates' => $dates,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/** Konfiguration für das Frontend: Kurse, Suchbegriffe, Anfrage-Angebote. */
function kursfinder_config(): array
{
    $bySlug = [];
    foreach (courses() as $c) {
        $bySlug[$c['slug']] = $c;
    }
    $has = fn(string $s) => isset($bySlug[$s]);
    $title = fn(string $s) => $bySlug[$s]['title'] ?? $s;

    // Suchbegriffe für die Freitext-Eingabe (Wortanfänge, klein geschrieben)
    $keywords = [
        'erste-hilfe-fortbildung' => ['fortbildung', 'auffrisch', 'wiederhol', 'refresh', 'verlänger'],
        'erste-hilfe-am-welpen' => ['welpe'],
        'erste-hilfe-am-hund' => ['hund', 'tier'],
        'erste-hilfe-am-kind' => ['kind', 'baby', 'säugling', 'eltern', 'mama', 'papa', 'kita', 'erzieh', 'tagesmutter', 'babysitt', 'enkel', 'oma', 'opa'],
        'brandschutzhelfer' => ['brand', 'feuer', 'lösch', 'evakuier'],
        'erste-hilfe-ausbildung' => ['führerschein', 'fuehrerschein', 'fahrschule', 'fahrerlaubnis', 'auto', 'moped', 'motorrad', 'lkw', 'studium', 'trainer', 'übungsleit', 'verein', 'ausbildung', 'erste hilfe', 'kurs'],
        '_betrieb' => ['betrieb', 'firma', 'arbeit', 'job', 'chef', 'ersthelfer', 'bg', 'berufsgenossenschaft', 'unternehmen'],
    ];
    $keywords = array_filter($keywords, fn($k) => $k === '_betrieb' || $has($k), ARRAY_FILTER_USE_KEY);

    $requests = [];
    foreach (courses() as $c) {
        if (empty($c['hiorg_id'])) {
            $requests[] = ['title' => $c['title'], 'teaser' => $c['teaser'], 'url' => course_url($c)];
        }
    }

    return [
        'api' => url('api/kursfinder'),
        'titles' => array_map(fn($c) => $c['title'], $bySlug),
        'keywords' => $keywords,
        'requests' => $requests,
        'contact' => url('kontakt'),
        'phone' => site('phone'),
        'phoneLink' => 'tel:' . site('phone_link'),
    ];
}

/** Markup: Startknopf + Chat-Fenster (wird in layout_end eingebunden). */
function kursfinder_markup(): string
{
    $cfg = json_encode(kursfinder_config(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS);
    ob_start(); ?>
<button class="kf-launch" type="button" data-kf-open aria-haspopup="dialog" aria-controls="kursfinder">
  <svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 11h.01M12 11h.01M15.5 11h.01"/></svg>
  <span>Kurs finden</span>
</button>
<section class="kf" id="kursfinder" role="dialog" aria-modal="false" aria-labelledby="kf-title" hidden data-kf-config='<?= $cfg ?>'>
  <header class="kf__head">
    <span class="kf__avatar" aria-hidden="true"><?= cross_svg('', '#fff') ?></span>
    <div><strong id="kf-title">Kursfinder</strong><span>Passenden Kurs in 3 Fragen</span></div>
    <button class="kf__icon" type="button" data-kf-restart title="Neu starten" aria-label="Neu starten"><svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg></button>
    <button class="kf__icon" type="button" data-kf-close aria-label="Schließen"><svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
  </header>
  <div class="kf__body" data-kf-log aria-live="polite"></div>
  <form class="kf__input" data-kf-form>
    <label class="sr-only" for="kf-text">Frage eingeben</label>
    <input id="kf-text" type="text" autocomplete="off" placeholder="z. B. Führerschein in Achim" maxlength="160">
    <button type="submit" aria-label="Senden"><?= icon('arrow') ?></button>
  </form>
</section>
<?php
    return (string) ob_get_clean();
}
