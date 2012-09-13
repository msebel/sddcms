<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('comment/library');
$Module =  new moduleComment();
$Module->loadObjects($Conn,$Res);
library::loadRelative('library');
blogEntryView::$Res = $Res;
blogEntryView::$Conn = $Conn;

$nConID = getInt($_GET['entry']);
$nBlogID = getInt($_GET['blog']);

// Konfiguration initialisieren
$Config = array();
pageConfig::get($nBlogID,$Conn,$Config);
$bError = false;

// Prüfen, ob Blog und Entry zusammengehören
if (!blogEntryView::checkBlogEntry($nBlogID,$nConID)) $bError = true;
// Prüfen ob Kommentare erlaubt sind
if ($Config['allowComments']['Value'] != 1) $bError = true;
// Prüfen ob Abbrechen gewählt
if (isset($_POST['cmdCancel'])) $bError = true;
// Prüfen ob die Session vorhanden ist
if (!isset($_SESSION['comment_'.page::menuID()]['entry_'.$nConID])) $bError = true;
// Wenn Fehler, auf Index der ID weiterleiten
if ($bError == true) {
	redirect('location: /modules/blog/entry.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$nConID);
}

// Ansonsten den Post erstellen
$sComContent = ''; $sComName = '';
if (isset($_POST['cmdSave'])) {
	$Module->saveEntry($sComName,$sComContent);
}

// Checking Session nutzen zum Zeit speichern
$_SESSION['comment_'.page::menuID()]['entry_'.$nConID] = time();

// Formular zum erstellen des Eintrages anzeigen
$out .= '
<form action="comment.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$nConID.'" method="post">
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(723,page::language()).'</h1>
			<br>
			'.$Module->showErrorSession().'<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(85,page::language()).':
		</td>
		<td valign="top">
			<input type="text" style="width:70%;" maxlength="255" name="comName" value="'.$sComName.'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(361,page::language()).':
		</td>
		<td valign="top">
			<textarea style="width:80%;height:100px;" rows="5" name="comContent">'.$sComContent.'</textarea>
		</td>
	</tr>
	'.$Module->getCaptchaCode().'
	<tr>
		<td width="150" valign="top">
			<div style="display:block;visibility:hidden;">
				<input type="text" name="comAdditional" value="">
			</div>
		</td>
		<td valign="top">
			<input type="submit" class="cButton" name="cmdSave" value="'.$Res->html(362,page::language()).'"> 
			<input type="submit" class="cButton" name="cmdCancel" value="'.$Res->html(234,page::language()).'">
		</td>
	</tr>
</table>
</post>
';
// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');