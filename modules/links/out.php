<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$tpl->setEmpty();

// Link ID holen
$nLnkID = getInt($_GET['link']);

// Klicks erhöhen
$sSQL = "UPDATE tblink 
SET lnk_Clicks = lnk_Clicks + 1
WHERE lnk_ID = $nLnkID";
$Conn->command($sSQL);

// Link URL holen
$sSQL = "SELECT lnk_URL FROM tblink 
WHERE lnk_ID = $nLnkID AND mnu_ID = ".page::menuID();
$sLink = $Conn->getFirstResult($sSQL);

// Wenn kein Link vorhanden, Error Link angeben
if (strlen($sLink) == 0) {
	$sLink = '/error.php?type=404';
}

// Weiterleiten zum angegebenen Link
redirect('location: '.$sLink);

// System abschliessen
require_once(BP.'/cleaner.php');