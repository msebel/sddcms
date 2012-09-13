<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
require_once(BP.'/library/class/mediaManager/mediaConst.php');
library::load('editor');
library::loadRelative('library');
$Module = new moduleCalendar();
$Module->loadObjects($Conn,$Res);
// Zugriff testen
$nCalID = getInt($_GET['item']);
$Module->checkAccess($nCalID);
// Konfiguration laden
$Config = array();
$Module->initConfig($Config);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveDate($Config);
if (isset($_GET['remove'])) $Module->removeFile();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Kalender Controls erstellen
$Calendar 	= htmlControl::calendar();
$Calendar->add('calStartDate');
$Calendar->add('calEndDate');

// Daten laden
$Data = NULL;
$Module->loadDate($Data);
// Toolbar erstellen
$out .= '
<form name="contentIndex" method="post" action="edit.php?id='.page::menuID().'&item='.$nCalID.'&save" enctype="multipart/form-data">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(546,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'">
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

$out .= '
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(553,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(554,page::language()).':
		</td>
		<td>
			<input type="text" name="calTitle" value="'.$Data['cal_Title'].'" class="adminBufferInput">
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(555,page::language()).':
		</td>
		<td>
			<input type="text" name="calCity" value="'.$Data['cal_City'].'" class="adminBufferInput">
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(556,page::language()).':
		</td>
		<td>
			<input type="text" name="calLocation" value="'.$Data['cal_Location'].'" class="adminBufferInput">
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(557,page::language()).':
		</td>
		<td>
			<input id="calStartDate" name="calStartDate" type="text" maxlength="10" style="width:100px;" value="'.$Data['cal_Start_Date'].'"> / 
			<input name="calStartTime" type="text" maxlength="8" style="width:80px;" value="'.$Data['cal_Start_Time'].'"> 
			'.$Calendar->get('calStartDate').'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(558,page::language()).':
		</td>
		<td>
			<input id="calEndDate" name="calEndDate" type="text" maxlength="10" style="width:100px;" value="'.$Data['cal_End_Date'].'"> / 
			<input name="calEndTime" type="text" maxlength="8" style="width:80px;" value="'.$Data['cal_End_Time'].'"> 
			'.$Calendar->get('calEndDate').'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(559,page::language()).':
		</td>
		<td>
			<select name="calType" class="adminBufferInput">
				'.$Module->getTypeDropdown($Data).'
			</select>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(746,page::language()).':
		</td>
		<td>
			'.$Module->getFileHtml($Data['ele_ID']).'
		</td>
	</tr>
	<tr>
		<td width="150">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="calActive" value="1"'.checkCheckbox(1,$Data['cal_Active']).'>
			'.$Res->html(560,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(745,page::language()).':<br>
			<br>
			'.editor::getSized('Config','calText',page::language(),$Data['cal_Text'],'100%','250').'
		</td>
	</tr>
</table>
';

// Hilfedialog zeigen
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
			<td width="120" valign="top"><em>'.$Res->html(557,page::language()).' / '.$Res->html(558,page::language()).'</em></td>
			<td valign="top">'.$Res->html(569,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(559,page::language()).'</em></td>
			<td valign="top">'.$Res->html(570,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');