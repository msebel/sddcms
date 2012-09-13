<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Content Teil
$nMandant = page::mandant();
// Startseite und deren Menutyp herausfinden
$sSQL = 'SELECT man_Start FROM tbmandant WHERE man_ID = '.$nMandant;
$nMenuID = $Conn->getFirstResult($sSQL);
$sSQL = 'SELECT mnu_Path FROM tbmenu WHERE mnu_ID = '.$nMenuID;
$sPath = $Conn->getFirstResult($sSQL);
// Der Controller erledigt den Rest
if (strlen($sPath) > 0) {
	redirect('location: /'.$sPath);
} else {
	redirect('location: /controller.php?id='.$nMenuID);
}

// System abschliessen
require_once(BP.'/cleaner.php');