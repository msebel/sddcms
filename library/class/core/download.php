<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');

$tpl->setEmpty();

// Gibts Zugriff auf das Element?
$bAccess = false;
$nElementID = getInt($_GET['file']);
$sFilename = basename($_GET['name']);
// Pr�fen ob Elementzugriff gestattet
$sSQL = 'SELECT COUNT(ele_ID) FROM tbelement WHERE ele_ID = '.$nElementID;
$nResult = $Conn->getCountResult($sSQL);
if ($nResult == 1) $bAccess = true;
if ($bAccess == false) {
	redirect('location: /error.php?type=noAccess');
}

// Elementpfad zusammenbauen
$sPath = mediaConst::FILEPATH;
$sPath = str_replace('{ELE_ID}',$nElementID,$sPath);
$sPath = str_replace('{PAGE_ID}',page::ID(),$sPath);
// Error wenns das File nicht gibt
if (!file_exists(BP.$sPath.$sFilename)) {
	redirect('location: /error.php?type=FileNotFound');
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Type: application/force-download');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename='.$sFilename);
header('Content-Length: '.getInt(filesize(BP.$sPath.$sFilename)));
header('Content-Transfer-Encoding: binary');
readfile(BP.$sPath.$sFilename);