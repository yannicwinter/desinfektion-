<?php
/**
 * Grundfunktionen: Pfade, Inhalte laden/speichern, Ausgabe-Helfer.
 * Alle Inhalte liegen in data/content.json und werden im Admin-Bereich gepflegt.
 */
declare(strict_types=1);

const ROOT = __DIR__ . '/..';
const DATA_DIR = ROOT . '/data';
const CACHE_DIR = ROOT . '/cache';
const UPLOAD_DIR = ROOT . '/uploads';
const CONTENT_FILE = DATA_DIR . '/content.json';

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Berlin');

/** Basis-Pfad, falls die Seite in einem Unterordner liegt (sonst ""). */
function base_path(): string
{
    static $b = null;
    if ($b === null) {
        $b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }
    return $b;
}

function url(string $path = '/'): string
{
    if (preg_match('~^(https?:|mailto:|tel:|#)~', $path)) {
        return $path;
    }
    return base_path() . '/' . ltrim($path, '/');
}

/** Absolute URL für Canonical, Sitemap, Open Graph. */
function abs_url(string $path = '/'): string
{
    $site = rtrim((string) (content()['site']['url'] ?? ''), '/');
    if ($site === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $site = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
    }
    return $site . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . $path) . '?v=' . $v;
}

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function content(bool $reload = false): array
{
    static $c = null;
    if ($c === null || $reload) {
        $json = is_file(CONTENT_FILE) ? file_get_contents(CONTENT_FILE) : '{}';
        $c = json_decode((string) $json, true) ?: [];
    }
    return $c;
}

function save_content(array $data): bool
{
    if (!is_dir(DATA_DIR . '/backups')) {
        @mkdir(DATA_DIR . '/backups', 0755, true);
    }
    if (is_file(CONTENT_FILE)) {
        @copy(CONTENT_FILE, DATA_DIR . '/backups/content-' . date('Ymd-His') . '.json');
        $old = glob(DATA_DIR . '/backups/content-*.json') ?: [];
        sort($old);
        foreach (array_slice($old, 0, max(0, count($old) - 30)) as $f) {
            @unlink($f);
        }
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $tmp = CONTENT_FILE . '.tmp';
    if ($json === false || file_put_contents($tmp, $json . "\n", LOCK_EX) === false) {
        return false;
    }
    $ok = rename($tmp, CONTENT_FILE);
    content(true);
    return $ok;
}

function site(string $key): string
{
    return (string) (content()['site'][$key] ?? '');
}

function page(string $page, string $key): string
{
    return (string) (content()['pages'][$page][$key] ?? '');
}

/** Mehrzeiliges Feld als Liste (leere Zeilen entfernt). */
function lines(?string $s): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $s)), 'strlen'));
}

/** "Label: Wert"-Zeilen als [[Label, Wert], ...]. */
function pairs(?string $s, string $sep = ':'): array
{
    $out = [];
    foreach (lines($s) as $l) {
        $p = explode($sep, $l, 2);
        $out[] = [trim($p[0]), trim($p[1] ?? '')];
    }
    return $out;
}

/**
 * Einfache Textformatierung für Admin-Texte:
 * Leerzeile = neuer Absatz, "- " = Liste, **fett**, [Linktext](url).
 */
function rich(?string $text): string
{
    $html = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) as $block) {
        $rows = preg_split('/\R/', $block);
        $isList = count(array_filter($rows, fn($r) => preg_match('/^\s*[-•]\s+/', $r))) === count($rows);
        if ($isList) {
            $html .= '<ul class="list">';
            foreach ($rows as $r) {
                $html .= '<li>' . inline(preg_replace('/^\s*[-•]\s+/', '', $r)) . '</li>';
            }
            $html .= '</ul>';
        } elseif (trim($block) !== '') {
            $html .= '<p>' . implode('<br>', array_map('inline', $rows)) . '</p>';
        }
    }
    return $html;
}

function inline(string $s): string
{
    $s = e($s);
    $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
    return preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) {
        $href = html_entity_decode($m[2]);
        if (!preg_match('~^(https?://|/|mailto:|tel:|#)~', $href)) {
            return $m[1];
        }
        $ext = str_starts_with($href, 'http') ? ' target="_blank" rel="noopener"' : '';
        return '<a href="' . e(url($href)) . '"' . $ext . '>' . $m[1] . '</a>';
    }, $s);
}

/** Aktive Kurse, optional nach Kategorie gefiltert. */
function courses(?string $category = null): array
{
    $all = array_filter(content()['courses'] ?? [], fn($c) => !empty($c['active']));
    if ($category !== null) {
        $all = array_filter($all, fn($c) => ($c['category'] ?? '') === $category);
    }
    return array_values($all);
}

