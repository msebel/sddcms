<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

library::loadRelative('library');
blogEntryView::$Res = $Res;
blogEntryView::$Conn = $Conn;

// Was soll geladen werden?
$nBlogID = getInt($_GET['blog']);
$nConID = getInt($_GET['entry']);

// Konfiguration des Blogs laden
$Config = array();
pageConfig::get($nBlogID,$Conn,$Config);

// Backlink anzeigen
$out .= '
<div style="float:right;">
	<p>
		<a href="javascript:history.back();">'.$Res->html(595,page::language()).'</a>
	</p>
</div>
';

// Einträge der Kategorie mit Paging anzeigen
$sSQL = "SELECT tbcontent.con_Title,tbcontent.con_Date,tbcontent.con_ShowDate,
tbcontent.con_ShowName,tbcontent.con_Content,tbuser.usr_Name,tbcontent.con_ID FROM tbcontent
LEFT JOIN tbuser ON tbcontent.usr_ID = tbuser.usr_ID
WHERE tbcontent.mnu_ID = $nBlogID AND tbcontent.con_ID = $nConID
AND tbcontent.con_Active = 1 ORDER BY con_Date DESC";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	blogEntryView::showEntryLong($row,$out);
	// Darunter Tags anzeigen
	$sSQL = "SELECT key_ID,key_Keyword FROM tbkeyword 
	WHERE owner_ID = ".$row['con_ID']." ORDER BY key_Keyword ASC";
	$nResKey = $Conn->execute($sSQL);
	$out .= 'Tags: ';
	$bFirst = true;
	while ($row = $Conn->next($nResKey)) {
		if (!$bFirst) $out .= ', ';
		$sKeyView = $row['key_Keyword'];
		stringOps::htmlEnt($sKeyView);
		$out .= '<a href="keyword.php?id='.page::menuID().'&blog='.$nBlogID.'&keyword='.$row['key_Keyword'].'">'.$sKeyView.'</a>';
		if ($bFirst) $bFirst = false;
	}
	// Meldung, wenn keine Tags
	if ($bFirst) $out .= $Res->html(656,page::language());
}

// Social Bookmarks anzeigen, wenn konfiguriert
if ($Config['socialBookmarking']['Value'] == 1) {
	$bm = socialButtons::blog($Res);
	$bm->setTitle($Menu->CurrentMenu->Name.' '.page::title());
	$bm->setUrl(stringOps::currentUrl());
	$out .= '
	<div class="cSocialButtonList">
		'.$bm->output().'
	</div>
	';
}

// Wenn konfiguriert, Kommentare anzeigen mit link zum kommentieren
if ($Config['allowComments']['Value'] == 1) {
	// Variable für Kommentar setzen
	$_SESSION['comment_'.page::menuID()]['entry_'.$nConID] = true;
	// Output HTML 
	$out .= '
	<div style="float:right;">
		<p>
			<a href="comment.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$nConID.'">'.$Res->html(657,page::language()).'</a>
		</p>
	</div>
	';
	// Eventuell Error Session ausgeben
	if (isset($_SESSION['errorSession'])) {
		$out .= '<p>'.$_SESSION['errorSession'].'</p>';
		// Fehler nicht nochmal zeigen
		unset($_SESSION['errorSession']);
	} 
	blogEntryView::showComments($out,$nConID,$nBlogID);
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');