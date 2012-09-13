<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen

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

// Fileupload starten
if (isset($_POST['cmdResize'])) {
	mediaLib::resizePictureSave($Res,$mediaInstance);
	// Thumbnail Info setzen
	if (fileLib::hasXLImage(BP.$sMediapath)) {
		$mediaInstance->setProperty('thumbed',1);
	} else {
		$mediaInstance->setProperty('thumbed',0);
	}
	mediaLib::updateElementData($Conn,$Res,$mediaInstance,'images/imgResize.php');
}

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
		<td class="cNavSelected" width="150">'.$Res->html(277,page::language()).'</td>
		'.fileLib::getXLFileHtml(BP.$sMediapath,$Res).'
		<td class="cNav" width="150"><a href="imgInfo.php?id='.page::menuID().'">'.$Res->html(278,page::language()).'</a></td>
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
				<a href="#" onClick="document.pictureForm.submit();">
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
<br>
';

// Breite des Bildes
$sRandom = stringOps::getRandom(50);
$ImageInfo = getimagesize(BP.$sMediapath);
$sImgWidth = ''; $sImgMessage = '';
if ($ImageInfo[0] > 650) {
	$sImgWidth = ' width="650"';
	$sImgMessage = '<em>'.$Res->html(279,page::language()).'!<em><br><br>';
}

$out .= '
<form name="pictureForm" method="post" action="imgResize.php?id='.page::menuID().'">
<input type="hidden" name="cmdResize" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td>
			<h1>'.$Res->html(294,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td>
			<input type="radio" name="resizeType" value="1" checked> '.$Res->html(255,page::language()).'...
			<input type="radio" name="resizeType" value="2"> '.$Res->html(256,page::language()).'...
			<input type="radio" name="resizeType" value="3"> '.$Res->html(257,page::language()).'...
			<input type="radio" name="resizeType" value="4"> '.$Res->html(295,page::language()).'...
		</td>
	</tr>
	<tr>
		<td>
			'.$Res->html(275,page::language()).' 
			<input type="text" name="resizeValue" value="" size="4" maxlength="4">
			 ... '.$Res->html(296,page::language()).'
		</td>
	</tr>
	<tr>
		<td>
			<br>
			<input type="checkbox" name="keepQuality" value="1"> '.$Res->html(259,page::language()).'<br>
			<input type="checkbox" name="sharpPicture" value="1"> '.$Res->html(260,page::language()).'<br>
			<input type="checkbox" name="keepOriginal" value="1"> '.$Res->html(261,page::language()).'
		</td>
	</tr>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td>
			<h1>'.$Res->html(276,page::language()).' - '.$mediaInstance->Progress.'</h1>
			<br>
		</td>
	</tr>
	<tr>
		<td>
			'.$Res->html(277,page::language()).': '.mediaLib::getImageSize(BP.$sMediapath).'
		</td>
	</tr>
	<tr>
		<td>
			<br>
			<br>
			'.$sImgMessage.'
			<img src="'.$sMediapath.'?'.$sRandom.'" border="0"'.$sImgWidth.'>
		</td>
	</tr>
</table>
</form>
';

// Hilfe anzeigen
$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none;">
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="150">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(277,page::language()).'</td>
		<td>'.$Res->html(297,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');