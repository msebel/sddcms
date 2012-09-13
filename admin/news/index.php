<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('content');
$Module = new moduleContent();
$Module->loadObjects($Conn,$Res);

// Zeugs machen
if (isset($_GET['add'])) $Module->addContentOnly();
if (isset($_GET['save'])) $Module->saveNews();
if (isset($_GET['paste'])) $Module->pasteNews();
if (isset($_GET['delete'])) $Module->deleteContent();
if (isset($_GET['copy'])) $Module->copyNews(false);
if (isset($_GET['cut'])) $Module->copyNews(true);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Konfiguration
$NewsConfig = array();
$Module->initConfig($NewsConfig);
// Daten laden
$sData = array();
$PagingHTML = $Module->loadNewsOverview($sData,$NewsConfig);

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(366,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(367,page::language()).'</td>
		<td class="cNav" width="150"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
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
				<a href="#" onClick="document.newsIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'/controller.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser&cmspreview\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add=news">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(364,page::language()).'" title="'.$Res->html(364,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				'.$Module->getNewsPasteIcon().'
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
<form name="newsIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(368,page::language()).'</h1>
		</td>
	</tr>
</table>
'.$PagingHTML.'
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:90px;float:left;"><strong>'.$Res->html(365,page::language()).'</strong></div>
		<div style="width:30px;float:left;"><strong>'.$Res->html(160,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($sData as $row) {
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:20px;float:left;">
			<a href="/admin/news/content.php?id='.page::menuID().'&content='.$row['con_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="#" onClick="openWindow(\'/admin/news/preview.php?id='.page::menuID().'&content='.$row['con_ID'].'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700);">
			<img src="/images/icons/bullet_magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['con_ID'].'\',\''.addslashes($row['con_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="index.php?id='.page::menuID().'&amp;copy='.$row['con_ID'].'">
			<img src="/images/icons/page_white_copy.png" title="'.$Res->html(733,page::language()).'" alt="'.$Res->html(733,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="index.php?id='.page::menuID().'&amp;cut='.$row['con_ID'].'">
			<img src="/images/icons/cut.png" title="'.$Res->html(1154,page::language()).'" alt="'.$Res->html(1154,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="name[]" value="'.$row['con_Title'].'" class="adminBufferInput">
		</div>
		<div style="width:90px;float:left;">
			<input type="text" name="date[]" value="'.$row['con_Date'].'" style="width:80px" disabled>
		</div>
		<div style="width:30px;float:left;text-align:center">
			<div name="divActive">
			<input type="hidden" name="id[]" value="'.$row['con_ID'].'">
			<input type="checkbox" name="active_'.$nCount++.'" value="1"'.checkCheckbox(1,$row['con_Active']).'>
			</div>
		</div>
	</div>
	';
}
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px;padding:3px;">'.$Res->html(369,page::language()).' ...</div>
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
			<td><img src="/images/icons/delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></td>
			<td>'.$Res->html(164,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></td>
			<td>'.$Res->html(165,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></td>
			<td>'.$Res->html(170,page::language()).'.</td>
		</tr>
	</table>
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top">'.$Res->html(365,page::language()).'</td>
		<td>'.$Res->html(370,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');