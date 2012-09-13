<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleDirectlink();
$Module->loadObjects($Conn,$Res);

if (isset($_GET['save'])) $Module->saveLinks();
if (isset($_GET['add'])) $Module->addLink();
if (isset($_GET['delete'])) $Module->deleteLink();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">Link Übersicht</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/world_add.png" alt="" title="" border="0">
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

// Daten laden
$Data = array();
$Module->loadData($Data);

$out .= '
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="width:100px;float:left;"><strong>Name</strong></div>
		<div style="width:200px;float:left;"><strong>Link</strong></div>
	</div>

	<div id="contentTable">
';

$TabRow = new tabRowExtender();

// Alle Daten Inputs anzeigen
foreach ($Data as $row) {
	$nCount++;
	// Daten validieren
	
	// Zeile ausgeben
	$out .= '
	<div class="'.$TabRow->get().'" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:30px;height:25px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['drl_ID'].'\',\''.addslashes($row['drl_Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
			<input type="hidden" name="id[]" value="'.$row['drl_ID'].'">
		</div>
		<div style="width:100px;float:left;">
			<input type="text" name="linkname[]" value="'.$row['drl_Name'].'" style="width:90px;">
		</div>
		<div style="width:200px;float:left;">
			<input type="text" name="linkurl[]" value="'.$row['drl_Url'].'" style="width:190px;">
		</div>
	</div>
	';
}

// Abschliessen
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(158,page::language()).' ...</div>
	</div>
	';
}

$out .= '</div></form>

<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td width="120">'.$Res->html(43,page::language()).'</td>
			<td>'.$Res->html(44,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>Name</em></td>
			<td valign="top">Über diesen Namen kann der Link aufgerufen werden. Wählen Sie zum Beispiel "test" als Namen, kann der Link über www.ihrdomain.ch/test aufgerufen werden.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>Link</em></td>
			<td valign="top">Dies ist der Link auf den weitergeleitet wird, wenn der Direktlink über Ihre Domain angesteuert wird.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');