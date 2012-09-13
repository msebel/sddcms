<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleTeaserentry();
$Module->loadObjects($Conn,$Res);

// Speichern von Daten, zuvor zugriff checken
$Module->checkAccess();
if (isset($_GET['save'])) $Module->saveEntry();

// Daten laden
$sData = array();
$Module->loadEntry($sData);
if(isset($_SESSION['ActualContentID'])) unset($_SESSION['ActualContentID']);
$_SESSION['ActualElementID'] = $sData['ele_ID'];
$_SESSION['ActualMenuID'] = page::menuID();
$_SESSION['ActualOwnerID'] = getInt($_GET['element']);

// Toolbar erstellen
$out = '
<form name="contentEdit" method="post" action="index.php?id='.page::menuID().'&element='.getInt($_GET['element']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="/admin/teaser/elements.php?id='.page::menuID().'&teaser='.$_SESSION['teaserBackID'].'">'.$Res->html(436,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(437,page::language()).'</td>
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
				<a href="#" onClick="document.contentEdit.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="/admin/teaser/elements.php?id='.page::menuID().'&teaser='.$_SESSION['teaserBackID'].'">
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
				&nbsp;'.$Module->checkErrorSession($Res).'
			</div>
		</td>
	</tr>
</table>
<br>
';

$out .= '
<table width="100%" cellspacing="0" border="0" cellpadding="3">
	<tr>
		<td><h1>'.$Res->html(438,page::language()).'</h1><br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
</table>
';

// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="150" valign="top">'.$Res->html(172,page::language()).':</td>
		<td valign="top">
			<input disabled name="date_date" type="text" maxlength="10" style="width:100px;" value="'.$sData['date_date'].'"> / 
			<input disabled name="date_time" type="text" maxlength="8" style="width:80px;" value="'.$sData['date_time'].'"> 
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(439,page::language()).':
		</td>
		<td valign="top">
			<select name="menulinkID" style="width:300px;">
				<optgroup label="">
					<option value="0">'.$Res->html(440,page::language()).'</option>
				</optgroup>
				<optgroup label="----------------">
					'.$Menu->getSelectOptions($sData['mnu_ID']).'
				</optgroup>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="top">'.$Res->html(179,page::language()).':<br>
		<br>
		'.editor::get('Default','content',page::language(),$sData['ten_Content']).'
		</td>
	</tr>
</table>
';
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();
// Hilfe anzeigen
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
		<td width="120"><em>'.$Res->html(439,page::language()).'</em></td>
		<td>'.$Res->html(441,page::language()).'.</td>
	</tr>
</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');