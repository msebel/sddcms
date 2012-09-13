<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Suchen nach dem gegebenen Ort
$Query = $_GET['query'];

$coord = new googleCoordinate($Query);

echo $coord->getLatitude().';'.$coord->getLongitude();

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');