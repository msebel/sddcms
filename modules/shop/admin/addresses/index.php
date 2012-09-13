<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopAddresses();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveAddresses();

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
		<td class="cNavSelected" width="150">'.$Res->html(1005,page::language()).'</td>
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
		<td><h1>'.$Res->html(1005,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Tabelle erstellen und konfigurieren
$table = htmlControl::admintable();
$table->setLineHeight(25);
$table->setErrorMessage($Res->html(1133,page::language()), 1);
$tooltip = htmlControl::tooltip();
$out .= $tooltip->initialize();
// Kopfzeile erstellen
$table->setHead(array(
	new adminTableHead(10,'&nbsp;'),
	new adminTableHead(180, 'E-Mail',986),
	new adminTableHead(180, 'Name/Adresse', 1127),
	new adminTableHead(27, 'RG', 1128),
	new adminTableHead(55, 'Rabatt',1129),
	new adminTableHead(35, 'Aktiv', 160)
));

// Alle Shop-User mit Main-Adresse laden
$sSQL = 'SELECT tbshopuser.shu_ID,tbshopuser.imp_ID,tbshopuser.shu_Billable,
tbshopuser.shu_Condition,tbshopuser.shu_Active,tbshopuser_address.sua_Type,
tbshopuser_address.sad_ID,tbshopaddress.sad_Firstname,tbshopaddress.sad_Lastname,
tbshopaddress.sad_City,tbshopaddress.sad_Street,tbshopaddress.sad_Zip,tbshopaddress.sad_Email
FROM tbshopuser
INNER JOIN tbshopuser_address ON tbshopuser.shu_ID = tbshopuser_address.shu_ID
INNER JOIN tbshopaddress on tbshopuser_address.sad_ID = tbshopaddress.sad_ID
WHERE tbshopuser.man_ID = '.page::mandant().' AND sua_Primary = 1
ORDER BY tbshopaddress.sad_Lastname ASC,tbshopaddress.sad_Firstname ASC';
$nRes = $Conn->execute($sSQL);

// Zeilen darstellen
while ($row = $Conn->next($nRes)) {
	// instanzen und daten laden
	$user = new shopUser();
	$user->loadRow($row);
	$address = new shopAddress();
	$address->loadRow($row);
	// Tooltip für Hauptadresse erstellen
	$sAddrTooltip = $address->getTooltip($tooltip,20);
	// Zeile hinzufügen
	$table->addRow($user->getShuID(),array(
		new adminCellText('&nbsp;'),
		new adminCellText('<a href="mailto:'.$user->getUsername().'">'.$user->getUsernameShortened(20).'</a>',false),
		new adminCellText($sAddrTooltip, false),
		new adminCellCheckbox('bill_'.$user->getShuID(), $user->getBillable(), 1, false),
		new adminCellInput('condition[]',$user->getCondition(),false),
		new adminCellCheckbox('active_'.$user->getShuID(), $user->getActive(), 1, true),
	));
}

$out .= $table->get();

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
