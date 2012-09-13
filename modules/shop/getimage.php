<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// Element Pfad laden
$nEleID = getInt($_GET['e']);

// Spezielles File oder Hauptfile?
if (isset($_GET['file'])) {
	$sPath = shopModuleConfig::ELEMENT_PATH.$_GET['file'];
	$sPath = str_replace('{PAGE}', page::id(), $sPath);
	$sPath = str_replace('{ELEMENT}', $nEleID, $sPath);
} else {
	$sPath = shopStatic::getElementPath($nEleID);
}

// File und Ordner wieder trennen
$folder = fileOps::getFileFolder($sPath);
$file = fileOps::getFileOnly($sPath);

// Gibt es den Ordner schon?
if (!file_exists(BP.$folder.$_GET['type'])) {
	mkdir(BP.$folder.$_GET['type'],0755);
}

// Prüfen ob das gewünschte File existiert
$sPath = BP.$folder.$_GET['type'].'/'.$file;

// Existiert das angeorderte File schon?
if (!file_exists($sPath)) {
	// Bildbearbeitung starten (Aus original)
	$Image = new imageManipulator(BP.$folder.$file,$sPath);
	// Verkleinern
	switch($_GET['type']) {
		case 'thumb':
			$nWidth = shopModuleConfig::THUMB_WIDTH; break;
		case 'resize':
			$nWidth = shopModuleConfig::RESIZE_WIDTH; break;
		case 'original':
			$nWidth = shopModuleConfig::ORIGINAL_WIDTH; break;
	}
	// Bild verkleinern
	$nHeight = $Image->getAspectOf(
		$nWidth,
		imageManipulatorToBrowser::WIDTH
	);
	// Wenn kleiner, originalgrösse nehmen
	list($noWidth,$noHeight) = getimagesize(BP.$folder.$file);
	if ($noWidth < $nWidth || $noHeight < $nHeight) {
		$nWidth = $noWidth;
		$nHeight = $noHeight;
	}
	$Image->Resize($nWidth,$nHeight);
}

// HTTP Header und caching
$sEnd = substr(fileOps::getExtension($sPath),1);
$nAge = time() + (60*60*24);
header('Content-type: image/'.$sEnd);
header('Expires: '.date('r',$nAge));
header('Cache-Control: max-age='.$nAge);
readfile($sPath);