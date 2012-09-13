<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('pageconfig');
$Module = new modulePageconfig();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Speichern der Daten
if (isset($_GET['save'])) $Module->editPage();

// Toolbar erstellen
$out = '
<form name="editPage" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(315,page::language()).'</td>
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
				<a href="#" onClick="document.editPage.submit();">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
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
			<h1>'.$Res->html(315,page::language()).'</h1>
			<br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(64,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="pagename" value="'.page::name().'"> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(489,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="title" value="'.page::title().'"> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(316,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="metaauthor" value="'.page::author().'"> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(317,page::language()).':</td>
		<td>
			<input type="text" style="width:250px;" maxlength="100" class="cTextfield" name="verify" value="'.page::verify().'"> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(41,page::language()).':</td>
		<td>
			<select name="start" class="cTextfield" style="width:200px;">
				'.$Menu->getSelectOptions(page::start()).'
			</select> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(401,page::language()).':</td>
		<td>
			<select name="teaser" class="cTextfield" style="width:200px;">
				'.$Module->getTeaserOptions().'
			</select> 
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(318,page::language()).':</td>
		<td>
			<textarea style="width:300px;height:80px;" class="cTextarea" name="metadesc">'.page::metadesc().'</textarea>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(128,page::language()).':</td>
		<td>
			<textarea style="width:300px;height:80px;" class="cTextarea" name="metakeys">'.page::metakeys().'</textarea>
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
		<td width="150" valign="top"><em>'.$Res->html(317,page::language()).'</em></td>
		<td valign="top">'.$Res->html(320,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(41,page::language()).'</em></td>
		<td valign="top">'.$Res->html(321,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(318,page::language()).'</em> '.$Res->html(319,page::language()).'<br><em>'.$Res->html(128,page::language()).'</em></td>
		<td valign="top">'.$Res->html(322,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');