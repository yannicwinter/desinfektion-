<?php
/**
 * HiOrg-Server-Anbindung.
 *
 * Statt eines iframes wird die öffentliche Kursliste (kurse_extern.php) serverseitig
 * abgerufen, zwischengespeichert (cache/) und in unser eigenes, mobiles Layout übersetzt.
 * Die eigentliche Anmeldung bleibt bei HiOrg (Link „Buchen“ je Termin).
 *
 * Fällt HiOrg aus, wird die letzte gespeicherte Version gezeigt; gibt es keine,
 * erscheint ein Button zur HiOrg-Seite.
 */
declare(strict_types=1);

const HIORG_BASE = 'https://www.hiorg-server.de/';

function hiorg_list_url(string $id): string
{
    $ov = site('hiorg_ov') ?: 'drkv';
    return HIORG_BASE . 'kurse_extern.php?ov=' . rawurlencode($ov) . '&id=' . rawurlencode($id);
}

function hiorg_cache_file(string $id): string
{
    return CACHE_DIR . '/hiorg-' . preg_replace('/\D/', '', $id) . '.html';
}

/**
 * Holt mehrere Listen parallel (curl_multi) und aktualisiert den Cache.
 * @return array<string,string> id => HTML ('' wenn nichts verfügbar)
 */
function hiorg_fetch_many(array $ids, bool $force = false): array
{
    $ttl = max(5, (int) (site('hiorg_cache_minutes') ?: 30)) * 60;
    $out = [];
    $todo = [];
    foreach (array_unique(array_filter($ids)) as $id) {
        $f = hiorg_cache_file($id);
        if (!$force && is_file($f) && filemtime($f) > time() - $ttl) {
            $out[$id] = (string) file_get_contents($f);
        } else {
            $todo[] = $id;
        }
    }
    if ($todo) {
        if (!is_dir(CACHE_DIR)) {
            @mkdir(CACHE_DIR, 0755, true);
        }
        $fresh = function_exists('curl_multi_init') ? hiorg_curl_multi($todo) : hiorg_fgc($todo);
        foreach ($todo as $id) {
            $f = hiorg_cache_file($id);
            $html = $fresh[$id] ?? '';
            if ($html !== '' && stripos($html, '<') !== false) {
                file_put_contents($f, $html, LOCK_EX);
                $out[$id] = $html;
            } elseif (is_file($f)) {
                // HiOrg nicht erreichbar: alte Version weiterverwenden, in 5 Min. erneut versuchen
                touch($f, time() - $ttl + 300);
                $out[$id] = (string) file_get_contents($f);
            } else {
                $out[$id] = '';
            }
        }
    }
    return $out;
}

function hiorg_curl_multi(array $ids): array
{
    $mh = curl_multi_init();
    $handles = [];
    foreach ($ids as $id) {
        $ch = curl_init(hiorg_list_url($id));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; DRK-Website Kursliste)',
            CURLOPT_HTTPHEADER => ['Accept-Language: de-DE,de;q=0.9'],
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$id] = $ch;
    }
    do {
        $status = curl_multi_exec($mh, $running);
        if ($running) {
            curl_multi_select($mh, 1.0);
        }
    } while ($running && $status === CURLM_OK);
    $out = [];
    foreach ($handles as $id => $ch) {
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $out[$id] = $code >= 200 && $code < 300 ? (string) curl_multi_getcontent($ch) : '';
        curl_multi_remove_handle($mh, $ch);
    }
    curl_multi_close($mh);
    return $out;
}

function hiorg_fgc(array $ids): array
{
    $ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Mozilla/5.0 (compatible; DRK-Website Kursliste)']]);
    $out = [];
    foreach ($ids as $id) {
        $out[$id] = (string) @file_get_contents(hiorg_list_url($id), false, $ctx);
    }
    return $out;
}

/**
 * Termine eines Kurses (sortiert, nur zukünftige).
 * @return array{ok:bool, items:array, source:string}
 */
function hiorg_dates(array $course, bool $force = false): array
{
    $id = trim((string) ($course['hiorg_id'] ?? ''));
    if ($id === '') {
        return ['ok' => false, 'items' => [], 'source' => ''];
    }
    $html = hiorg_fetch_many([$id], $force)[$id];
    return ['ok' => $html !== '', 'items' => hiorg_parse($html, $course), 'source' => hiorg_list_url($id)];
}

