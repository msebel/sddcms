<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$tpl->setEmpty();
$Access->control();

// Bei allen Links des Menu di Klicks auf 0 setzen
$sSQL = "UPDATE tblink SET lnk_Clicks = 0 WHERE mnu_ID = ".page::menuID();
$Conn->command($sSQL);

// Erfolg melden
echo ' ...erledigt!';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');