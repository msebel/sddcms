<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');

$Module = new moduleShopArticles();
$Module->loadObjects($Conn,$Res);
$article = $Module->loadArticle();

// Zeugs machen
if (isset($_GET['save'])) $Module->saveArticle($article);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="shopAdminForm" method="post" action="edit.php?id='.page::menuID().'&a='.$article->getShaID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(37,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="150">'.$Res->html(1019, page::language()).'</td>
		<td class="cNav" width="150">
			<a href="detail.php?id='.page::menuID().'&a='.$article->getShaID().'">
				'.$Res->html(1018,page::language()).'
			</a>
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
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
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
';

$out .= '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2"><h1>'.$Res->html(1017,page::language()).'</h1><br></td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(1022, page::language()).':</td>
		<td>
			<input type="text" name="shaTitle" value="'.$article->getTitle().'" style="width:300px;">
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(1023, page::language()).':</td>
		<td>
			<input type="text" name="shaGuarantee" value="'.$article->getGuarantee().'" style="width:300px;">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(1024, page::language()).':</td>
		<td>
			<input type="checkbox" name="shaActive" value="1"'.checkCheckBox(1, $article->getActive()).'> '.$Res->html(1025, page::language()).'<br>
			<input type="checkbox" name="shaTip" value="1"'.checkCheckBox(1, $article->getTip()).'> '.$Res->html(1026, page::language()).'<br>
			<input type="checkbox" name="shaNew" value="1"'.checkCheckBox(1, $article->getNew()).'> '.$Res->html(1027, page::language()).'<br>
			<input type="checkbox" name="shaAction" value="1"'.checkCheckBox(1, $article->getAction()).'> '.$Res->html(1028, page::language()).'<br>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(1029, page::language()).':</td>
		<td>
			<input type="text" name="shaArticlenumber" value="'.$article->getArticlenumber().'" style="width:100px;">
		</td>
	</tr>
';

// Preis je nach Aktion anzeigen
if ($article->getAction() == 1) {
	$out .= '
	<tr>
		<td width="150">'.$Res->html(1030, page::language()).':</td>
		<td>
			<input type="text" name="shaPriceAction" value="'.$article->getPriceAction().'" style="width:100px;">
		</td>
	</tr>';
} else {
	$out .= '
	<tr>
		<td width="150">'.$Res->html(1031, page::language()).':</td>
		<td>
			<input type="text" name="shaPrice" value="'.$article->getPrice().'" style="width:100px;">
		</td>
	</tr>';
}

// Lieferentität, wenn so eingeschaltet
if (shopConfig::Delivery()) {
	$out .= '
	<tr>
		<td width="150">'.$Res->html(1032, page::language()).':</td>
		<td>
			<input type="text" name="shaDeliveryEntity" value="'.$article->getDeliveryEntity().'" style="width:100px;">
		</td>
	</tr>
	';
}

// Statistik
$out .= '
<tr>
	<td width="150" valign="top">'.$Res->html(1033, page::language()).':</td>
	<td>
		'.$Res->html(1034, page::language()).' '.$article->getPurchased().' '.$Res->html(1035, page::language()).'<br>
		'.$Res->html(1034, page::language()).' '.$article->getRemoved().' '.$Res->html(1036, page::language()).'<br>
		'.$Res->html(1034, page::language()).' '.$article->getVisited().' '.$Res->html(1037, page::language()).'<br>
	</td>
</tr>
';

// Content ID setzen, damit der Mediamanager funktioniert
$_SESSION['ActualContentID'] = $article->getConID();

// Bildupload
$out .= '
<tr>
	<td width="150" valign="top">'.$Res->html(1038, page::language()).':</td>
	<td>
		'.$Res->html(1039, page::language()).'
		<a href="/admin/mediamanager/index.php?id='.page::menuID().'&element='.$article->getImage().'&caller=content" target="_blank">
		'.$Res->html(246, page::language()).'</a>
		'.$Res->html(1040, page::language()).'
	</td>
</tr>
';

// Editor
$out .= '
<tr>
	<td colspan="2">
		<br>
		'.$Res->html(396,page::language()).':<br>
		<br>
		'.editor::getSized('Config','conContent',page::language(),$article->getContent(),'100%','250').'
	</td>
</tr>
';

// Tabelle und Formular beenden
$out .= '</table></form>';

// Hilfe!
$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td width="150">'.$Res->html(43,page::language()).'</td>
			<td>'.$Res->html(44,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="150" valign="top"><em>'.$Res->html(1023, page::language()).'</em></td>
			<td valign="top">'.$Res->html(1041, page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="150" valign="top"><em>'.$Res->html(1029, page::language()).'</em></td>
			<td valign="top">'.$Res->html(1042, page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="150" valign="top"><em>'.$Res->html(1031, page::language()).'</em></td>
			<td valign="top">'.$Res->html(1043, page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="150" valign="top"><em>'.$Res->html(1038, page::language()).'</em></td>
			<td valign="top">'.$Res->html(1044, page::language()).'.</td>
		</tr>
	</table>
</div>';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
