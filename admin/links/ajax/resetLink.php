<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$tpl->setEmpty();
$Access->control();

$nLink = getInt($_GET['link']);

// Link zurücksetzen
$sSQL = "UPDATE tblink SET lnk_Clicks = 0
WHERE lnk_ID = $nLink AND mnu_ID = ".page::menuID();
$Conn->command($sSQL);

// Erfolg melden
echo '1';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');