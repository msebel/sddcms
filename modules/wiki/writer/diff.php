<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Meta->addJavascript('/modules/wiki/script/wiki.js',true);
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Initialisieren des Wiki
$Module->initialize();
$Module->checkWriterAccess($Access);

// Parameter validieren
$nFirst = getInt($_GET['first']);
$nSecond = getInt($_GET['second']);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);


// Registrierungsformular anzeigen
$out .= '
<div id="divWikiContent">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbarWoRegister">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:showHelp()">
				<img src="/images/icons/help.png" alt="'.$Res->html(8,page::language()).'" title="'.$Res->html(8,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
</div>
';

// Beide Versionen laden
$First = array();
$Module->loadWikiEntry($nFirst,$First);
$Second = array();
$Module->loadWikiEntry($nSecond,$Second);


// Zwei Textfelder anzeigen
$out .= '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>
			<textarea name="conContent" class="diffTextarea" id="left">'.$First['con_Content'].'</textarea>
		</td>
		<td>
			<textarea name="conContent" class="diffTextarea" id="right">'.$Second['con_Content'].'</textarea>
		</td>
	</tr>
</table>
';

/*
// Merging klappte nicht ganz, vorerst primitiver Editor
$Merger = new mergingEditor(
	$First['con_Content'],
	$Second['con_Content'],
	$Meta
);
$out .= $Merger->output();
*/

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');