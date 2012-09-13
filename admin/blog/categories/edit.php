<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new blogCategory($Conn);
$Module->loadObjects($Conn,$Res);
$Module->checkAccess();
$nCurrentEntry = getInt($_GET['entry']);

// Daten verändern
if (isset($_GET['save'])) $Module->saveCategory();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar
$out .= '
<form name="contentIndex" method="post" action="edit.php?id='.page::menuID().'&entry='.$nCurrentEntry.'&category='.getInt($_GET['category']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'&entry='.$nCurrentEntry.'">'.$Res->html(620,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="#" onClick="document.contentIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="/admin/blog/categories/index.php?id='.page::menuID().'&entry='.$nCurrentEntry.'">
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
<br>
';

$Category = $Module->get(getInt($_GET['category']));
// Kopfbereich und Formular
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2"><h1>'.$Res->html(633,page::language()).'</h1><br></td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(634,page::language()).':
		</td>
		<td>
			<input type="text" name="blcTitle" maxlength="255" value="'.$Category['blc_Title'].'">
		</td>
	</tr>
		<td colspan="2">
			'.$Res->html(635,page::language()).':<br>
			<br>
			'.editor::getSized('Config','blcDesc',page::language(),$Category['blc_Desc'],'100%','250').'
		</td>
	</tr>
</table>
';

// Hilfetexte
$TabRow = new tabRowExtender();
$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td width="120">'.$Res->html(43,page::language()).'</td>
			<td>'.$Res->html(44,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(635,page::language()).'</em></td>
			<td valign="top">'.$Res->html(636,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');