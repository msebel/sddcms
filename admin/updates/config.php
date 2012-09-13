<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
$Meta->addJavascript('/admin/links/functions.js',true);
$Meta->addJavascript('/scripts/system/ajax.js',true);
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleUpdates();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$Config = array();
$nMenuID = page::menuID();
pageConfig::get($nMenuID,$Conn,$Config);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveConfig($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="config.php?id='.page::menuID().'&link='.$nLink.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="140"><a href="index.php?id='.page::menuID().'">'.$Res->html(535,page::language()).'</a></td>
		<td class="cNavDisabled" width="140">'.$Res->html(536,page::language()).'</td>
		<td class="cNavSelected" width="140">'.$Res->html(329,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
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
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(329,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(126,page::language()).':
		</td>
		<td>
			<input type="radio" name="futureUpdates" '.checkCheckBox(0,$Config['htmlCode']['Value']).' value="0">
			'.$Res->html(846,page::language()).'<br>
			<input type="radio" name="futureUpdates" '.checkCheckBox(1,$Config['futureUpdates']['Value']).' value="1">
			'.$Res->html(847,page::language()).'<br>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(531,page::language()).':
		</td>
		<td valign="top">
			-> <a href="#" onClick="'.ajaxRequest::response('ajax/resetLinks.php?id='.page::menuID(),'resetLinkResult').'">'.$Res->html(533,page::language()).'</a> 
			<span id="resetLinkResult">&nbsp;</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');