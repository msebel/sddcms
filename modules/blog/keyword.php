<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

library::loadRelative('library');
blogEntryView::$Res = $Res;
blogEntryView::$Conn = $Conn;

// Was soll geladen werden?
$nBlogID = getInt($_GET['blog']);
$sKeyword = stringOps::getGetEscaped('keyword',$Conn);
if (empty($sKeyword) || strlen($sKeyword) == 0) {
	$sKeyword = stringOps::getPostEscaped('keyword',$Conn);
}

// Konfiguration des Blogs laden
$Config = array();
pageConfig::get($nBlogID,$Conn,$Config);

$out = '';
$nCount = 0;
// Einträge der Kategorie mit Paging anzeigen
$sSQL = "SELECT tbcontent.con_Title,tbcontent.con_Date,tbcontent.con_ShowDate,
tbcontent.con_ShowName,tbcontent.con_Content,tbuser.usr_Name,tbcontent.con_ID FROM tbcontent
INNER JOIN tbkeyword ON tbkeyword.owner_ID = tbcontent.con_ID
LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID 
WHERE tbcontent.con_Active = 1 AND tbkeyword.key_Keyword = '$sKeyword'
ORDER BY con_Date DESC";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	blogEntryView::showEntryShort($row,$out,$nBlogID);
	$nCount++; // Gefundene Einträge
}

// Meldung wenn nichts gefunden und Suchformular anzeigen
if ($nCount == 0) {
	$out .= '
	<form action="keyword.php?id='.page::menuID().'&blog='.$nBlogID.'" method="post">
		<p>'.$Res->html(500,page::language()).'.</p>
		<p>
		<input name="keyword" style="width:100px;" type="text"> 
		<input type="submit" name="submit" value="'.$Res->html(497,page::language()).'">
		<p>
	</form>
	';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');