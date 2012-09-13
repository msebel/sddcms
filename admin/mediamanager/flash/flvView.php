<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/flashCode.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/deprecated/content/content.php');				// Allgemeine Mediafunktionen

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

// Grösse des Videos
if (strlen($mediaInstance->getProperty('width')) == 0) {
	$mediaInstance->setProperty('width',300);
}
if (strlen($mediaInstance->getProperty('height')) == 0) {
	$mediaInstance->setProperty('height',200);
}

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="200">Flashvideo Vorschau</td>
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
				<a href="/admin/mediamanager/index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;
			</div>
		</td>
	</tr>
</table>
<br>
<br>
';


// Titel anzeigen
$out .= '
<h1>'.$Res->html(306,page::language()).' - '.$mediaInstance->Progress.'</h1>
<br>
<br>
';
// Flash Code erzeugen
flashCode::getFlvPlayerCode(
	$sMediapath,
	$mediaInstance->getProperty('width'),
	$mediaInstance->getProperty('height'),
	$mediaInstance->getProperty('skin'),
	$mediaInstance->getProperty('align'),
	$out
);
$out .= '
<br>
<br>
';


// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');