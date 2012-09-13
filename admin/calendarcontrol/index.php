<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleTeaserCalendar();
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
<form name="contentEdit" method="post" action="index.php?id='.page::menuID().'&element='.$nTapID.'&save">
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
				<a href="#" onClick="document.contentEdit.submit()">
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
		<td colspan="2"><h1>'.$Res->html(547,page::language()).'</h1><br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(659,page::language()).':
		</td>
		<td>
			<select name="menuSource" style="width:200px;">
				<option value="0">- - - </option>
				'.$Module->getCalendarOptions($Config['menuSource']['Value']).'
			</select>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(661,page::language()).':
		</td>
		<td>
			<input type="checkbox" name="viewConcerts" value="1" '.checkCheckbox(1,$Config['viewConcerts']['Value']).'> '.$Res->html(561,page::language()).'<br>
			<input type="checkbox" name="viewOthers" value="1" '.checkCheckbox(1,$Config['viewOthers']['Value']).'> '.$Res->html(562,page::language()).'
		</td>
	</tr>
</table>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');