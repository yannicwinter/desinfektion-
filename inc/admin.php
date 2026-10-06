<?php
/**
 * Admin-Bereich unter /admin – Texte, Kurse, FAQ, Ansprechpersonen, Bilder, Termine-Diagnose.
 * Zugangsdaten liegen gehasht in data/admin.json (wird beim ersten Aufruf angelegt).
 */
declare(strict_types=1);

const ADMIN_FILE = DATA_DIR . '/admin.json';

const PAGE_NAMES = [
    'home' => 'Startseite',
    'erste-hilfe' => 'Erste Hilfe',
    'brandschutz' => 'Brandschutz',
    'unternehmen' => 'Für Unternehmen',
    'termine' => 'Kurstermine',
    'faq' => 'FAQ',
    'kontakt' => 'Kontakt',
    'impressum' => 'Impressum',
    'datenschutz' => 'Datenschutz',
];

const FIELD_LABELS = [
    'angebot_title' => ['Angebot – Überschrift', ''], 'angebot_eh' => ['Angebot – Erste Hilfe', ''], 'angebot_bs' => ['Angebot – Brandschutz', ''], 'angebot_as' => ['Angebot – Arbeitssicherheit', ''],
    'termine_title' => ['Termine – Überschrift', ''], 'termine_lead' => ['Termine – Text', ''], 'termine_eyebrow' => ['Termine – Dachzeile', ''],
    'angebot_eyebrow' => ['Angebot – Dachzeile', ''], 'angebot_lead' => ['Angebot – Text', ''],
    'beliebt' => ['Suche – „Beliebt“-Links', 'Eine Zeile je Link: Text | Pfad, z. B. „Führerschein | termine/erste-hilfe-ausbildung“.'],
    'trust' => ['Vorteile unter der Suche', 'Eine Zeile je Punkt.'],
    'band_title' => ['Rotes Band – Überschrift', 'Leer = Band ausblenden.'], 'band_text' => ['Rotes Band – Text', ''],
    'insta_lead' => ['Instagram – Text', ''],
    'berater' => ['Ansprechperson in der Beratungsbox', 'Name wie unter „Ansprechpersonen“, z. B. Jan Wille.'],
    'wege_eyebrow' => ['Wegweiser – Dachzeile', ''], 'wege_title' => ['Wegweiser – Überschrift', ''], 'wege_lead' => ['Wegweiser – Text', ''],
    'wege' => ['Wegweiser – Karten', 'Eine Zeile je Karte: Titel | Text | Ziel (Pfad) | Bild (z. B. kurs-erste-hilfe-ausbildung).'],
    'faq_title' => ['Häufige Fragen – Überschrift', ''],
    'intro_eyebrow' => ['Einleitung – Dachzeile', ''], 'intro_title' => ['Einleitung – Überschrift', 'Leer = Abschnitt ausblenden.'],
    'intro_text' => ['Einleitung – Text', 'Absätze durch eine Leerzeile trennen.'],
    'intro_list_title' => ['Einleitung – Kasten-Überschrift', ''], 'intro_list' => ['Einleitung – Kasten-Liste', 'Ein Punkt pro Zeile.'],
    'wissen_title' => ['„Gut zu wissen“ – Überschrift', ''], 'wissen_lead' => ['„Gut zu wissen“ – Text', ''],
    'wissen' => ['„Gut zu wissen“ – Kacheln', 'Eine Zeile je Kachel: Titel | Text | Symbol (clock, euro, shield, award, pin, users, info).'],
    'pflichten_title' => ['Pflichten – Überschrift', ''], 'pflichten_lead' => ['Pflichten – Text', ''],
    'pflichten' => ['Pflichten – Karten', 'Eine Zeile je Karte: Zahl | Titel | Text | Kurs (Kurz-Name, z. B. brandschutzhelfer).'],
    'leistungen_title' => ['Leistungen – Überschrift', ''], 'leistungen_lead' => ['Leistungen – Text', ''],
    'inhouse_text' => ['Schulung im Betrieb – Text', ''],
    'seo_title' => ['Google-Titel', 'Erscheint als Überschrift in Suchergebnissen. Ideal: 50–60 Zeichen, wichtigster Begriff vorne.'],
    'seo_description' => ['Google-Beschreibung', 'Kurzer Text unter dem Titel in Suchergebnissen. Ideal: 120–155 Zeichen.'],
    'eyebrow' => ['Dachzeile', ''], 'title' => ['Überschrift', ''], 'lead' => ['Einleitung', ''],
    'hero_eyebrow' => ['Dachzeile oben', ''], 'hero_title' => ['Große Überschrift', ''], 'hero_lead' => ['Einleitung', ''],
    'usp1_title' => ['Vorteil 1 – Titel', ''], 'usp1_text' => ['Vorteil 1 – Text', ''],
    'usp2_title' => ['Vorteil 2 – Titel', ''], 'usp2_text' => ['Vorteil 2 – Text', ''],
    'usp3_title' => ['Vorteil 3 – Titel', ''], 'usp3_text' => ['Vorteil 3 – Text', ''],
    'kurse_title' => ['Kurse – Überschrift', ''], 'kurse_lead' => ['Kurse – Untertitel', 'Welche Kurse erscheinen, legen Sie unter „Kurse“ mit „Auf Startseite“ fest.'],
    'firma_eyebrow' => ['Unternehmen – Dachzeile', ''], 'firma_title' => ['Unternehmen – Überschrift', ''], 'firma_text' => ['Unternehmen – Text', ''],
    'firma_list' => ['Unternehmen – Liste', 'Ein Punkt pro Zeile.'],
    'stat_value' => ['Kennzahl', ''], 'stat_text' => ['Kennzahl – Text', ''],
    'cta_title' => ['Kontaktbalken – Überschrift', 'Wird auf mehreren Seiten unten angezeigt.'], 'cta_text' => ['Kontaktbalken – Text', ''],
    'outro_title' => ['Seitenbox – Überschrift', ''], 'outro_text' => ['Seitenbox – Text', ''],
    'contact_title' => ['Ansprechpartner – Überschrift', ''], 'contact_text' => ['Ansprechpartner – Name/Text', ''],
    'asi_title' => ['Arbeitssicherheit – Überschrift', ''], 'asi_text' => ['Arbeitssicherheit – Text', ''], 'asi_list' => ['Arbeitssicherheit – Liste', 'Ein Punkt pro Zeile.'],
    'inhouse_title' => ['Inhouse – Überschrift', ''], 'inhouse_steps' => ['Inhouse – Schritte', 'Eine Zeile pro Schritt: Titel | Text'],
    'inhouse_needs' => ['Inhouse – Voraussetzungen', 'Ein Punkt pro Zeile.'],
    'form_title' => ['Formular – Überschrift', ''], 'form_text' => ['Formular – Text', ''],
    'hint' => ['Hinweis unter der Terminliste', ''], 'orte' => ['Kursorte', 'Ein Ort pro Zeile.'],
    'vergleich' => ['Vergleichstabelle „Welcher Kurs passt?“', 'Erste Zeile = Überschriften. Spalten mit | trennen. „ja“/„nein“ werden als Symbol angezeigt.'],
    'asi_benefits' => ['Fachkraft – Nutzen', 'Ein Punkt pro Zeile.'],
    'asi_offer' => ['Fachkraft – Angebot', 'Ein Punkt pro Zeile.'],
    'ausbildung_title' => ['Überschrift Ausbildung', ''],
    'dienst_title' => ['Überschrift Dienstleistung', ''],
    'kosten' => ['Hinweis Kosten', 'Eine Zeile pro Hinweis: „Thema: Text“'],
    'insta_title' => ['Instagram – Überschrift', 'Die Beiträge erscheinen, sobald unter „Allgemein“ der Behold-Feed-Link eingetragen ist – bis dahin Platzhalter.'],
    'body' => ['Seiteninhalt', 'Leerzeile = neuer Absatz · **fett** · [Linktext](https://…) · Zeilen mit „- “ = Liste'],
];

