# DRK Arbeitssicherheit Mittelweser – Website

PHP-Website (ohne Datenbank) für das Erste-Hilfe- und Brandschutz-Angebot des
DRK-Kreisverbands Verden e.V. (www.drk-sicherheit.de).

## Voraussetzungen
- Apache mit `mod_rewrite` (übliches Webhosting), PHP **8.0 oder neuer** mit `curl`, `dom`, `mbstring`, `gd`
- Schreibrechte für `data/`, `cache/`, `uploads/`

## Hochladen
1. Alle Dateien inkl. `.htaccess` per FTP ins Webverzeichnis laden.
2. `https://www.drk-sicherheit.de/admin` aufrufen → Benutzername + Passwort festlegen.
3. Unter **Admin → Termine** prüfen, ob die HiOrg-Termine erkannt werden.

## Aufbau
| Pfad | Inhalt |
|---|---|
| `index.php` | Front-Controller, alle URLs laufen hier durch (`.htaccess`) |
| `inc/` | Logik: `bootstrap` (Helfer), `hiorg` (Termine), `layout` (Kopf/Fuß/SEO), `form`, `admin` |
| `templates/` | Seitenvorlagen |
| `data/content.json` | **Alle Texte, Kurse, FAQ** – wird im Admin gepflegt, automatische Backups in `data/backups/` |
| `assets/` | CSS, JS, Schriften (lokal, DSGVO-freundlich) |
| `uploads/` | Im Admin hochgeladene Bilder |
| `cache/` | Zwischengespeicherte HiOrg-Terminlisten |

## URLs
`/` · `/erste-hilfe` · `/brandschutz` · `/unternehmen` · `/termine` · `/termine/{kurs}` ·
`/arbeitssicherheit` · `/faq` · `/kontakt` · `/impressum` · `/datenschutz` · `/admin` · `/sitemap.xml` · `/robots.txt`

Keine `.php`/`.html`-Endungen sichtbar; alte Adressen werden per 301 umgeleitet.

## Kurstermine (HiOrg-Server)
Kein iframe: Die öffentlichen Listen `kurse_extern.php?ov=drkv&id=…` werden serverseitig
abgerufen, 30 Min. zwischengespeichert und im eigenen, mobilen Layout angezeigt.
„Buchen“ führt je Termin direkt zur HiOrg-Anmeldung. Ist HiOrg nicht erreichbar, wird die
letzte gespeicherte Liste gezeigt, sonst ein Link zur HiOrg-Seite.
Die HiOrg-Nummer je Kurs wird im Admin unter **Kurse** gepflegt.

## Passwort vergessen
Per FTP `data/admin.json` löschen und `/admin` neu einrichten.

## Instagram-Feed
Unter Admin → Allgemein ein Instagram-Zugangstoken eintragen (Instagram API mit Instagram-Login,
Meta-Entwicklerkonto). Die Beiträge werden stündlich geladen, Bilder lokal in `uploads/instagram/`
gespeichert, das Token wird automatisch verlängert. Ohne Token wird der Bereich ausgeblendet.

## Kursfinder
Der „Kurs finden“-Knopf (unten rechts) fragt nach Zweck, Ort und Tag und zeigt die nächsten freien
Termine. Läuft komplett auf dem eigenen Server, keine Daten an Dritte.
