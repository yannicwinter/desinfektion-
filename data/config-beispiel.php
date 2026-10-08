<?php
/**
 * Mailversand über Microsoft 365 (Graph).
 * Diese Datei in „config.php“ umbenennen (gleicher Ordner data/) und die vier Werte eintragen.
 * Absender = Empfänger: Anfragen aus dem Kontaktformular gehen an GRAPH_SENDER,
 * „Antworten“ schreibt direkt an die anfragende Person.
 * data/config.php wird bei Updates nicht mitgeliefert und so nie überschrieben.
 */
if (!defined('GRAPH_TENANT_ID'))     define('GRAPH_TENANT_ID',     'PLATZHALTER');
if (!defined('GRAPH_CLIENT_ID'))     define('GRAPH_CLIENT_ID',     'PLATZHALTER');
if (!defined('GRAPH_CLIENT_SECRET')) define('GRAPH_CLIENT_SECRET', 'PLATZHALTER');
if (!defined('GRAPH_SENDER'))        define('GRAPH_SENDER',        'PLATZHALTER');
