<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Initialisieren des Wiki
$Module->initialize();

// Ausloggen und wieder auf die Wiki Seite
impersonation::logout($Access,page::menuID(),'/modules/wiki/index.php?id='.page::menuID());
// Wenn es soweit kommt, allgemein ausloggen
$Access->logMeOut('/modules/wiki/index.php?id='.page::menuID());

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');