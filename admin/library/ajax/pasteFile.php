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

$sDirectory		 = $Module->Options->get('rootFolder');
$sDirectory		.= $Module->Options->get('currentFolder');
$sSource		 = $Module->Options->get('copyFile');
$sCopyType		 = $Module->Options->get('copyType');
$sDestination	 = $sDirectory.basename($sSource);

if (strlen($sSource) > 0 && strlen($sDestination) > 0) {
	// File kopieren
	copy($sSource,$sDestination);
	// Wenn ausschneiden, Quelle löschen
	if ($sCopyType == 'cut') {
		unlink($sSource);
	}
	echo 'true';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');