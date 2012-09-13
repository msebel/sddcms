<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library.detail');
$Meta->addJavascript('/modules/shop/admin/article/detail.js',false);

$Module = new moduleShopArticleDetail();
$Module->loadObjects($Conn,$Res);
$article = $Module->loadArticle();

// Zeugs machen
if (isset($_GET['addmeta'])) $Module->addMeta($article);
if (isset($_GET['delmeta'])) $Module->deleteMeta($article);
if (isset($_GET['addsize'])) $Module->addSize($article);
if (isset($_GET['delsize'])) $Module->deleteSize($article);
if (isset($_GET['savesizes'])) $Module->saveSizes($article);
if (isset($_GET['savesize'])) $Module->saveSize($article);
if (isset($_GET['addstock'])) $Module->addStockarea($article);
if (isset($_GET['savestock'])) $Module->saveStock($article);
if (isset($_GET['delstock'])) $Module->deleteStock($article);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

$window = htmlControl::window();
$out .= $window->initialize();

// Toolbar erstellen
$out .= '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(37,page::language()).'</a>
		</td>
		<td class="cNav" width="150">
			<a href="edit.php?id='.page::menuID().'&a='.$article->getShaID().'">
				'.$Res->html(1019, page::language()).'
			</a>
		</td>
		<td class="cNavSelected" width="150">
			'.$Res->html(1018,page::language()).'
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
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="javascript:'.$article->getOpenWinCode().'">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(1014,page::language()).'" title="'.$Res->html(1014,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:showHelp()">
				<img src="/images/icons/help.png" alt="'.$Res->html(8, page::language()).'" title="'.$Res->html(8, page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
'.$Module->getArticleSizesForm($article).'
<br>
'.$Module->getArticleSizesList($article).'
<br>
'.$Module->getArticleMetaBlock().'
'.$Module->getArticleMetaAdder($article).'
'.$Module->getArticleMetaList($article).'
<br>
'.$Module->getArticleStockForm($article).'
'.$Module->getArticleStockAdder($article).'
'.$Module->getArticleStockList($article).'
<br>
'.$Module->getArticleGroupForm($article).'
'.$Module->getArticleGroupSelector($article).'
<script type="text/javascript">
	new DetailFormMainClass('.$Module->getOpenBlock().');
</script>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
