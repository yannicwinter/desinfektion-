<?php
/** Kopf- und Fußbereich aller öffentlichen Seiten inkl. SEO-Metadaten. */
declare(strict_types=1);

/**
 * @param array $meta title, description, path, active, schema (Liste JSON-LD-Objekte), noindex, breadcrumb [[Name, Pfad], ...]
 */
function layout_start(array $meta): void
{
    $title = $meta['title'] ?? site('name');
    $desc = $meta['description'] ?? '';
    $canonical = abs_url($meta['path'] ?? '/');
    $active = $meta['active'] ?? '';
    $GLOBALS['__active'] = $active;
    $og = site('default_og_image') ?: (slot_image('home') ? abs_url(ltrim(strtok((string) slot_image('home'), '?'), '/')) : '');

    $schema = $meta['schema'] ?? [];
    $schema[] = org_schema();
    if (!empty($meta['breadcrumb'])) {
        $pos = 1;
        $list = [['@type' => 'ListItem', 'position' => $pos++, 'name' => 'Start', 'item' => abs_url('/')]];
        foreach ($meta['breadcrumb'] as [$name, $p]) {
            $list[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $name, 'item' => abs_url($p)];
        }
        $schema[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }
    ?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<?php if (!empty($meta['noindex'])): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="de_DE">
<meta property="og:site_name" content="<?= e(site('name')) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if ($og): ?><meta property="og:image" content="<?= e($og) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#ffffff">
<meta name="geo.region" content="DE-NI">
<meta name="geo.placename" content="Verden (Aller)">
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preload" href="<?= url('assets/fonts/inter-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= url('assets/fonts/inter-latin-600-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php foreach ($schema as $s): ?>
<script type="application/ld+json"><?= json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body class="page-<?= e($active ?: 'home') ?>">
<a class="skip" href="#inhalt">Zum Inhalt springen</a>
<div class="topbar">
  <div class="wrap topbar__in">
    <span><?= icon('shield') ?><?= e(site('topbar') ?: site('org')) ?></span>
    <nav aria-label="Kontakt">
      <a href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?><?= e(site('phone')) ?></a>
      <a href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?><?= e(site('email')) ?></a>
    </nav>
  </div>
</div>
<header class="header">
  <div class="wrap header__in">
    <a class="brand" href="<?= url('/') ?>" aria-label="<?= e(site('name')) ?> · <?= e(site('org')) ?> – Startseite">
      <picture>
        <img src="<?= asset('img/logo-drk-mittelweser.png') ?>" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V.">
      </picture>
    </a>
    <nav class="nav" aria-label="Hauptnavigation">
      <ul class="nav__list">
        <li><a href="<?= url('/') ?>"<?= ($meta['path'] ?? '') === '/' ? ' aria-current="page"' : '' ?>>Start</a></li>
        <?php foreach (nav_items() as $slug => $label): ?>
        <li><a href="<?= url($slug) ?>"<?= $active === $slug || ($slug === 'termine' && $active === 'anmeldung') ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <a class="btn btn--red btn--sm header__cta" href="<?= url('termine') ?>" aria-label="Kurs buchen"><?= icon('calendar') ?><span>Kurs buchen</span></a>
  </div>
</header>
<main id="inhalt">
<?php
}

function layout_end(): void
{
    ?>
</main>
<footer class="footer<?= !empty($GLOBALS['__flush']) ? ' footer--flush' : '' ?>">
  <div class="wrap">
    <div class="footer__grid">
      <div class="footer__brand">
        <a href="<?= url('/') ?>" aria-label="Startseite"><img src="<?= asset('img/logo-drk-mittelweser-weiss.png') ?>" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V." loading="lazy"></a>
        <p><?= e(site('name')) ?><br><?= e(site('org')) ?><br><?= e(site('street')) ?> · <?= e(site('zip')) ?> <?= e(site('city')) ?></p>
      </div>
      <div>
        <h2 class="footer__h">Kurse</h2>
        <ul>
          <?php foreach (array_slice(bookable_courses(), 0, 5) as $c): ?>
          <li><a href="<?= course_url($c) ?>"><?= e($c['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2 class="footer__h">Angebot</h2>
        <ul>
          <li><a href="<?= url('erste-hilfe') ?>">Erste Hilfe</a></li>
          <li><a href="<?= url('brandschutz') ?>">Brandschutz</a></li>
          <li><a href="<?= url('arbeitssicherheit') ?>">Für Unternehmen</a></li>
          <li><a href="<?= url('termine') ?>">Alle Termine</a></li>
          <li><a href="<?= url('faq') ?>">Häufige Fragen</a></li>
        </ul>
      </div>
      <div>
        <h2 class="footer__h">Kontakt</h2>
        <ul>
          <li><a href="tel:<?= e(site('phone_link')) ?>"><?= e(site('phone')) ?></a></li>
          <li><a href="mailto:<?= e(site('email')) ?>"><?= e(site('email')) ?></a></li>
          <?php if (site('instagram')): ?><li><a href="<?= e(site('instagram')) ?>" target="_blank" rel="noopener">Instagram</a></li><?php endif; ?>
          <li><a href="<?= url('kontakt') ?>">Kontaktformular</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span>© <?= date('Y') ?> <?= e(site('org')) ?></span>
      <nav aria-label="Rechtliches"><a href="<?= url('impressum') ?>">Impressum</a><a href="<?= url('datenschutz') ?>">Datenschutz</a></nav>
    </div>
  </div>
</footer>
<?php
    $tabs = [['', 'Start', 'home'], ['erste-hilfe', 'Erste Hilfe', 'heart'], ['termine', 'Termine', 'calendar'], ['brandschutz', 'Brandschutz', 'flame']];
    $moreActive = in_array($GLOBALS['__active'] ?? '', ['arbeitssicherheit', 'faq', 'kontakt'], true);
?>
<nav class="tabbar" aria-label="Schnellnavigation">
  <?php foreach ($tabs as [$slug, $label, $ic]): $on = ($GLOBALS['__active'] ?? '') === $slug || ($slug === 'termine' && ($GLOBALS['__active'] ?? '') === 'anmeldung'); ?>
  <a class="tabbar__item<?= $slug === 'termine' ? ' tabbar__item--main' : '' ?>" href="<?= url($slug === '' ? '/' : $slug) ?>"<?= $on ? ' aria-current="page"' : '' ?>><span class="tabbar__icon"><?= icon($ic) ?></span><span><?= $label ?></span></a>
  <?php endforeach; ?>
  <button class="tabbar__item" type="button" data-more-open aria-haspopup="dialog" aria-controls="mehr"<?= $moreActive ? ' aria-current="page"' : '' ?>><span class="tabbar__icon"><?= icon('grid') ?></span><span>Mehr</span></button>
</nav>
<div class="more" id="mehr" role="dialog" aria-modal="true" aria-label="Weitere Seiten" hidden>
  <div class="more__backdrop" data-more-close></div>
  <div class="more__sheet">
    <span class="more__grip" aria-hidden="true"></span>
    <ul class="more__list">
      <li><a href="<?= url('arbeitssicherheit') ?>"><?= icon('building') ?><span><strong>Für Unternehmen</strong><small>Arbeitssicherheit · Fachkraft · Schulung im Betrieb</small></span></a></li>
      <li><a href="<?= url('faq') ?>"><?= icon('search') ?><span><strong>Häufige Fragen</strong><small>Prüfung, Gültigkeit, Kosten</small></span></a></li>
      <li><a href="<?= url('kontakt') ?>"><?= icon('users') ?><span><strong>Kontakt</strong><small>Ansprechpersonen & Formular</small></span></a></li>
    </ul>
    <div class="more__actions">
      <a class="btn btn--ghost" href="tel:<?= e(site('phone_link')) ?>"><?= icon('phone') ?> Anrufen</a>
      <a class="btn btn--ghost" href="mailto:<?= e(site('email')) ?>"><?= icon('mail') ?> E-Mail</a>
    </div>
    <button class="more__close" type="button" data-more-close>Schließen</button>
  </div>
</div>
<?php if (($GLOBALS['__active'] ?? '') !== 'anmeldung'): ?>
<?= kursfinder_markup() ?>
<script src="<?= asset('js/kursfinder.js') ?>" defer></script>
<?php endif; ?>
<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
<?php
}

function org_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => ['NGO', 'LocalBusiness'],
        '@id' => abs_url('/') . '#org',
        'name' => site('org') . ' – ' . site('name'),
        'alternateName' => site('name'),
        'url' => abs_url('/'),
        'logo' => abs_url('assets/img/favicon.svg'),
        'telephone' => site('phone_link'),
        'email' => site('email'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => site('street'),
            'postalCode' => site('zip'),
            'addressLocality' => site('city'),
            'addressRegion' => site('region'),
            'addressCountry' => 'DE',
        ],
        'areaServed' => ['Landkreis Verden', 'Mittelweser', 'Verden (Aller)', 'Achim'],
        'sameAs' => array_values(array_filter([site('instagram')])),
    ];
}

/** schema.org Course inkl. kommender Termine (für Google-Kurs-Snippets). */
function course_schema(array $c, array $dates = []): array
{
    $s = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $c['title'],
        'description' => preg_replace('/\s+/', ' ', $c['teaser'] . ' ' . $c['text']),
        'url' => abs_url(!empty($c['hiorg_id']) ? 'termine/' . $c['slug'] : ltrim(course_url($c), '/')),
        'inLanguage' => 'de',
        'provider' => ['@id' => abs_url('/') . '#org', '@type' => 'Organization', 'name' => site('org'), 'sameAs' => abs_url('/')],
    ];
    if ($dates) {
        $s['hasCourseInstance'] = [];
        foreach (array_slice($dates, 0, 10) as $d) {
            $inst = [
                '@type' => 'CourseInstance',
                'courseMode' => 'Onsite',
                'startDate' => $d['date']->format('Y-m-d'),
                'location' => ['@type' => 'Place', 'name' => $d['details'] ?: site('org'), 'address' => site('city')],
            ];
            if ($d['end']) {
                $inst['endDate'] = $d['end']->format('Y-m-d');
            }
            $s['hasCourseInstance'][] = $inst;
        }
    }
    if (preg_match('/(\d+(?:,\d+)?)\s*€/', (string) $c['price'], $m)) {
        $s['offers'] = ['@type' => 'Offer', 'category' => 'Paid', 'price' => str_replace(',', '.', $m[1]), 'priceCurrency' => 'EUR'];
    }
    return $s;
}

/** Brotkrumen: [[Name, Pfad], ...], der letzte Eintrag ist die aktuelle Seite. */
function crumbs_html(array $crumbs): string
{
    if (!$crumbs) {
        return '';
    }
    $h = '<nav class="crumbs" aria-label="Brotkrumen"><a href="' . url('/') . '">Start</a>';
    $last = count($crumbs) - 1;
    foreach ($crumbs as $i => [$n, $p]) {
        $h .= '<span aria-hidden="true">›</span>' . ($i === $last ? '<span aria-current="page">' . e($n) . '</span>' : '<a href="' . url($p) . '">' . e($n) . '</a>');
    }
    return $h . '</nav>';
}

/**
 * Seitenkopf. Mit $photo: Foto komplett in eigenem Format (`hero_media`), Titel am Desktop links im Verlauf,
 * mobil darunter. $overlap = true lässt unten Platz für eine überlappende Leiste (Suche, Infoleiste).
 */
function page_head(string $eyebrow, string $title, string $lead, array $crumbs = [], string $extra = '', string $photo = '', bool $overlap = false): void
{
    if ($photo && slot_image($photo)) {
        ?>
<section class="hero hero--photo hero--page<?= $overlap ? ' hero--overlap' : ' hero--short' ?>">
  <?= hero_media($photo, $title) ?>
  <div class="hero__in">
    <?= crumbs_html($crumbs) ?>
    <h1><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="hero__lead"><?= e($lead) ?></p><?php endif; ?>
    <?php if ($extra): ?><div class="hero__acts"><?= $extra ?></div><?php endif; ?>
  </div>
</section>
<?php
        return;
    }
    ?>
<section class="phead">
  <div class="wrap">
    <?= crumbs_html($crumbs) ?>
    <h1 class="h1"><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
    <?php if ($extra): ?><div class="btn-row" style="margin-top:22px"><?= $extra ?></div><?php endif; ?>
  </div>
</section>
<?php
}

/** Abschnittskopf: Dachzeile, Überschrift, Text, Link rechts. */
function shead(string $eyebrow, string $title, string $text = '', string $link = '', string $href = ''): string
{
    return '<div class="shead reveal"><div>' . ($eyebrow ? '<p class="eyebrow">' . e($eyebrow) . '</p>' : '') . '<h2>' . e($title) . '</h2>'
        . ($text ? '<p>' . e($text) . '</p>' : '') . '</div>'
        . ($link ? '<a class="alink" href="' . e($href) . '">' . e($link) . ' ' . icon('arrow') . '</a>' : '') . '</div>';
}

/** Freie, buchbare Termine eines Kurses (je Aufruf zwischengespeichert). */
function free_dates(array $c): array
{
    static $memo = [];
    $id = trim((string) ($c['hiorg_id'] ?? ''));
    if ($id === '') {
        return [];
    }
    $key = $id . '|' . $c['slug'];
    if (!isset($memo[$key])) {
        $memo[$key] = array_values(array_filter(hiorg_dates($c)['items'], fn($it) => $it['status'] !== 'full' && !empty($it['kid'])));
    }
    return $memo[$key];
}

/** Wert einer Kurs-Angabe („Dauer: …“) aus dem Feld „Fakten“. */
function course_fact(array $c, string $label): string
{
    foreach (pairs($c['facts'] ?? '') as [$k, $v]) {
        if (mb_strtolower($k) === mb_strtolower($label)) {
            return $v;
        }
    }
    return '';
}

/** Kurze Dauer für Karten („9 UE · 08:30–16:30 Uhr“ → „9 UE“). */
function course_duration(array $c): string
{
    return trim(explode('·', course_fact($c, 'Dauer'))[0]);
}

/** Kurskarte mit Foto, Preis, Dauer und nächstem freien Termin. */
function course_card(array $c): string
{
    $dates = free_dates($c);
    $towns = array_values(array_unique(array_filter(array_map(fn($it) => hiorg_town($it['details']), $dates))));
    $tag = $c['category'] === 'brandschutz' ? ($c['group'] ?: 'Brandschutz') : ($c['group'] ?: 'Erste Hilfe');
    $dur = course_duration($c);
    if ($dates) {
        $next = '<p class="kcard__next"><span class="dot"></span>Nächster Termin: ' . e(de_date($dates[0]['date'], 'WW, D. MMM')) . '</p>';
    } elseif (!empty($c['hiorg_id'])) {
        $next = '<p class="kcard__next kcard__next--q">Neue Termine folgen</p>';
    } else {
        $next = '<p class="kcard__next kcard__next--q">Termin auf Anfrage · auch im Betrieb</p>';
    }
    $meta = ($dur ? '<span>' . icon('clock') . e($dur) . '</span>' : '') . ($towns ? '<span>' . icon('pin') . e(implode(' · ', array_slice($towns, 0, 2))) . '</span>' : '');
    return '<a class="kcard reveal" id="' . e($c['slug']) . '" href="' . course_url($c) . '">'
        . '<div class="kcard__img">' . course_media($c, '') . '<span class="kcard__tag">' . e($tag) . '</span></div>'
        . '<div class="kcard__body"><h3>' . e($c['title']) . '</h3><p>' . e($c['teaser']) . '</p>'
        . ($meta ? '<div class="kcard__meta">' . $meta . '</div>' : '') . $next
        . '<div class="kcard__foot"><span class="price">' . e($c['price'] ?: 'auf Anfrage') . '</span><span class="alink">Zum Kurs ' . icon('arrow') . '</span></div></div></a>';
}

/** Themenkarte mit Foto (Startseite, Wegweiser, Leistungen). */
function topic_card(string $img, string $title, string $text, string $link, string $href): string
{
    return '<a class="tcard reveal" href="' . e($href) . '"><div class="tcard__img">' . $img . '</div>'
        . '<div class="tcard__body"><h3>' . e($title) . '</h3><p>' . e($text) . '</p><span class="alink">' . e($link) . ' ' . icon('arrow') . '</span></div></a>';
}

/** Ansprechperson aus den Kontakten (nach Name, sonst die erste). */
function contact_person(string $name = ''): ?array
{
    $all = content()['contacts'] ?? [];
    foreach ($all as $p) {
        if ($name !== '' && mb_stripos($p['name'], $name) !== false) {
            return $p;
        }
    }
    return $all[0] ?? null;
}

/** Ansprechperson eines Kurses (Feld „contact“), sonst Standard je Bereich. */
function course_contact(array $c): ?array
{
    $name = trim((string) ($c['contact'] ?? '')) ?: ($c['category'] === 'brandschutz' ? 'Matthias True' : 'Jan Wille');
    return contact_person($name);
}

/** Person nach Aufgabe (z. B. „Kosten“), ohne Rückfall auf die erste Person. */
function contact_by_role(string $role): ?array
{
    foreach (content()['contacts'] ?? [] as $p) {
        if (mb_stripos($p['role'], $role) !== false) {
            return $p;
        }
    }
    return null;
}

/** Kasten mit Foto, Name, Aufgabe, E-Mail und Telefon einer Ansprechperson. */
function person_card(?array $p, string $title = 'Noch Fragen?'): string
{
    if (!$p) {
        return '';
    }
    $img = person_photo($p);
    $mail = $p['email'] ?: site('email');
    $tel = $p['phone'] ?: site('phone');
    $telLink = preg_replace('/[^\d+]/', '', preg_replace('/\s*\(.*\)$/', '', $tel));
    if (str_starts_with($telLink, '0')) {
        $telLink = '+49' . substr($telLink, 1);
    }
    return '<div class="pcard"><p class="pcard__t">' . e($title) . '</p><div class="pcard__p">'
        . ($img ? '<img src="' . e($img) . '" alt="' . e($p['name']) . '" loading="lazy" width="64" height="64">' : '<span class="pcard__ph">' . icon('users') . '</span>')
        . '<div><b>' . e($p['name']) . '</b><span>' . e($p['role']) . '</span></div></div>'
        . '<ul class="pcard__c"><li><a href="mailto:' . e($mail) . '">' . icon('mail') . e($mail) . '</a></li>'
        . '<li><a href="tel:' . e($telLink) . '">' . icon('phone') . e($tel) . '</a></li></ul></div>';
}

function person_photo(?array $p): string
{
    return ($p && !empty($p['photo']) && is_file(ROOT . '/' . ltrim($p['photo'], '/'))) ? url($p['photo']) : '';
}

/** Dunkle Box „Persönliche Beratung“ mit Foto der Ansprechperson. */
function advice_box(?array $p, string $text = ''): string
{
    $img = person_photo($p);
    $ini = $p ? implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', $p['name']), 0, 2))) : 'DRK';
    $text = $text ?: ($p ? $p['name'] . ' · ' . $p['role'] . ' – wir helfen dir, den passenden Kurs zu finden.' : '');
    $GLOBALS['__flush'] = true; // Box steht direkt über dem Footer
    return '<section class="advice"><div class="wrap"><div class="cta reveal">'
        . '<svg class="cta__x" viewBox="0 0 100 100" aria-hidden="true"><path d="M35 0h30v35h35v30H65v35H35V65H0V35h35z"/></svg>'
        . ($img ? '<img class="cta__face" src="' . e($img) . '" alt="' . e($p['name']) . '" loading="lazy" width="96" height="96">' : '<span class="cta__ini">' . e($ini) . '</span>')
        . '<div><h2>' . e(page('home', 'cta_title') ?: 'Persönliche Beratung') . '</h2><p>' . e($text) . '</p></div>'
        . '<div class="btn-row"><a class="btn btn--red" href="tel:' . e(site('phone_link')) . '">' . icon('phone') . e(site('phone')) . '</a>'
        . '<a class="btn btn--line" href="mailto:' . e(($p['email'] ?? '') ?: site('email')) . '">' . icon('mail') . 'E-Mail schreiben</a></div>'
        . '</div></div></section>';
}

/** Rotes Band mit Text und Button. */
function band(string $title, string $text, string $btn, string $href): string
{
    if ($title === '') {
        return '';
    }
    return '<section class="band"><svg class="band__x" viewBox="0 0 100 100" aria-hidden="true"><path d="M35 0h30v35h35v30H65v35H35V65H0V35h35z"/></svg>'
        . '<div class="wrap band__in"><div><h2>' . e($title) . '</h2>' . ($text ? '<p>' . e($text) . '</p>' : '') . '</div>'
        . ($btn ? '<a class="btn btn--white" href="' . e($href) . '">' . e($btn) . ' ' . icon('arrow') . '</a>' : '') . '</div></section>';
}

/** Häufige Fragen als Akkordeon (Liste aus content.json → faq). */
function faq_list(array $items): string
{
    $h = '<div class="acc">';
    foreach ($items as $f) {
        $h .= '<details class="acc__item"><summary class="acc__sum"><span class="acc__title">' . e($f['q']) . '</span>' . icon('plus', 'i acc__chev') . '</summary>'
            . '<div class="acc__body"><div class="prose">' . rich($f['a']) . '</div></div></details>';
    }
    return $h . '</div>';
}

/** Tabelle „Welcher Kurs passt?“ (Zeilen: „Kurs | Spalte | …“, erste Zeile = Überschriften). */
function compare_table(string $data, bool $compact = false): string
{
    $rows = lines($data);
    if (count($rows) < 2) {
        return '';
    }
    $h = '<div class="compare' . ($compact ? ' compare--compact' : ' reveal') . '"><h2 class="' . ($compact ? 'h5' : 'group-title') . '">Welcher Kurs passt?</h2><div class="compare__scroll"><table><thead><tr>';
    foreach (array_map('trim', explode('|', $rows[0])) as $th) {
        $label = e($th);
        if ($compact) { // schmale Spalte: kürzen und Trennstellen setzen
            $label = strtr($label, ['Abrechnung über BG' => 'über BG', 'Führerschein' => 'Führer&shy;schein', 'Selbstzahler' => 'Selbst&shy;zahler']);
        }
        $h .= '<th scope="col">' . $label . '</th>';
    }
    $h .= '</tr></thead><tbody>';
    foreach (array_slice($rows, 1) as $line) {
        $cells = array_map('trim', explode('|', $line));
        $h .= '<tr><th scope="row">' . e(array_shift($cells)) . '</th>';
        foreach ($cells as $cell) {
            $k = mb_strtolower($cell);
            $h .= '<td class="' . ($k === 'ja' ? 'yes' : ($k === 'nein' ? 'no' : 'part')) . '">'
                . ($k === 'ja' ? icon('check') . '<span class="sr-only">ja</span>' : ($k === 'nein' ? '<span aria-hidden="true">–</span><span class="sr-only">nein</span>' : e($cell)))
                . '</td>';
        }
        $h .= '</tr>';
    }
    return $h . '</tbody></table></div></div>';
}

/** Foto eines Bildplatzes als <img> (Upload oder Standardfoto), sonst leer. */
function photo(string $slot, string $alt, string $class = '', bool $lazy = true): string
{
    $src = slot_image($slot);
    if (!$src) {
        return '';
    }
    return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt) . '"' . ($lazy ? ' loading="lazy"' : ' fetchpriority="high"') . ' decoding="async">'
        . (is_ai_slot($slot) ? '<span class="ki-tag" title="Dieses Bild wurde mit künstlicher Intelligenz erzeugt.">KI-generiert</span>' : '');
}

/** Kopffoto: komplett sichtbar (eigenes Seitenverhältnis, Höhe begrenzt); freie Ränder füllt eine unscharfe Kopie. */
function hero_media(string $slot, string $alt): string
{
    $src = slot_image($slot);
    return '<div class="hero__blur" style="background-image:url(\'' . e($src) . '\')" aria-hidden="true"></div>'
        . '<div class="hero__media" style="--ar:' . slot_ratio($slot) . '">' . photo($slot, $alt, 'hero__img', false) . '</div>';
}

/** Bild-Slots mit KI-erzeugten Fotos (Admin → Allgemein, „KI-Bilder“) – werden sichtbar gekennzeichnet. */
function is_ai_slot(string $slot): bool
{
    static $slots = null;
    $slots ??= array_filter(array_map('trim', preg_split('/[,\s]+/', (string) site('ki_bilder'))));
    return in_array($slot, $slots, true);
}

/** Kursbild: Foto, sonst gezeichnetes Motiv. */
function course_media(array $c, string $class = 'card__photo'): string
{
    return photo('kurs-' . $c['slug'], $c['title'], $class) ?: illus_course($c);
}
