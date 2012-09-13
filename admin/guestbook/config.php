<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleGuestbook();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();
// Konfiguration initialisieren
$GBConfig = array();
$Module->initConfig($GBConfig);

// Operationen durchführen
if (isset($_GET['save'])) $Module->saveConfig($GBConfig);
// Meldung generieren wenn vorhanden
$sMessage = '';

if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="gbIndex" method="post" action="config.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(328,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(329,page::language()).'</td>
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
				<a href="#" onClick="document.gbIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
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

// Einleitungstext
$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(336,page::language()).'</h1><br><br>
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(337,page::language()).':</td>
		<td>
			<div style="width:100px;float:left;">
				<input type="radio" value="1" name="activationNeeded"'.checkCheckbox(1,$GBConfig['activationNeeded']['Value']).'> '.$Res->html(231,page::language()).'
			</div>
			<div style="width:100px;float:left;">
				<input type="radio" value="0" name="activationNeeded"'.checkCheckbox(0,$GBConfig['activationNeeded']['Value']).'> '.$Res->html(230,page::language()).'
			</div>
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(338,page::language()).':</td>
		<td>
			<div style="width:100px;float:left;">
				<input type="radio" value="1" name="useCaptcha"'.checkCheckbox(1,$GBConfig['useCaptcha']['Value']).'> '.$Res->html(231,page::language()).'
			</div>
			<div style="width:100px;float:left;">
				<input type="radio" value="0" name="useCaptcha"'.checkCheckbox(0,$GBConfig['useCaptcha']['Value']).'> '.$Res->html(230,page::language()).'
			</div>
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(339,page::language()).':</td>
		<td>
			<input type="text" value="'.$GBConfig['emailAddress']['Value'].'" style="width:200px;" name="emailAddress">
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(340,page::language()).':</td>
		<td>
			<input type="text" value="'.$GBConfig['postsPerPage']['Value'].'" style="width:80px;" name="postsPerPage"> '.$Res->html(328,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(341,page::language()).':</td>
		<td>
			<input type="text" value="'.$GBConfig['SpamLockSecs']['Value'].'" style="width:80px;" name="SpamLockSecs"> '.$Res->html(342,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<br>
		'.$Res->html(396,page::language()).':<br>
		<br>
		'.editor::getSized('Config','htmlCode',page::language(),$GBConfig['htmlCode']['Value'],'100%','250').'
	</tr>
</table>
<br>
<br>
';

$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(337,page::language()).'</em></td>
		<td>'.$Res->html(344,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(338,page::language()).'</em></td>
		<td>'.$Res->html(345,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(339,page::language()).'</em></td>
		<td>'.$Res->html(346,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(341,page::language()).'</em></td>
		<td>'.$Res->html(347,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');