/** Termine mehrerer Kurse, zusammengeführt und nach Datum sortiert. */
function hiorg_dates_all(array $courses): array
{
    $htmls = hiorg_fetch_many(array_map(fn($c) => trim((string) $c['hiorg_id']), $courses));
    $items = [];
    $seen = [];
    foreach ($courses as $c) {
        $id = trim((string) $c['hiorg_id']);
        // Gleiche HiOrg-Liste bei zwei Kursen nur einmal anzeigen
        if (isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        foreach (hiorg_parse($htmls[$id] ?? '', $c) as $it) {
            $items[] = $it;
        }
    }
    usort($items, fn($a, $b) => $a['ts'] <=> $b['ts']);
    return $items;
}

/**
 * Wandelt die HiOrg-HTML-Liste in einheitliche Termin-Datensätze um.
 * Robust gegen Layoutänderungen: Es wird nach Zeilen/Blöcken mit Datum gesucht und
 * Uhrzeit, Preis, freie Plätze und Anmeldelink per Muster erkannt.
 */
function hiorg_parse(string $html, array $course = []): array
{
    if (trim($html) === '') {
        return [];
    }
    if (!mb_check_encoding($html, 'UTF-8')) {
        $html = mb_convert_encoding($html, 'UTF-8', 'Windows-1252');
    }
    // Sonderzeichen als Entities → unabhängig von der Zeichensatz-Angabe der Seite
    $html = mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0x1FFFFF], 'UTF-8');
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
    libxml_clear_errors();
    $xp = new DOMXPath($doc);
    foreach ($xp->query('//script|//style|//noscript|//head') as $n) {
        $n->parentNode->removeChild($n);
    }

    // Aktuelles HiOrg-Layout (div.termine-container) direkt auslesen
    $boxes = $xp->query('//div[contains(concat(" ", normalize-space(@class), " "), " termine-container ")]');
    if ($boxes->length) {
        return hiorg_parse_boxes($xp, $boxes, $course);
    }

    // Rückfallebene für abweichende Layouts: Muster erkennen
    $dateRe = '/\b(\d{1,2})\.(\d{1,2})\.(\d{4}|\d{2})\b/';
    $blocks = [];
    foreach ($xp->query('//tr[td]') as $tr) {
        if (preg_match($dateRe, hiorg_text($tr))) {
            $blocks[] = $tr;
        }
    }
    if (!$blocks) {
        // Kein Tabellenlayout: kleinste Blöcke mit Datum + Link verwenden
        foreach ($xp->query('//div|//li|//article|//section') as $el) {
            if (!preg_match($dateRe, hiorg_text($el)) || !$xp->query('.//a[@href]', $el)->length) {
                continue;
            }
            $inner = false;
            foreach ($xp->query('.//div|.//li|.//article', $el) as $child) {
                if (preg_match($dateRe, hiorg_text($child)) && $xp->query('.//a[@href]', $child)->length) {
                    $inner = true;
                    break;
                }
            }
            if (!$inner) {
                $blocks[] = $el;
            }
        }
    }

    $today = (new DateTimeImmutable('today'))->getTimestamp();
    $items = [];
    $keys = [];
    foreach ($blocks as $b) {
        $cells = [];
        $cellNodes = $b->nodeName === 'tr' ? $xp->query('./td', $b) : $xp->query('./*', $b);
        foreach ($cellNodes as $cn) {
            $t = hiorg_text($cn);
            if ($t !== '') {
                $cells[] = $t;
            }
        }
        $all = hiorg_text($b);
        if (!$cells) {
            $cells = [$all];
        }

        preg_match_all($dateRe, $all, $dm, PREG_SET_ORDER);
        $first = $dm[0];
        $y = strlen($first[3]) === 2 ? 2000 + (int) $first[3] : (int) $first[3];
        $d = DateTimeImmutable::createFromFormat('!Y-n-j', "$y-{$first[2]}-{$first[1]}");
        if (!$d) {
            continue;
        }
        $end = null;
        $last = end($dm);
        if ($last[0] !== $first[0]) {
            $ly = strlen($last[3]) === 2 ? 2000 + (int) $last[3] : (int) $last[3];
            $end = DateTimeImmutable::createFromFormat('!Y-n-j', "$ly-{$last[2]}-{$last[1]}") ?: null;
        }
        if (($end ?? $d)->getTimestamp() < $today) {
            continue;
        }

        $time = '';
        if (preg_match('/(\d{1,2})[:.](\d{2})\s*(?:Uhr)?\s*(?:-|–|—|bis)\s*(\d{1,2})[:.](\d{2})/u', $all, $tm)) {
            $time = sprintf('%02d:%s–%02d:%s', $tm[1], $tm[2], $tm[3], $tm[4]);
        } elseif (preg_match('/\b(\d{1,2})[:.](\d{2})\s*Uhr/u', $all, $tm)) {
            $time = sprintf('ab %02d:%s', $tm[1], $tm[2]);
        }

        $price = '';
        if (preg_match('/(\d{1,4}(?:[.,]\d{2})?)\s*(?:€|EUR|Euro)\b/iu', $all, $pm) || preg_match('/(?:€|EUR)\s*(\d{1,4}(?:[.,]\d{2})?)/iu', $all, $pm)) {
            $price = str_replace('.', ',', preg_replace('/[.,]00$/', '', $pm[1])) . ' €';
        }

        $status = 'open';
        $free = '';
        if (preg_match('/ausgebucht|belegt|voll\b|keine\s+(?:freien\s+)?pl(?:ä|ae)tze/iu', $all)) {
            $status = 'full';
            $free = 'Ausgebucht';
        } elseif (preg_match('/warteliste/iu', $all)) {
            $status = 'full';
            $free = 'Warteliste';
        } elseif (preg_match('/(\d+)\s*(?:von|\/)\s*\d+\s*(?:Pl(?:ä|ae)tzen?)?\s*frei/iu', $all, $fm)
            || preg_match('/(\d+)\s*(?:freie?n?\s*)?(?:Pl(?:ä|ae)tze?n?|Platz)\s*frei|freie?\s*Pl(?:ä|ae)tze\s*:?\s*(\d+)|frei\s*:\s*(\d+)/iu', $all, $fm)) {
            $n = (int) ($fm[1] ?? '' ?: ($fm[2] ?? '' ?: ($fm[3] ?? '0')));
            $free = $n === 1 ? '1 Platz frei' : $n . ' Plätze frei';
            $status = $n === 0 ? 'full' : ($n <= 3 ? 'few' : 'open');
            if ($n === 0) {
                $free = 'Ausgebucht';
            }
        }

        // Anmeldelink: bevorzugt Links mit "anmeld"/"buch", sonst erster Link
        $link = '';
        $links = $xp->query('.//a[@href]', $b);
        foreach ($links as $a) {
            $h = $a->getAttribute('href');
            if (preg_match('/anmeld|buch|register|kurs_?detail|kurse_extern/i', $h . ' ' . $a->textContent)) {
                $link = $h;
                break;
            }
        }
        if ($link === '' && $links->length) {
            $link = $links->item(0)->getAttribute('href');
        }
        $link = hiorg_abs($link);

        // Restliche Zellen = Ort / Beschreibung
        $details = [];
        foreach ($cells as $c) {
            $rest = trim(preg_replace([
                $dateRe,
                '/(\d{1,2})[:.](\d{2})\s*(?:Uhr)?\s*(?:-|–|—|bis)\s*(\d{1,2})[:.](\d{2})\s*(?:Uhr)?/u',
                '/\b\d{1,2}[:.]\d{2}\s*Uhr/u',
                '/\d{1,4}(?:[.,]\d{2})?\s*(?:€|EUR|Euro)\b/iu',
                '/(?:jetzt\s*)?\d+\s*(?:von|\/)\s*\d+\s*(?:Pl(?:ä|ae)tzen?)?\s*frei/iu',
                '/(?:noch\s*)?\d+\s*(?:freie?n?\s*)?(?:Pl(?:ä|ae)tze?n?|Platz)(?:\s*frei)?/iu',
                '/\b(?:Mo|Di|Mi|Do|Fr|Sa|So)(?:ntag|nstag|ttwoch|nnerstag|eitag|mstag)?\b\.?,?/u',
                '/\b(?:anmelden|anmeldung|buchen|details|mehr|ausgebucht|warteliste)\b/iu',
            ], ' ', $c));
            $rest = trim(preg_replace(['/\s{2,}/', '/\(\s*\)/', '/,\s*,/'], [' ', '', ','], $rest), " \t\n\r\0\x0B,;·|-–");
            if (mb_strlen($rest) > 1 && !in_array($rest, $details, true)) {
                $details[] = $rest;
            }
        }
        $details = implode(' · ', $details);
        if (mb_strlen($details) > 160) {
            $details = mb_substr($details, 0, 157) . '…';
        }

        $key = $d->format('Ymd') . '|' . $time . '|' . $link . '|' . $details;
        if (isset($keys[$key])) {
            continue;
        }
        $keys[$key] = true;

        $items[] = [
            'ts' => $d->getTimestamp(),
            'date' => $d,
            'end' => $end,
            'time' => $time,
            'price' => $price ?: (string) ($course['price'] ?? ''),
            'free' => $free,
            'status' => $status,
            'details' => $details,
            'link' => $link ?: hiorg_list_url((string) ($course['hiorg_id'] ?? '')),
            'course' => $course,
        ];
    }
    usort($items, fn($a, $b) => $a['ts'] <=> $b['ts']);
    return $items;
}

