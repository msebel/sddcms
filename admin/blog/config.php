<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Klassen laden
library::loadRelative('library');
library::load('editor');
// Modulbezogene Funktionsklasse
$Module = new moduleBlog();
$Module->loadObjects($Conn,$Res);

// Daten laden und Kalender starten
$Config = array();
$Module->initConfig($Config);

// Daten verändern
if (isset($_GET['save'])) $Module->saveConfig($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
};

// Toolbar und Einleitung
$out .= '
<form name="contentIndex" method="post" action="config.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(600,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
		<td class="cNavSelected" width="150">'.$Res->html(329,page::language()).'</td>
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
// Kopfbereich, Listendefinition
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(329,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="150">'.$Res->html(626,page::language()).':</td>
		<td width="60">
			<input name="postsPerPage" type="text" style="width:50px;" value="'.$Config['postsPerPage']['Value'].'">
		</td>
		<td>
			'.$Res->html(627,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(531,page::language()).':</td>
		<td width="60">
			<input name="allowComments" type="checkbox" value="1"'.checkCheckbox(1,$Config['allowComments']['Value']).'>
		</td>
		<td>
			'.$Res->html(629,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">&nbsp;</td>
		<td width="60">
			<input name="socialBookmarking" type="checkbox" value="1"'.checkCheckbox(1,$Config['socialBookmarking']['Value']).'>
		</td>
		<td>
			'.$Res->html(855,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="3">
		<br>
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
';

// Tabrow objekt
$TabRow = new tabRowExtender();
// Hilfe
$out .= '

';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');