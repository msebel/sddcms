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

// Suche laden
$sSearch = stringOps::getPostEscaped('wikiSearch', $Conn);
$Results = array();
$Module->loadSearchResults($sSearch, $Results);

// Inhalte ausgeben 
$out .= '
<div id="divWikiContent">
	<div class="cWikiColumn">
		'.$Module->getWikiSearch().'
	</div>
';

foreach ($Results as $Result) {
	// Text verkleinern
	$text = wikiEditor::unparse($Result->Content);
	$text = stringOps::chopString($text,250,true);
	$text = wikiEditor::parseWords($text);
	// Output des Eitnrags anhängen
	$out .= '
	<div class="cWikiColumnSearch">
		<div class="cWikiColumnSearchTitle">'.$Result->Title.'</div>
		'.$text.'
		<a class="cMoreLink" href="show.php?id='.page::menuID().'&article='.$Result->Title.'">
			'.$Res->html(442,page::language()).'
		</a>
	</div>
	';
}

// Meldung, wenn keine Resultate
if (count($Results) == 0) {
	$out .= '
	<div class="cWikiColumnSearch">
		'.$Res->html(996, page::language()).'
	</div>
	';
}

$out .= '</div>';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');