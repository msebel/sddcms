<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('teaser');
$Module = new moduleTeaser();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['add'])) $Module->addTeaserSection();
if (isset($_GET['save'])) $Module->saveTeaserSections();
if (isset($_GET['delete'])) $Module->deleteTeaserSection();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$sData = array();
$PagingHTML = $Module->loadTeaserSections($sData);

// Toolbar erstellen
$out = '
<form name="teaserIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(409,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(410,page::language()).'</td>
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
				<a href="#" onClick="document.teaserIndex.submit()">
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
				<img src="/images/icons/page_add.png" alt="'.$Res->html(422,page::language()).'" title="'.$Res->html(422,page::language()).'" border="0">
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
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(409,page::language()).'</h1><br>
			'.$Res->html(411,page::language()).'.
			<br>
		</td>
	</tr>
</table>
'.$PagingHTML.'
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(412,page::language()).'</strong></div>
		<div style="width:120px;float:left;"><strong>'.$Res->html(413,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($sData as $row) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:20px;float:left;">
			<a href="elements.php?id='.page::menuID().'&teaser='.$row['tas_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(414,page::language()).'" alt="'.$Res->html(414,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&teaser='.$row['tas_ID'].'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
			<img src="/images/icons/bullet_magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			'.$Module->getDeleteable($row).'
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="hidden" name="id[]" value="'.$row['tas_ID'].'">
			<input type="text" name="name[]" value="'.$row['tas_Desc'].'" class="adminBufferInput">
		</div>
		<div style="width:120px;float:left;">
			'.$Res->html(420,page::language()).' '.$row['tas_Count'].' '.$Res->html(421,page::language()).'
		</div>
	</div>
	';
}
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px;padding:3px;">'.$Res->html(415,page::language()).' ...</div>
	</div>
	';
}
// Ende der Content Tabelle
$out .= '
</div>
'.$PagingHTML;

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
			<td><img src="/images/icons/delete.png" title="'.$Res->html(405,page::language()).'" alt="'.$Res->html(405,page::language()).'" border="0"></td>
			<td>'.$Res->html(416,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete_disabled.png" title="'.$Res->html(406,page::language()).'" alt="'.$Res->html(406,page::language()).'" border="0"></td>
			<td>'.$Res->html(417,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(414,page::language()).'" alt="'.$Res->html(414,page::language()).'" border="0"></td>
			<td>'.$Res->html(418,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></td>
			<td>'.$Res->html(419,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');