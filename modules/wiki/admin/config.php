<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
library::load('editor');
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);
// Zugriff für Admin prüfen
$Module->checkAdminAccess($Access);
// Initialisieren des Wiki
$Module->initialize();

// Kommandos ausführen
if (isset($_GET['save'])) $Module->saveConfig();

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);
$TabRow = new tabRowExtender();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfigurationsformular anzeigen
$out .= '
<div id="divWikiContent">
<form method="post" action="config.php?id='.page::menuID().'&save" name="config">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbarWoRegister">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="document.config.submit()">
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
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
<h1>'.$Res->html(939,page::language()).'</h1><br>
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			'.$Res->html(940,page::language()).':
		</td>
		<td>
			<input type="text" name="wkiTitle" value="'.$Module->getWiki()->Title.'" style="width:300px;">
		</td>
	</tr>
	<tr>
		<td valign="top">
			'.$Res->html(941,page::language()).':
		</td>
		<td>
			<input type="radio" name="wkiOpen" value="1"'.checkCheckBox(1,$Module->getWiki()->Open).'>
			'.$Res->html(942,page::language()).'<br>
			<input type="radio" name="wkiOpen" value="0"'.checkCheckBox(0,$Module->getWiki()->Open).'>
			'.$Res->html(943,page::language()).'
		</td>
	</tr>
	<tr>
		<td valign="top">
			'.$Res->html(944,page::language()).':
		</td>
		<td>
			<select name="wkiAdminuser" style="width:300px;">
				<option value="0">'.$Res->html(945,page::language()).'</option>
				'.$Module->getUserDropdown($Module->getWiki()->Adminuser,access::ACCESS_ADMIN).'
			</select>
		</td>
	</tr>
	<tr>
		<td valign="top">
			'.$Res->html(946,page::language()).':
		</td>
		<td>
			<select name="wkiCuguser" style="width:300px;">
				<option value="0">'.$Res->html(945,page::language()).'</option>
				'.$Module->getUserDropdown($Module->getWiki()->Cuguser,access::ACCESS_CUG).'
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','wkiText',page::language(),$Module->getWiki()->Text,'100%','250').'
		</td>
	</tr>
</table>
<div id="helpDialog" style="display:none">
<br>
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(941,page::language()).'</em></td>
		<td valign="top">'.$Res->html(947,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(944,page::language()).'</em></td>
		<td valign="top">'.$Res->html(948,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(946,page::language()).'</em></td>
		<td valign="top">'.$Res->html(949,page::language()).'.</td>
	</tr>
</table>
</div>
</form>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');