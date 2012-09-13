<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/modules/gallery/GalleryFile.php');
require_once(BP.'/modules/gallery/galleryConst.php');
require_once(BP.'/system.php');

if (!isset($_SESSION['galleryFiles_'.page::menuID()])) {
	exit();
}

$nColumns = getInt(option::get('simpleViewerColumns'));
$nRows = getInt(option::get('simpleViewerRows'));

// Wenn 0, Standardwert nehmen
if ($nColumns == 0) $nColumns = 2;
if ($nRows == 0) $nRows = 5;

// Ansonsten XML für SimpleViewer ausgeben
$out = '';
// Header
$out .= '<?xml version="1.0" encoding="UTF-8"?>
<simpleviewergallery maxImageWidth="640" maxImageHeight="480" 
textColor="0xCCCCCC" frameColor="0xDDDDDD" frameWidth="10" 
stagePadding="40" navPadding="40" thumbnailColumns="'.$nColumns.'" 
thumbnailRows="'.$nRows.'" navPosition="left" vAlign="center" 
hAlign="center" title="" enableRightClickOpen="true" backgroundImagePath="" 
imagePath="/modules/gallery/SimpleViewerGallery/file.php?option='.page::menuID().',normal," 
thumbPath="/modules/gallery/SimpleViewerGallery/file.php?option='.page::menuID().',thumb,">';
// Bilder ausgeben
$Files = $_SESSION['galleryFiles_'.page::menuID()];
foreach ($Files as $File) {
	stringOps::htmlEntRev($File->Description);
	$File->Description = utf8_encode($File->Description);
	$out .= '
	<image>
		<filename>'.$File->Filename.'</filename>
		<caption><![CDATA['.$File->Description.']]></caption>	
	</image>
	';
}
// Gallerie abschliessen
$out .= '</simpleviewergallery>';
// Header setzen und ausgeben
header("Content-Type: text/xml");
echo $out;