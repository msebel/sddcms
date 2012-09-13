<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Direkter Output ohne CMS Design
$tpl->setEmpty();

// Konfiguration lesen
$nMenuID = page::menuID();
$Config = array();
pageConfig::get($nMenuID,$Conn,$Config);
// Prüfen, ob der Zugriff gewährt ist, Kein Zugriff, wenn es nicht
// dem Mandanten gehört. Ansonsten direkt exit, kein output.
$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE man_ID = ".page::mandant()." 
AND mnu_ID = ".$nMenuID." AND mnu_Active = 1";
$nResult = getInt($Conn->getCountResult($sSQL));
if ($nResult !== 1) exit();

// Titel anhand der Menubeschreibung holen
$sTitle = '';
if (strlen($Menu->CurrentMenu->Name) > 0) {
	$sTitle = $Menu->CurrentMenu->Name.' - ';
}
$sTitle .= page::name();
// Als Description Einleitung ohne HTML nehmen
$sDesc = $Config['htmlCode']['Value'];
stringOps::noHtml($sDesc);

// Protokoll für Links herausfinden
$sProtocol = 'http';
if ($_SERVER['https'] == 'on') $sProtocol = 'https';

// Link direkt zu den Einträgen
$sEntryLink = $sProtocol.'://'.page::domain().'/modules/blog/entry.php?id='.page::menuID();

// Wenn noch kein Exit, RSS mit der gegebenen ID erstellen
$sPubDate = dateOps::getTime(dateOps::SQL_DATETIME,time());

if (!isset($_GET['ca'])) {
	$sCatExt = '';
	$nBlogID = $Config['blogID']['Value'];
	$nCatID = 0;
	$sLink = $sProtocol.'://'.page::domain().'/modules/blog/recent.php?';
	$sLink.= 'id='.page::menuID();
	
	// RSS Dokument mit den genannten Daten erstellen
	$rssDoc = new rssDocument($sTitle,$sDesc,$sLink,$sPubDate);
	// Wenn deaktiviert, nur ein Item zeigen, welches den Nutzer Informiert
	$rssItem = new rssItem();
	// Aktive Newseintäge per rssItems dem rssDoc hinzufügen
	$sSQL = "SELECT tbcontent.con_Title,tbcontent.con_Date,tbcontent.con_ShowDate,
	tbcontent.con_ShowName,tbcontent.con_Content,tbuser.usr_Name,tbcontent.con_ID FROM tbcontent
	LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID
	WHERE tbcontent.mnu_ID = ".$nBlogID." AND tbcontent.con_Active = 1
	ORDER BY con_Date DESC LIMIT 0,".$Config['postCount']['Value'];
	$nRes = $Conn->execute($sSQL);
} else {
	$nBlogID = $Config['blogID']['Value'];
	$nCatID = $Config['categoryID']['Value'];
	$sLink = $sProtocol.'://'.page::domain().'/modules/blog/category.php?';
	$sLink.= 'id='.page::menuID().'&blog='.$nBlogID.'&category='.$nCatID;
	
	// RSS Dokument mit den genannten Daten erstellen
	$rssDoc = new rssDocument($sTitle,$sDesc,$sLink,$sPubDate);
	// Wenn deaktiviert, nur ein Item zeigen, welches den Nutzer Informiert
	$rssItem = new rssItem();
	
	// Einträge der Kategorie mit Paging anzeigen
	$sSQL = "SELECT tbcontent.con_Title,tbcontent.con_Date,tbcontent.con_ShowDate,
	tbcontent.con_ShowName,tbcontent.con_Content,tbuser.usr_Name,tbcontent.con_ID FROM tbcontent
	INNER JOIN tbblogcategory_content ON tbblogcategory_content.con_ID = tbcontent.con_ID
	LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID
	WHERE tbblogcategory_content.blc_ID = $nCatID AND tbcontent.mnu_ID = $nBlogID 
	AND tbcontent.con_Active = 1 ORDER BY con_Date DESC";
	$nRes = $Conn->execute($sSQL);
}

while ($row = $Conn->next($nRes)) {
	// Titel des Elementes
	$rssItem->title = $row['con_Title'];
	// Inhalt, gekürzt auf 300 Zeichen
	$rssItem->description = $row['con_Content'];
	// Weitere Daten (Guid, Link, Datum)
	$Link = $sEntryLink.'&blog='.$nBlogID.'&category='.$nCatID.'&entry='.$row['con_ID'];
	$rssItem->link = $Link;
	$rssItem->guid = $Link;
	$rssItem->date = $row['con_Date'];
	// ITem einfügen
	$rssDoc->addItem($rssItem);
}

// Output der XML Daten
$rssDoc->output();

// System abschliessen
require_once(BP.'/cleaner.php');