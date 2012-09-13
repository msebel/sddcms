<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopOrders();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveOrders();
if (isset($_GET['search'])) $Module->setSearch();
if (isset($_GET['reset'])) $Module->resetSearch();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(1006,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(1130,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&reset">
				<img src="/images/icons/magifier_zoom_out.png" alt="'.$Res->html(1016,page::language()).'" title="'.$Res->html(1016,page::language()).'" border="0"></a>
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
<form name="frmOrderSearch" id="frmOrderSearch" method="post" action="index.php?id='.page::menuID().'&search">
<h1>'.$Res->html(1006,page::language()).'</h1><br>
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td>
			<div>
				<input type="text" name="searchTerm" value="'.$Module->getSearchTerm().'">
				<input type="submit" name="cmdSearch" value="'.$Res->html(497, page::language()).'" class="cButton">
			</div>
		</td>
		<td align="right">
			<input '.  checkCheckBox(true, $Module->isSetStateFilter()).' type="checkbox" onclick="document.frmOrderSearch.submit()" name="completed" value="true"> inkl. abgeschlossene Auftr&auml;ge (alle)
		</td>
	</tr>
</table>
</form>

<form name="shopAdminForm" method="post" action="index.php?id='.page::menuID().'&save">
';

// Tabelle erstellen und konfigurieren
$table = htmlControl::admintable();
$table->setLineHeight(25);
$table->setErrorMessage($Res->html(1134,page::language()),1);
// Kopfzeile erstellen
$table->setHead(array(
	new adminTableHead(40, '&nbsp;'),
	new adminTableHead(50, 'Nr', 1131),
	new adminTableHead(80, 'Datum', 970),
	new adminTableHead(50, 'Zeit', 727),
	new adminTableHead(70, 'Total', 1132),
	new adminTableHead(130, 'Name', 85),
	new adminTableHead(92, 'Status', 960),
	new adminTableHead(20, '&nbsp;', 0)
));

// Bestellungen laden
$nRes = $Module->loadOrders();
while ($row = $Conn->next($nRes)) {
	$order = new shopOrder();
	$order->loadRow($row);
	$orderaddress = new shopAddress();
	$orderaddress->loadRow($row);
	// Zeile für Name (Es gibt Menschen die Ihre Firma da eintragen...)
	$nametext = $orderaddress->getLastname().' '.$orderaddress->getFirstname();
	$nametextInner = stringOps::chopString($nametext,16,true);
	$nametext = '<span title="'.$nametext.'">'.$nametextInner.'</span>';

	// Zeile hinzufügen
	$table->addRow($order->getShoID(),array(
		new adminCellIcon(
			'detail.php?id='.page::menuID().'&o='.$order->getShoID(),
			'/images/icons/bullet_magnifier.png',
			$Res->html(1135,page::language()),
			true
		),
		new adminCellText($order->getShoID(),false),
		new adminCellText(
			dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$order->getDate()
			),false),
		new adminCellText(
			dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_CLOCK,
				$order->getDate()
			),false),
		new adminCellText($order->getTotal(),false),
		new adminCellText($nametext,false),
		new adminCellDropdown('state[]', $order->getStates(), $order->getState()),
		new adminCellText('<img src="/images/icons/'.$order->getStateIcon().'" >', $bCenter)
	));
}

// Tabelle ausgeben
$out .= $table->get();
$out .= '</form>';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
