<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren f�r Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');

$tpl->setEmpty();

// Elementpfad zusammenbauen
$sFile = $_GET['file'];
$sPath = str_replace(basename($sFile),'',$sFile);

// Allfällige Punkte aus dem Pfad entfernen
$sPath = str_replace('.','',$sPath);
$nCount = 1;
// Solange doppelte Slashes entfernen bis es keine mehr hat
while ($nCount > 0) {
	$sPath = str_replace('//','/',$sPath,$nCount);
}

// Datei zusammenführen
$sFile = BP.'/page/'.page::id().'/library/'.$sPath.basename($sFile);
// Error wenns das File nicht gibt
if (!file_exists($sFile)) {
	redirect('location: /error.php?type=FileNotFound');
}

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Type: application/force-download');
header('Content-Description: File Transfer');
header('Content-Disposition: attachment; filename='.basename($sFile));
header('Content-Length: '.getInt(filesize($sFile)));
header('Content-Transfer-Encoding: binary');
readfile($sFile);