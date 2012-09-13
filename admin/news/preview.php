<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$Access->control();

$out = '';
// Anzuzeigende ID
$nContentID = getInt($_GET['content']);
// Gehört der Content zum Menu?
$sSQL = "SELECT COUNT(con_ID) FROM tbcontent WHERE
con_ID = $nContentID AND mnu_ID = ".page::menuID();
$nReturn = $Conn->getCountResult($sSQL);
if ($nReturn != 1) {
	redirect('location: /error.php?type=noAccess');
}
// Konfiguration holen
$NewsConfig = array();
pageConfig::get(page::menuID(),$Conn,$NewsConfig);
// Alle Content Sektionen holen die Aktiv sind
$sSQL = "SELECT tbcontent.con_ID,tbcontent.con_Modified,tbcontent.con_ShowDate,
tbcontent.con_Title,tbuser.usr_Name, IFNULL(tbcontent.con_DateFrom,tbcontent.con_Date) 
AS con_ViewDate,tbcontent.con_Content 
FROM tbcontent LEFT JOIN tbuser ON tbuser.usr_ID = tbcontent.usr_ID
WHERE tbcontent.con_ID = $nContentID";
$nRes = $Conn->execute($sSQL);
// News anzeigen
while ($row = $Conn->next($nRes)) {
	contentView::getNews(
		$row,
		$out,
		$Res,
		$NewsConfig
	);
}
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');