<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$tpl->setEmpty();
$Access->control();

$Updates = array();
$nCount = 1;

// Alle Links nach Alphabet auslesen und updaten
$sSQL = "SELECT lnc_ID FROM tblinkcategory WHERE mnu_ID = ".page::menuID()." ORDER BY lnc_Title ASC";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$sUpdate = "UPDATE tblinkcategory SET lnc_Order = $nCount WHERE lnc_ID = ".$row['lnc_ID'];
	array_push($Updates,$sUpdate); $nCount++;
}

// Update Statements ausführen
foreach ($Updates as $Update) {
	$Conn->command($Update);
}

// Erfolg melden
echo ' ...erledigt!';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');