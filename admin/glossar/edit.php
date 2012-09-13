<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Bibliothek laden und Objekte übergeben
library::load('editor');
library::loadRelative('library');
$Module = new moduleGlossary();
$Module->loadObjects($Conn,$Res);
// Kalender JS einbinden
$Meta->addJavascript('/scripts/calendar/cal.js',true);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Item ID validieren
$nConID = getInt($_GET['item']);
$Module->validateEntry($nConID);

// Eintrag speichern
if (isset($_GET['save'])) $Module->saveItem($nConID);

// Kalender Controls erstellen
$Calendar 	= htmlControl::calendar();
$Calendar->add('conDate');

// Dateisessions starten
if(isset($_SESSION['ActualElementID'])) unset($_SESSION['ActualElementID']);
if(isset($_SESSION['ActualOwnerID'])) unset($_SESSION['ActualOwnerID']);
$_SESSION['ActualContentID'] = getInt($_GET['item']);
$_SESSION['ActualMenuID'] = getInt($_GET['id']);
// Eintragsdaten laden
$Data = array();
$Module->loadItem($nConID,$Data);

// Toolbar erstellen
$out = '
<form name="formIndex" method="post" action="edit.php?id='.page::menuID().'&item='.$nConID.'&page='.getInt($_GET['page']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">'.$Res->html(474,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(457,page::language()).'</td>
		<td class="cNav" width="150"><a href="config.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">'.$Res->html(329,page::language()).'</a></td>
		<td class="cNav">&nbsp;</td>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbar">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="document.formIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
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
';

// Formular darstellen
$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(486,page::language()).'</h1>
			<br>
		</td>
	</tr>
	<tr>
		<td valign="top" width="100">
			'.$Res->html(64,page::language()).':
		</td>
		<td>
			<input type="text" class="adminBufferField" name="conTitle" value="'.$Data['con_Title'].'">
		</td>
	</tr>
	<tr>
		<td valign="top" width="100">
			'.$Res->html(365,page::language()).':
		</td>
		<td>
			<input id="conDate" name="conDate" type="text" maxlength="10" style="width:120px;" value="'.$Data['con_Date'].'">
			'.$Calendar->get('conDate').'
		</td>
	</tr>
	<tr>
		<td valign="top" width="100">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="conShowDate" value="1"'.checkCheckbox(1,$Data['con_ShowDate']).'> Datum anzeigen
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(487,page::language()).':<br>
			<br>
			'.editor::get('Default','conContent',page::language(),$Data['con_Content']).'
		</td>
	</tr>
</table>
</form>
';


// Hilfedialog zeigen
$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(64,page::language()).'</em></td>
		<td valign="top">'.$Res->html(488,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');