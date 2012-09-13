<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
require_once(BP.'/admin/blog/library.php');
$Module = new moduleBlog();
$Module->loadObjects($Conn,$Res);
$Module->checkBlogentryAccess();

// Alle Parameter lesen
$nConID = getInt($_GET['entry']);
$sModus = $_GET['mode'];
$nBlcID = getInt($_GET['bindID']);

// Kategorien ID validieren
$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
WHERE blc_ID = $nBlcID AND mnu_ID = ".page::menuID();
$nResult = $Conn->getCountResult($sSQL);
if ($nResult != 1) {
	redirect('location: /error.php?type=noAccess');
}

// Je nach Modus SQL ausführen zum (de)selektieren
switch ($sModus) {
	case 'select':
		$sSQL = "SELECT COUNT(bcc_ID) FROM tbblogcategory_content
		WHERE blc_ID = $nBlcID AND con_ID = $nConID";
		$nResult = $Conn->getCountResult($sSQL);
		// Nur Verbinden, wenn noch nicht vorhanden
		if ($nResult == 0) {
			$sSQL = "INSERT INTO tbblogcategory_content 
			(blc_ID,con_ID) VALUES ($nBlcID,$nConID)";
			$Conn->command($sSQL);
		}
		break;
	case 'unselect':
		$sSQL = "DELETE FROM tbblogcategory_content 
		WHERE blc_ID = $nBlcID AND con_ID = $nConID";
		$Conn->command($sSQL);
		break;
}

exit();