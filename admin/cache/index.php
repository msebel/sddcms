<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleCache();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveConfig();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="cacheIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="200">'.$Res->html(1164,page::language()).'</td>
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
				<a href="#" onClick="document.cacheIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
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
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(1164,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="20">
			<input type="checkbox" name="activateCache" id="activateCache" value="1"'.checkCheckBox(1,option::get('caching')).'>
		</td>
		<td><label for="activateCache">'.$Res->html(1165,page::language()).'</label></td>
	</tr>
</table>
';

// Weitere Infos / Optionen nur zeigen, wenn aktiv
if (option::get('caching') == 1) {
	$out .= '
		<!-- Gnihihi, hier gibts noch nichts zu sehen -->
	';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');