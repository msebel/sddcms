<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/displayObject.php');		// Allgemeine Mediafunktionen

$Meta->addJavascript('/scripts/system/mediamanager.js',true);		// Mediamanager Javascripts

// Zugriff testen und Fehler melden
$Access->control();
$tpl->setPopup();
// Medieninstanz erstellen
$mediaInstance = new mediaInstance($Conn);

// Gibts Zugriff auf das Element?
$bAccess = mediaLib::checkElementAccess($Conn,$mediaInstance);
$nElementID = $mediaInstance->Element;
if ($bAccess == false) {
	redirect('location: /error.php?type=noAccess');
}

// Fileupload starten
if (isset($_POST['cmdUpload'])) mediaLib::handleUpload($Conn,$mediaInstance,$Res);
// File löschen
if (isset($_GET['delete'])) fileLib::deleteFile($mediaInstance,$Res);
// Filedaten mit jsSave zurückgeben
if (isset($_POST['cmdJsSave'])) mediaLib::jsSave($mediaInstance,$out);
// Filedaten per Datenbank speichern
if (isset($_POST['cmdDbSave'])) mediaLib::dbSave($Conn,$Res,$mediaInstance);

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
		<td class="cNavSelected" width="150">'.$Res->html(248,page::language()).'</td>
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
				<a href="#" onClick="'.mediaLib::getSaveMethod($mediaInstance).'">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="window.close()">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(249,page::language()).'" title="'.$Res->html(249,page::language()).'" border="0">
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
<br>
';

// Formular anzeigen
$out .= '
<form name="fileUpload" action="index.php?id='.page::menuID().'&element='.$nElementID.'" method="post" enctype="multipart/form-data">
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2"><h1>'.$Res->html(250,page::language()).'</h1></td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(251,page::language()).':</td>
		<td>
			<input type="file" name="uploadFile" onChange="checkFile()" size="30" onKeyup="noInputs()" maxlength="'.mediaConst::MAXSIZE_FILES.'">&nbsp;
			<input type="submit" name="cmdUpload" value="'.$Res->html(250,page::language()).'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(263,page::language()).':</td>
		<td>
			<div id="fileOptions_Choose" style="display:block;">
				'.$Res->html(252,page::language()).' ...
			</div>
			<div id="fileOptions_None" style="display:none;">
				'.$Res->html(253,page::language()).'
			</div>
			<div id="fileOptions_Zip" style="display:none;">
				<input type="checkbox" value="1" name="unzip"> '.$Res->html(382,page::language()).'
			</div>
			<div id="fileOptions_Picture" style="display:none;">
				<input type="checkbox" value="1" name="resize" onChange="toggleOptions()"> '.$Res->html(254,page::language()).'
				<div id="resizeOptions" style="display:none;">
				<br>
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
							<td>
								<input type="radio" name="resizeType" value="1" checked> '.$Res->html(255,page::language()).'...
								<input type="radio" name="resizeType" value="2"> '.$Res->html(256,page::language()).'...
								<input type="radio" name="resizeType" value="3"> '.$Res->html(257,page::language()).'...
							</td>
						</tr>
						<tr>
							<td>
								'.$Res->html(275,page::language()).' 
								<input type="text" name="resizeValue" value="" size="4" maxlength="4">
								 ... '.$Res->html(258,page::language()).'
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
				</div>
			</div>
		</td>
	</tr>
</table>
</form>
';
$arrFiles = fileLib::getFileList($nElementID);
// Wenn mehr als ein File, diese Anzeigen
if (count($arrFiles) > 0) {
	$sBackground = option::get('mmCellBackground');
	if ($sBackground == NULL) $sBackground = '#eee';
	$out .= '
	<br>
	<br>
	<table width="100%" cellpadding="0" cellspacing="3" border="0">
		<tr>
			<td><h1>'.$Res->html(248,page::language()).'</h1></td>
		</tr>
	</table>
	<br>
	<form name="fileSelector" method="post" action="index.php?id='.page::menuID().'">
		<input type="hidden" name="selectedFile" value="'.fileLib::getSelectedFile($arrFiles,$mediaInstance).'">
		<input type="hidden" name="cmdDbSave" value="1">
		<input type="hidden" id="fallbackColor" value="'.$sBackground.'">
		<table class="mmFileTable" width="100%" cellpadding="0" cellspacing="1" border="0">
			<tr>
		';
			// Files anzeigen
			fileLib::showFiles($arrFiles,$out,$mediaInstance,$Res);
			$out .= '
			</tr>
		</table>
	</form>
	';
}

// Spezialform für jsSave
$out .= '
<form name="jsSaveForm" method="post" action="index.php?id='.page::menuID().'">
	<input type="hidden" name="selectedFile" value="'.fileLib::getSelectedFile($arrFiles,$mediaInstance).'">
	<input type="hidden" name="cmdJsSave" value="1">
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
		<td width="150">'.$Res->html(251,page::language()).'</td>
		<td>'.$Res->html(262,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(263,page::language()).'</td>
		<td>'.$Res->html(264,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(265,page::language()).'</td>
		<td>'.$Res->html(266,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(267,page::language()).'</td>
		<td>'.$Res->html(268,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(269,page::language()).'</td>
		<td>'.$Res->html(270,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(271,page::language()).'</td>
		<td>'.$Res->html(272,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150">'.$Res->html(273,page::language()).'</td>
		<td>'.$Res->html(274,page::language()).'.</td>
	</tr>
</table>
</div>
';
// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');