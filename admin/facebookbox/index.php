<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleFacebookBox();
$Module->loadObjects($Conn,$Res);

// Daten laden
$nTapID = getInt($_GET['element']);
$Config = array();
$Module->initConfig($nTapID,$Config);

// Speichern von Daten, zuvor zugriff checken
$Module->checkAccess();
if (isset($_GET['save'])) $Module->saveConfig($nTapID,$Config);

// Toolbar erstellen
$out = '
<form name="fbBoxEdit" method="post" action="index.php?id='.page::menuID().'&element='.$nTapID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="/admin/teaser/elements.php?id='.page::menuID().'&teaser='.$_SESSION['teaserBackID'].'">'.$Res->html(436,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(437,page::language()).'</td>
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
				<a href="#" onClick="document.fbBoxEdit.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="/admin/teaser/elements.php?id='.page::menuID().'&teaser='.$_SESSION['teaserBackID'].'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
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
				&nbsp;'.$Module->checkErrorSession($Res).'
			</div>
		</td>
	</tr>
</table>
<br>
';

$out .= '
<table width="100%" cellspacing="0" border="0" cellpadding="3">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(1159,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150">
			Facebook Page (Link):
		</td>
		<td>
			<input type="text" name="pageLink" value="'.$Config['pageLink']['Value'].'" style="width:250px;">
		</td>
	</tr>
	<tr>
		<td width="150">
			Boxbreite in Pixeln:
		</td>
		<td>
			<input type="text" name="widthPixel" value="'.$Config['widthPixel']['Value'].'" style="width:50px;">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			<div style="padding-top:5px;">Anzeige:</div>
		</td>
		<td>
			<input type="radio" name="showStream" value="0"'.checkCheckBox(0, $Config['showStream']['Value']).'> 
			Nur "Liker" anzeigen<br>
			<input type="radio" name="showStream" value="1"'.checkCheckBox(1, $Config['showStream']['Value']).'> 
			Pinnwand und "Liker" anzeigen
		</td>
	</tr>
</table>
</form>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');