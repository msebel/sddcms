<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('group');
$Module = new moduleGroup();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Prüfen ob editiertbar, wenn nicht, geht die Seite auf index.php
if (isset($_GET['group'])) $Module->checkEditable();
$nGroupID = getInt($_GET['group']);
$sGroupData = $Module->loadData($nGroupID);

// Benutzerdaten laden (Resultrow aus Datenbank)
if (isset($_GET['save'])) $Module->saveUsersGroup($nGroupID);

// Gruppen des Users und Gruppen denen er nicht zugehört laden
$UsersIn = array();
$UsersNotIn = array();
$Module->loadUsers($UsersIn,$UsersNotIn,$nGroupID);
// Toolbar erstellen
$out = '
<form name="groupForm" method="post" action="users.php?id='.page::menuID().'&group='.$nGroupID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="edit.php?id='.page::menuID().'&group='.$nGroupID.'">'.$Res->html(100,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(101,page::language()).'</td>
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
				<a href="#" onClick="document.editGroup.submit();">
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
		<td colspan="3">	
			<h1>'.$Res->html(101,page::language()).' - '.$sGroupData['ugr_Desc'].'</h1><br>
			'.$Res->html(102,page::language()).'.
			<br><br><br>
		</td>
	</tr>
	<tr>
		<td width="200"><strong>'.$Res->html(76,page::language()).'</strong>:<br>
			<br>
			<select name="UsersNotIn[]" size="15" style="width:200px;" multiple>
				'.$Module->getHtmlUsers($UsersNotIn).'
			</select>
		</td>
		<td width="60">
			<div style="text-align:center">
				<a href="#" onClick="document.groupForm.submit()">
				<img src="/images/icons/arrow_right.png" border="0">
				</a>
				<br>
				<br>
				<a href="#" onClick="document.groupForm.submit()">
				<img src="/images/icons/arrow_left.png" border="0">
				</a>
			</div>
		</td>
		<td><strong>'.$Res->html(77,page::language()).'</strong>:<br>
			<br>
			<select name="UsersIn[]" size="15" style="width:200px;" multiple>
				'.$Module->getHtmlUsers($UsersIn).'
			</select>
		</td>
	</tr>	
</table>
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
		<td width="150"><em>'.$Res->html(78,page::language()).'</em></td>
		<td>'.$Res->html(103,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150"><em>'.$Res->html(80,page::language()).'</em></td>
		<td>'.$Res->html(104,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150"><em>'.$Res->html(82,page::language()).' 1</em></td>
		<td>'.$Res->html(83,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150"><em>'.$Res->html(82,page::language()).' 2</em></td>
		<td>'.$Res->html(105,page::language()).'</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');