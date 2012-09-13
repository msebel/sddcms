<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new blogCategory($Conn);
$Module->loadObjects($Conn,$Res);
$nCurrentEntry = getInt($_GET['entry']);

// Daten verändern
if (isset($_GET['add'])) $Module->addCategory();
if (isset($_GET['save'])) $Module->saveCategories();
if (isset($_GET['delete'])) $Module->deleteCategory();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar
$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&entry='.$nCurrentEntry.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(620,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="/admin/blog/edit.php?id='.page::menuID().'&entry='.$nCurrentEntry.'">
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
				<a href="index.php?id='.page::menuID().'&entry='.$nCurrentEntry.'&add">
				<img src="/images/icons/table_add.png" alt="'.$Res->html(631,page::language()).'" title="'.$Res->html(631,page::language()).'" border="0">
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

$TabRow = new tabRowExtender();
// Kopfbereich, Listendefinition
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(630,page::language()).'</h1><br></td>
	</tr>
</table>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:30px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Daten anzeigen
$nCount = 0;
foreach ($Module->Categories as $Entry) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&entry='.$nCurrentEntry.'&category='.$Entry['blc_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&entry='.$nCurrentEntry.'&delete='.$Entry['blc_ID'].'\',\''.addslashes($Entry['blc_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="title[]" value="'.$Entry['blc_Title'].'" class="adminBufferInput">
		</div>
		<div style="width:30px;float:left;text-align:center">
			<input type="hidden" name="id[]" value="'.$Entry['blc_ID'].'">
		</div>
	</div>
	';
}

// Wenn keine Daten
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(483,page::language()).' ...</div>
	</div>
	';
}

// Formular beenden
$out .= '
</div></form>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');