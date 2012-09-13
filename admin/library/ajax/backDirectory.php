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
$sCurr = $Module->Options->get('currentFolder');

// Letzten Ordner entfernen
$prevFolder = substr($sRoot.$sCurr,0,strlen($sRoot.$sCurr)-1);
$prevFolder = substr($prevFolder,0,strrpos($prevFolder,'/')+1);
$prevFolder = str_replace($sRoot,'',$prevFolder);

if (file_exists($sRoot.$prevFolder)) {
	$Module->Options->set('currentFolder',$prevFolder);
	session_write_close();
	echo 'true';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');