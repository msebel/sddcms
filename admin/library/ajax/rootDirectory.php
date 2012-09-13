<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/admin/library/library.php');
$tpl->setEmpty();

$Module = new moduleFilelibrary();
$Module->loadObjects($Conn,$Res);
// Zugriff auf Bibliothek prüfen und initialisieren
$Module->controlAccess($Access);
$Module->initialize();

$sRoot = $Module->Options->get('rootFolder');

if (file_exists($sRoot)) {
	$Module->Options->set('currentFolder','');
	session_write_close();
	echo 'true';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');