<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleGallery();
$Module->loadObjects($Conn,$Res);
// Kein Inhalt (ausser Bild)
$tpl->setEmpty();
$Module->getThumb();

// System abschliessen
require_once(BP.'/cleaner.php');