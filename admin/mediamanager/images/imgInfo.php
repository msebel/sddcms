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
if (isset($_POST['cmdSave'])) {
	// Daten abholen und escapen
	$sDesc = stringOps::getPostEscaped('desc',$Conn);
	$sDesc = stringOps::chopString($sDesc,255);
	$sLong = stringOps::getPostEscaped('longdesc',$Conn);
	$sLong = stringOps::chopString($sLong,2000,true);
	// Floating switchen
	switch ($_POST['floating']) {
		case 'none': $sFloat = 'none'; break;
		case 'left': $sFloat = 'left'; break;
		case 'right': $sFloat = 'right'; break;
	}
	// Thumbnail Info setzen
	if (fileLib::hasXLImage(BP.$sMediapath)) {
		$mediaInstance->setProperty('thumbed',1);
	} else {
		$mediaInstance->setProperty('thumbed',0);
	}
	// Daten in die Instanz schreiben
	$mediaInstance->setProperty('desc',$sDesc);
	$mediaInstance->setProperty('longdesc',$sLong);
	$mediaInstance->setProperty('align',$sFloat);
	// Daten speichern in DB
	mediaLib::updateElementData($Conn,$Res,$mediaInstance,'images/imgInfo.php');
}

// $sFloat setzen für Select box
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
		<td class="cNav" width="150"><a href="imgResize.php?id='.page::menuID().'">'.$Res->html(277,page::language()).'</a></td>
		'.fileLib::getXLFileHtml(BP.$sMediapath,$Res).'
		<td class="cNavSelected" width="150">'.$Res->html(278,page::language()).'</td>
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
				<a href="#" onClick="document.infoForm.submit();">
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
$ImageInfo = getimagesize(BP.$sMediapath);
$sImgWidth = ''; $sImgMessage = '';
if ($ImageInfo[0] > 650) {
	$sImgWidth = ' width="650"';
	$sImgMessage = '<em>'.$Res->html(279,page::language()).'!<em><br><br>';
}

$out .= '
<form name="infoForm" method="post" action="imgInfo.php?id='.page::menuID().'">
<input type="hidden" name="cmdSave" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(280,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(281,page::language()).':
		</td>
		<td valign="top">
			<input type="text" maxlength="255" name="desc" value="'.$mediaInstance->getProperty('desc').'" style="width:300px">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(282,page::language()).':
		</td>
		<td valign="top">
			<textarea rows="4" name="longdesc" style="width:300px">'.$mediaInstance->getProperty('longdesc').'</textarea>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(283,page::language()).':
		</td>
		<td valign="top">
			<select name="floating" style="width:200px">
				<option value="none"'.checkDropdown('none',$sFloat).'>'.$Res->html(284,page::language()).'</option>
				<option value="left"'.checkDropdown('left',$sFloat).'>'.$Res->html(285,page::language()).'</option>
				<option value="right"'.checkDropdown('right',$sFloat).'>'.$Res->html(286,page::language()).'</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
			<h1>'.$Res->html(287,page::language()).'</h1><br>
		</td>
	</tr>
';
// Dateigrösse
$sImgPath = BP.$mediaInstance->getProperty('path').$mediaInstance->Progress;
$sSize = (string) filesize($sImgPath) / 1024;
if (stristr($sSize,".") !== false) {
	$sSize = substr($sSize,0,strpos($sSize,".")+3);
}
$sSize .= ' KB';
$ImageInfo = getimagesize($sImgPath);
$sSize .= ' ('.$ImageInfo[0].' x '.$ImageInfo[1].')';
// Dateigrösse XL File
$sXLSize = '';
if (fileLib::hasXLImage($sImgPath)) {
	$sXLSize = (string) filesize(fileLib::getXLVersion($sImgPath)) / 1024;
	if (stristr($sSize,".") !== false) {
		$sXLSize = substr($sXLSize,0,strpos($sXLSize,".")+3);
	}
	$sXLSize .= ' KB';
	$ImageInfo = getimagesize(fileLib::getXLVersion($sImgPath));
	$sXLSize .= ' ('.$ImageInfo[0].' x '.$ImageInfo[1].')';
}

// Dateigrösse anzeigen
$out .= '
	<tr>
		<td width="150" valign="top">
			'.$Res->html(288,page::language()).':
		</td>
		<td valign="top">
			'.$sSize.'
		</td>
	</tr>
';

// Vergrösserungsgrösse
if (strlen($sXLSize) > 0) {
	$out .= '
	<tr>
		<td width="150" valign="top">
			'.$Res->html(201,page::language()).':
		</td>
		<td valign="top">
			'.$Res->html(289,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(290,page::language()).':
		</td>
		<td valign="top">
			'.$sXLSize.'
		</td>
	</tr>
	';
}

$out .= '
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
		<td width="150">'.$Res->html(281,page::language()).'</td>
		<td>'.$Res->html(291,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(282,page::language()).'</td>
		<td>'.$Res->html(292,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(283,page::language()).'</td>
		<td>'.$Res->html(293,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');