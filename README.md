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
`/` · `/erste-hilfe` · `/erste-hilfe/{kurs}` · `/brandschutz` · `/brandschutz/{kurs}` · `/arbeitssicherheit` ·
`/termine` · `/termine/{kurs}` · `/termine/{kurs}/anmeldung/{termin}` · `/faq` · `/kontakt` · `/impressum` · `/datenschutz` ·
`/admin` · `/sitemap.xml` · `/robots.txt`

Keine `.php`/`.html`-Endungen sichtbar; alte Adressen werden per 301 umgeleitet.

## Kurstermine (HiOrg-Server)
Kein iframe: Die öffentlichen Listen `kurse_extern.php?ov=drkv&id=…` werden serverseitig
abgerufen, 30 Min. zwischengespeichert und im eigenen, mobilen Layout angezeigt.
„Buchen“ öffnet eine eigene Anmeldeseite mit dem HiOrg-Formular. Ist HiOrg nicht erreichbar, wird die
letzte gespeicherte Liste gezeigt, sonst ein Link zur HiOrg-Seite.
Die HiOrg-Nummer je Kurs wird im Admin unter **Kurse** gepflegt.

## Passwort vergessen
Per FTP `data/admin.json` löschen und `/admin` neu einrichten.

## Instagram-Feed (offizielle Schnittstelle, ohne Fremddienst)
Einmalig, ca. 20–30 Minuten:
1. Instagram-App → Profil → Einstellungen → **Kontotyp und Tools** → auf **professionelles Konto** (Business oder Creator) umstellen.
2. Auf **developers.facebook.com** mit einem Facebook-Konto anmelden → **Meine Apps → App erstellen** → Anwendungsfall
   **„Nachrichten und Inhalte auf Instagram verwalten“** (bzw. Typ „Business“) wählen, Name z. B. „DRK Website“.
3. In der App links **Instagram → API-Einrichtung mit Instagram-Login** öffnen.
4. Unter **„Zugriffsschlüssel generieren“** auf **Konto hinzufügen** klicken und sich mit den Instagram-Zugangsdaten
   von @drk_kreisverband_verden anmelden. Danach **Schlüssel generieren** → Token kopieren.
5. Im Website-Admin unter **Allgemein → Zugangsschlüssel (Token) von Meta** einfügen und speichern.
   Die Website prüft den Schlüssel sofort und zeigt „Verbunden mit @…“.

Danach läuft alles automatisch: Beiträge werden alle 3 Std. abgerufen, Bilder lokal gespeichert, der Schlüssel
wird alle 7 Tage verlängert (sonst liefe er nach 60 Tagen ab). Besucher laden nichts von Instagram.
Ohne Schlüssel erscheinen die Bilder „Instagram 1–6“ (Admin → Bilder, Links unter Allgemein), sonst Platzhalter.

## Kursfinder
Der „Kurs finden“-Knopf (unten rechts) fragt nach Zweck, Ort und Tag und zeigt die nächsten freien
Termine. Läuft komplett auf dem eigenen Server, keine Daten an Dritte.