function course(string $slug): ?array
{
    foreach (courses() as $c) {
        if ($c['slug'] === $slug) {
            return $c;
        }
    }
    return null;
}

/** Kurse mit HiOrg-Terminliste. */
function bookable_courses(): array
{
    return array_values(array_filter(courses(), fn($c) => trim((string) ($c['hiorg_id'] ?? '')) !== ''));
}

function course_url(array $c): string
{
    return url(($c['category'] === 'brandschutz' ? 'brandschutz' : 'erste-hilfe') . '#' . $c['slug']);
}

/** Hochgeladenes Bild zu einem Bild-Platz (z. B. "home"), sonst null. */
function slot_image(string $slot): ?string
{
    // Nur im Admin hochgeladene Bilder
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $f = UPLOAD_DIR . '/' . $slot . '.' . $ext;
        if (is_file($f)) {
            return url('uploads/' . $slot . '.' . $ext) . '?v=' . filemtime($f);
        }
    }
    // Standardfoto (assets/img/foto), sonst gezeichnetes Motiv (inc/illus.php)
    $f = ROOT . '/assets/img/foto/' . $slot . '.jpg';
    return is_file($f) ? asset('img/foto/' . $slot . '.jpg') : null;
}

/** Bildplätze für den Admin: feste Seitenbilder + ein Bild je Kurs. */
function image_slots(): array
{
    $slots = [
        'hero' => 'Startseite – großes Bild oben',
        'home' => 'Startseite – Arbeitssicherheit',
        'erste-hilfe' => 'Seite Erste Hilfe',
        'brandschutz' => 'Seite Brandschutz',
        'unternehmen' => 'Seite Arbeitssicherheit',
        'fachkraft' => 'Arbeitssicherheit – Fachkraft',
        'inhouse' => 'Arbeitssicherheit – Schulung im Betrieb',
    ];
    foreach (content()['courses'] ?? [] as $c) {
        $slots['kurs-' . $c['slug']] = 'Kurs: ' . $c['title'];
    }
    return $slots;
}

function nav_items(): array
{
    return [
        '' => 'Start',
        'erste-hilfe' => 'Erste Hilfe',
        'brandschutz' => 'Brandschutz',
        'arbeitssicherheit' => 'Arbeitssicherheit',
        'faq' => 'FAQ',
        'kontakt' => 'Kontakt',
    ];
}

/** Wochentag / Monat auf Deutsch. */
function de_date(DateTimeInterface $d, string $fmt): string
{
    static $days = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    static $daysLong = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    static $months = ['', 'Jan', 'Feb', 'März', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    return strtr($fmt, [
        'WWW' => $daysLong[(int) $d->format('w')],
        'WW' => $days[(int) $d->format('w')],
        'MMM' => $months[(int) $d->format('n')],
        'DD' => $d->format('d'),
        'D' => $d->format('j'),
        'YYYY' => $d->format('Y'),
    ]);
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('drk_sid');
    session_set_cookie_params(['lifetime' => 0, 'path' => base_path() . '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    start_session();
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

/** Geheimschlüssel für Formular-Signaturen (wird einmalig erzeugt). */
function app_secret(): string
{
    $f = DATA_DIR . '/secret.key';
    if (!is_file($f)) {
        file_put_contents($f, bin2hex(random_bytes(32)), LOCK_EX);
        @chmod($f, 0600);
    }
    return trim((string) file_get_contents($f));
}

function redirect(string $path, int $code = 303): void
{
    header('Location: ' . (preg_match('~^https?://~', $path) ? $path : url($path)), true, $code);
    exit;
}

function icon(string $name, string $class = 'i'): string
{
    $p = [
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron' => '<path d="m6 9 6 6 6-6"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'hand' => '<path d="M18 11V6a2 2 0 0 0-4 0v5M14 10V4a2 2 0 0 0-4 0v6M10 10.5V6a2 2 0 0 0-4 0v8a8 8 0 0 0 16 0v-3a2 2 0 0 0-4 0"/>',
        'building' => '<path d="M3 21V8l6-4v17M9 21V10l6-3v14M15 21V11l6 2v8M2 21h20"/>',
        'flame' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.4-.5-2-1-3-1.1-2.1-.2-4 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.2.4-2.3 1-3.3.3 1.5 1.3 2.8 2.5 2.8z"/>',
        'heart' => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7z"/><path d="M3.2 12H9l.5-1 2 4.5 2-7 1.5 3.5h5.3"/>',
        'external' => '<path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    ][$name] ?? '';
    return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

function cross_svg(string $class = '', string $fill = '#E60005'): string
{
    return '<svg class="' . $class . '" viewBox="0 0 44 44" aria-hidden="true"><path fill="' . $fill . '" d="M15 2h14v13h13v14H29v13H15V29H2V15h13z"/></svg>';
}
