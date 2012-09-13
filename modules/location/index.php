<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Konfiguration laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Header erstellen
if (strlen($Config['htmlCode']['Value']) > 0) {
	stringOps::htmlViewEnt($Config['htmlCode']['Value']);
	$out .= '<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>';
}

// Map ID holen
$sSQL = "SELECT map_ID FROM tbmap_menu WHERE mnu_ID = ".page::menuID();
$nMapID = getInt($Conn->getFirstResult($sSQL));

// Wenn Map vorhanden, anzeigen sonst Fehler
if ($nMapID > 0) {
	$map = new googleMap();
	// Konfigurieren (Alternative CSS)
	if (strlen($Config['altCssMap']['Value'])) {
		$map->setProperty('MapClass',$Config['altCssMap']['Value']);
	}
	if (strlen($Config['altCssRoute']['Value'])) {
		$map->setProperty('RouteClass',$Config['altCssRoute']['Value']);
	}
	$map->load($nMapID,$Conn);
	$out .= $map->output();
} else {
	$out .= $Res->html(885,page::language());
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');