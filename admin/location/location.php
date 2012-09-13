<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
$Meta->addJavascript('/admin/location/library.js',true);
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');

$Module = new moduleLocation();
$Module->loadObjects($Conn,$Res);
$Module->initialize();

// Daten laden (Location)
$nBack = getInt($_GET['back']);
$nMlcID = getInt($_GET['mlc']);
$nMrtID = getInt($_GET['mrt']);
// Menu Zugriff prüfen
$Module->checkLocationAccess($nMlcID);

// Requests verarbeiten
if (isset($_GET['savelocation'])) $Module->saveLocation($nMlcID);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$Data = array();
$Module->loadLocation($nMlcID,$Data);

$out .= '
<form name="contentIndex" method="post" action="location.php?id='.page::menuID().'&mlc='.$nMlcID.'&mrt='.$nMrtID.'&back='.$nBack.'&savelocation" onsubmit="return false">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(474,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(423,page::language()).'</td>
		<td class="cNav" width="150"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
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
				<a href="'.$Module->getLocationBacklink().'">
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
<h1>'.$Res->html(886,page::language()).'</h1>
<br>
';

// Formular für Bearbeitung
$additional = $Module->getAddLocationCode($nBack,$Data);
// query, icon, html
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="150">
			'.$Res->html(887,page::language()).':
		</td>
		<td>
			<input type="text" style="width:230px;float:left;" name="query" id="query" value="'.$Data['mlc_Query'].'">
			<div style="float:left;padding:4px;" id="divResult"></div>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(888,page::language()).':
		</td>
		<td>
			<input type="text" style="width:230px;" name="iconurl" id="iconurl" value="'.$Data['mlc_Icon'].'"> 
			<a href="javascript:openFileLibrary();">
			<img src="/images/icons/folder_explore.png" alt="'.$Res->html(687,page::language()).'" title="'.$Res->html(687,page::language()).'" border="0"></a>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(889,page::language()).':
		</td>
		<td>
			<input type="text" style="width:80px;" name="latitude" id="latitude" value="'.$Data['mlc_Latitude'].'" readonly> 
			<input type="text" style="width:80px;" name="longitude" id="longitude" value="'.$Data['mlc_Longitude'].'" readonly>
		</td>
	</tr>
	'.$additional.'
</table>
';

// Events für das Query Formular
$out .= '
<script type="text/javascript">
	addQueryEvent("query");
</script>
';

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
			<td width="120" valign="top"><em>'.$Res->html(887,page::language()).'</em></td>
			<td valign="top">'.$Res->html(890,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(888,page::language()).'</em></td>
			<td valign="top">'.$Res->html(891,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(889,page::language()).'</em></td>
			<td valign="top">'.$Res->html(892,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');