<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('updatelist');
library::loadRelative('updatelistTable');

class updatelistConst {
	const TYPE_TABLE = 1;
}

// Konfiguration initialisieren
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// HTML Code einfügen
if (strlen($Config['htmlCode']['Value']) > 0) {
	stringOps::htmlViewEnt($Config['htmlCode']['Value']);
	$out .= '<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>';
}

// Je nach Modus eine Galerie starten
switch ($Config['viewType']['Value']) {
	case updatelistConst::TYPE_TABLE:
	default:
		$Updatelist = new UpdatelistTable($Conn,$Res,$Config);
		break;
}

// Galerie HTML zurückbekommen
$Updatelist->appendHtml($out);
// HTML kodieren
stringOps::htmlViewEnt($out);
// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');