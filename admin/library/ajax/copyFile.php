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

$sDirectory = $Module->Options->get('rootFolder');
$sDirectory.= $Module->Options->get('currentFolder');
$sFile = $_GET['file'];
// Nur alphanumerische Zeichen im Filenamen
stringOps::alphaNumFiles($sFile);

if (file_exists($sDirectory.$sFile)) {
	// File kopieren
	$Module->Options->set('copyFile',$sDirectory.$sFile);
	$Module->Options->set('copyType','copy');
	echo 'true';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');