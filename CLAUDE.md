# DRK Arbeitssicherheit Mittelweser – Hinweise für die Weiterentwicklung

- PHP ≥ 8.0, keine Datenbank, kein Build. Inhalte in `data/content.json` (Admin unter `/admin`).
- Routing nur über `index.php` + `.htaccess`; neue Seite = Case in `index.php` + Template + Eintrag in `templates/sitemap.php`.
- Seitentexte gehören in `content.json` → `pages.{seite}.{feld}`; neue Felder bekommen ein Label in `FIELD_LABELS` (`inc/admin.php`).
- HiOrg-Parser (`inc/hiorg.php`) ist musterbasiert (Datum/Uhrzeit/Preis/Plätze). Bei Layoutänderung bei HiOrg: Admin → Termine zeigt Textauszug.
- Design: Figtree 400/500 (700 nur für kleine Überschriften), DRK-Rot #E60005 sparsam. Menü bleibt auf allen Breiten einzeilig (bei ≤360 px Kurzlabels).
- Öffentliche Seiten setzen keine Cookies (Formular nutzt signiertes Token); Session nur im Admin.
- Sprache: Deutsch, Sie-Form, kurze Texte. Unbekanntes als `[PLATZHALTER]`.
- Lokal testen: `php -S 127.0.0.1:8080` mit einem Router, der `.htaccess` nachbildet.
