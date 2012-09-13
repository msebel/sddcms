<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleBlogComments();
$Module->loadObjects($Conn,$Res);
$Module->CurrentEntry = getInt($_GET['entry']);

// Zugriff testen und Fehler melden
$Access->control();

// Operationen durchführen
if (isset($_GET['toggleActive'])) $Module->toggleActive();
if (isset($_GET['delete'])) $Module->deletePost();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="commentIndex" method="post" action="index.php?id='.page::menuID().'&refresh">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(638,page::language()).'</td>
		<td class="cNav">&nbsp;</td>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbar">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="/admin/blog/edit.php?id='.page::menuID().'&entry='.$Module->CurrentEntry.'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:showHelp()">
				<img src="/images/icons/help.png" alt="'.$Res->html(8,page::language()).'" title="'.$Res->html(8,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
';

$CommentData = array();
$sPageHtml = $Module->LoadData($CommentData,10);

// Einleitungstext
$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(638,page::language()).'</h1>
		</td>
	</tr>
</table>
';

// Einträge inkl. Paging ausgeben
$out .= $sPageHtml;
$Module->showCommentsAdmin($CommentData,$out);
$out .= $sPageHtml;

$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td colspan="3">'.$Res->html(22,page::language()).':</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td>
				<img src="/images/icons/page_delete.png" alt="'.$Res->html(157,page::language()).'" title="'.$Res->html(157,page::language()).'" border="0">
			</td>
			<td colspan="3">'.$Res->html(641,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td>
				<img src="/images/icons/page_go.png" alt="'.$Res->html(639,page::language()).'" title="'.$Res->html(639,page::language()).'" border="0">
			</td>
			<td colspan="3">'.$Res->html(642,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td>
				<img src="/images/icons/page_error.png" alt="'.$Res->html(640,page::language()).'" title="'.$Res->html(640,page::language()).'" border="0">
			</td>
			<td colspan="3">'.$Res->html(643,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');