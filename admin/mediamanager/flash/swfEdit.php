<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/flashCode.php');		// Erzeugt $mediaInstance Objekt

$Meta->addJavascript('/scripts/system/mediamanager.js',true);		// Mediamanager Javascripts

// Zugriff testen und Fehler melden
$Access->control();
$tpl->setPopup();

// Medieninstanz erstellen
$mediaInstance = new mediaInstance($Conn);

// Gesamter Pfad
$sMediapath = $mediaInstance->getProperty('path').$mediaInstance->Progress;

// Gibts Zugriff auf das Element?
$bAccess = mediaLib::checkElementAccess($Conn,$mediaInstance);
$nElementID = $mediaInstance->Element;
if ($bAccess == false) {
	redirect('location: /error.php?type=noAccess');
}

// FLV Daten speichern
if (isset($_POST['cmdSave'])) {
	// Floating switchen
	switch ($_POST['floating']) {
		case 'none': $sFloat = 'none'; break;
		case 'left': $sFloat = 'left'; break;
		case 'right': $sFloat = 'right'; break;
	}
	// Daten in die Instanz schreiben
	$mediaInstance->setProperty('align',$sFloat);
	$mediaInstance->setProperty('thumbed',0);
	$mediaInstance->setProperty('width',getInt($_POST['width']));
	$mediaInstance->setProperty('height',getInt($_POST['height']));
	// Optimale grösse setzen wenn nötig
	if (getInt($_POST['optimalSize'])) {
		$SWFInfo = getimagesize(BP.$sMediapath);
		$mediaInstance->setProperty('width',$SWFInfo[0]);
		$mediaInstance->setProperty('height',$SWFInfo[1]);
	}
	// Elementdaten speichern
	mediaLib::updateElementData($Conn,$Res,$mediaInstance,'flash/swfEdit.php');
}

// Standardgrösse holen, wenn nicht vorhanden
$SWFInfo = getimagesize(BP.$sMediapath);
if (getInt($mediaInstance->getProperty('width')) <= 0) {
	$mediaInstance->setProperty('width',$SWFInfo[0]);
}
if (getInt($mediaInstance->getProperty('height')) <= 0) {
	$mediaInstance->setProperty('height',$SWFInfo[1]);
}
$sFloat = $mediaInstance->getProperty('align');

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($mediaInstance->hasErrorSession() == true) {
	$sMessage = $mediaInstance->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">Flash Optionen</td>
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
				<a href="#" onClick="document.swfForm.submit();">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="/admin/mediamanager/index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
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
<br>
';


$out .= '
<form name="swfForm" method="post" action="swfEdit.php?id='.page::menuID().'">
<input type="hidden" name="cmdSave" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(307,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(302,page::language()).':
		</td>
		<td valign="top">
			<input type="text" size="10" maxlength="4" name="width" value="'.$mediaInstance->getProperty('width').'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(302,page::language()).':
		</td>
		<td valign="top">
			<input type="text" size="10" maxlength="4" name="height" value="'.$mediaInstance->getProperty('height').'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			&nbsp;
		</td>
		<td valign="top">
			<input type="checkbox" name="optimalSize" value="1"> '.$Res->html(308,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(303,page::language()).':
		</td>
		<td valign="top">
			<select name="floating" style="width:200px">
				<option value="none"'.checkDropdown('none',$sFloat).'>'.$Res->html(284,page::language()).'</option>
				<option value="left"'.checkDropdown('left',$sFloat).'>'.$Res->html(285,page::language()).'</option>
				<option value="right"'.checkDropdown('right',$sFloat).'>'.$Res->html(286,page::language()).'</option>
			</select>
		</td>
	</tr>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td>
			<h1>'.$Res->html(304,page::language()).' - '.$mediaInstance->Progress.'</h1>
			<br>
			<br>';
// Video einbinden
flashCode::getSwfCode(
	$sMediapath,
	$mediaInstance->getProperty('width'),
	$mediaInstance->getProperty('height'),
	$mediaInstance->getProperty('align'),
	$out
);

$out .= '</td>
	</tr>
</table>
</form>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');