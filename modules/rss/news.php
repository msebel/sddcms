<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Direkter Output ohne CMS Design
$tpl->setEmpty();

// Konfiguration lesen
$nMenuID = page::menuID();
$Config = array();
pageConfig::get($nMenuID,$Conn,$Config);
// Prüfen, ob der Zugriff gewährt ist, Kein Zugriff, wenn die News inaktiv sind 
// oder nicht dem aktuellen Mandanten gehören. Ansonsten direkt exit, kein output.
$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE man_ID = ".page::mandant()." 
AND mnu_ID = ".$nMenuID." AND mnu_Active = 1";
$nResult = getInt($Conn->getCountResult($sSQL));
if ($nResult !== 1) exit();

// Wenn noch kein Exit, RSS mit der gegebenen ID erstellen
$sPubDate = dateOps::getTime(dateOps::SQL_DATETIME,time()); 
$sProtocol = 'http';
if ($_SERVER['https'] == 'on') $sProtocol = 'https';
$sLink = $sProtocol.'://'.page::domain().'/modules/news/index.php?id='.$nMenuID;
// Titel anhand der Menubeschreibung holen
$sTitle = '';
if (strlen($Menu->CurrentMenu->Name) > 0) {
	$sTitle = $Menu->CurrentMenu->Name.' - ';
}
$sTitle .= page::name();
// Als Description Einleitung ohne HTML nehmen
$sDesc = $Config['htmlCode']['Value'];
stringOps::noHtml($sDesc);
// RSS Dokument mit den genannten Daten erstellen
$rssDoc = new rssDocument($sTitle,$sDesc,$sLink,$sPubDate);

// Wenn deaktiviert, nur ein Item zeigen, welches den Nutzer Informiert
$rssItem = new rssItem();
if (getInt($Config['hasRss']['Value']) == 0) {
	// Titel des Elementes
	$rssItem->title = $Res->html(752,page::language());
	$rssItem->description = $Res->html(753,page::language());
	// Weitere Daten (Guid, Link, Datum)
	$Link = $sLink.'#0';
	$rssItem->link = $Link;
	$rssItem->guid = $Link;
	$rssItem->date = dateOps::getTime(dateOps::SQL_DATETIME,time());
	// ITem einfügen
	$rssDoc->addItem($rssItem);
} else {
	// Aktive Newseintäge per rssItems dem rssDoc hinzufügen
	$sSQL = "SELECT tbcontent.con_ID,tbcontent.con_Title,tbcontent.con_Content,
	IFNULL(tbcontent.con_DateFrom,tbcontent.con_Date) AS con_ViewDate FROM tbcontent
	WHERE tbcontent.mnu_ID = ".page::menuID()." 
	AND IFNULL(tbcontent.con_DateTo,NOW()) >= NOW()
	AND IFNULL(tbcontent.con_DateFrom,tbcontent.con_Date) <= NOW()
	AND tbcontent.con_Active = 1 ORDER BY con_ViewDate DESC";
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		// Titel des Elementes
		$rssItem->title = $row['con_Title'];
		// Inhalt, gekürzt auf 300 Zeichen
		$rssItem->description = $row['con_Content'];
		// Weitere Daten (Guid, Link, Datum)
		$Link = $sLink.'#'.$row['con_ID'];
		$rssItem->link = $Link;
		$rssItem->guid = $Link;
		$rssItem->date = $row['con_ViewDate'];
		// ITem einfügen
		$rssDoc->addItem($rssItem);
	}
}

// Output der XML Daten
$rssDoc->output();

// System abschliessen
require_once(BP.'/cleaner.php');