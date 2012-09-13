<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Meta->addJavascript('/modules/wiki/script/wiki.js',true);
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Parameter validieren
$nWkeID = getInt($_GET['entry']);

// Initialisieren des Wiki
$Module->initialize();
$Module->checkWriterAccess($Access);

// Speichern
if (isset($_GET['save'])) $Module->saveWikiEntry();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Daten des Eintrags laden (Alles)
$Entry = array();
$Module->loadWikiEntry($nWkeID,$Entry);
$Editor = new wikiEditor($Conn,$Res,$Meta);

// Wenn Template, dessen Inhalt nehmen
$nTplEntry = getInt($_GET['template']);
if ($nTplEntry > 0) {
	// Entry laden und Inhalt ersetzen
	$Template = array();
	$Module->loadWikiEntry($nTplEntry,$Template);
	$Entry['con_Content'] = $Template['con_Content'];
}

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
				<a href="#" onClick="document.wikiEdit.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="/modules/wiki/index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
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

// Formular ausgeben
$out .= '
<form action="edit.php?id='.page::menuID().'&entry='.$nWkeID.'&save" method="post" name="wikiEdit">
<table width="100%" border="0" cellpadding="3" cellspacing="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(457,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(64,page::language()).':
		</td>
		<td>
			<input type="text" style="width:250px;" name="conTitle" value="'.$Entry['con_Title'].'" disabled>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(449,page::language()).':
		</td>
		<td>
			<input type="text" style="width:250px;" name="conDate" value="'.$Entry['con_Date'].'" disabled>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(976,page::language()).':
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Editor->getEditor('conContent','95%','400px',$Entry['con_Content']).'
		</td>
	</tr>
</table>
</form>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');