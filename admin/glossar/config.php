<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Bibliothek laden und Objekte übergeben
library::load('editor');
library::loadRelative('library');
$Module = new moduleGlossary();
$Module->loadObjects($Conn,$Res);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfiguration erstellen/laden
$Config = array();
$Module->initConfig($Conn,$Config);
// Konfiguration speichern wenn erforderlich
if (isset($_GET['save'])) $Module->saveConfig($Config);

// Toolbar erstellen
$out = '
<form name="formIndex" method="post" action="config.php?id='.page::menuID().'&page='.getInt($_GET['page']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">'.$Res->html(474,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(457,page::language()).'</td>
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
				<a href="#" onClick="document.formIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">
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
';

$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(329,page::language()).'</h1>
			<br>
		</td>
	</tr>
	<tr>
		<td>
			<input type="radio" name="viewType" value="1"'.checkCheckbox(1,$Config['viewType']['Value']).'> '.$Res->html(479,page::language()).'<br>
			<br>
			<table width="80%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="cNav"><a href="#">A - C</a></td>
					<td class="cNav"><a href="#">D - F</a></td>
					<td class="cNav"><a href="#">G - I</a></td>
					<td class="cNav"><a href="#">J - L</a></td>
					<td class="cNav"><a href="#">M - O</a></td>
					<td class="cNav"><a href="#">P - S</a></td>
					<td class="cNav"><a href="#">T - V</a></td>
					<td class="cNav"><a href="#">W - Z</a></td>
					<td class="cNav"><a href="#">0 - 9</a></td>
					<td class="cNav"><a href="#">#</a></td>
				</tr>
			</table>
			<br>
			<br>
			<input type="radio" name="viewType" value="2"'.checkCheckbox(2,$Config['viewType']['Value']).'> '.$Res->html(480,page::language()).'<br>
			<br>
			<table width="80%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="cNav"><a href="#">A</a></td>
					<td class="cNav"><a href="#">B</a></td>
					<td class="cNav"><a href="#">C</a></td>
					<td class="cNav"><a href="#">D</a></td>
					<td class="cNav"><a href="#">E</a></td>
					<td class="cNav"><a href="#">F</a></td>
					<td class="cNav"><a href="#">G</a></td>
					<td class="cNav"><a href="#">H</a></td>
					<td class="cNav"><a href="#">I</a></td>
					<td class="cNav"><a href="#">J</a></td>
					<td class="cNav"><a href="#">K</a></td>
					<td class="cNav"><a href="#">L</a></td>
					<td class="cNav"><a href="#">M</a></td>
					<td class="cNav"><a href="#">N</a></td>
					<td class="cNav"><a href="#">O</a></td>
					<td class="cNav"><a href="#">P</a></td>
					<td class="cNav"><a href="#">Q</a></td>
					<td class="cNav"><a href="#">R</a></td>
					<td class="cNav"><a href="#">S</a></td>
					<td class="cNav"><a href="#">T</a></td>
					<td class="cNav"><a href="#">U</a></td>
					<td class="cNav"><a href="#">V</a></td>
					<td class="cNav"><a href="#">W</a></td>
					<td class="cNav"><a href="#">X</a></td>
					<td class="cNav"><a href="#">Y</a></td>
					<td class="cNav"><a href="#">Z</a></td>
					<td class="cNav"><a href="#">0-9</a></td>
					<td class="cNav"><a href="#">#</a></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<br>
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
';

// Hilfedialog zeigen
$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(481,page::language()).'</em></td>
		<td valign="top">'.$Res->html(482,page::language()).'</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');