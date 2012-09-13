<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopStockarea();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveAreas();
if (isset($_GET['add'])) $Module->addArea();
if (isset($_GET['delete'])) $Module->deleteArea();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="shopAdminForm" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(1004,page::language()).'</td>
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

$out .= '
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(1004,page::language()).'</h1></td>
	</tr>
</table>
<br>
';

// Tabelle erstellen und konfigurieren
$table = htmlControl::admintable();
$table->setLineHeight(25);
$table->setErrorMessage($Res->html(1084,page::language()), 1);
// Kopfzeile erstellen
$table->setHead(array(
	new adminTableHead(30, '&nbsp;'),
	new adminTableHead(200, 'Name des Lagers', 1080),
	new adminTableHead(200, 'Öffnungszeiten', 1081),
	new adminTableHead(20, 'Versand', 1082)
));

// Alle Lager laden
$sSQL = 'SELECT ssa_ID,man_ID,ssa_Name,ssa_Opening,ssa_Delivery FROM
tbshopstockarea WHERE man_ID = '.page::mandant().' ORDER BY ssa_Name ASC';
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$area = new shopStockarea();
	$area->loadRow($row);
	// Zeile hinzufügen
	$table->addRow($area->getSsaID(),array(
		new adminCellDeleteIcon(
			'index.php?id='.page::menuID().'&delete='.$area->getSsaID(),
			$area->getName(),
			$Res->html(213,page::language()),
			true
		),
		new adminCellInput('name[]', $area->getName()),
		new adminCellInput('opening[]', $area->getOpening()),
		new adminCellCheckbox(
			'send_'.$area->getSsaID(),$area->getDelivery(),1,true
		)
	));
}

// Tabelle ausgeben
$out .= $table->get();

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
