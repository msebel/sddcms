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

// Den gewünschten Beitrag laden (Falls nichts, Meldung)
$entrytext = '';
$entryname = stringOps::getGetEscaped('article',$Conn);
$entry = $Module->getEntryByName($entryname);

// Wenn etwas geladen wurde
if ($entry instanceOf wikiEntry) {
	$entrytext = $entry->Content;
	$entrytext = wikiEditor::parse($entrytext);
	$entrytext = wikiEditor::parseWords($entrytext);
}

// Wenn keine Inhalte
if (strlen($entrytext) == 0) {
	$entrytext = $Res->html(979,page::language());
}

// Inhalte ausgeben 
$out .= '
<div id="divWikiContent">
	'.$entrytext.'
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');