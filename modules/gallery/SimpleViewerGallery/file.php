<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/modules/gallery/GalleryFile.php');
require_once(BP.'/modules/gallery/galleryConst.php');
require_once(BP.'/system.php');

$Options = explode(',',$_GET['option']);
$nMenu = getInt($Options[0]);
$sType = $Options[1];
$sFile = $Options[2];

if (!isset($_SESSION['galleryFiles_'.$nMenu])) {
	exit();
}

// Bild suchen
$Filename = '';
$Thumb = '';
$hasThumb = false;
$Files = $_SESSION['galleryFiles_'.$nMenu];
foreach ($Files as $File) {
	if ($File->Filename == $sFile) {
		$Filename = BP.$File->View;
		$Thumb = BP.$File->Thumb;
		$hasThumb = $File->hasThumb;
	}
}

header("Content-Type: image/jpeg");
switch ($sType) {
	case 'normal':
		header("Content-Length: ".filesize($Filename));
  		readfile($Filename);
		break;
	case 'thumb':
		if ($hasThumb) {
			header("Content-Length: ".filesize($Thumb));
  			readfile($Thumb);
		} else {
			header("Content-Length: ".filesize($Filename));
  			readfile($Filename);
		}
		break;
}