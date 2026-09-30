<?php
/**
 * Front-Controller: Alle Anfragen laufen über diese Datei (siehe .htaccess).
 * Saubere URLs ohne .php, z. B. /erste-hilfe, /termine/brandschutzhelfer, /admin
 */
declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/hiorg.php';
require __DIR__ . '/inc/layout.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rawurldecode(substr($path, strlen(base_path())));
$path = trim($path, '/');
$parts = $path === '' ? [] : explode('/', $path);
$route = $parts[0] ?? '';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Alte Adressen des Vorgänger-Entwurfs / alter Seite dauerhaft umleiten
$legacy = [
    'index' => '/',
    'kurs-erste-hilfe' => 'erste-hilfe#erste-hilfe-ausbildung',
    'kurs-ersthelfer-betrieb' => 'erste-hilfe#erste-hilfe-ausbildung',
    'kurs-erste-hilfe-kind' => 'erste-hilfe#erste-hilfe-am-kind',
    'kurs-brandschutzhelfer' => 'brandschutz#brandschutzhelfer',
];
if (isset($legacy[$path])) {
    redirect($legacy[$path], 301);
}

switch ($route) {
    case '':
        require __DIR__ . '/templates/home.php';
        break;

    case 'erste-hilfe':
    case 'brandschutz':
        if (count($parts) > 1) {
            not_found();
        }
        $category = $route;
        require __DIR__ . '/templates/kategorie.php';
        break;

    case 'unternehmen':
    case 'faq':
    case 'kontakt':
        if (count($parts) > 1) {
            not_found();
        }
        require __DIR__ . '/templates/' . $route . '.php';
        break;

    case 'impressum':
    case 'datenschutz':
        $legalPage = $route;
        require __DIR__ . '/templates/rechtliches.php';
        break;

    case 'termine':
        $slug = $parts[1] ?? '';
        if ($slug !== '' && (!($current = course($slug)) || empty($current['hiorg_id']))) {
            not_found();
        }
        // /termine/{kurs}/anmeldung/{kid} → Anmeldung eingebettet auf unserer Seite
        if (count($parts) === 4 && $parts[2] === 'anmeldung' && ctype_digit($parts[3])) {
            $kid = $parts[3];
            require __DIR__ . '/templates/anmeldung.php';
            break;
        }
        if (count($parts) > 2) {
            not_found();
        }
        require __DIR__ . '/templates/termine.php';
        break;

    case 'api':
        // /api/termine/{slug}?limit=3 → HTML-Fragment für Akkordeon-Vorschau
        if (($parts[1] ?? '') === 'termine' && ($c = course($parts[2] ?? '')) && !empty($c['hiorg_id'])) {
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: public, max-age=300');
            header('X-Robots-Tag: noindex');
            $res = hiorg_dates($c);
            $limit = min(10, max(1, (int) ($_GET['limit'] ?? 3)));
            if ($res['items']) {
                echo '<ul class="dates dates--compact">' . render_dates($res['items'], ['limit' => $limit]) . '</ul>';
            } else {
                echo '<p class="muted small">' . ($res['ok'] ? 'Aktuell sind keine Termine eingestellt.' : 'Termine konnten gerade nicht geladen werden.') . '</p>';
            }
            exit;
        }
        not_found();
        break;

    case 'sitemap.xml':
        require __DIR__ . '/templates/sitemap.php';
        break;

    case 'robots.txt':
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nDisallow: /admin\nDisallow: /api/\n\nSitemap: " . abs_url('sitemap.xml') . "\n";
        break;

    case 'admin':
        require __DIR__ . '/inc/admin.php';
        admin_dispatch($parts[1] ?? '');
        break;

    default:
        not_found();
}

function not_found(): void
{
    http_response_code(404);
    layout_start(['title' => 'Seite nicht gefunden | ' . site('name'), 'description' => '', 'noindex' => true]);
    page_head('Fehler 404', 'Diese Seite gibt es nicht (mehr).', 'Vielleicht finden Sie hier, was Sie suchen:');
    ?>
<section class="section section--tight"><div class="wrap btn-row">
  <a class="btn btn--red" href="<?= url('termine') ?>">Kurstermine</a>
  <a class="btn btn--ghost" href="<?= url('erste-hilfe') ?>">Erste Hilfe</a>
  <a class="btn btn--ghost" href="<?= url('brandschutz') ?>">Brandschutz</a>
  <a class="btn btn--ghost" href="<?= url('kontakt') ?>">Kontakt</a>
</div></section>
<?php
    layout_end();
    exit;
}
