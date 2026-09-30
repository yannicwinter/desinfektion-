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
    header('X-Robots-Tag: noindex');
    if (isset($_GET['frage'])) {
        header('Cache-Control: no-store');
        echo json_encode(kursfinder_answer(mb_substr((string) $_GET['frage'], 0, 200)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return;
    }
    header('Cache-Control: public, max-age=300');
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
        'erste-hilfe-ausbildung' => ['führerschein', 'fuehrerschein', 'fahrschule', 'fahrerlaubnis', 'auto', 'moped', 'motorrad', 'lkw', 'erste hilfe', 'kurs'],
        '_betrieb' => ['betrieb', 'firma', 'arbeit', 'job', 'chef', 'ersthelfer', 'bg', 'berufsgenossenschaft', 'unternehmen', 'trainer', 'übungsleit', 'verein', 'studium', 'uni', 'lizenz']
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
        'keywords' => ['_betrieb' => $keywords['_betrieb']],
        'requests' => $requests,
        'contact' => url('kontakt'),
        'faq' => url('faq'),
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

// ---------------------------------------------------------------------------
// Wissenssuche („kleine KI“): beantwortet Freitext aus den eigenen Inhalten.
// Durchsucht Kurse (Titel, Suchbegriffe, Texte, Eckdaten) und FAQ, gewichtet
// seltene Wörter stärker (TF-IDF) und erkennt ganze Kursnamen als Phrase.
// ---------------------------------------------------------------------------

const KF_STOP = ['ich', 'du', 'wir', 'mein', 'meine', 'meinen', 'meinem', 'dein', 'euer', 'unser', 'unsere', 'möchte', 'moechte', 'will', 'würde',
    'gerne', 'gern', 'bitte', 'einen', 'eine', 'einem', 'einer', 'ein', 'der', 'die', 'das', 'den', 'dem', 'des', 'und', 'oder', 'für', 'fuer', 'mit',
    'von', 'zu', 'zum', 'zur', 'im', 'in', 'am', 'an', 'auf', 'bei', 'ist', 'sind', 'bin', 'es', 'gibt', 'habe', 'hab', 'hat', 'kann', 'man',
    'wie', 'was', 'wo', 'wann', 'wer', 'welche', 'welcher', 'welches', 'buchen', 'anmelden', 'machen', 'suche', 'brauche', 'nächste', 'naechste',
    'nächsten', 'termin', 'termine', 'kurs', 'kurse', 'kursen', 'lehrgang', 'hallo', 'hi', 'noch', 'mal', 'auch', 'so', 'da', 'denn', 'dass', 'nicht'];

/** Wörter normalisieren und grob auf den Wortstamm kürzen. */
function kf_tokens(string $text): array
{
    $t = mb_strtolower($text);
    $t = strtr($t, ['ø' => 'o', 'ß' => 'ss']);
    $t = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $t);
    $out = [];
    foreach (preg_split('/\s+/', trim($t)) as $w) {
        if ($w === '' || in_array($w, KF_STOP, true) || mb_strlen($w) < 2) {
            continue;
        }
        $out[] = kf_stem($w);
    }
    return $out;
}

function kf_stem(string $w): string
{
    $w = strtr($w, ['ä' => 'a', 'ö' => 'o', 'ü' => 'u']);
    foreach (['ungen', 'erinnen', 'ern', 'ens', 'en', 'er', 'es', 'e', 'n', 's'] as $suf) {
        if (mb_strlen($w) - mb_strlen($suf) >= 4 && str_ends_with($w, $suf)) {
            return mb_substr($w, 0, -mb_strlen($suf));
        }
    }
    return $w;
}

/** Suchindex aus Kursen und FAQ (Wort → Gewicht je Dokument). */
function kf_index(): array
{
    static $idx = null;
    if ($idx !== null) {
        return $idx;
    }
    $docs = [];
    foreach (courses() as $c) {
        $w = [];
        $add = function (string $text, float $weight) use (&$w) {
            foreach (kf_tokens($text) as $tok) {
                $w[$tok] = ($w[$tok] ?? 0) + $weight;
            }
        };
        $add($c['title'], 5);
        $add(str_replace('-', ' ', $c['slug']), 3);
        $add((string) ($c['keywords'] ?? ''), 4);
        $add($c['teaser'], 2);
        $add($c['group'], 1);
        $add($c['text'], 1);
        $add($c['learn'], 0.7);
        $phrases = array_filter(array_map('trim', explode(',', (string) ($c['keywords'] ?? ''))), fn($p) => str_contains($p, ' '));
        $phrases[] = $c['title'];
        $docs[] = ['type' => 'course', 'c' => $c, 'w' => $w, 'phrases' => array_map(fn($p) => implode(' ', kf_tokens($p)), $phrases)];
    }
    foreach (content()['faq'] ?? [] as $f) {
        $w = [];
        foreach (kf_tokens($f['q']) as $tok) {
            $w[$tok] = ($w[$tok] ?? 0) + 3;
        }
        foreach (kf_tokens($f['a']) as $tok) {
            $w[$tok] = ($w[$tok] ?? 0) + 1;
        }
        $docs[] = ['type' => 'faq', 'f' => $f, 'w' => $w, 'phrases' => []];
    }
    $df = [];
    foreach ($docs as $d) {
        foreach (array_keys($d['w']) as $tok) {
            $df[(string) $tok] = ($df[$tok] ?? 0) + 1;
        }
    }
    return $idx = ['docs' => $docs, 'df' => $df, 'n' => count($docs)];
}

function kf_score(array $doc, array $q, string $qJoined, array $idx): float
{
    $s = 0.0;
    foreach (array_unique($q) as $tok) {
        $hit = $doc['w'][$tok] ?? 0;
        if (!$hit) {
            // Teilwort-Treffer (z. B. „hundekurs“ ↔ „hund“), schwächer gewichtet
            foreach ($doc['w'] as $dt => $dw) {
                $dt = (string) $dt;
                if (mb_strlen($dt) >= 4 && mb_strlen($tok) >= 4 && (str_starts_with($tok, $dt) || str_starts_with($dt, $tok))) {
                    $hit = max($hit, $dw * 0.5);
                }
            }
        }
        if ($hit) {
            $s += $hit * log(1 + $idx['n'] / ($idx['df'][(string) $tok] ?? 1));
        }
    }
    foreach ($doc['phrases'] as $p) {
        if ($p !== '' && str_contains(' ' . $qJoined . ' ', ' ' . $p . ' ')) {
            $s += 25 + 5 * substr_count($p, ' ');
        }
    }
    return $s;
}

/** Antwort auf eine Freitext-Frage: bester Kurs, beste FAQ, erkannte Absicht. */
function kursfinder_answer(string $question): array
{
    $q = kf_tokens($question);
    $raw = mb_strtolower($question);
    if (!$q) {
        return ['type' => 'none'];
    }
    $idx = kf_index();
    $qJoined = implode(' ', $q);
    $best = ['course' => [null, 0.0], 'faq' => [null, 0.0]];
    foreach ($idx['docs'] as $d) {
        $s = kf_score($d, $q, $qJoined, $idx);
        if ($s > $best[$d['type']][1]) {
            $best[$d['type']] = [$d, $s];
        }
    }
    [$cd, $cs] = $best['course'];
    [$fd, $fs] = $best['faq'];

    // Absicht: Preis, Dauer oder Ort zu einem Kurs?
    $intent = '';
    if (preg_match('/kost|preis|euro|€|teuer|bezahl|gebühr/u', $raw)) {
        $intent = 'preis';
    } elseif (preg_match('/dauer|lange|wie lang|stunden|uhrzeit|uhr\b|wann geht/u', $raw)) {
        $intent = 'dauer';
    }
    $isQuestion = (bool) preg_match('/\?|^(was|wie|wer|wann|wo|muss|darf|kann|gibt|brauch|ist|sind|wird)\b/u', trim($raw));

    $out = ['type' => 'none'];
    if ($cd && $cs >= 6) {
        $c = $cd['c'];
        $facts = [];
        foreach (pairs($c['facts']) as [$k, $v]) {
            $facts[mb_strtolower($k)] = $k . ': ' . $v;
        }
        $fact = '';
        if ($intent === 'preis') {
            $fact = $facts['preis'] ?? $facts['kosten'] ?? '';
        } elseif ($intent === 'dauer') {
            $fact = $facts['dauer'] ?? $facts['module'] ?? '';
        }
        $out = [
            'type' => 'course',
            'slug' => $c['slug'],
            'title' => $c['title'],
            'teaser' => $c['teaser'],
            'bookable' => !empty($c['hiorg_id']),
            'fact' => $fact,
            'url' => course_url($c),
            'inquiry' => url('kontakt?thema=' . $c['slug']),
            'score' => round($cs, 1),
        ];
    }
    // Allgemeine Frage (z. B. „Gibt es eine Prüfung?“) → FAQ-Antwort, wenn sie klar besser passt
    if ($fd && $fs >= 5 && ($out['type'] === 'none' || ($isQuestion && !$out['fact'] && $fs > ($cs * 0.8)))) {
        $out['faq'] = ['q' => $fd['f']['q'], 'a' => trim(strip_tags(rich($fd['f']['a'])))];
        if ($out['type'] === 'none') {
            $out['type'] = 'faq';
        }
    }
    return $out;
}