/** Liest die Termin-Boxen des HiOrg-Layouts „kurse_extern.php“. */
function hiorg_parse_boxes(DOMXPath $xp, DOMNodeList $boxes, array $course): array
{
    $cls = fn(string $c) => './/*[contains(concat(" ", normalize-space(@class), " "), " ' . $c . ' ")]';
    $get = function (DOMNode $box, string $c) use ($xp, $cls): string {
        $n = $xp->query($cls($c), $box)->item(0);
        return $n ? hiorg_text($n) : '';
    };
    $today = (new DateTimeImmutable('today'))->getTimestamp();
    $price = hiorg_price((string) ($course['hiorg_id'] ?? '')) ?: (string) ($course['price'] ?? '');
    $items = [];
    foreach ($boxes as $box) {
        preg_match_all('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', $get($box, 'termine-start-ende-datum'), $dm, PREG_SET_ORDER);
        if (!$dm) {
            continue;
        }
        $d = DateTimeImmutable::createFromFormat('!Y-n-j', "{$dm[0][3]}-{$dm[0][2]}-{$dm[0][1]}");
        $last = end($dm);
        $end = $last[0] !== $dm[0][0] ? (DateTimeImmutable::createFromFormat('!Y-n-j', "{$last[3]}-{$last[2]}-{$last[1]}") ?: null) : null;
        if (!$d || ($end ?? $d)->getTimestamp() < $today) {
            continue;
        }
        $time = '';
        if (preg_match('/(\d{1,2})[:.](\d{2})\s*(?:-|–|bis)\s*(\d{1,2})[:.](\d{2})/u', $get($box, 'div-termine-zeit'), $tm)) {
            $time = sprintf('%02d:%s–%02d:%s', $tm[1], $tm[2], $tm[3], $tm[4]);
        }
        $freeText = $get($box, 'freie-plaetze-container');
        $status = 'open';
        $free = '';
        if (preg_match('/ausgebucht|warteliste|keine/iu', $freeText)) {
            $status = 'full';
            $free = stripos($freeText, 'warteliste') !== false ? 'Warteliste' : 'Ausgebucht';
        } elseif (preg_match('/(\d+)\s*(?:von\s*\d+)?/u', $freeText, $fm)) {
            $n = (int) $fm[1];
            $free = $n === 0 ? 'Ausgebucht' : ($n === 1 ? '1 Platz frei' : $n . ' Plätze frei');
            $status = $n === 0 ? 'full' : ($n <= 3 ? 'few' : 'open');
        }
        $a = $xp->query('.//a[contains(@class, "button-anmelden") or contains(@href, "tn_anmeldung")]', $box)->item(0);
        $link = $a ? hiorg_abs($a->getAttribute('href')) : '';
        if ($link === '' && $status !== 'full') {
            $status = 'full';
            $free = $free ?: 'Anmeldung geschlossen';
        }
        $details = array_filter([$get($box, 'kurs-ort'), $get($box, 'kurs-bemerkung')]);
        $items[] = [
            'ts' => $d->getTimestamp(),
            'date' => $d,
            'end' => $end,
            'time' => $time,
            'price' => $price,
            'free' => $free,
            'status' => $status,
            'details' => implode(' · ', $details),
            'title' => $get($box, 'kurstyp-bezeichung'),
            'link' => $link ?: hiorg_list_url((string) ($course['hiorg_id'] ?? '')),
            'bookable' => $link !== '',
            'kid' => preg_match('/[?&]kid=(\d+)/', $link, $km) ? $km[1] : '',
            'course' => $course,
        ];
    }
    usort($items, fn($a, $b) => $a['ts'] <=> $b['ts']);
    return $items;
}

