<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/flashCode.php');			// Erzeugt $mediaInstance Objekt

$Meta->addJavascript('/scripts/system/mediamanager.js',true);		// Mediamanager Javascripts

// Zugriff testen und Fehler melden
$Access->control();
$tpl->setEmpty();

// Medieninstanz erstellen
$mediaInstance = new mediaInstance($Conn);

// Gesamter Pfad
$sMediapath = $mediaInstance->getProperty('path').basename($mediaInstance->Progress);

// Gibts Zugriff auf das Element?
$bAccess = mediaLib::checkElementAccess($Conn,$mediaInstance);
$nElementID = $mediaInstance->Element;
if ($bAccess == false) {
	redirect('location: /error.php?type=noAccess');
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Type: application/force-download');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename='.$mediaInstance->Progress);
header('Content-Length: '.getInt(filesize(BP.$sMediapath)));
header('Content-Transfer-Encoding: binary');
readfile(BP.$sMediapath);