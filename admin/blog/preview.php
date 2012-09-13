<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
// Klassen laden
library::loadRelative('library');
library::loadRelative('keywords/library');
// Modulbezogene Funktionsklasse
$Module = new moduleBlog();
$Module->loadObjects($Conn,$Res);
$Module->checkBlogentryAccess();

// Objekt für Keywords
$Keywords = new keywords($Conn);
$Keywords->loadObjects($Conn,$Res);
$Module->setKeywordsObject($Keywords);

// Daten laden und Kalender starten
$Data = array();
$Module->loadBlogentry($Data);

// Titel erstellen
$sNewsHead = '<h1>'.$Data['con_Title'].'</h1>';
if ($Data['con_ShowDate'] == 1 || $Data['con_ShowName'] == 1) {
	$sNewsHead.= '<br>';
	$bPrintedName = false;
	// Erfassennamen anzeigen
	if ($Data['con_ShowName'] == 1) {
		$bPrintedName = true;
		$sNewsHead.= $Res->html(646,page::language()).' ';
		$sNewsHead.= $Module->getUsername($Data['usr_ID']);
	}
	// Datum anzeigen
	if ($Data['con_ShowDate'] == 1) {
		if ($bPrintedName) $sNewsHead .= ', ';
		$sNewsHead .= $Res->html(647,page::language()).' ';
		$sNewsHead .= dateOps::convertDate(
			dateOps::SQL_DATETIME,
			dateOps::EU_DATE,
			$Data['con_Date']
		).' ';
		$sNewsHead .= $Res->html(648,page::language()).' ';
		$sNewsHead .= dateOps::convertDate(
			dateOps::SQL_DATETIME,
			dateOps::EU_CLOCK,
			$Data['con_Date']
		).' ';
		$sNewsHead .= $Res->html(581,page::language());
	}
}
$out = '
<div class="newsHead">
	'.$sNewsHead.'
</div>
<div class="newsContent">
	'.$Data['con_Content'].'
</div>
<div class="cDivider"></div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');