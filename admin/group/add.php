<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('group');
$Module = new moduleGroup();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Speichern der Daten
if (isset($_GET['save'])) $Module->addGroup();

// Toolbar erstellen
$out = '
<form name="newGroup" method="post" action="add.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(94,page::language()).'</td>
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
				<a href="#" onClick="document.newGroup.submit();">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'">
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
				&nbsp;'.$Module->checkErrorSession($Res).'
			</div>
		</td>
	</tr>
</table>
<br>
';

// Kopfzeile und formular ausgeben
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(94,page::language()).'</h1>
			<br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(95,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="desc" value=""> 
			<span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(41,page::language()).':</td>
		<td>
			<select name="start" class="cTextfield" style="width:200px;">
				'.$Menu->getSelectOptions().'
			</select> 
			<span class="red">*</span>
		</td>
	</tr>
</table>
</form>
<br>
<br>
';

// Hilfe anzeigen
$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none;">
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="150">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(95,page::language()).'</em></td>
		<td valign="top">'.$Res->html(96,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(41,page::language()).'</em></td>
		<td valign="top">'.$Res->html(97,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');