<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
$Meta->addJavascript('/admin/location/library.js',true);
$Meta->addJavascript('/scripts/system/formAdmin.js',true);
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleLocation();
$Module->loadObjects($Conn,$Res);
$Module->initialize();

// Daten laden (Location)
$nBack = getInt($_GET['back']);
$nMrtID = getInt($_GET['mrt']);
// Menu Zugriff prüfen
$Module->checkRouteAccess($nMrtID);

// Requests verarbeiten
if (isset($_GET['saveroute'])) $Module->saveRoute($nMrtID);
if (isset($_GET['addvia'])) $Module->addVia($nMrtID);
if (isset($_GET['deletevia'])) $Module->deleteVia($nMrtID);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$Data = array();
$Module->loadRoute($nMrtID,$Data);

$out .= '
<form name="contentIndex" method="post" action="route.php?id='.page::menuID().'&mrt='.$nMrtID.'&back='.$nBack.'&saveroute">
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
				<a href="route.php?id='.page::menuID().'&mrt='.$nMrtID.'&back='.$nBack.'&addvia">
				<img src="/images/icons/map_add.png" alt="'.$Res->html(895,page::language()).'" title="'.$Res->html(895,page::language()).'" border="0">
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
<h1>'.$Res->html(896,page::language()).'</h1>
<br>
';

$TabRow = new tabRowExtender();

$out .= '
<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
	<div style="width:30px;float:left;">&nbsp;</div>
	<div style="width:60px;float:left;">&nbsp;</div>
	<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
</div>

<div id="contentTableHead">
	<div class="'.$TabRow->get().'" name="tabRowHead[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:30px;float:left;">
			<a href="location.php?id='.page::menuID().'&mrt='.$nMrtID.'&mlc='.$Data['start']['mlc_ID'].'&back='.$nBack.'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:60px;float:left;">
			'.$Res->html(901,page::language()).':
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="hidden" name="startid" value="'.$Data['start']['mlc_ID'].'">
			<input type="text" class="adminBufferInput" name="startname" value="'.$Data['start']['mlc_Name'].'">
		</div>
	</div>
	<div class="'.$TabRow->get().'" name="tabRowHead[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:30px;float:left;">
			<a href="location.php?id='.page::menuID().'&mrt='.$nMrtID.'&mlc='.$Data['goal']['mlc_ID'].'&back='.$nBack.'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:60px;float:left;">
			'.$Res->html(902,page::language()).':
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="hidden" name="goalid" value="'.$Data['goal']['mlc_ID'].'">
			<input type="text" class="adminBufferInput" name="goalname" value="'.$Data['goal']['mlc_Name'].'">
		</div>
	</div>
</div>
';

// Vias / Wegpunkte
$out .= '
<br><h1>'.$Res->html(897,page::language()).'</h1><br>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:20px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data['vias'] as $row) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="location.php?id='.page::menuID().'&mrt='.$nMrtID.'&mlc='.$row['mlc_ID'].'&back='.$nBack.'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="javascript:deleteConfirm(\'route.php?id='.page::menuID().'&mrt='.$nMrtID.'&back='.$nBack.'&deletevia='.$row['mlc_ID'].'\',\''.addslashes($row['mlc_Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="hidden" name="viaid[]" value="'.$row['mlc_ID'].'">
			<input type="text" name="vianame[]" value="'.$row['mlc_Name'].'">
			<input type="hidden" name="sort[]" value="'.$row['mlc_Sortorder'].'">
		</div>
		<div style="width:50px;float:left;">
		
		</div>
		<div style="width:20px;float:left;">
			<a href="#" id="tabRow_'.$nCount.'" onMouseover="SetPointer(this.id,\'move\')" onMouseout="SetPointer(this.id,\'default\')" title="'.$Res->html(214,page::language()).'">
			<img src="/images/icons/arrow_in.png" border="0" alt="'.$Res->html(214,page::language()).'" title="'.$Res->html(214,page::language()).'"></a>
		</div>
	</div>
	';
}
	
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(894,page::language()).' ...</div>
	</div>
	';
}
// Ende der Content Tabelle
$out .= '</div>
<script type="text/javascript">
	Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateSort});
</script>
';

// Hilfetext
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
			<td><img src="/images/icons/map_add.png" title="'.$Res->html(895,page::language()).'" alt="'.$Res->html(895,page::language()).'"></td>
			<td>'.$Res->html(898,page::language()).'.</td>
		</tr>
	</table>
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td width="120">'.$Res->html(43,page::language()).'</td>
			<td>'.$Res->html(44,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(899,page::language()).'</em></td>
			<td valign="top">'.$Res->html(900,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');