<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');

$Module = new moduleFindus();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$Config = array();
$Module->initConfig($Config);

// Speichern der Konfiguration
if (isset($_GET['save'])) $Module->saveConfig($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'/modules/findus/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
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

// Javascript funktion für API Call
$out .= '
<script type="text/javascript">
	function searchLocation(query) {
		ajaxApiCall(\'LocationAvailable\',\'class=google&query=\' + query,\'goalAddressAnswer\');
	}
</script>
';

// Konfigurationen
$out .= '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(864,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(866,page::language()).':
		</td>
		<td>
			<input type="text" value="'.$Config['goalAddress']['Value'].'" name="goalAddress" size="40" onblur="javascript:searchLocation(this.value)"> 
			<div style="float:right;" id="goalAddressAnswer"></div>
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

$TabRow = new tabRowExtender();
// Hilfedialog
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
			<td width="120" valign="top"><em>'.$Res->html(866,page::language()).'</em></td>
			<td valign="top">'.$Res->html(867,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');