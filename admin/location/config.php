<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleLocation();
$Module->loadObjects($Conn,$Res);
$Module->initialize();

// Konfiguration
$nMenuID = page::menuID();
$Config = array();
pageConfig::get($nMenuID,$Conn,$Config);

// Requests verarbeiten
if (isset($_GET['saveconfig'])) $Module->saveConfig($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden (Routen und Locations mit Paging)
$Data = array();
$sPagingHtml = $Module->loadData($Data);

$out .= '
<form name="contentIndex" method="post" action="config.php?id='.page::menuID().'&saveconfig">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(474,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(423,page::language()).'</td>
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
				<a href="#" onClick="document.contentIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'">
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
<br>
<h1>'.$Res->html(329,page::language()).'</h1><br>
';

// Formular anzeigen
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td width="150">'.$Res->html(903,page::language()).':</td>
		<td>
			<input type="text" style="width:50px;" maxlength="3" name="zoom" value="'.$Module->getMapData('map_Zoom').'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(904,page::language()).':</td>
		<td>
			<input type="text" style="width:200px;" name="altCssMap" value="'.$Config['altCssMap']['Value'].'">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(905,page::language()).':</td>
		<td>
			<input type="text" style="width:200px;" name="altCssRoute" value="'.$Config['altCssRoute']['Value'].'">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
</form>
';

// Help Texte
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
			<td width="120" valign="top"><em>'.$Res->html(903,page::language()).'</em></td>
			<td valign="top">'.$Res->html(906,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(904,page::language()).'</em></td>
			<td valign="top">'.$Res->html(907,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(905,page::language()).'</em></td>
			<td valign="top">'.$Res->html(908,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');