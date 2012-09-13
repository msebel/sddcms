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
$order = $Module->loadOrder();

if (isset($_GET['save'])) {
	$Module->setUrl('/modules/shop/admin/orders/detail.php?id='.page::menuID().'&o='.$order->getShoID());
	$Module->saveOrders();
}

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out .= '
<form name="shopAdminForm" method="post" action="detail.php?id='.page::menuID().'&o='.$order->getShoID().'&save">
<input type="hidden" name="id[]" value="'.$order->getShoID().'">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(1006,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="150">'.$Res->html(1130,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="document.shopAdminForm.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>';

$message = $order->getMessage();
if (strlen($message) == 0)
	$message = $Res->html(656,page::language());

$out .= '
<h1>'.$Res->html(1130,page::language()).' #'.$order->getShoID().'</h1><br>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
	<tr>
		<td width="150">
			'.$Res->html(970,page::language()).' /
			'.$Res->html(727,page::language()).':</td>
		<td>
			'.dateOps::toHumanReadable($order->getDate()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(1087,page::language()).':</td>
		<td>
			'.$message.'
		</td>
	</tr>
	<tr>
		<td>Status:</td>
		<td><select name="state[]">
';

// Status auflisten
foreach ($order->getStates() as $state) {
	$sSelected = checkDropDown($order->getState(), $state[0]);
	$out .= '<option '.$sSelected.' value="'.$state[0].'">'.$state[1];
}
$out .= '
		</select></td>
	</tr>
</table></form>
<br>';

// Warenkorb anzeigen
$out .= $Module->getCart().'<br>';

// Adressen anzeigen
$address = new shopAddress($order->getDeliveryaddress());
$out .= '
<div class="cAddressHalf">
	<h2>'.$Res->html(1136,page::language()).'</h2><br>
	'.$address->toHtml().'
</div>
';

if ($order->getPayment() == shopOrder::PAYMENT_BILL) {
	$address = new shopAddress($order->getBillingaddress());
	$out .= '
	<div class="cAddressHalf">
		<h2>'.$Res->html(1137,page::language()).'</h2><br>
		'.$address->toHtml().'
	</div>
	';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