const SITE_LABELS = [
    'name' => 'Name der Website', 'org' => 'Träger', 'url' => 'Adresse der Website (für Google, ohne / am Ende)',
    'phone' => 'Telefon (Anzeige)', 'phone_link' => 'Telefon (zum Wählen, z. B. +49423192450)', 'email' => 'E-Mail (Anzeige)',
    'email_erste_hilfe' => 'E-Mail für Fragen zur Anmeldung (Erste Hilfe)', 'email_brandschutz' => 'E-Mail für Fragen zur Anmeldung (Brandschutz, Arbeitssicherheit)',
    'form_recipient' => 'Empfänger des Kontaktformulars', 'street' => 'Straße', 'zip' => 'PLZ', 'city' => 'Ort', 'region' => 'Bundesland',
    'topbar' => 'Text in der dunklen Leiste ganz oben',
    'instagram' => 'Instagram-Link',
    'instagram_links' => 'Instagram ohne Schlüssel: Links zu den Beiträgen der Bilder „Instagram 1–6“ (unter „Bilder“), einer pro Zeile',
    'hiorg_booking' => 'Anmeldung: leer = direkt auf unserer Seite eingebettet, „tab“ = HiOrg in neuem Tab',
    'default_og_image' => 'Vorschaubild für Social Media (volle URL, optional)',
    'ki_bilder' => 'KI-Bilder: Bild-Plätze mit KI-erzeugtem Foto, durch Komma getrennt (z. B. hero, erste-hilfe) – werden mit „KI-generiert“ gekennzeichnet. Bei neuem Foto anpassen.',
];

