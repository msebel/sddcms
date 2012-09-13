<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse / Javascript
library::load('teaser');
$Module = new moduleTeaser();
$Module->loadObjects($Conn,$Res);

// Teaser / Element prüfen
$nTeaserID = getInt($_GET['teaser']);
$nTapID = getInt($_GET['element']);
// Gehören Teaser und Element dem Mandanten?
$Module->checkSectionAccess($nTeaserID);
$Module->checkElementAccess($nTapID);
// Gehören Teaser und Element zusammen?
$Module->checkSectionElementMatch($nTeaserID,$nTapID);

// Zum Edit Mode weiterleiten oder zurück 
// zum Teasermenu wenn Fehler auftreten
$Module->doAdminRedirect($nTapID,$nTeaserID);

require_once(BP.'/cleaner.php');