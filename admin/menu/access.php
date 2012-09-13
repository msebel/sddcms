<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('menu');
$MenuModule = new moduleMenu();
$MenuModule->loadObjects($Conn,$Res);

library::load('access');
$AccModule = new moduleAccess();
$AccModule->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Neuerungen Speichern
if (isset($_GET['menu'])) $MenuModule->checkEditable();
if (isset($_GET['save'])) $AccModule->saveMenuAccess();

$nMenuID = getInt($_GET['menu']);
// Menudaten und Zugriffe laden
$sMenuData = $MenuModule->loadData($nMenuID);
$sGroupData = $AccModule->loadGroups($nMenuID);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($MenuModule->hasErrorSession() == true) {
	$sMessage = $MenuModule->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// inkludieren von alternativvariable $outAlt
require_once('noaccess.php');

// Toolbar erstellen
$out = '
<form name="menuEdit" method="post" action="access.php?id='.page::menuID().'&menu='.$nMenuID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(108,page::language()).'</a></td>
		<td class="cNav" width="150"><a href="menu.php?id='.page::menuID().'&menu='.$_GET['menu'].'">'.$Res->html(109,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(110,page::language()).'</td>
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
				<a href="#" onClick="document.menuEdit.submit()">
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
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
';


$out .= '
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(110,page::language()).' - '.stringOps::chopString($sMenuData['mnu_Name'],30,true).'</h1><br>
		'.$Res->html(123,page::language()).'.</td>
	</tr>
</table>
<br>
';
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();
// Usergruppen anzeigen
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="25">&nbsp;</td>
		<td>'.$Res->html(95,page::language()).'</td>
	</tr>
';

foreach ($sGroupData as $Group) {
	$sChecked = '';
	if ($Group['ugr_Access'] == true) $sChecked = ' checked';
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td width="25">
			<input type="checkbox" name="checkedUsergroups[]" value="'.$Group['ugr_ID'].'" '.$sChecked.'>
		</td>
		<td>
			'.$Group['ugr_Desc'].'
		</td>
	</tr>
	';
}
// Tabelle abschliessen
$out .= '</table>';

// Ans Template weitergeben, jenachdem. Wenn gesicherter Menupunkt, $out, 
// wenn nicht gesichert die alternative aus noaccess.php, die $outAlt
if ($sMenuData['mnu_Secured'] == 1) {
	$tpl->aC($out);
} else {
	$tpl->aC($outAlt);
}
// System abschliessen
require_once(BP.'/cleaner.php');