function admin_dispatch(string $section): void
{
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    header("X-Frame-Options: DENY");
    start_session();
    $account = is_file(ADMIN_FILE) ? json_decode((string) file_get_contents(ADMIN_FILE), true) : null;

    if (!$account) {
        admin_setup();
        return;
    }
    if ($section === 'logout') {
        $_SESSION = [];
        session_destroy();
        redirect('admin');
    }
    if (empty($_SESSION['admin'])) {
        admin_login($account);
        return;
    }
    if (($_SESSION['admin_seen'] ?? 0) < time() - 7200) {
        $_SESSION = [];
        redirect('admin');
    }
    $_SESSION['admin_seen'] = time();

    $map = [
        '' => 'admin_home', 'allgemein' => 'admin_general', 'seiten' => 'admin_pages', 'kurse' => 'admin_courses',
        'kurs' => 'admin_course', 'faq' => 'admin_faq', 'kontakte' => 'admin_contacts', 'bilder' => 'admin_images',
        'termine' => 'admin_dates', 'passwort' => 'admin_password',
    ];
    if (!isset($map[$section])) {
        redirect('admin');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ok()) {
        flash('Sitzung abgelaufen – bitte erneut speichern.', 'err');
        redirect('admin/' . $section);
    }
    $map[$section]();
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = [$msg, $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function admin_start(string $title, string $active = ''): void
{
    $f = flash();
    ?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Admin · <?= e(site('name')) ?></title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="admin">
<header class="admin__bar">
  <div class="wrap admin__barin">
    <a class="logo" href="<?= url('admin') ?>"><?= cross_svg('logo__cross') ?><span class="logo__text"><strong>Admin</strong><span><?= e(site('name')) ?></span></span></a>
    <?php if (!empty($_SESSION['admin'])): ?>
    <nav class="admin__nav">
      <?php foreach (['allgemein' => 'Allgemein', 'seiten' => 'Seitentexte', 'kurse' => 'Kurse', 'faq' => 'FAQ', 'kontakte' => 'Personen', 'bilder' => 'Bilder', 'termine' => 'Termine', 'passwort' => 'Passwort'] as $k => $l): ?>
      <a href="<?= url('admin/' . $k) ?>"<?= $active === $k ? ' aria-current="page"' : '' ?>><?= $l ?></a>
      <?php endforeach; ?>
      <a href="<?= url('/') ?>" target="_blank">Website ↗</a>
      <a href="<?= url('admin/logout') ?>">Abmelden</a>
    </nav>
    <?php endif; ?>
  </div>
</header>
<main class="wrap admin__main">
  <h1 class="h3"><?= e($title) ?></h1>
  <?php if ($f): ?><div class="notice notice--<?= e($f[1]) ?>" role="status"><?= e($f[0]) ?></div><?php endif; ?>
<?php
}

function admin_end(): void
{
    echo '</main><script src="' . asset('js/main.js') . '" defer></script></body></html>';
}

function login_throttle_file(): string
{
    return CACHE_DIR . '/login-' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . floor(time() / 900)) . '.cnt';
}

function admin_setup(): void
{
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $u = trim((string) ($_POST['user'] ?? ''));
        $p = (string) ($_POST['pass'] ?? '');
        if (!csrf_ok()) {
            $err = 'Sitzung abgelaufen, bitte erneut versuchen.';
        } elseif ($u === '' || mb_strlen($p) < 10) {
            $err = 'Benutzername angeben, Passwort mindestens 10 Zeichen.';
        } elseif ($p !== (string) ($_POST['pass2'] ?? '')) {
            $err = 'Die Passwörter stimmen nicht überein.';
        } else {
            file_put_contents(ADMIN_FILE, json_encode(['user' => $u, 'hash' => password_hash($p, PASSWORD_DEFAULT)]), LOCK_EX);
            @chmod(ADMIN_FILE, 0600);
            session_regenerate_id(true);
            $_SESSION['admin'] = $u;
            $_SESSION['admin_seen'] = time();
            flash('Zugang angelegt. Willkommen!');
            redirect('admin');
        }
    }
    admin_start('Admin-Zugang einrichten');
    ?>
  <form class="panel form admin__narrow" method="post">
    <p>Es ist noch kein Zugang vorhanden. Legen Sie jetzt Benutzername und Passwort fest.</p>
    <?php if ($err): ?><div class="notice notice--err"><?= e($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label class="field"><span>Benutzername</span><input name="user" required autocomplete="username"></label>
    <label class="field"><span>Passwort (mind. 10 Zeichen)</span><input type="password" name="pass" required minlength="10" autocomplete="new-password"></label>
    <label class="field"><span>Passwort wiederholen</span><input type="password" name="pass2" required minlength="10" autocomplete="new-password"></label>
    <button class="btn btn--red">Zugang anlegen</button>
  </form>
<?php
    admin_end();
}

function admin_login(array $account): void
{
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tf = login_throttle_file();
        $tries = is_file($tf) ? (int) file_get_contents($tf) : 0;
        if ($tries >= 8) {
            $err = 'Zu viele Versuche. Bitte in 15 Minuten erneut versuchen.';
        } elseif (!csrf_ok()) {
            $err = 'Sitzung abgelaufen, bitte erneut versuchen.';
        } elseif (hash_equals((string) $account['user'], (string) ($_POST['user'] ?? '')) && password_verify((string) ($_POST['pass'] ?? ''), (string) $account['hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $account['user'];
            $_SESSION['admin_seen'] = time();
            @unlink($tf);
            redirect('admin');
        } else {
            @file_put_contents($tf, (string) ($tries + 1));
            usleep(500000);
            $err = 'Benutzername oder Passwort falsch.';
        }
    }
    admin_start('Anmelden');
    ?>
  <form class="panel form admin__narrow" method="post">
    <?php if ($err): ?><div class="notice notice--err"><?= e($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label class="field"><span>Benutzername</span><input name="user" required autocomplete="username" autofocus></label>
    <label class="field"><span>Passwort</span><input type="password" name="pass" required autocomplete="current-password"></label>
    <button class="btn btn--red">Anmelden</button>
    <p class="muted small">Passwort vergessen? Per FTP die Datei <code>data/admin.json</code> löschen und neu einrichten.</p>
  </form>
<?php
    admin_end();
}

function admin_home(): void
{
    admin_start('Übersicht');
    $tiles = [
        ['seiten', 'Seitentexte', 'Überschriften, Texte und Google-Angaben jeder Seite.'],
        ['kurse', 'Kurse', 'Kurse im Akkordeon, Preise, HiOrg-Nummern.'],
        ['faq', 'FAQ', 'Häufige Fragen und Antworten.'],
        ['kontakte', 'Ansprechpersonen', 'Personen auf der Kontaktseite.'],
        ['bilder', 'Bilder', 'Fotos hochladen oder austauschen.'],
        ['termine', 'Termine prüfen', 'HiOrg-Abruf testen, Zwischenspeicher leeren.'],
        ['allgemein', 'Allgemein', 'Telefon, E-Mail, Adresse, Formular-Empfänger.'],
    ];
    echo '<div class="cards cards--3">';
    foreach ($tiles as [$k, $t, $d]) {
        echo '<a class="card" href="' . url('admin/' . $k) . '"><h2 class="h5">' . e($t) . '</h2><p>' . e($d) . '</p><div class="card__foot"><span></span><span class="card__cta">Öffnen ' . icon('arrow') . '</span></div></a>';
    }
    echo '</div>';
    admin_end();
}

function field_input(string $name, string $label, string $value, string $help = '', bool $multi = false, int $max = 0): string
{
    $counter = $max ? ' data-count="' . $max . '"' : '';
    $h = '<label class="field"><span>' . e($label) . '</span>';
    if ($multi) {
        $rows = max(3, min(14, substr_count($value, "\n") + 2 + (int) (mb_strlen($value) / 90)));
        $h .= '<textarea name="' . e($name) . '" rows="' . $rows . '"' . $counter . '>' . e($value) . '</textarea>';
    } else {
        $h .= '<input name="' . e($name) . '" value="' . e($value) . '"' . $counter . '>';
    }
    if ($help) {
        $h .= '<small>' . e($help) . '</small>';
    }
    return $h . '</label>';
}

function is_multi(string $key, string $value): bool
{
    return str_contains($value, "\n") || mb_strlen($value) > 90 || (bool) preg_match('/(text|body|list|steps|needs|orte|lead|description|wissen|wege|pflichten|kosten)$/', $key);
}

function admin_general(): void
{
    $c = content();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (array_keys(SITE_LABELS) as $k) {
            $c['site'][$k] = trim((string) ($_POST['site'][$k] ?? ''));
        }
        $c['site']['url'] = rtrim($c['site']['url'], '/');
        $igErr = '';
        if (isset($_POST['ig_token']) && trim((string) $_POST['ig_token']) !== '') {
            $igErr = instagram_set_token((string) $_POST['ig_token']);
        } elseif (!empty($_POST['ig_remove'])) {
            instagram_set_token('');
        }
        if ($igErr) {
            flash($igErr, 'err');
        } else {
            save_content($c) ? flash('Gespeichert.') : flash('Speichern fehlgeschlagen – Schreibrechte für data/ prüfen.', 'err');
        }
        redirect('admin/allgemein');
    }
    admin_start('Allgemein', 'allgemein');
    echo '<form class="panel form" method="post">' . csrf_field() . '<div class="form__grid">';
    foreach (SITE_LABELS as $k => $l) {
        echo field_input("site[$k]", $l, (string) ($c['site'][$k] ?? ''));
    }
    echo '</div>';
    $ig = instagram_state();
    echo '<h2 class="h5" style="margin-top:28px">Instagram-Feed (offizielle Schnittstelle)</h2>';
    if (!empty($ig['token'])) {
        echo '<p class="notice' . (!empty($ig['error']) ? ' notice--err' : '') . '">Verbunden mit <b>@' . e((string) ($ig['username'] ?? '')) . '</b> · Schlüssel gültig bis ' . date('d.m.Y', (int) ($ig['expires'] ?? 0)) . ' (wird automatisch verlängert)'
            . (!empty($ig['fetched']) ? ' · zuletzt abgerufen ' . date('d.m.Y H:i', (int) $ig['fetched']) : '')
            . (!empty($ig['error']) ? '<br>' . e((string) $ig['error']) : '') . '</p>';
        echo '<label class="check"><input type="checkbox" name="ig_remove" value="1"><span>Verbindung trennen</span></label>';
    } else {
        echo '<p>Noch nicht verbunden. Ohne Schlüssel erscheinen die Bilder „Instagram 1–6“ (unter „Bilder“), sonst Platzhalter.</p>';
    }
    echo '<label class="field"><span>' . (!empty($ig['token']) ? 'Neuen Zugangsschlüssel eintragen (optional)' : 'Zugangsschlüssel (Token) von Meta') . '</span><input name="ig_token" type="password" autocomplete="off" value=""><small>Wird sicher in data/instagram.json gespeichert und nie angezeigt. Nicht das Instagram-Passwort eintragen!</small></label>';
    echo '<button class="btn btn--red">Speichern</button></form>';
    admin_end();
}

function admin_pages(): void
{
    $c = content();
    $p = (string) ($_GET['p'] ?? 'home');
    if (!isset(PAGE_NAMES[$p])) {
        $p = 'home';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (array_keys($c['pages'][$p] ?? []) as $k) {
            if (isset($_POST['f'][$k])) {
                $c['pages'][$p][$k] = trim(str_replace("\r\n", "\n", (string) $_POST['f'][$k]));
            }
        }
        save_content($c) ? flash('Gespeichert.') : flash('Speichern fehlgeschlagen – Schreibrechte für data/ prüfen.', 'err');
        redirect('admin/seiten?p=' . $p);
    }
    admin_start('Seitentexte', 'seiten');
    echo '<nav class="chips chips--admin">';
    foreach (PAGE_NAMES as $k => $l) {
        echo '<a class="chip" href="' . url('admin/seiten?p=' . $k) . '"' . ($k === $p ? ' aria-current="page"' : '') . '>' . e($l) . '</a>';
    }
    echo '</nav><form class="panel form" method="post">' . csrf_field();
    echo '<p class="muted small">Seite ansehen: <a href="' . url($p === 'home' ? '/' : $p) . '" target="_blank">' . e(PAGE_NAMES[$p]) . ' ↗</a></p>';
    foreach ($c['pages'][$p] ?? [] as $k => $v) {
        [$label, $help] = FIELD_LABELS[$k] ?? [ucfirst(str_replace('_', ' ', $k)), ''];
        $max = $k === 'seo_title' ? 60 : ($k === 'seo_description' ? 155 : 0);
        echo field_input("f[$k]", $label, (string) $v, $help, is_multi($k, (string) $v) && $k !== 'seo_title', $max);
    }
    echo '<button class="btn btn--red">Speichern</button></form>';
    admin_end();
}

function admin_courses(): void
{
    $c = content();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $i = (int) ($_POST['i'] ?? -1);
        $n = count($c['courses']);
        if (isset($_POST['up']) && $i > 0 && $i < $n) {
            [$c['courses'][$i - 1], $c['courses'][$i]] = [$c['courses'][$i], $c['courses'][$i - 1]];
        } elseif (isset($_POST['down']) && $i >= 0 && $i < $n - 1) {
            [$c['courses'][$i + 1], $c['courses'][$i]] = [$c['courses'][$i], $c['courses'][$i + 1]];
        } elseif (isset($_POST['delete']) && isset($c['courses'][$i])) {
            array_splice($c['courses'], $i, 1);
        }
        save_content($c);
        flash('Gespeichert.');
        redirect('admin/kurse');
    }
    admin_start('Kurse', 'kurse');
    echo '<p><a class="btn btn--red btn--sm" href="' . url('admin/kurs') . '">+ Neuer Kurs</a></p>';
    echo '<p class="muted small">Die Reihenfolge hier ist die Reihenfolge im Akkordeon. Gruppen-Überschriften ergeben sich aus dem Feld „Gruppe“.</p>';
    echo '<div class="admin-list">';
    foreach ($c['courses'] as $i => $k) {
        $cat = $k['category'] === 'brandschutz' ? 'Brandschutz' : 'Erste Hilfe';
        $badges = (empty($k['active']) ? '<span class="pill">ausgeblendet</span>' : '')
            . (!empty($k['hiorg_id']) ? '<span class="pill pill--red">HiOrg ' . e($k['hiorg_id']) . '</span>' : '<span class="pill">Anfrage</span>')
            . (!empty($k['featured']) ? '<span class="pill">Startseite</span>' : '');
        echo '<div class="admin-row"><div><strong>' . e($k['title']) . '</strong><span class="muted small">' . e($cat . ' · ' . $k['group']) . '</span><span>' . $badges . '</span></div>'
            . '<form method="post" class="admin-row__act">' . csrf_field() . '<input type="hidden" name="i" value="' . $i . '">'
            . '<button class="btn btn--ghost btn--sm" name="up" aria-label="Nach oben">↑</button><button class="btn btn--ghost btn--sm" name="down" aria-label="Nach unten">↓</button>'
            . '<a class="btn btn--dark btn--sm" href="' . url('admin/kurs?slug=' . rawurlencode($k['slug'])) . '">Bearbeiten</a>'
            . '<button class="btn btn--ghost btn--sm" name="delete" data-confirm="Kurs „' . e($k['title']) . '“ wirklich löschen?">Löschen</button></form></div>';
    }
    echo '</div>';
    admin_end();
}

function admin_course(): void
{
    $c = content();
    $slug = (string) ($_GET['slug'] ?? '');
    $idx = null;
    foreach ($c['courses'] as $i => $k) {
        if ($k['slug'] === $slug) {
            $idx = $i;
        }
    }
    $empty = ['slug' => '', 'category' => 'erste-hilfe', 'group' => '', 'title' => '', 'teaser' => '', 'text' => '', 'learn' => '', 'facts' => '', 'price' => '', 'hiorg_id' => '', 'cta' => 'termine', 'featured' => '', 'active' => '1', 'keywords' => '', 'contact' => '', 'learn_title' => ''];
    $k = $idx !== null ? array_merge($empty, $c['courses'][$idx]) : $empty;
    $err = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (array_keys($empty) as $f) {
            $k[$f] = trim(str_replace("\r\n", "\n", (string) ($_POST[$f] ?? '')));
        }
        $k['slug'] = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr($k['slug'] ?: $k['title'], ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', 'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue', 'ø' => 'o', 'Ø' => 'o'])), '-'));
        $k['hiorg_id'] = implode(', ', array_map(fn($x) => $x[1] !== '' ? $x[0] . ':' . $x[1] : $x[0], hiorg_ids($k)));
        $k['category'] = $k['category'] === 'brandschutz' ? 'brandschutz' : 'erste-hilfe';
        foreach ($c['courses'] as $i => $o) {
            if ($o['slug'] === $k['slug'] && $i !== $idx) {
                $err = 'Diese Web-Adresse (Kürzel) ist schon vergeben.';
            }
        }
        if ($k['title'] === '' || $k['slug'] === '') {
            $err = 'Bitte einen Titel angeben.';
        }
        if (!$err) {
            if ($idx === null) {
                $c['courses'][] = $k;
            } else {
                $c['courses'][$idx] = $k;
            }
            save_content($c) ? flash('Kurs gespeichert.') : flash('Speichern fehlgeschlagen.', 'err');
            redirect('admin/kurs?slug=' . rawurlencode($k['slug']));
        }
    }

    admin_start($idx === null ? 'Neuer Kurs' : 'Kurs bearbeiten', 'kurse');
    if ($err) {
        echo '<div class="notice notice--err">' . e($err) . '</div>';
    }
    echo '<p><a class="link-arrow" href="' . url('admin/kurse') . '">← Alle Kurse</a></p>';
    echo '<form class="panel form" method="post">' . csrf_field() . '<div class="form__grid">';
    echo field_input('title', 'Titel', $k['title']);
    echo field_input('slug', 'Kürzel für die Web-Adresse', $k['slug'], 'Wird aus dem Titel erzeugt, wenn leer. Z. B. /termine/erste-hilfe-am-kind');
    echo '<label class="field"><span>Bereich</span><select name="category"><option value="erste-hilfe"' . ($k['category'] === 'erste-hilfe' ? ' selected' : '') . '>Erste Hilfe</option><option value="brandschutz"' . ($k['category'] === 'brandschutz' ? ' selected' : '') . '>Brandschutz</option></select></label>';
    echo field_input('group', 'Gruppe', $k['group'], 'Zwischenüberschrift auf der Bereichsseite, z. B. „Familie & Kinder“.');
    echo field_input('hiorg_id', 'HiOrg-Kursliste (id)', $k['hiorg_id'], 'Die Zahl hinter „id=“ im HiOrg-Link. Mehrere Listen mit Bezeichnung möglich, z. B. „3871:Ausbildung, 3872:Fortbildung“. Leer = nur Anfrage, keine Online-Termine.');
    echo field_input('price', 'Preis (Anzeige)', $k['price'], 'Z. B. „55 €“. Leer lassen, wenn variabel.');
    echo '</div>';
    echo field_input('teaser', 'Kurzbeschreibung (eine Zeile)', $k['teaser'], 'Erscheint auf den Kurskarten und oben auf der Kursseite.');
    echo field_input('text', 'Beschreibung', $k['text'], 'Absätze durch eine Leerzeile trennen. Kurz halten – 2 bis 3 kurze Absätze.', true);
    echo field_input('learn_title', 'Überschrift der Inhalte', $k['learn_title'], 'Leer = „Das lernst du“ (Erste Hilfe) bzw. „Inhalte“ (Brandschutz).');
    echo field_input('learn', 'Inhalte', $k['learn'], 'Ein Punkt pro Zeile.', true);
    echo field_input('facts', 'Eckdaten', $k['facts'], 'Eine Zeile pro Angabe: „Dauer: 9 UE“', true);
    $opts = '<option value="">Standard (Erste Hilfe: Jan Wille, Brandschutz: Matthias True)</option>';
    foreach ($c['contacts'] ?? [] as $pp) {
        $opts .= '<option value="' . e($pp['name']) . '"' . ($k['contact'] === $pp['name'] ? ' selected' : '') . '>' . e($pp['name'] . ' – ' . $pp['role']) . '</option>';
    }
    echo '<label class="field"><span>Ansprechperson bei „Fragen zum Kurs“</span><select name="contact">' . $opts . '</select></label>';
    echo field_input('keywords', 'Suchbegriffe für den Kursfinder', $k['keywords'], 'Mit Komma trennen: Wörter, mit denen Leute nach diesem Kurs fragen (z. B. „Führerschein, Fahrschule“).', true);
    echo '<div class="form__checks">';
    echo '<label class="check"><input type="checkbox" name="active" value="1"' . (!empty($k['active']) ? ' checked' : '') . '><span>Auf der Website anzeigen</span></label>';
    echo '<label class="check"><input type="checkbox" name="featured" value="1"' . (!empty($k['featured']) ? ' checked' : '') . '><span>Auf der Startseite zeigen</span></label>';
    echo '</div><input type="hidden" name="cta" value="' . e($k['cta']) . '"><button class="btn btn--red">Speichern</button></form>';
    admin_end();
}

/** Wiederholbare Zeilen (FAQ, Personen). */
function admin_rows(string $key, string $title, string $section, array $fields): void
{
    $c = content();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rows = [];
        foreach ((array) ($_POST['rows'] ?? []) as $r) {
            if (!empty($r['_del'])) {
                continue;
            }
            $row = [];
            foreach (array_keys($fields) as $f) {
                $row[$f] = trim(str_replace("\r\n", "\n", (string) ($r[$f] ?? '')));
            }
            if (implode('', $row) !== '') {
                $rows[] = $row;
            }
        }
        $c[$key] = $rows;
        save_content($c) ? flash('Gespeichert.') : flash('Speichern fehlgeschlagen.', 'err');
        redirect('admin/' . $section);
    }
    admin_start($title, $section);
    echo '<form class="form" method="post">' . csrf_field() . '<div class="admin-rows">';
    $rows = $c[$key] ?? [];
    $rows[] = array_fill_keys(array_keys($fields), '');
    $rows[] = array_fill_keys(array_keys($fields), '');
    foreach ($rows as $i => $r) {
        $isNew = $i >= count($c[$key] ?? []);
        echo '<fieldset class="panel admin-rows__item"><legend>' . ($isNew ? 'Neu' : '#' . ($i + 1)) . '</legend><div class="form__grid">';
        foreach ($fields as $f => [$label, $multi]) {
            echo field_input("rows[$i][$f]", $label, (string) ($r[$f] ?? ''), '', $multi);
        }
        echo '</div>' . ($isNew ? '' : '<label class="check"><input type="checkbox" name="rows[' . $i . '][_del]" value="1"><span>Entfernen</span></label>') . '</fieldset>';
    }
    echo '</div><div class="admin__save"><button class="btn btn--red">Speichern</button></div></form>';
    admin_end();
}

