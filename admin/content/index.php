<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('content');
$Module = new moduleContent();
$Module->loadObjects($Conn,$Res);
$Meta->addJavascript('/scripts/system/formAdmin.js',true);

// Zeugs machen
if (isset($_GET['add'])) $Module->addSection();
if (isset($_GET['save'])) $Module->saveContentSections();
if (isset($_GET['delete'])) $Module->deleteSection();
if (isset($_GET['copy'])) $Module->copySection(false);
if (isset($_GET['cut'])) $Module->copySection(true);
if (isset($_GET['paste'])) $Module->pasteSection();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Daten laden
$sData = $Module->loadContentSections($Conn,$Res);

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="200">'.$Res->html(153,page::language()).'</td>
		<td class="cNavDisabled" width="200">'.$Res->html(154,page::language()).'</td>
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
				<a href="#" onClick="openWindow(\'/controller.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser&cmspreview\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				'.$Module->getPasteIcon().'
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add=content">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(147,page::language()).'" title="'.$Res->html(147,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add=media">
				<img src="/images/icons/image_add.png" alt="'.$Res->html(148,page::language()).'" title="'.$Res->html(148,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add=form">
				<img src="/images/icons/table_add.png" alt="'.$Res->html(149,page::language()).'" title="'.$Res->html(149,page::language()).'" border="0">
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
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="5">
			<h1>'.$Module->getContentAdminTitle($Menu->CurrentMenu->Type).'</h1><br>
		</td>
	</tr>
</table>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="width:50px;float:left;text-align:center;"><strong>'.$Res->html(10,page::language()).'</strong></div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:50px;float:left;text-align:center"><strong>'.$Res->html(160,page::language()).'</strong></div>
		<div style="width:20px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($sData as $row) {
	// Wenn Formular, statt Owner, CseID zeigen
	$sGetId = $row['con_ID'];
	if ($row['cse_Type'] != 1) {
		$sGetId = $row['cse_ID'];
	}
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:20px;float:left;">
			<a href="'.$row['link'].'?id='.page::menuID().'&content='.$sGetId.'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&section='.$row['cse_ID'].'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
			<img src="/images/icons/bullet_magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['cse_ID'].'\',\''.addslashes($row['cse_Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;">
			<a href="index.php?id='.page::menuID().'&amp;copy='.$row['cse_ID'].'">
			<img src="/images/icons/page_white_copy.png" title="'.$Res->html(733,page::language()).'" alt="'.$Res->html(733,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="index.php?id='.page::menuID().'&amp;cut='.$row['cse_ID'].'">
			<img src="/images/icons/cut.png" title="'.$Res->html(1154,page::language()).'" alt="'.$Res->html(1154,page::language()).'" border="0"></a>
		</div>
		<div style="width:50px;float:left;text-align:center">
			<img src="/images/icons/'.$row['image'].'" title="'.$row['desc'].'" alt="'.$row['desc'].'">
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="name[]" value="'.$row['cse_Name'].'" class="adminBufferInput">
			<input type="hidden" name="sort[]" value="'.$row['cse_Sortorder'].'" size="4" maxlength="3">
			<input type="hidden" name="id[]" value="'.$row['cse_ID'].'">
		</div>
		<div style="width:50px;float:left;text-align:center">
			<div name="divActive">
			<input type="checkbox" name="active_'.$nCount++.'" value="1"'.checkCheckbox(1,$row['cse_Active']).'>
			</div>
		</div>
		<div style="width:20px;float:left;">
			<a href="#" id="tabRow_'.$nCount.'" onMouseover="SetPointer(this.id,\'move\')" onMouseout="SetPointer(this.id,\'default\')" title="'.$Res->html(214,page::language()).'">
			<img src="/images/icons/arrow_in.png" border="0" alt="'.$Res->html(214,page::language()).'" title="'.$Res->html(214,page::language()).'"></a>
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
// Ende der Content Tabelle
$out .= '</div>';

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
			<td><img src="/images/icons/page.png" title="'.$Res->html(150,page::language()).'" alt="'.$Res->html(150,page::language()).'"></td>
			<td>'.$Res->html(161,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/image.png" title="'.$Res->html(151,page::language()).'" alt="'.$Res->html(151,page::language()).'"></td>
			<td>'.$Res->html(162,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/table.png" title="'.$Res->html(152,page::language()).'" alt="'.$Res->html(152,page::language()).'"></td>
			<td>'.$Res->html(163,page::language()).'.</td>
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
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/page_white_copy.png" title="'.$Res->html(733,page::language()).'" alt="'.$Res->html(733,page::language()).'" border="0"></td>
			<td>'.$Res->html(734,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/paste_plain.png" title="'.$Res->html(737,page::language()).'" alt="'.$Res->html(737,page::language()).'" border="0"></td>
			<td>'.$Res->html(739,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/arrow_in.png" title="'.$Res->html(214,page::language()).'" alt="'.$Res->html(214,page::language()).'" border="0"></td>
			<td>'.$Res->html(215,page::language()).'.</td>
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
		<td width="120" valign="top"><em>'.$Res->html(44,page::language()).'</em></td>
		<td valign="top">'.$Res->html(166,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(159,page::language()).'.</em></td>
		<td valign="top">'.$Res->html(167,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(82,page::language()).'</em></td>
		<td valign="top">'.$Res->html(168,page::language()).'.</td>
	</tr>
	</table>
</div>
';

$out .= '
<script type="text/javascript">
	Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateContentIndizes});
</script>
';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');