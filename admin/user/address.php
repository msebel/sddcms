<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('user');
$Module = new moduleUser();
$Module->loadObjects($Conn,$Res);
library::load('address');
$Address = new moduleAddress();
$Address->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Prüfen ob editiertbar, wenn nicht, geht die Seite auf index.php
if (isset($_GET['user'])) $Module->checkEditable();
if (isset($_GET['save'])) $Address->saveAddress();
// Daten der Adresse holen
$AddrData = $Address->loadData();
$nUserID = getInt($_GET['user']);
$sUserData = $Module->loadData($nUserID);
// Toolbar erstellen
$out = '
<form name="addressForm" method="post" action="address.php?id='.page::menuID().'&user='.$nUserID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="edit.php?id='.page::menuID().'&user='.$nUserID.'">'.$Res->html(58,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(59,page::language()).'</td>
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
				<a href="#" onClick="document.addressForm.submit();">
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

// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(61,page::language()).' - '.stringOps::chopString($sUserData['usr_Alias'],30,true).'</h1>
			<br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(62,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="firstname" value="'.$AddrData['adr_Firstname'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(63,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="lastname" value="'.$AddrData['adr_Lastname'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(64,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="title" value="'.$AddrData['adr_Title'].'">
		</td>
	</tr>
	<tr>
		<td width="150">Geschlecht:</td>
		<td>
			<select style="width:200px;" class="cTextfield" name="gender">
				<option value="0"'.checkDropDown(0,$AddrData['adr_Gender']).'>- - -</option>
				<option value="1"'.checkDropDown(1,$AddrData['adr_Gender']).'>'.$Res->html(724,page::language()).'</option>
				<option value="2"'.checkDropDown(2,$AddrData['adr_Gender']).'>'.$Res->html(725,page::language()).'</option>
				<option value="3"'.checkDropDown(3,$AddrData['adr_Gender']).'>'.$Res->html(726,page::language()).'</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(65,page::language()).':</td>
		<td>
			<input type="text" style="width:55px;" maxlength="15" class="cTextfield" name="zip" value="'.$AddrData['adr_Zip'].'"> 
			<input type="text" style="width:185px;" maxlength="100" class="cTextfield" name="city" value="'.$AddrData['adr_City'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(66,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="street" value="'.$AddrData['adr_Street'].'">
		</td>
	</tr>
	<tr>
		<td width="150">Adresszusatz:</td>
		<td>
			<input type="text" style="width:250px;" maxlength="255" class="cTextfield" name="addition" value="'.$AddrData['adr_Addition'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(67,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="15" class="cTextfield" name="postbox" value="'.$AddrData['adr_Postbox'].'">
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(68,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="email" value="'.$AddrData['adr_Email'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(69,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="20" class="cTextfield" name="phone" value="'.$AddrData['adr_Phone'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(70,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="20" class="cTextfield" name="mobile" value="'.$AddrData['adr_Mobile'].'">
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
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr class="tabRowHead">
		<td width="150">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(73,page::language()).'</em></td>
		<td valign="top">
			'.$Res->html(71,page::language()).'.
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="20" valign="top"><em>'.$Res->html(64,page::language()).'</em></td>
		<td valign="top">
			'.$Res->html(72,page::language()).'.
		</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');