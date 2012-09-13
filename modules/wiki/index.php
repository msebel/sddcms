<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Initialisieren des Wiki
$Module->initialize();

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Datenliste laden (Alle Einträge)
$Data = array();
$Module->loadWikiList($Data,$Access);

// Listen für Übersicht erstellen
$Newest = array();
$Update = array();
$Module->getOverviewLists($Newest,$Update,$Data);

// Inhalte ausgeben 
$out .= '
<div id="divWikiContent">
	<div class="cWikiColumn">
		'.$Module->getWikiSearch().'
		'.$Module->getWikiBoxes($Newest,$Res->html(977,page::language())).'
	</div>
	<div class="cWikiColumn">
		'.$Module->getWikiBoxes($Update,$Res->html(978,page::language())).'
	</div>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');