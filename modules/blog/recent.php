<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

library::loadRelative('library');
blogEntryView::$Res = $Res;
blogEntryView::$Conn = $Conn;

// Konfiguration des Blogs laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Suchformular anzeigen
$out .= '
<p>
<form action="keyword.php?id='.page::menuID().'&blog='.$Config['blogID']['Value'].'" method="post">
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
LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID
WHERE tbcontent.mnu_ID = ".$Config['blogID']['Value']." AND tbcontent.con_Active = 1
ORDER BY con_Date DESC LIMIT 0,".$Config['postCount']['Value'];
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	blogEntryView::showEntryShort($row,$out,$Config['blogID']['Value']);
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');