<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/modules/gallery/GalleryFile.php');
require_once(BP.'/modules/gallery/galleryConst.php');
require_once(BP.'/system.php');

if (!isset($_SESSION['galleryFiles_'.page::menuID()])) {
	exit();
}

// Ansonsten XML für SimpleViewer ausgeben
$out = '';
// Header
$out .= '<tiltviewergallery>
<photos>';
// Bilder ausgeben
$Files = $_SESSION['galleryFiles_'.page::menuID()];
foreach ($Files as $File) {
	$out .= '
	<photo imageurl="'.$File->View.'" showFlipButton="false">
		<title></title>	
	</photo>
	';
}
// Gallerie abschliessen
$out .= '</photos></tiltviewergallery>';
// Header setzen und ausgeben
header("Content-Type: text/xml");
echo $out;