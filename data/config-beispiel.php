<?php
/**
 * Eigene Einstellungen. Diese Datei in „config.php“ umbenennen (gleicher Ordner data/).
 * data/config.php wird bei Updates nicht mitgeliefert und so nie überschrieben.
 */

// Wartungsmodus: true = Besucher sehen die Wartungsseite, false = Website normal.
// (Im Admin angemeldet sieht man die Website trotzdem normal.)
if (!defined('WARTUNG')) define('WARTUNG', false);

/**
 * Mailversand über Microsoft 365 (Graph): die vier Werte eintragen.
 * Absender = Empfänger: Anfragen aus dem Kontaktformular gehen an GRAPH_SENDER,
 * „Antworten“ schreibt direkt an die anfragende Person.
 */
if (!defined('GRAPH_TENANT_ID'))     define('GRAPH_TENANT_ID',     'PLATZHALTER');
if (!defined('GRAPH_CLIENT_ID'))     define('GRAPH_CLIENT_ID',     'PLATZHALTER');
if (!defined('GRAPH_CLIENT_SECRET')) define('GRAPH_CLIENT_SECRET', 'PLATZHALTER');
if (!defined('GRAPH_SENDER'))        define('GRAPH_SENDER',        'PLATZHALTER');
