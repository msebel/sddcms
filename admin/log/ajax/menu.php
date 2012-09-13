<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// User auslesen
$nMenuID = getInt($_GET['menu']);
$sSQL = "SELECT mnu_Name,typ_ID FROM tbmenu
WHERE mnu_ID = $nMenuID AND man_ID = ".page::mandant();
$nRes = $Conn->Execute($sSQL);
// Rückgabe String vorbereiten
$sMenu = 'No Menu info';
while ($row = $Conn->next($nRes)) {
	$sMenu = $row['mnu_Name'].' (Typ_ID #'.$row['typ_ID'].')';
}
// Ergebnis choppen und ausgeben
$sMenu = stringOps::chopString($sMenu,15,true);
echo $sMenu;