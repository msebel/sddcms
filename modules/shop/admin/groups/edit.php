<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');

$Module = new moduleShopGroups();
$Module->loadObjects($Conn,$Res);
$group = $Module->loadGroup();

// Zeugs machen
if (isset($_GET['save'])) {
	$Module->setUrl('/modules/shop/admin/groups/edit.php?id='.page::menuID().'&g='.$group->getSagID());
	$Module->saveGroups();
}

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="shopAdminForm" method="post" action="edit.php?id='.page::menuID().'&g='.$group->getSagID().'&save">
<input type="hidden" name="id[]" value="'.$group->getSagID().'">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(1003,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="150">'.$Res->html(212, page::language()).'</td>
		<td class="cNav" width="150">
			<a href="subgroups.php?id='.page::menuID().'&g='.$group->getSagID().'">'.$Res->html(1147,page::language()).'</a>
		</td>
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
				<a href="#" onClick="document.shopAdminForm.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="'.$Module->getBacklink($group).'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:showHelp()">
				<img src="/images/icons/help.png" alt="'.$Res->html(8, page::language()).'" title="'.$Res->html(8, page::language()).'" border="0">
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

// Tabelle mit Formular
$out .= '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2"><h1>'.$Res->html(1121,page::language()).'</h1><br></td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(1122,page::language()).':</td>
		<td>
			<input type="text" name="title[]" value="'.$group->getTitle().'" style="width:300px;">
		</td>
	</tr>
	<tr>
		<td>'.$Res->html(1123,page::language()).':</td>
		<td>
';

// Durch Ansichtstypen iterieren und Radio-buttons anzeigen
foreach ($group->getViewTypes() as $option) {
	$sSelected = checkCheckBox($group->getViewtype(), $option[0]);
	$out .='<input '.$sSelected.' type="radio" name="viewtype[]" value="'.$option[0].'">&nbsp;'.$option[1];
}

// Tabelle fertigstellen
$out .= '
	</td>
	</tr>
	<tr>
		<td>'.$Res->html(1124,page::language()).':</td>
		<td>
			<input type="text" name="articles[]" value="'.$group->getArticles().'" style="width:50px;">
		</td>
	</tr>
	<tr>
		<td>'.$Res->html(1125,page::language()).':</td>
		<td>
			<input type="text" name="delivery[]" value="'.$group->getDeliveryEntity().'" style="width:50px;">
		</td>
	</tr>
';

// Mediamanager auf die Gruppe vorbereiten
$_SESSION['Shop:ArticleGroup'] = $group->getSagID();
// Bildupload
$out .= '
<tr>
	<td width="150" valign="top">'.$Res->html(1038, page::language()).':</td>
	<td>
		'.$Res->html(1039, page::language()).'
		<a href="/admin/mediamanager/index.php?id='.page::menuID().'&element='.$group->getImage().'&caller=content" target="_blank">
		'.$Res->html(246, page::language()).'</a>
		'.$Res->html(1040, page::language()).'
	</td>
</tr>
';

// Editor
$out .= '
<tr>
	<td colspan="2">
		<br>'.$Res->html(1126,page::language()).':<br>
		<br>
		'.editor::getSized('Config','desc[]',page::language(),$group->getDesc(),'100%','250').'
	</td>
</tr>
';

// Tabelle und Formular beenden
$out .= '</table></form>';

// Hilfe!
$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td width="150">'.$Res->html(43,page::language()).'</td>
			<td>'.$Res->html(44,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="150" valign="top"><em>'.$Res->html(1038, page::language()).'</em></td>
			<td valign="top">'.$Res->html(1044, page::language()).'.</td>
		</tr>
	</table>
</div>';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');