function admin_faq(): void
{
    admin_rows('faq', 'FAQ', 'faq', ['group' => ['Gruppe', false], 'q' => ['Frage', false], 'a' => ['Antwort', true], 'start' => ['Auf der Startseite zeigen? (1 = ja, leer = nein)', false]]);
}

function admin_contacts(): void
{
    admin_rows('contacts', 'Ansprechpersonen', 'kontakte', ['name' => ['Name', false], 'role' => ['Aufgabe', false], 'email' => ['E-Mail', false], 'phone' => ['Telefon', false], 'photo' => ['Foto (Pfad, z. B. assets/img/team/jan-wille.jpg)', false]]);
}

function admin_images(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slot = (string) ($_POST['slot'] ?? '');
        if (!isset(image_slots()[$slot])) {
            redirect('admin/bilder');
        }
        if (!is_dir(UPLOAD_DIR)) {
            @mkdir(UPLOAD_DIR, 0755, true);
        }
        $clear = function () use ($slot) {
            foreach (glob(UPLOAD_DIR . '/' . $slot . '.*') ?: [] as $old) {
                @unlink($old);
            }
        };
        if (isset($_POST['delete'])) {
            $clear();
            flash('Bild entfernt.');
            redirect('admin/bilder');
        }
        $f = $_FILES['img'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
            flash('Kein Bild empfangen (max. ' . ini_get('upload_max_filesize') . ').', 'err');
            redirect('admin/bilder');
        }
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset($types[$mime]) || !getimagesize($f['tmp_name']) || $f['size'] > 8 * 1024 * 1024) {
            flash('Bitte ein JPG, PNG oder WebP bis 8 MB hochladen.', 'err');
            redirect('admin/bilder');
        }
        $clear();
        $target = UPLOAD_DIR . '/' . $slot . '.' . $types[$mime];
        if (!admin_resize($f['tmp_name'], $target, $mime)) {
            move_uploaded_file($f['tmp_name'], $target);
        }
        flash('Bild gespeichert.');
        redirect('admin/bilder');
    }
    admin_start('Bilder', 'bilder');
    echo '<p class="muted">Bilder werden automatisch auf max. 1600 px verkleinert. Ohne Bild erscheint eine gestaltete Fläche mit Symbol. Ohne eigenes Bild wird das mitgelieferte Platzhalter-Foto gezeigt. „Bild entfernen“ löscht nur Ihr hochgeladenes Bild.</p><div class="cards cards--2">';
    foreach (image_slots() as $slot => $label) {
        $src = slot_image($slot);
        $own = (bool) glob(UPLOAD_DIR . '/' . $slot . '.*');
        echo '<div class="panel stack"><h2 class="h5">' . e($label) . '</h2>';
        echo $src ? '<img class="admin__thumb" src="' . e($src) . '" alt="">' : '<div class="admin__thumb admin__thumb--empty">Kein Bild</div>';
        echo '<p class="muted small">' . ($own ? 'Eigenes Bild' : ($src ? 'Platzhalter-Foto' : 'Ohne Bild')) . '</p>';
        echo '<form method="post" enctype="multipart/form-data" class="btn-row">' . csrf_field() . '<input type="hidden" name="slot" value="' . e($slot) . '"><input type="file" name="img" accept="image/jpeg,image/png,image/webp" required><button class="btn btn--red btn--sm">Hochladen</button></form>';
        if ($own) {
            echo '<form method="post">' . csrf_field() . '<input type="hidden" name="slot" value="' . e($slot) . '"><button class="btn btn--ghost btn--sm" name="delete" value="1">Bild entfernen</button></form>';
        }
        echo '</div>';
    }
    echo '</div>';
    admin_end();
}

