<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

library::loadRelative('library');
blogEntryView::$Res = $Res;
blogEntryView::$Conn = $Conn;

// Was soll geladen werden?
$nBlogID = getInt($_GET['blog']);
$nCatID = getInt($_GET['category']);

// Konfiguration des Blogs laden
$Config = array();
pageConfig::get($nBlogID,$Conn,$Config);

// Suchformular anzeigen
$out .= '
<p>
<form action="keyword.php?id='.page::menuID().'&blog='.$nBlogID.'" method="post">
	<div style="float:right;">
		<input name="keyword" style="width:100px;" type="text"> 
		<input type="submit" name="submit" value="'.$Res->html(497,page::language()).'">
	</div>
</form>
</p>
';

// Einträge der Kategorie mit Paging anzeigen
$sSQL = "SELECT tbcontent.con_Title,tbcontent.con_Date,tbcontent.con_ShowDate,
tbcontent.con_ShowName,tbcontent.con_Content,tbuser.usr_Name,tbcontent.con_ID FROM tbcontent
INNER JOIN tbblogcategory_content ON tbblogcategory_content.con_ID = tbcontent.con_ID
LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID
WHERE tbblogcategory_content.blc_ID = $nCatID AND tbcontent.mnu_ID = $nBlogID 
AND tbcontent.con_Active = 1 ORDER BY con_Date DESC";
$paging = new paging($Conn,'category.php?id='.page::menuID().'&blog='.$nBlogID.'&category='.$nCatID);
$paging->start($sSQL,$Config['postsPerPage']['Value']);
$nRes = $Conn->execute($paging->getSQL());
while ($row = $Conn->next($nRes)) {
	blogEntryView::showEntryShort($row,$out,$nBlogID);
}
$out .= $paging->getHtml();

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');