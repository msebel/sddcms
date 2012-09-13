<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Schauen, ob der Content und das Menu zusammenpassen
$nMenuID = getInt($_GET['id']);
$nSection = getInt($_GET['section']);
// Zählen ob diese Kombination existiert
$sSQL = "SELECT COUNT(cse_ID) FROM tbcontentsection
WHERE mnu_ID = $nMenuID AND cse_ID = $nSection";
$nResult = $Conn->getCountResult($sSQL);
// Wenn Ergebnis nicht 1, auf Startseite gehen
if ($nResult != 1) {
	redirect('location: /error.php?type=noAccess');
}

$out = '';
// Simulieren des Aufrufs aus einer Schleife von
// ContentSections, owner_ID und Typ holen
$sSQL = "SELECT cse_ID,con_ID,cse_Type FROM tbcontentsection WHERE cse_ID = $nSection";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$sData = $row;
}
contentView::getElement($sData['cse_ID'],$sData['con_ID'],$sData['cse_Type'],$out,$Conn);

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');