function admin_resize(string $src, string $target, string $mime): bool
{
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    [$w, $h] = getimagesize($src);
    $max = 1600;
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($src),
        'image/png' => @imagecreatefrompng($src),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
        default => false,
    };
    if (!$img) {
        return false;
    }
    $scale = min(1, $max / max($w, $h));
    $nw = (int) round($w * $scale);
    $nh = (int) round($h * $scale);
    $out = imagecreatetruecolor($nw, $nh);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return match ($mime) {
        'image/jpeg' => imagejpeg($out, $target, 82),
        'image/png' => imagepng($out, $target, 8),
        'image/webp' => imagewebp($out, $target, 82),
    };
}

function admin_dates(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (glob(CACHE_DIR . '/hiorg-*.html') ?: [] as $f) {
            @unlink($f);
        }
        flash('Zwischenspeicher geleert – Termine werden beim nächsten Aufruf neu geladen.');
        redirect('admin/termine');
    }
    admin_start('Termine prüfen', 'termine');
    echo '<form method="post" class="btn-row">' . csrf_field() . '<button class="btn btn--dark btn--sm">Zwischenspeicher leeren</button></form>';
    echo '<p class="muted small">Termine werden alle ' . e(site('hiorg_cache_minutes') ?: '30') . ' Minuten von HiOrg-Server abgeholt. Hier sehen Sie, was die Website aktuell erkennt.</p>';
    foreach (bookable_courses() as $c) {
        $res = hiorg_dates($c);
        $dup = '';
        $file = hiorg_cache_file(hiorg_first_id($c));
        $age = is_file($file) ? 'Stand: ' . date('d.m.Y H:i', filemtime($file)) : 'nicht abgerufen';
        echo '<details class="panel admin-diag"' . ($dup ? ' open' : '') . '><summary><strong>' . e($c['title']) . '</strong> <span class="pill">' . ($res['ok'] ? count($res['items']) . ' Termine' : 'nicht erreichbar') . '</span> <span class="muted small">' . e($age) . '</span></summary>' . $dup;
        echo '<p class="small"><a href="' . e($res['source']) . '" target="_blank" rel="noopener">' . e($res['source']) . '</a> · <a href="' . url('termine/' . $c['slug']) . '" target="_blank">Seite ansehen</a></p>';
        if ($res['items']) {
            echo '<ul class="dates dates--compact">' . render_dates(array_slice($res['items'], 0, 5)) . '</ul>';
        } elseif ($res['ok']) {
            $html = (string) @file_get_contents($file);
            $txt = trim(preg_replace('/\s+/u', ' ', strip_tags(preg_replace('~<(script|style)[^>]*>.*?</\1>~is', '', $html))));
            echo '<p class="small">Keine Termine erkannt. Textauszug der HiOrg-Seite (zur Kontrolle):</p><pre class="admin-pre">' . e(mb_substr($txt, 0, 1500)) . '</pre>';
        }
        echo '</details>';
    }
    admin_end();
}

