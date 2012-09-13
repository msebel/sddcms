<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
$Meta->addJavascript('/scripts/system/formAdmin.js');
library::loadRelative('library');
$Module = new moduleCalendar();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$Config = array();
$Module->initConfig($Config);

// Zeugs machen
if (isset($_GET['add'])) $Module->addDate($Config);
if (isset($_GET['save'])) $Module->saveDates($Config);
if (isset($_GET['delete'])) $Module->deleteDate();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$Data = array();
$PagingHTML = $Module->loadDates($Data);

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(546,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/calendar_add.png" alt="'.$Res->html(548,page::language()).'" title="'.$Res->html(548,page::language()).'" border="0">
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
		<td colspan="5">
			<h1>'.$Res->html(547,page::language()).'</h1>
		</td>
	</tr>
</table>
'.$PagingHTML.'
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:90px;float:left;"><strong>'.$Res->html(365,page::language()).'</strong></div>
		<div style="width:60px;float:left;"><strong>'.$Res->html(727,page::language()).'</strong></div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:50px;float:left;text-align:center"><strong>'.$Res->html(160,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($Data as $row) {
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&item='.$row['cal_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:16px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['cal_ID'].'\',\''.addslashes($row['cal_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" style="width:90px;">
			<input type="text" name="date[]" value="'.$row['cal_Start_Date'].'" style="width:80px;" maxlength="10">
		</div>
		<div style="float:left;" style="width:60px;">
			<input type="text" name="time[]" value="'.$row['cal_Start_Time'].'" style="width:55px;" maxlength="5">
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="title[]" value="'.$row['cal_Title'].'" class="adminBufferInput" maxlength="255">
			<input type="hidden" name="id[]" value="'.$row['cal_ID'].'">
		</div>
		<div style="width:50px;float:left;text-align:center">
			<div name="divActive">
			<input type="checkbox" name="active_'.$nCount++.'" value="1"'.checkCheckbox(1,$row['cal_Active']).'>
			</div>
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
$out .= '</div>'.$PagingHTML;

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
			<td><img src="/images/icons/calendar_add.png" title="'.$Res->html(548,page::language()).'" alt="'.$Res->html(548,page::language()).'"></td>
			<td>'.$Res->html(549,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'"></td>
			<td>'.$Res->html(550,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'"></td>
			<td>'.$Res->html(551,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'"></td>
			<td>'.$Res->html(552,page::language()).'.</td>
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