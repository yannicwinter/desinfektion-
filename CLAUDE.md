# DRK Arbeitssicherheit Mittelweser – Hinweise für die Weiterentwicklung

- PHP ≥ 8.0, keine Datenbank, kein Build. Inhalte in `data/content.json` (Admin unter `/admin`).
- Routing nur über `index.php` + `.htaccess`; neue Seite = Case in `index.php` + Template + Eintrag in `templates/sitemap.php`.
- Seitentexte gehören in `content.json` → `pages.{seite}.{feld}`; neue Felder bekommen ein Label in `FIELD_LABELS` (`inc/admin.php`).
- HiOrg-Parser (`inc/hiorg.php`) liest `div.termine-container` (Klassen: termine-start-ende-datum, div-termine-zeit, kurs-ort, freie-plaetze-container, a.button-anmelden); Muster-Erkennung nur als Rückfallebene. Preis kommt aus dem Anmeldeformular (tn_anmeldung.php), 12 Std. Cache.
- HiOrg-IDs (ov=drkv): 3870 Brandschutz, 3871 EH-Ausbildung, 3872 Fortbildung, 3873 Kind, 4007 Hund, 4310 Welpe.
- „Buchen“ führt auf /termine/{kurs}/anmeldung/{kid}: eigene Seite, HiOrg-Formular (tn_anmeldung.php) als iframe eingebettet (HiOrg sendet X-Frame-Options: ALLOWALL). Eigenes Formular nicht möglich: HiOrg-API hat keine Kursanmeldung, Zahlung per PayPal hängt an der HiOrg-Sitzung. `site.hiorg_booking` = „tab“ → neuer Tab.
- Animationen bewusst sparsam: Kreuz blendet einmal ein, dezentes Einblenden beim Scrollen, keine Dauer-Animationen.
- Design: Figtree 400/500 (700 nur für kleine Überschriften), DRK-Rot #E60005 sparsam. Menü bleibt auf allen Breiten einzeilig (bei ≤360 px Kurzlabels).
- Öffentliche Seiten setzen keine Cookies (Formular nutzt signiertes Token); Session nur im Admin.
- Sprache: Deutsch, Sie-Form, kurze Texte. Unbekanntes als `[PLATZHALTER]`.
- Lokal testen: `php -S 127.0.0.1:8080` mit einem Router, der `.htaccess` nachbildet.