/**
 * Kursgebühr aus dem HiOrg-Anmeldeformular (erster Termin der Liste), 12 Std. zwischengespeichert.
 * So stimmt der angezeigte Preis immer mit HiOrg überein.
 */
function hiorg_price(string $id): string
{
    $id = preg_replace('/\D/', '', $id);
    if ($id === '') {
        return '';
    }
    $f = CACHE_DIR . '/hiorg-price-' . $id . '.txt';
    if (is_file($f) && filemtime($f) > time() - 43200) {
        return trim((string) file_get_contents($f));
    }
    $list = is_file(hiorg_cache_file($id)) ? (string) file_get_contents(hiorg_cache_file($id)) : '';
    if (!preg_match('/href="(tn_anmeldung\.php\?[^"]+)"/', $list, $m)) {
        return is_file($f) ? trim((string) file_get_contents($f)) : '';
    }
    $html = '';
    if (function_exists('curl_init')) {
        $ch = curl_init(hiorg_abs($m[1]));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_ENCODING => '', CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; DRK-Website Kursliste)']);
        $html = (string) curl_exec($ch);
        curl_close($ch);
    }
    $price = '';
    if (preg_match('/(\d{1,4}),(\d{2})\s*(?:&euro;|€|Eur)/iu', $html, $pm)) {
        $price = $pm[1] . ($pm[2] !== '00' ? ',' . $pm[2] : '') . ' €';
    }
    if ($html !== '') {
        @file_put_contents($f, $price, LOCK_EX);
    } else {
        @touch($f, time() - 43200 + 600);
    }
    return $price;
}

