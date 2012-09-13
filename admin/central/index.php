<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
$Meta->addJavascript('/admin/central/library.js',true);
library::loadRelative('library');
$Module = new moduleCentral();
$Module->loadObjects($Conn,$Res);

// Daten verändern
if (isset($_GET['add'])) $Module->addEntity();
if (isset($_GET['save'])) $Module->saveEntities();
if (isset($_GET['delete'])) $Module->deleteEntity();

// Daten laden
$Data = array();
$PagingHTML = $Module->loadEntities($Data);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar und Einleitung
$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(809,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(810,page::language()).'</td>
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
				<a href="#" onClick="openWindow(\'/modules/central/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0">
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
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="5">
			<h1>'.$Res->html(812,page::language()).'</h1><br>
		</td>
	</tr>
</table>
';

$TabRow = new tabRowExtender();

$out .= '
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:50px;float:left;"><strong>'.$Res->html(160,page::language()).'</strong></div>
		<div style="width:20px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data as $row) {
	$nCount++;
	// Daten validieren
	if ($row['cse_Name'] == NULL) $row['cse_Name'] = $Res->html(813,page::language());
	if ($row['cse_Active'] == NULL) $row['cse_Active'] = false;
	$sTypeIcon = $Module->getTypeIcon($row['cse_Type']);
	$sPreviewHtml = $Module->getPreviewIcon($row['cse_Type'],$row['mcs_ID']);
	// Zeile ausgeben
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&entity='.$row['mcs_ID'].'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'"></a>
		</div>
		<div style="width:16px;float:left;">
			'.$sPreviewHtml.'
		</div>	
		<div style="width:16px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['mcs_ID'].'\',\''.addslashes($row['cse_Name']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;text-align:center;">
			<img src="'.$sTypeIcon.'" title="'.$Res->html(428,page::language()).'" alt="'.$Res->html(428,page::language()).'">
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" disabled="disabled" value="'.$row['cse_Name'].'" class="adminBufferInput">
			<input type="hidden" name="sort[]" value="'.$row['mcs_Sort'].'" size="4" maxlength="3">
			<input type="hidden" name="id[]" value="'.$row['mcs_ID'].'">
		</div>
		<div style="width:50px;float:left;">
			<input type="checkbox" disabled="disabled"'.checkCheckbox(1,$row['cse_Active']).'>
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
$out .= '</div></form>
<script type="text/javascript">
	Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateSort});
</script>
';

// Hilfe
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
			<td><img src="/images/icons/page_add.png" title="'.$Res->html(425,page::language()).'" alt="'.$Res->html(425,page::language()).'"></td>
			<td>'.$Res->html(811,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/page.png" title="'.$Res->html(428,page::language()).'" alt="'.$Res->html(428,page::language()).'"></td>
			<td>'.$Res->html(161,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/image.png" title="'.$Res->html(428,page::language()).'" alt="'.$Res->html(428,page::language()).'"></td>
			<td>'.$Res->html(162,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/table.png" title="'.$Res->html(428,page::language()).'" alt="'.$Res->html(428,page::language()).'"></td>
			<td>'.$Res->html(163,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/cancel.png" title="'.$Res->html(428,page::language()).'" alt="'.$Res->html(428,page::language()).'"></td>
			<td>'.$Res->html(814,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');