<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('linklist');
library::loadRelative('linklistLeft');
library::loadRelative('linklistBelow');
library::loadRelative('linklistSimple');

class linklistConst {
	const TYPE_LEFT = 1;
	const TYPE_BELOW = 2;
	const TYPE_SIMPLE = 3;
}

// Konfiguration initialisieren
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// HTML Code einfügen
if (strlen($Config['htmlCode']['Value']) > 0) {
	stringOps::htmlViewEnt($Config['htmlCode']['Value']);
	$out .= '<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>';
}

// Je nach Modus eine View starten
switch ($Config['viewType']['Value']) {
	case linklistConst::TYPE_BELOW:
		$Linklist = new linklistBelow($Conn,$Res);
		break;
	case linklistConst::TYPE_SIMPLE:
		$Linklist = new linklistSimple($Conn,$Res);
		break;
	case linklistConst::TYPE_LEFT:
	default:
		$Linklist = new linklistLeft($Conn,$Res);
		break;
}

// Galerie HTML zurückbekommen
$Linklist->appendHtml($out);
// HTML kodieren
stringOps::htmlViewEnt($out);
// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');