/** Text eines Knotens; Textteile mit Leerzeichen getrennt, damit Zellen nicht verkleben. */
function hiorg_text(DOMNode $n): string
{
    $parts = [];
    foreach ((new DOMXPath($n->ownerDocument))->query('.//text()', $n) as $t) {
        $parts[] = $t->nodeValue;
    }
    return hiorg_clean(implode(' ', $parts));
}

/** HiOrg liefert teils HTML5-Entities (&lpar; &NewLine; …) als Text – dekodieren und glätten. */
function hiorg_clean(string $s): string
{
    $s = preg_replace('/&(?:amp;)?NewLine;/i', ', ', $s);
    $s = html_entity_decode(html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = str_replace("\xC2\xA0", ' ', $s);
    $s = preg_replace('/\s+/u', ' ', $s);
    $s = preg_replace('/\s*,(\s*,)+/', ',', $s);
    return trim(preg_replace('/\s+,/', ',', $s), " ,");
}

function hiorg_abs(string $href): string
{
    $href = trim(html_entity_decode($href));
    if ($href === '' || str_starts_with($href, '#') || stripos($href, 'javascript:') === 0) {
        return '';
    }
    if (preg_match('~^https?://~i', $href)) {
        return $href;
    }
    if (str_starts_with($href, '//')) {
        return 'https:' . $href;
    }
    return HIORG_BASE . ltrim($href, '/');
}

/** Ort (Stadt) aus der Adresse, z. B. „…, 27283 Verden (Aller)“ → „Verden“. */
function hiorg_town(string $details): string
{
    if (preg_match('/\b\d{5}\s+([^,(·]+)/u', $details, $m)) {
        return trim($m[1]);
    }
    return '';
}

/** Auswahl-Optionen für den Terminfilter aus den vorhandenen Terminen. */
function date_filter_options(array $items): array
{
    static $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    static $days = [1 => 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    $o = ['ort' => [], 'monat' => [], 'wtag' => []];
    foreach ($items as $it) {
        if ($t = hiorg_town($it['details'])) {
            $o['ort'][$t] = $t;
        }
        $o['monat'][$it['date']->format('Y-m')] = $months[(int) $it['date']->format('n')] . ' ' . $it['date']->format('Y');
        $o['wtag'][(int) $it['date']->format('N')] = $days[(int) $it['date']->format('N')];
    }
    ksort($o['ort']);
    ksort($o['monat']);
    ksort($o['wtag']);
    return $o;
}

/** Einzelnen Termin anhand der HiOrg-Kurs-ID (kid) finden. */
function hiorg_find(array $course, string $kid): ?array
{
    foreach (hiorg_dates($course)['items'] as $it) {
        if (($it['kid'] ?? '') === $kid) {
            return $it;
        }
    }
    return null;
}

/** Kurze Ortsangabe: „Aller-Weser-Zentrum, Lindhooper Straße 57, 27283 Verden (Aller)“ → „Aller-Weser-Zentrum · Verden“. */
function hiorg_place_short(string $details): string
{
    $town = hiorg_town($details);
    $name = trim(explode(',', $details)[0]);
    if ($name === '' || preg_match('/^\d{5}\b/', $name) || preg_match('/(straße|str\.|weg|platz|allee)\s*\d/iu', $name)) {
        return $town ?: $details;
    }
    // „DRK Zentrum Achim“ enthält den Ort schon
    return ($town && mb_stripos($name, $town) === false) ? $name . ' · ' . $town : $name;
}

/**
 * Ausgabe einer Terminliste (auch für /api/termine per fetch).
 * Optionen: limit, show_course (Kursname je Zeile), show_price, months (Monatsüberschriften).
 */
function render_dates(array $items, array $opt = []): string
{
    static $months = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    $limit = $opt['limit'] ?? 0;
    $showCourse = $opt['show_course'] ?? false;
    $showPrice = $opt['show_price'] ?? $showCourse;
    $groupMonths = $opt['months'] ?? false;
    $inline = site('hiorg_booking') !== 'tab';
    if ($limit) {
        $items = array_slice($items, 0, $limit);
    }
    $lastMonth = '';
    ob_start();
    foreach ($items as $it) {
        /** @var DateTimeImmutable $d */
        $d = $it['date'];
        $c = $it['course'];
        $when = de_date($d, 'WW, D. MMM');
        if ($it['end']) {
            $when .= ' – ' . de_date($it['end'], 'WW, D. MMM');
        }
        $full = $it['status'] === 'full' || empty($it['bookable'] ?? true);
        if ($groupMonths && $d->format('Y-m') !== $lastMonth) {
            $lastMonth = $d->format('Y-m');
            echo '<li class="dates__month" data-month="' . $lastMonth . '">' . $months[(int) $d->format('n')] . ' ' . $d->format('Y') . '</li>';
        }
        $book = $inline && !empty($it['kid']) ? url('termine/' . ($c['slug'] ?? '') . '/anmeldung/' . $it['kid']) : $it['link'];
        $time = $it['time'] ? $it['time'] . ' Uhr' : '';
        $place = $it['details'] ? hiorg_place_short($it['details']) : '';
        ?>
<li class="date<?= $full ? ' date--full' : '' ?>" data-kurs="<?= e($c['slug'] ?? '') ?>" data-ort="<?= e(hiorg_town($it['details'])) ?>" data-monat="<?= $d->format('Y-m') ?>" data-wtag="<?= $d->format('N') ?>">
  <time class="date__cal" datetime="<?= $d->format('Y-m-d') ?>"><span><?= de_date($d, 'MMM') ?></span><strong><?= $d->format('j') ?></strong></time>
  <div class="date__info">
    <span class="date__title"><?= $showCourse ? e($c['title'] ?? '') : e($when) ?></span>
    <span class="date__meta">
      <span><?= icon('clock') ?><?= e(trim(($showCourse ? $when : '') . ($showCourse && $time ? ' · ' : '') . $time)) ?></span>
      <?php if ($place): ?><span title="<?= e($it['details']) ?>"><?= icon('pin') ?><?= e($place) ?></span><?php endif; ?>
    </span>
  </div>
  <div class="date__side">
    <?php if ($full): ?>
      <span class="tag-full"><?= e($it['free'] ?: 'Ausgebucht') ?></span>
    <?php else: ?>
      <?php if ($it['free']): ?><span class="date__free date__free--<?= e($it['status']) ?>"><span class="dot"></span><?= e($it['free']) ?></span><?php endif; ?>
      <?php if ($showPrice && $it['price']): ?><span class="date__price"><?= e($it['price']) ?></span><?php endif; ?>
      <a class="btn btn--red btn--sm" href="<?= e($book) ?>"<?= $book === $it['link'] ? ' target="_blank" rel="noopener"' : '' ?>>Buchen</a>
    <?php endif; ?>
  </div>
</li>
<?php
    }
    return (string) ob_get_clean();
}
