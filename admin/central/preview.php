<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Schauen, ob der Content und das Menu zusammenpassen
$nMenuID = page::menuID();
$nEntity = getInt($_GET['entity']);
// Zählen ob diese Kombination existiert
$sSQL = "SELECT COUNT(mcs_ID) FROM tbmenu_contentsection
WHERE mnu_ID = $nMenuID AND mcs_ID = $nEntity";
$nResult = $Conn->getCountResult($sSQL);
// Wenn Ergebnis nicht 1, auf Startseite gehen
if ($nResult != 1) {
	redirect('location: /error.php?type=noAccess');
}

$out = '';
// Simulieren des Aufrufs aus einer Schleife von
// ContentSections, owner_ID und Typ holen
$sSQL = "SELECT tbcontentsection.cse_ID,tbcontentsection.con_ID,
tbcontentsection.cse_Type FROM tbcontentsection INNER JOIN 
tbmenu_contentsection ON tbcontentsection.cse_ID = tbmenu_contentsection.cse_ID
WHERE tbmenu_contentsection.mcs_ID = $nEntity";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$sData = $row;
}
contentView::getElement($sData['cse_ID'],$sData['con_ID'],$sData['cse_Type'],$out,$Conn);

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');