<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleFaq();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveConfig();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfiguration laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Toolbar erstellen
$out = '
<form name="configForm" method="post" action="config.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(456,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(457,page::language()).'</td>
		<td class="cNavSelected" width=150">'.$Res->html(329,page::language()).'</td>
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
				<a href="#" onClick="document.configForm.submit()">
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

// Bearbeitungsformular
$out .= '
<table width="100% border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(329,page::language()).'</h1>
			<br>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(471,page::language()).':	
		</td>
		<td>
			<input type="checkbox" value="1" name="displayNumeration"'.checkCheckbox(1,$Config['displayNumeration']['Value']).'> '.$Res->html(472,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(856,page::language()).':	
		</td>
		<td>
			<input type="checkbox" value="1" name="showUnexpanded"'.checkCheckbox(1,$Config['showUnexpanded']['Value']).'> '.$Res->html(857,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2">
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