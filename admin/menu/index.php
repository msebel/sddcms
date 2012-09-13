<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('menu');
$Module = new moduleMenu();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Neuerungen Speichern
if (isset($_GET['newitem'])) $Module->addMenu();
if (isset($_GET['delete'])) $Module->deleteMenu();

// Meldung generieren wenn vorhanden
$sMessage = '';
// Menu neu laden, da eventuell was gespeichert wurde
// was man nun auch sehen sollte
$Menu->reset();
$Menu->loadAllMenuObjects("0");
// Menuobjekte holen
$menuObjects = $Menu->getMenuObjects();
// Aktuelles Menu speichern (für auf/zuklappen)
$currentMenu = $Module->getCurrentMenu($menuObjects);

if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="menuIndex" method="post" action="index.php?id='.page::menuID().'&refresh">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(108,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(109,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(110,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'&newitem=0">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(113,page::language()).'" title="'.$Res->html(113,page::language()).'" border="0">
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
';

// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<br>
<table border="0" width="100%" cellspacing="0" cellpadding="3">
	<tr>
		<td colspan="4">
			<h1>'.$Res->html(108,page::language()).'</h1>
			<br>
		</td>
	</tr>
';

// Alle Menus durchgehen
foreach ($menuObjects as $menuObject) {
	$nPadding = ($menuObject->Level * moduleMenu::MENU_OFFSET) + moduleMenu::MENU_ADDITION;
	// Löschbar oder nicht?
	$nResult = $Module->isLastItem($menuObject->ID);
	// Wenn Result == 0, dann ist es löschbar
	if ($nResult == 0) {
		$aStart = '<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$menuObject->ID.'&menu='.getInt($_GET['menu']).'\',\''.$menuObject->Name.'\','.page::language().')">';
		$sImg = '<img src="/images/icons/page_delete.png" alt="'.$Res->html(111,page::language()).'" title="'.$Res->html(111,page::language()).'" border="0">';
		$aEnd = '</a>';
		// Bestimmen ob Menu für ausklappen verlinkt ist
		$sMenu = stringOps::htmlEnt($menuObject->Name);
	} else {
		$aStart = '';
		$sImg = '<img src="/images/icons/page_delete_disabled.png" alt="'.$Res->html(112,page::language()).'" title="'.$Res->html(112,page::language()).'" border="0">';
		$aEnd = '';
		$sMenu = '<a href="index.php?id='.page::menuID().'&menu='.$menuObject->ID.'">'.stringOps::htmlEnt($menuObject->Name).'</a>';
	}
	// Wenn fünfte hierarchie, keine weitere zulassen
	if ($menuObject->Level <= moduleMenu::MAX_HIERARCHY) {
		$aStartNew = '<a href="index.php?id='.page::menuID().'&newitem='.$menuObject->Item.'&menu='.$menuObject->ID.'">';
		$sImgNew = '<img src="/images/icons/page_add.png" alt="'.$Res->html(113,page::language()).'" title="'.$Res->html(113,page::language()).'" border="0">';
		$aEndNew = '</a>';
	} else {
		$aStartNew = '';
		$sImgNew = '<img src="/images/icons/page_add_disabled.png" alt="'.$Res->html(113,page::language()).'" title="'.$Res->html(113,page::language()).'" border="0">';
		$aEndNew = '';
	}
	// Per Hierarchie checken, ob Menupunkt gezeigt wird
	$bShow = false;
	if ($Module->checkHierarchy($menuObject,$currentMenu)) $bShow = true;
	
	if ($bShow) {
		$out .= '
		<tr class="'.$TabRow->getLine().'">
			<td width="18">
				'.$aStartNew.$sImgNew.$aEndNew.'
			</td>
			<td width="18">
				<a href="menu.php?id='.page::menuID().'&menu='.$menuObject->ID.'">
				<img src="/images/icons/page_edit.png" alt="'.$Res->html(114,page::language()).'" title="'.$Res->html(114,page::language()).'" border="0"></a>
			</td>
			<td width="18">
				'.$aStart.$sImg.$aEnd.'
			</td>
			<td>
				<span style="padding-left:'.$nPadding.'px;">
				'.$sMenu.' ('.$menuObject->Index.')&nbsp;'.$Module->getMenuInfo($menuObject).'
				</span>
			</td>
		</tr>
		';
	}
}

$out .= '
</table>
<br>
<br>
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td>&nbsp;</td>
			<td colspan="3">'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_edit.png" alt="'.$Res->html(114,page::language()).'" title="'.$Res->html(114,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(116,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_add.png" alt="'.$Res->html(113,page::language()).'" title="'.$Res->html(113,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(115,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_add_disabled.png" alt="'.$Res->html(113,page::language()).'" title="'.$Res->html(113,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(121,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_delete.png" alt="'.$Res->html(111,page::language()).'" title="'.$Res->html(111,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(117,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_delete_disabled.png" alt="'.$Res->html(112,page::language()).'" title="'.$Res->html(112,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(118,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/key.png" alt="'.$Res->html(106,page::language()).'" title="'.$Res->html(106,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(119,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/magifier_zoom_out.png" alt="'.$Res->html(107,page::language()).'" title="'.$Res->html(107,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(120,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" width="36"><img src="/images/icons/page_inactive.png" alt="'.$Res->html(443,page::language()).'" title="'.$Res->html(443,page::language()).'" border="0"></td>
			<td colspan="3" valign="top">'.$Res->html(444,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');