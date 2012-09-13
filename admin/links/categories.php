<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleLink();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['add'])) $Module->addCategory();
if (isset($_GET['delete'])) $Module->deleteCategory();
if (isset($_GET['save'])) $Module->saveCategories();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfiguration initialisieren
$Config = array();
$Module->initConfig($Config);

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="categories.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="140">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(509,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="140">'.$Res->html(620,page::language()).'</td>
		<td class="cNav" width="140">
			<a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a>
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
				<a href="#" onClick="openWindow(\'/modules/links/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="categories.php?id='.page::menuID().'&add">
				<img src="/images/icons/tag_blue_add.png" alt="'.$Res->html(632,page::language()).'" title="'.$Res->html(632,page::language()).'" border="0">
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
		<td><h1>'.$Res->html(1155,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Tabellenkopf erstellen
// Tabelle erstellen und konfigurieren
$table = htmlControl::admintable();
$table->setLineHeight(25);
$table->setSortable(true);
$table->setErrorMessage($Res->html(1063,page::language()), 1);
$tooltip = htmlControl::tooltip();
$out .= $tooltip->initialize();
// Kopfzeile erstellen
$table->setHead(array(
	new adminTableHead(30,'&nbsp;'),
	new adminTableHead(400, 'Name der Kategorie',1156)
));

$sSQL = 'SELECT lnc_ID,lnc_Title FROM tblinkcategory
WHERE mnu_ID = '.page::menuID().' ORDER BY lnc_Order ASC';
$nRes = $Conn->execute($sSQL);

while ($row = $Conn->next($nRes)) {
	$table->addRow($row['lnc_ID'],array(
		new adminCellDeleteIcon(
			'categories.php?id='.page::menuID().'&delete='.$row['lnc_ID'],
			$row['lnc_Title'],
			$Res->html(213, page::language()),
			true
		),
		new adminCellInput('lncTitle[]', $row['lnc_Title'])
	));
}

$out .= $table->get();


// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');