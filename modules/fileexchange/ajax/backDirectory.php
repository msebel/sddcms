<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$FAILWHALE = true;
require_once(BP.'/modules/fileexchange/index.php');
$tpl->setEmpty();

$Module = new viewFileExchange($tpl);
// Zugriff auf Bibliothek prüfen und initialisieren
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