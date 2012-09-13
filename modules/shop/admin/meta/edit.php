<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopMeta();
$Module->loadObjects($Conn,$Res);
$field = $Module->loadField();

// Zeugs machen
if (isset($_GET['save'])) $Module->saveMeta($field);
if (isset($_GET['add'])) $Module->addValue($field);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Ein Icon nur für Check/Dropdown
$sHtmlTb = '';
if ($field->getType() == shopDynamicfield::TYPE_SINGLE || $field->getType() == shopDynamicfield::TYPE_MULTIPLE) {
	$sHtmlTb = '
	<div class="cToolbarItem">
		<a href="edit.php?id='.page::menuID().'&field='.$field->getSdfID().'&add">
		<img src="/images/icons/textfield_add.png" alt="" title="" border="0">
		</a>
	</div>';
}

// Toolbar erstellen
$out = '
<form name="shopAdminForm" method="post" action="edit.php?id='.page::menuID().'&field='.$field->getSdfID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(1007,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="150">
			'.$Res->html(1058, page::language()).'
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
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="' . $Res->html(37, page::language()) . '" title="' . $Res->html(37, page::language()) . '" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			'.$sHtmlTb.'
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
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2"><h1>'.$Res->html(1059,page::language()).'</h1><br></td>
	</tr>
';

// Name definieren
$out .= '
<tr>
	<td width="150">'.$Res->html(1051, page::language()).':</td>
	<td>
		<input type="text" name="sdfName" style="width:300px" value="'.$field->getName().'">
	</td>
</tr>
<tr>
	<td width="150">'.$Res->html(1052, page::language()).':</td>
	<td>
		<input type="text" name="sdfType" style="width:300px" value="'.$field->getFieldType().'" disabled>
	</td>
</tr>
';

// Wenn Textfeld, textuellen Dropdown Wert als Default
if ($field->getType() == shopDynamicfield::TYPE_TEXT) {
	$out .= '
	<tr>
		<td width="150">'.$Res->html(1060, page::language()).':</td>
		<td>
			<input type="text" name="sdfDefault" style="width:300px" value="'.$field->getDefault().'">
		</td>
	</tr>
	';
}

// Wenn Dropdown, Wert aus Dropdown als Default definieren
if ($field->getType() == shopDynamicfield::TYPE_SINGLE) {
	$out .= '
	<tr>
		<td width="150">'.$Res->html(1060, page::language()).':</td>
		<td>
			<select name="sdfDefault" style="width:250px">
			<option value="">'.$Res->html(1061, page::language()).'</option>
	';
	$values = $field->getValues();
	foreach ($values as $value) {
		$sChecked = checkDropDown($field->getDefault(), $value);
		$out .= '<option value="'.$value.'"'.$sChecked.'>'.$value.'</option>';
	}
	// Tabellenzeile beenden
	$out .= '</td></tr>';
}

// Verwaltung von Default Einträgen bei Dropdown / Multi
if ($field->getType() == shopDynamicfield::TYPE_SINGLE || $field->getType() == shopDynamicfield::TYPE_MULTIPLE) {
	$out .= '
	<tr>
		<td colspan="2"><br><h1>'.$Res->html(1062, page::language()).'</h1><br></td>
	</tr>
	<td colspan="2">
	';
	$table = htmlControl::admintable();
	// Tabelle konfigurieren
	$table->setErrorMessage($Res->html(1063, page::language()), 1);
	$table->setLineHeight(25);
	$table->setSortable(true);
	// Kopfdaten definieren
	$table->setHead(array(
		new adminTableHead(30, '&nbsp;'),
		new adminTableHead(300, 'Auswählbarer Wert', 1064)
	));
	// Tabelle erstellen
	$msg = $Res->html(213, page::language());
	$values = $field->getValues();
	foreach ($values as $key => $value) {
		$link = 'edit.php?id='.page::menuID().'&delete='.$key;
		$table->addRow($key,array(
			new adminCellDeleteIcon($link, $value, $msg, true),
			new adminCellInput('value[]', $value)
		));
	}
	// Tabelle ausgeben
	$out .= $table->get().'</td></tr>';
}

// Tabelle abschliessen
$out .= '</table>';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
