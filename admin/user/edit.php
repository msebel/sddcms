<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('user');
$Module = new moduleUser();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Inkludieren von Javascript Funktionen
$Meta->addJavascript('/admin/user/functions.js',true);

// Prüfen ob editiertbar, wenn nicht, geht die Seite auf index.php
if (isset($_GET['user'])) $Module->checkEditable();
if (isset($_GET['save'])) $Module->editUser();
if (isset($_GET['access'])) $Module->saveAccess();

// Benutzerdaten laden (Resultrow aus Datenbank)
$nUserID = getInt($_GET['user']);
$sUserData = $Module->loadData($nUserID);

// Toolbar erstellen
$out = '
<form name="editUser" method="post" action="edit.php?id='.page::menuID().'&user='.$nUserID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(58,page::language()).'</td>
		<td class="cNav" width="150"><a href="address.php?id='.page::menuID().'&user='.$nUserID.'">'.$Res->html(59,page::language()).'</a></td>
		<td class="cNav" width="150"><a href="groups.php?id='.page::menuID().'&user='.$nUserID.'">'.$Res->html(60,page::language()).'</a></td>
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
				<a href="#" onClick="document.editUser.submit();">
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
			<h1>'.$Res->html(58,page::language()).' - '.stringOps::chopString($sUserData['usr_Alias'],30,true).'</h1>
			<br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(9,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" class="cTextfield" name="alias" value="'.$sUserData['usr_Alias'].'"> <span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(38,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" class="cTextfield" name="name" value="'.$sUserData['usr_Name'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(39,page::language()).':</td>
		<td>
			<input type="password" style="width:250px;" class="cTextfield" name="pass1" value=""> <span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(40,page::language()).':</td>
		<td>
			<input type="password" style="width:250px;" class="cTextfield" name="pass2" value=""> <span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(41,page::language()).':</td>
		<td>
			<select name="start" class="cTextfield" style="width:200px;">
				'.$Menu->getSelectOptions($sUserData['usr_Start']).'
			</select> <span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(42,page::language()).':</td>
		<td>
			<select name="access" class="cTextfield" style="width:200px">
				<option value="0"'.checkDropDown(0,$sUserData['usr_Access']).'>'.$Res->html(23,page::language()).'</option>
				<option value="1"'.checkDropDown(1,$sUserData['usr_Access']).'>'.$Res->html(24,page::language()).'</option>
				<option value="2"'.checkDropDown(2,$sUserData['usr_Access']).'>'.$Res->html(25,page::language()).'</option>
				<option value="3"'.checkDropDown(3,$sUserData['usr_Access']).'>'.$Res->html(26,page::language()).'</option>
			</select> <span class="red">*</span> '.$Module->getUseraccessIcon($sUserData['usr_Access']).'
		</td>
	</tr>
</table>
</form>
<br>
<br>
';

// Flying Window für Benutzerrechte erstellen
$Window = htmlControl::window();
$HTML = $Module->getUseraccessHtml($Menu);
$Title = $Res->html(758,page::language());
$Window->add('windowUseraccess',$HTML,$Title);
$out .= $Window->get('windowUseraccess');

// Hilfe anzeigen
$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none;">
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="175">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="175" valign="top"><em>'.$Res->html(9,page::language()).'</em></td>
		<td valign="top">'.$Res->html(45,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="175" valign="top"><em>'.$Res->html(38,page::language()).'</em></td>
		<td valign="top">'.$Res->html(46,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="175" valign="top"><em>'.$Res->html(41,page::language()).'</em></td>
		<td valign="top">'.$Res->html(47,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="175" valign="top">
			<em>'.$Res->html(759,page::language()).'</em> 
			( <img src="/images/icons/key_add.png" alt="'.$Res->html(758,page::language()).'" title="'.$Res->html(758,page::language()).'"> )
		</td>
		<td valign="top">'.$Res->html(760,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');