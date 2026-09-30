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
<link rel="preload" href="<?= url('assets/fonts/figtree-latin-400-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= url('assets/fonts/figtree-latin-500-normal.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<?php foreach ($schema as $s): ?>
<script type="application/ld+json"><?= json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body class="page-<?= e($active ?: 'home') ?>">
<a class="skip" href="#inhalt">Zum Inhalt springen</a>
<header class="header">
  <div class="wrap header__top">
    <a class="brand" href="<?= url('/') ?>" aria-label="<?= e(site('name')) ?> · <?= e(site('org')) ?> – Startseite">
      <picture>
        <img src="<?= asset('img/logo-drk-mittelweser.png') ?>" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V.">
      </picture>
    </a>
    <nav class="nav" aria-label="Hauptnavigation">
      <ul class="nav__list">
        <?php foreach (nav_items() as $slug => $label): ?>
        <li><a href="<?= url($slug === '' ? '/' : $slug) ?>"<?= $active === $slug || ($slug === '' && $active === '') ? ' aria-current="page"' : '' ?><?= $slug === '' ? ' class="nav__home" aria-label="Startseite"' : '' ?>><?= $slug === '' ? icon('home') . '<span>' . e($label) . '</span>' : nav_label($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header__actions">
      <a class="header__phone" href="tel:<?= e(site('phone_link')) ?>" aria-label="Anrufen: <?= e(site('phone')) ?>"><?= icon('phone') ?><span><?= e(site('phone')) ?></span></a>
      <a class="btn btn--red btn--sm" href="<?= url('termine') ?>"<?= $active === 'termine' ? ' aria-current="page"' : '' ?>><?= icon('calendar') ?><span>Kurs buchen</span></a>
    </div>
  </div>
</header>
<main id="inhalt">
<?php
}

function layout_end(): void
{
    ?>
</main>
<footer class="footer">
  <div class="wrap">
    <div class="footer__grid">
      <div class="footer__brand">
        <a class="brand brand--footer" href="<?= url('/') ?>" aria-label="Startseite"><img src="<?= asset('img/logo-drk-mittelweser.png') ?>" width="1100" height="105" alt="Deutsches Rotes Kreuz – DRK Arbeitssicherheit Mittelweser – DRK-Kreisverband Verden e.V." loading="lazy"></a>
        <p><?= e(site('name')) ?> · <?= e(site('org')) ?><br><?= e(site('street')) ?> · <?= e(site('zip')) ?> <?= e(site('city')) ?></p>
      </div>
      <div>
        <h2 class="footer__h">Kurse</h2>
        <ul>
          <?php foreach (array_slice(bookable_courses(), 0, 5) as $c): ?>
          <li><a href="<?= url('termine/' . $c['slug']) ?>"><?= e($c['title']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2 class="footer__h">Angebot</h2>
        <ul>
          <li><a href="<?= url('erste-hilfe') ?>">Erste Hilfe</a></li>
          <li><a href="<?= url('brandschutz') ?>">Brandschutz</a></li>
          <li><a href="<?= url('arbeitssicherheit') ?>">Arbeitssicherheit</a></li>
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
      <li><a href="<?= url('arbeitssicherheit') ?>"><?= icon('building') ?><span><strong>Arbeitssicherheit</strong><small>Für Betriebe · Fachkraft · Beratung</small></span></a></li>
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

/** Auf sehr schmalen Displays kürzere Menübegriffe, damit alles nebeneinander passt. */
function nav_label(string $label): string
{
    $short = ['Arbeitssicherheit' => 'Betriebe'][$label] ?? null;
    return $short ? '<span class="nav__long">' . e($label) . '</span><span class="nav__short">' . e($short) . '</span>' : e($label);
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
        'description' => $c['teaser'] . ' ' . $c['text'],
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

/** Wiederverwendbarer Seitenkopf (Unterseiten). */
function page_head(string $eyebrow, string $title, string $lead, array $crumbs = [], string $extra = '', string $photo = ''): void
{
    $img = $photo ? photo($photo, $title, 'phead__img', false) : '';
    ?>
<section class="phead<?= $img ? ' phead--photo' : '' ?>">
  <?php if ($img): ?><div class="wrap phead__grid"><?php endif; ?>
  <div class="<?= $img ? '' : 'wrap ' ?>phead__inner">
    <?php if ($crumbs): ?>
    <nav class="crumbs" aria-label="Brotkrumen"><a href="<?= url('/') ?>">Start</a><?php foreach ($crumbs as [$n, $p]): ?><span aria-hidden="true">/</span><a href="<?= url($p) ?>"><?= e($n) ?></a><?php endforeach; ?></nav>
    <?php endif; ?>
    <h1 class="h1"><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
    <?= $extra ?>
  </div>
  <?php if ($img): ?><figure class="phead__media"><?= $img ?></figure></div><?php endif; ?>
</section>
<?php
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

/** Bild-Platz: hochgeladenes Bild oder gestaltete Fläche mit Symbol. */
function image_slot(string $slot, string $alt, string $icon = 'heart', string $class = ''): string
{
    $src = slot_image($slot);
    if ($src) {
        return '<figure class="media ' . e($class) . '"><img src="' . e($src) . '" alt="' . e($alt) . '" loading="lazy" decoding="async"></figure>';
    }
    return '<figure class="media media--empty ' . e($class) . '" role="img" aria-label="' . e($alt) . '">' . cross_svg('media__cross', 'currentColor') . icon($icon, 'media__icon') . '</figure>';
}

/** Foto eines Bildplatzes als <img> (Upload oder Standardfoto), sonst leer. */
function photo(string $slot, string $alt, string $class = '', bool $lazy = true): string
{
    $src = slot_image($slot);
    if (!$src) {
        return '';
    }
    return '<img class="' . e($class) . '" src="' . e($src) . '" alt="' . e($alt) . '"' . ($lazy ? ' loading="lazy"' : ' fetchpriority="high"') . ' decoding="async">';
}

/** Kursbild: Foto, sonst gezeichnetes Motiv. */
function course_media(array $c, string $class = 'card__photo'): string
{
    return photo('kurs-' . $c['slug'], $c['title'], $class) ?: illus_course($c);
}
