<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleLocation();
$Module->loadObjects($Conn,$Res);
$Module->initialize();

// Konfiguration vorladen
$Config = array();
$Module->initConfig($Config);

// Requests verarbeiten
if (isset($_GET['addlocation'])) $Module->addLocation();
if (isset($_GET['addroute'])) $Module->addRoute();
if (isset($_GET['save'])) $Module->saveMap();
if (isset($_GET['deleteroute'])) $Module->deleteRoute();
if (isset($_GET['deletelocation'])) $Module->deleteLocation();

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
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(474,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(423,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'/modules/location/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&addlocation">
				<img src="/images/icons/world_add.png" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&addroute">
				<img src="/images/icons/map_add.png" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0">
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
<h1>'.$Res->html(880,page::language()).'</h1>
'.$sPagingHtml;

// Daten anzeigen (Nicht sortierbar)
$TabRow = new tabRowExtender();

$out .= '
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:25px;float:left;">&nbsp;</div>
		<div style="width:25px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data as $row) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="'.$Module->getEditLink($row['Type'],$row['ID']).'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:25px;float:left;">
			<a href="javascript:deleteConfirm(\''.$Module->getDeleteLink($row['Type'],$row['ID']).'\',\''.addslashes($row['Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:25px;float:left;">
			'.$Module->getIcon($row['Type']).'
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="name[]" value="'.$row['Name'].'" class="adminBufferInput">
			<input type="hidden" name="id[]" value="'.$row['ID'].'">
			<input type="hidden" name="type[]" value="'.$row['Type'].'">
		</div>
	</div>
	';
}
	
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(158,page::language()).' ...</div>
	</div>
	';
}
// Ende der Content Tabelle
$out .= '</div>'.$sPagingHtml.'</form>';


$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/world.png" title="'.$Res->html(878,page::language()).'" alt="'.$Res->html(878,page::language()).'"></td>
			<td>'.$Res->html(881,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/map.png" title="'.$Res->html(879,page::language()).'" alt="'.$Res->html(879,page::language()).'"></td>
			<td>'.$Res->html(882,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(423,page::language()).'" alt="'.$Res->html(423,page::language()).'"></td>
			<td>'.$Res->html(883,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(429,page::language()).'" alt="'.$Res->html(429,page::language()).'"></td>
			<td>'.$Res->html(884,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');