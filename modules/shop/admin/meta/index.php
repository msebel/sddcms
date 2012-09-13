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

// Zeugs machen
if (isset($_GET['save'])) $Module->saveMetas();
if (isset($_GET['add'])) $Module->addMeta();
if (isset($_GET['delete'])) $Module->deleteMeta();

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
		<td class="cNavSelected" width="150">
			'.$Res->html(1007,page::language()).'
		</td>
		<td class="cNavDisabled" width="150">
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
				<img src="/images/icons/page_add.png" id="addField" alt="'.$Res->html(1056, page::language()).'" title="'.$Res->html(1056, page::language()).'" border="0">
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
<br>
';

$out .= '
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(1008,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Daten laden
$fields = array();
$Module->loadFields($fields);
$table = htmlControl::admintable();

// Tabelle Grundkonfigurieren
$table->setErrorMessage($Res->html(1050, page::language()), 2);
$table->setLineHeight(25);

// Kopfdaten vorbereiten
$table->setHead(array(
	new adminTableHead(20, '&nbsp;'), // Bearbeiten
	new adminTableHead(20, '&nbsp;'), // Löschen
	new adminTableHead(200, 'Feldname', 1051),
	new adminTableHead(120, 'Feldtyp', 1052),
	new adminTableHead(150, 'Verwendung', 1053),
));

// Tabellendaten durchgehen
foreach ($fields as $field) {
	$table->addRow($field->getSdfID(), array(
		new adminCellIcon(
			'edit.php?id='.page::menuID().'&field='.$field->getSdfID(),
			'/images/icons/bullet_wrench.png',
			$Res->html(1054, page::language()),
			true
		),
		new adminCellDeleteIcon(
			'index.php?id='.page::menuID().'&delete='.$field->getSdfID(),
			$field->getName(),
			$Res->html(1055, page::language()),
			true
		),
		new adminCellInput('name[]', $field->getName()),
		new adminCellText($field->getFieldType()),
		new adminCellText($field->getUsedReadable()),
	));
}

// Tabelle ausgeben
$out .= $table->get();
// Formular schliessen
$out .= '</form>';

// Flying Window für Add Funktion erstellen
$sHtml = '
<form method="post" action="index.php?id='.page::menuID().'&add">
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">'.$Res->html(1057, page::language()).'.</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="100" valign="top">'.$Res->html(1051, page::language()).':</td>
		<td valign="top">
			<input type="text" name="sdfName" style="width:250px;">
		</td>
	</tr>
	<tr>
		<td width="100" valign="top">'.$Res->html(1052, page::language()).':</td>
		<td valign="top">
			<div class="cAddNewFieldShop" id="0" style="width:250px;padding:5px;border-bottom:1px solid #ccc;">
				<input type="text" value="Textfeld" style="width:200px;">
			</div>
			<div class="cAddNewFieldShop" id="1" style="width:250px;padding:5px;border-bottom:1px solid #ccc;">
				<select style="width:200px;">
					<option>Vorgegebene Werte</option>
				</select>
			</div>
			<div class="cAddNewFieldShop" id="2" style="width:250px;padding:5px;border-bottom:1px solid #ccc;">
				Mehrfache Auswahl aus Vorgabewerten:<br>
				<input type="checkbox"> Wahl 1
				<input type="checkbox"> Wahl 2
				<input type="checkbox"> Wahl 3
			</div>
			<input type="hidden" id="sdfType" name="sdfType" value="0">
		</td>
	</tr>
	<tr>
		<td width="100" valign="top">&nbsp;</td>
		<td valign="top">
			<br>
			<input type="submit" name="cmdNew" class="cButton" value="Speichern">
		</td>
	</tr>
</table>
</form>
';

// Erstellen des Window
$Meta->addJavascript('meta.js',true);
$window = htmlControl::window();
$window->add('addField', $sHtml, 'Neues Feld erstellen', 400, 300, 'new AddNewFieldClass()');
$out .= $window->get('addField');

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
