<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::load('content');
$Module = new moduleContent();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$NewsConfig = array();
pageConfig::get(page::menuID(),$Conn,$NewsConfig);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveNewsConfig($NewsConfig);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="configIndex" method="post" action="config.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(366,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(367,page::language()).'</td>
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
				<a href="#" onClick="document.configIndex.submit()">
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
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="5">
			<h1>'.$Res->html(329,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(371,page::language()).':</td>
		<td>
			<div style="width:100px;float:left;">
				<input type="radio" value="1" name="showName"'.checkCheckbox(1,$NewsConfig['showName']['Value']).'> '.$Res->html(231,page::language()).'
			</div>
			<div style="width:100px;float:left;">
				<input type="radio" value="0" name="showName"'.checkCheckbox(0,$NewsConfig['showName']['Value']).'> '.$Res->html(230,page::language()).'
			</div>
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(372,page::language()).':</td>
		<td>
			<div style="width:100px;float:left;">
				<input type="radio" value="1" name="shortnews"'.checkCheckbox(1,$NewsConfig['shortnews']['Value']).'> '.$Res->html(231,page::language()).'
			</div>
			<div style="width:100px;float:left;">
				<input type="radio" value="0" name="shortnews"'.checkCheckbox(0,$NewsConfig['shortnews']['Value']).'> '.$Res->html(230,page::language()).'
			</div>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(531,page::language()).':</td>
		<td width="60">
			<input name="socialBookmarking" type="checkbox" value="1"'.checkCheckbox(1,$NewsConfig['socialBookmarking']['Value']).'> 
			'.$Res->html(855,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="180">'.$Res->html(340,page::language()).':</td>
		<td>
			<input type="text" value="'.$NewsConfig['postsPerPage']['Value'].'" style="width:80px;" name="postsPerPage"> '.$Res->html(328,page::language()).'
		</td>
	</tr>
	'.$Module->getRssConfig($NewsConfig).'
	<tr>
		<td colspan="2">
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$NewsConfig['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
';

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="180">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top">'.$Res->html(371,page::language()).'</td>
		<td>'.$Res->html(373,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top">'.$Res->html(372,page::language()).'</td>
		<td>'.$Res->html(374,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');