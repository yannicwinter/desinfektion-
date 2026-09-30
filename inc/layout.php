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
    <a class="logo" href="<?= url('/') ?>" aria-label="<?= e(site('name')) ?> – Startseite">
      <?= cross_svg('logo__cross') ?>
      <span class="logo__text"><strong>Deutsches Rotes Kreuz</strong><span><?= e(site('org')) ?></span></span>
    </a>
    <nav class="nav" aria-label="Hauptnavigation">
      <ul class="nav__list">
        <?php foreach (nav_items() as $slug => $label): ?>
        <li><a href="<?= url($slug) ?>"<?= $active === $slug ? ' aria-current="page"' : '' ?>><?= nav_label($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header__actions">
      <a class="header__phone" href="tel:<?= e(site('phone_link')) ?>" aria-label="Anrufen: <?= e(site('phone')) ?>"><?= icon('phone') ?><span><?= e(site('phone')) ?></span></a>
      <a class="btn btn--red btn--sm" href="<?= url('termine') ?>"<?= $active === 'termine' ? ' aria-current="page"' : '' ?>><?= icon('calendar') ?><span>Termine</span></a>
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
        <a class="logo logo--light" href="<?= url('/') ?>" aria-label="Startseite"><?= cross_svg('logo__cross') ?><span class="logo__text"><strong>Deutsches Rotes Kreuz</strong><span><?= e(site('org')) ?></span></span></a>
        <p><?= e(site('name')) ?><br><?= e(site('street')) ?> · <?= e(site('zip')) ?> <?= e(site('city')) ?></p>
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
          <li><a href="<?= url('unternehmen') ?>">Für Unternehmen</a></li>
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
<script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
<?php
}

/** Auf sehr schmalen Displays kürzere Menübegriffe, damit alles nebeneinander passt. */
function nav_label(string $label): string
{
    $short = ['Unternehmen' => 'Firmen'][$label] ?? null;
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
function page_head(string $eyebrow, string $title, string $lead, array $crumbs = [], string $extra = ''): void
{
    ?>
<section class="phead">
  <?= hero_art(false) ?>
  <div class="wrap phead__inner">
    <?php if ($crumbs): ?>
    <nav class="crumbs" aria-label="Brotkrumen"><a href="<?= url('/') ?>">Start</a><?php foreach ($crumbs as [$n, $p]): ?><span aria-hidden="true">/</span><a href="<?= url($p) ?>"><?= e($n) ?></a><?php endforeach; ?></nav>
    <?php endif; ?>
    <?php if ($eyebrow): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
    <h1 class="h1"><?= e($title) ?></h1>
    <?php if ($lead): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
    <?= $extra ?>
  </div>
</section>
<?php
}

/**
 * Animiertes Kreuz im Hintergrund: Kontur zeichnet sich, Fläche blendet ein,
 * Puls-Wellen laufen nach außen, EKG-Linie läuft durch. Folgt leicht Maus & Scroll.
 */
function hero_art(bool $big = true): string
{
    $cross = 'M15 2h14v13h13v14H29v13H15V29H2V15h13z';
    ob_start(); ?>
<div class="art<?= $big ? ' art--big' : '' ?>" aria-hidden="true">
  <div class="art__grid"></div>
  <svg class="art__cross" viewBox="-12 -12 68 68" data-parallax>
    <defs><linearGradient id="crossfill" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FDE7E7"/><stop offset="1" stop-color="#FBD6D7"/></linearGradient></defs>
    <g class="art__pulses"><path d="<?= $cross ?>"/><path d="<?= $cross ?>"/><path d="<?= $cross ?>"/></g>
    <path class="art__fill" d="<?= $cross ?>"/>
    <path class="art__line" d="<?= $cross ?>" pathLength="100"/>
    <path class="art__run" d="<?= $cross ?>" pathLength="100"/>
  </svg>
  <?php if ($big): ?>
  <?php $ecg = 'M0 70h380l18-10 14 10h40l12 12 22-78 24 102 16-36h34l20-14 20 14h600'; ?>
  <svg class="art__ecg" viewBox="0 0 1200 120" preserveAspectRatio="none"><path class="art__ecg-base" d="<?= $ecg ?>"/><path class="art__ecg-run" pathLength="100" d="<?= $ecg ?>"/></svg>
  <?php endif; ?>
</div>
<?php
    return (string) ob_get_clean();
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
