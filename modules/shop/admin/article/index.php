<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');

$Module = new moduleShopArticles();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveArticles();
if (isset($_GET['delete'])) $Module->deleteArticle();
if (isset($_GET['search'])) $Module->setSearch();
if (isset($_GET['reset'])) $Module->resetSearch();
if (isset($_GET['add'])) $Module->addArticle();

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
		<td class="cNavSelected" width="150">'.$Res->html(1002,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/cart_add.png" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0"></a>
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
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td width="200"><h1>'.$Res->html(1002,page::language()).'</h1><br></td>
		<td>
			<form method="post" action="index.php?id='.page::menuID().'&search">
				<div style="float:right">
					<input type="text" name="searchTerm" value="'.$Module->getSearch().'">
					<input type="submit" name="cmdSearch" value="'.$Res->html(497, page::language()).'" class="cButton">
				</div>
			</form>
		</td>
	</tr>
</table>
<form name="shopAdminForm" method="post" action="index.php?id='.page::menuID().'&save">
';

// Artikel laden
$articles = array();
$paging = $Module->loadArticles($articles);

// Admintabelle erstellen
$table = htmlControl::admintable();
// Tabelle konfigurieren
$table->setErrorMessage($Res->html(1012, page::language()), 3);
$table->setLineHeight(25);
$table->setPaging($paging);
// Tabellenkopf
$table->setHead(array(
	new adminTableHead(20, '&nbsp;'), // Artikel bearbeiten (inkl. Metadaten)
	new adminTableHead(20, '&nbsp;'), // Artikeldetails ansehen (direkt in View)
	new adminTableHead(30, '&nbsp;'), // Artikel löschen
	new adminTableHead(200, 'Artikelname', 1009),
	new adminTableHead(100, 'Preis', 1010),
	new adminTableHead(35, 'Aktiv', 1011),
));

// Tabellendaten einfüllen
foreach ($articles as $article) {
	$table->addRow($article->getShaID(),array(
		new adminCellIcon(
			'edit.php?id='.page::menuID().'&a='.$article->getShaID(),
			'/images/icons/bullet_wrench.png',
			$Res->html(1013, page::language()),
			true
		),
		new adminCellJsIcon(
			$article->getOpenWinCode(),
			'/images/icons/bullet_magnifier.png',
			$Res->html(1014, page::language()),
			true
		),
		new adminCellDeleteIcon(
			'index.php?id='.page::menuID().'&delete='.$article->getShaID(),
			$article->getTitle(),
			$Res->html(1015, page::language()),
			true
		),
		new adminCellInput('title[]', $article->getTitle()),
		new adminCellInput('price[]', $article->getPrice()),
		new adminCellCheckbox('active_'.$article->getShaID(), $article->getActive(), 1, true)
	));
}

// Tabelle ausgeben
$out .= $table->get();
// Formular beenden
$out .= '</form>';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