function admin_password(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acc = json_decode((string) file_get_contents(ADMIN_FILE), true);
        $new = (string) ($_POST['new'] ?? '');
        if (!password_verify((string) ($_POST['old'] ?? ''), (string) $acc['hash'])) {
            flash('Aktuelles Passwort ist falsch.', 'err');
        } elseif (mb_strlen($new) < 10 || $new !== (string) ($_POST['new2'] ?? '')) {
            flash('Neues Passwort: mindestens 10 Zeichen und zweimal gleich eingeben.', 'err');
        } else {
            $acc['hash'] = password_hash($new, PASSWORD_DEFAULT);
            file_put_contents(ADMIN_FILE, json_encode($acc), LOCK_EX);
            flash('Passwort geändert.');
        }
        redirect('admin/passwort');
    }
    admin_start('Passwort ändern', 'passwort');
    ?>
  <form class="panel form admin__narrow" method="post">
    <?= csrf_field() ?>
    <label class="field"><span>Aktuelles Passwort</span><input type="password" name="old" required autocomplete="current-password"></label>
    <label class="field"><span>Neues Passwort</span><input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
    <label class="field"><span>Neues Passwort wiederholen</span><input type="password" name="new2" required minlength="10" autocomplete="new-password"></label>
    <button class="btn btn--red">Ändern</button>
  </form>
<?php
    admin_end();
}
