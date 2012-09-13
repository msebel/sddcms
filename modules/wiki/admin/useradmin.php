<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);
// Zugriff für Admin prüfen
$Module->checkAdminAccess($Access);
// Initialisieren des Wiki
$Module->initialize();

// Kommandos ausführen
if (isset($_GET['add'])) $Module->addWikiUser();
if (isset($_GET['save'])) $Module->saveWikiUser();
if (isset($_GET['delete'])) $Module->deleteWikiUser();

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);
$TabRow = new tabRowExtender();
$window = htmlControl::window();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden mit Paging
$Data = array();
$sPagingHtml = $Module->loadWikiUserList($Data);

// Konfigurationsformular anzeigen
$out .= '
<div id="divWikiContent">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbarWoRegister">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="document.useradmin.submit()">
				<img src="/images/icons/user_add.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
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
<h1>'.$Res->html(5,page::language()).'</h1>
<form method="post" action="useradmin.php?id='.page::menuID().'&add" name="useradmin"></form>
'.$sPagingHtml.'
<table width="100%" cellpadding="3" cellspacing="0" border="0">
<tr class="tabRowHead">
	<td width="20">&nbsp;</td>
	<td width="20">&nbsp;</td>
	<td>'.$Res->html(795,page::language()).'</td>
	<td width="180">'.$Res->html(928,page::language()).'</td>
	<td width="50">'.$Res->html(160,page::language()).'</td>
</tr>';
// Daten anzeigen
foreach ($Data as $row) {
	$nCount++;
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td width="20">
			'.$Module->getWikiUserEdit($row,$window,$nCount).'
		</td>
		<td width="20">
			<a href="javascript:deleteConfirm(\'useradmin.php?id='.page::menuID().'&delete='.$row['imp_ID'].'\',\''.addslashes($row['imp_Alias']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</td>
		<td>'.$row['imp_Alias'].'</td>
		<td width="180">'.$row['imp_Email'].'</td>
		<td width="50">
			<input type="checkbox" name="impActiveShow'.$nCount.'"'.checkCheckBox(1,$row['imp_Active']).' disabled>
		</td>
	</tr>
	';
}
// Wenn keine Benutzer, Melden als Tabellenzeile
if ($nCount == 0) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td colspan="5">
			'.$Res->html(954,page::language()).'
		</td>
	</tr>
	';
}
$out .= '
</table>'.$sPagingHtml.'
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_add.png" title="'.$Res->html(36,page::language()).'" alt="'.$Res->html(36,page::language()).'"></td>
			<td>'.$Res->html(955,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(953,page::language()).'" alt="'.$Res->html(953,page::language()).'"></td>
			<td>'.$Res->html(956,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'"></td>
			<td>'.$Res->html(957,page::language()).'.</td>
		</tr>
	</table>
</div>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');