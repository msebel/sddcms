<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
$design = (int) ($_GET['p']['d']);

// Cache this like... forever!
header('Content-Type: text/css');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + 315360000 ) . ' GMT');
header("Cache-Control: public, max-age=315360000");

// Daten der CSS Files lesen und ausgeben
echo file_get_contents(BP.'/resource/css/default.css')."\n";
echo file_get_contents(BP.'/design/'.$design.'/format.css')."\n";
echo file_get_contents(BP.'/design/'.$design.'/default.css')."\n";
echo file_get_contents(BP.'/design/'.$design.'/design.css')."\n";
// Spezialfall der nicht berücksichtig wird auf Änderungen: Lightbox CSS
echo file_get_contents(BP.'/scripts/lightbox/lightbox.css')."\n";