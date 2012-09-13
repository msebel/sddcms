<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleGallery();
$Module->loadObjects($Conn,$Res);
// Zugriff testen und Fehler melden
$Access->control();

// Operationen durchführen
if (isset($_GET['save'])) $Module->saveText();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="galleryIndex" method="post" action="text.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(386,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(747,page::language()).'</td>
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
				<a href="#" onClick="document.galleryIndex.submit();">
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
				<a href="#" onClick="openWindow(\'/modules/gallery/index.php?id='.page::menuID().'\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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

// Filedaten laden
$Data = array();
$Module->getFileData($Data);
$TabRow = new tabRowExtender();

$out .= '
<table width="100%" cellpadding="3" cellspacing="0">
<tr>
	<td colspan="3">
		<h1>'.$Res->html(749,page::language()).'</h1><br>
	</td>
</tr>
<tr class="tabRowHead">
	<td width="50"><strong>'.$Res->html(169,page::language()).'</strong></td>
	<td width="100"><strong>'.$Res->html(682,page::language()).'</strong></td>
	<td><strong>'.$Res->html(750,page::language()).'</strong></td>
</tr>
';

$nCount = 0;
foreach ($Data as $File) {
	$sFilename = $File->Filename;
	$sFilename = stringOps::chopString($sFilename,20,true);
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td width="70">
			<a href="'.$File->View.'" rel="lightbox[grouped]" target="_blank">
			<img src="getimage.php?id='.page::menuID().'&file='.$File->Filename.'" border="0"></a></td>
		<td width="150">'.$sFilename.'</td>
		<td>
			<input type="hidden" name="filename[]" value="'.$File->Filename.'">
			<input type="text" maxlength="255" class="adminBufferInput" name="filetext[]" value="'.$File->Description.'">
		</td>
	</tr>
	';
	$nCount++;
}

// Abschliessen
if ($nCount == 0) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td colspan="3">
			'.$Res->html(748,page::language()).'
		</td>
	</tr>
	';
}
$out .= '</table></form>';

// Hilfetexte
$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(750,page::language()).'</em></td>
		<td>'.$Res->html(751,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');