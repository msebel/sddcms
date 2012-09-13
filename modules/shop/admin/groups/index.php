<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopGroups();
$Module->loadObjects($Conn,$Res);

// Actions abfangen
if (isset($_GET['save'])) $Module->saveGroups();
if (isset($_GET['add'])) $Module->addGroup();
if (isset($_GET['delete'])) $Module->deleteGroup();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen, uso
$out = '
<form name="shopAdminForm" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(1003,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(1147,page::language()).'</td>
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
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(425, page::language()).'" title="'.$Res->html(425, page::language()).'" border="0">
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
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
';

$out .= '
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(1003,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Tabelle erstellen und konfigurieren
$table = htmlControl::admintable();
$table->setLineHeight(25);
$table->setErrorMessage($Res->html(1148,page::language()), 2);
// Kopfzeile erstellen
$table->setHead(array(
	new adminTableHead(20, '&nbsp;'),
	new adminTableHead(20, '&nbsp;'),
	new adminTableHead(200, 'Gruppenname', 1117),
	new adminTableHead(75, 'Art./Seite', 1118),
	new adminTableHead(121, 'Ansicht', 1119),
	new adminTableHead(50, 'Liefereinheiten', 1120)
));

// Alle Gruppen laden
$sSQL = 'SELECT sag_ID,sag_Title,sag_Articles,sag_Viewtype,sag_DeliveryEntity FROM
tbshoparticlegroup WHERE man_ID = '.page::mandant().' AND sag_Parent = 0
ORDER BY sag_Title ASC';
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$group = new shopArticlegroup();
	$group->loadRow($row);
	// Zeile hinzufügen
	$table->addRow($group->getSagID(),array(
		new adminCellIcon(
			'edit.php?id='.page::menuID().'&g='.$group->getSagID(),
			'/images/icons/bullet_wrench.png',
			$Res->html(1013, page::language()),
			true
		),
		new adminCellDeleteIcon(
			'index.php?id='.page::menuID().'&delete='.$group->getSagID(),
			$group->getTitle(),
			$Res->html(213,page::language()),
			true
		),
		new adminCellInput('title[]', $group->getTitle()),
		new adminCellInput('articles[]', $group->getArticles()),
		new adminCellDropdown('viewtype[]', $group->getViewTypes(), $group->getViewtype(),false),
		new adminCellInput('delivery[]', $group->getDeliveryEntity(),true),
	));
}

// Tabelle in den Output
$out .= $table->get();

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
