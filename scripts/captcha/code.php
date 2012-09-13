<?php
session_start();
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
if (isset($_SESSION['captchaCode'])) {
	unset($_SESSION['captchaCode']);
}

function randomString($len) {               
	// Liste möglicher Zahlen
	$sChars  = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUFVXYZ23456789";
	//Startwert für den Zufallsgenerator festlegen
	$sToken = '';
	for ($nChars = 0; $nChars < $len; $nChars++) { 
		$sToken .= $sChars[mt_rand(0,55)];
	}
	return($sToken);
}

$text = randomString(5);  //Die Zahl bestimmt die Anzahl stellen
$_SESSION['captchaCode'] = $text;
      
header('Content-type: image/png');
$imgNr = mt_rand(1,5);
$img = ImageCreateFromPNG('captcha'.$imgNr.'.png'); //Backgroundimage
$R = mt_rand(60,70);
$G = mt_rand(60,70);
$B = mt_rand(60,70);
$color = ImageColorAllocate($img, $R, $G, $B); //Farbe
$ttf = BP.'/scripts/captcha/xfiles.ttf'; //Schriftart
$ttfsize = 24; //Schriftgrösse
$angle = mt_rand(0,5);
$t_x = mt_rand(5,30);
$t_y = 35;
imagettftext($img, $ttfsize, $angle, $t_x, $t_y, $color, $ttf, $text);
imagepng($img);
imagedestroy($img); 