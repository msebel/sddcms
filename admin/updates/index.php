<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
$Meta->addJavascript('/scripts/system/formAdmin.js',true);
$Meta->addJavascript('/admin/updates/functions.js',true);
library::loadRelative('library');
$Module = new moduleUpdates();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['add'])) $Module->addUpdate();
if (isset($_GET['save'])) $Module->saveUpdates();
if (isset($_GET['delete'])) $Module->deleteUpdate();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfiguration initialisieren
$Config = array();
$Module->initConfig($Config);

// Daten laden
$Data = array();
$Module->loadData($Data);

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="140">'.$Res->html(535,page::language()).'</td>
		<td class="cNavDisabled" width="140">'.$Res->html(536,page::language()).'</td>
		<td class="cNav" width="140"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
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
				<a href="#" onClick="openWindow(\'/modules/updates/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/world_add.png" alt="'.$Res->html(542,page::language()).'" title="'.$Res->html(542,page::language()).'" border="0">
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
';
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="5">
			<h1>'.$Res->html(540,page::language()).'</h1><br>
		</td>
	</tr>
</table>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:100px;float:left;"><strong>'.$Res->html(365,page::language()).'</strong></div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:50px;float:left;text-align:center"><strong>'.$Res->html(160,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($Data as $row) {
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&link='.$row['lnk_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:16px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['lnk_ID'].'\',\''.addslashes($row['lnk_Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:100px;float:left;">
			<input type="text" name="date[]" value="'.$row['lnk_Date'].'" style="width:90px;" maxlength="10">
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="name[]" value="'.$row['lnk_Name'].'" class="adminBufferInput" maxlength="100">
			<input type="hidden" name="sort[]" value="'.$row['lnk_Sortorder'].'" size="4" maxlength="3">
			<input type="hidden" name="id[]" value="'.$row['lnk_ID'].'">
		</div>
		<div style="width:50px;float:left;text-align:center">
			<div name="divActive">
			<input type="checkbox" name="active_'.$nCount++.'" value="1"'.checkCheckbox(1,$row['lnk_Active']).'>
			</div>
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
$out .= '</div>';

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
			<td><img src="/images/icons/world_add.png" title="'.$Res->html(542,page::language()).'" alt="'.$Res->html(542,page::language()).'"></td>
			<td>'.$Res->html(543,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'"></td>
			<td>'.$Res->html(544,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'"></td>
			<td>'.$Res->html(545,page::language()).'.</td>
		</tr>
	</table>
</div>
';

$out .= '
<script type="text/javascript">
	Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateContentIndizes});
</script>
';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');