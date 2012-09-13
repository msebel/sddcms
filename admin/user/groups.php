<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('user');
$Module = new moduleUser();
$Module->loadObjects($Conn,$Res);

library::load('group');
$Group = new moduleGroup();
$Group->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Aktuellen User holen
$nUserID = getInt($_GET['user']);
// Prüfen ob editiertbar, wenn nicht, geht die Seite auf index.php
if (isset($_GET['user'])) $Module->checkEditable();
// Daten speichern
if (isset($_GET['save'])) $Group->saveGroupsUser($nUserID);

// Gruppen des Users und Gruppen denen er nicht zugehört laden
$GroupsIn = array();
$GroupsNotIn = array();
$Group->loadGroups($GroupsIn,$GroupsNotIn,$nUserID);
$nUserID = getInt($_GET['user']);
$sUserData = $Module->loadData($nUserID);
// Toolbar erstellen
$out = '
<form name="groupForm" method="post" action="groups.php?id='.page::menuID().'&user='.$nUserID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="edit.php?id='.page::menuID().'&user='.$nUserID.'">'.$Res->html(58,page::language()).'</a></td>
		<td class="cNav" width="150"><a href="address.php?id='.page::menuID().'&user='.$nUserID.'">'.$Res->html(59,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(60,page::language()).'</td>
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

// Formulare anzeigen
$out .= '
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="3">	
			<h1>'.$Res->html(74,page::language()).' - '.stringOps::chopString($sUserData['usr_Alias'],30,true).'</h1><br>
			'.$Res->html(75,page::language()).'.
			<br><br><br>
		</td>
	</tr>
	<tr>
		<td width="200"><strong>'.$Res->html(76,page::language()).'</strong>:<br>
			<br>
			<select name="GroupsNotIn[]" size="15" style="width:200px;" multiple>
				'.$Group->getHtml($GroupsNotIn).'
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
			<select name="GroupsIn[]" size="15" style="width:200px;" multiple>
				'.$Group->getHtml($GroupsIn).'
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
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr class="tabRowHead">
		<td width="150">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(78,page::language()).'</em></td>
		<td valign="top">
			'.$Res->html(79,page::language()).'.
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(80,page::language()).'</em></td>
		<td valign="top">
			'.$Res->html(81,page::language()).'.
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(82,page::language()).' 1</em></td>
		<td valign="top">
			'.$Res->html(83,page::language()).'.
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(82,page::language()).' 2</em></td>
		<td valign="top">
			'.$Res->html(84,page::language()).'.
		</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');