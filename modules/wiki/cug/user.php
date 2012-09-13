<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Initialisieren des Wiki
$Module->initialize();
$Module->checkCugAccess($Access);

// Registrierung durchführen
if (isset($_GET['add'])) $Module->addEntry();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Registrierungsformular anzeigen
$out .= '
<div id="divWikiContent">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbarWoRegister">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/page_add.png" id="addEntry" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0">
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
</div>
';

// Daten mit Paging holen (initiierte Beiträge)
$Data = array();
$Module->loadUserEntries($Data,$Access);

$TabRow = new tabRowExtender();

$out .= '
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data as $Entry) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:20px;float:left;">
			<a href="/modules/wiki/writer/edit.php?id='.page::menuID().'&entry='.$Entry->EntryID.'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="/modules/wiki/writer/versions.php?id='.page::menuID().'&entry='.$Entry->EntryID.'">
			<img src="/images/icons/images.png" border="0" alt="'.$Res->html(966,page::language()).'" title="'.$Res->html(966,page::language()).'"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			'.$Entry->Title.'
		</div>
	</div>
	';
}
	
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(158,page::language()).' ...</div>
	</div>
	';
}

$out .= '</div>';

// Fenster zum Sonntag (Oder zum erstellen von Einträgen?)
$sHtml = '
<h1>'.$Res->html(425,page::language()).'</h1>
<form name="userAdd" action="user.php?id='.page::menuID().'&add" method="post">
<br>
<table width="100% border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td width="100">Begriff:</td>
		<td>
			<div style="float:left;">
				<input type="text" name="conTitle" style="width:150px;">
			</div>
			<div style="float:left;padding-left:5px;">
				<a href="javascript:document.userAdd.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
			</div>
		</td>
	</tr>
</table>
</form>
';

$window = htmlControl::window();
$window->add('addEntry',$sHtml,'',350,100);
$out .= $window->get('addEntry');

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(212,page::language()).'" alt="'.$Res->html(212,page::language()).'"></td>
			<td>'.$Res->html(967,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/images.png" title="'.$Res->html(966,page::language()).'" alt="'.$Res->html(966,page::language()).'"></td>
			<td>'.$Res->html(968,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');