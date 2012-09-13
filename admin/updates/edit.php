<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
$Meta->addJavascript('/admin/updates/functions.js',true);
$Meta->addJavascript('/scripts/system/ajax.js',true);
$Meta->addJavascript('/scripts/calendar/cal.js',true);
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleUpdates();
$Module->loadObjects($Conn,$Res);

// Zugriff testen
$nLink = getInt($_GET['link']);
$Module->CheckAccess($nLink);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveUpdate();

// Kalender Controls erstellen
$Calendar 	= htmlControl::calendar();
$Calendar->add('lnkDate');

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$Data = array();
$Module->loadUpdate($nLink,$Data);

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="edit.php?id='.page::menuID().'&link='.$nLink.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="140"><a href="index.php?id='.page::menuID().'">'.$Res->html(535,page::language()).'</a></td>
		<td class="cNavSelected" width="140">'.$Res->html(536,page::language()).'</td>
		<td class="cNav" width="140"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
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
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(538,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(515,page::language()).':
		</td>
		<td>
			<input type="text" maxlength="100" name="lnkName" value="'.$Data['lnk_Name'].'" class="adminBufferField">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(365,page::language()).':
		</td>
		<td>
			<input type="text" maxlength="255" id="lnkDate" name="lnkDate" value="'.$Data['lnk_Date'].'" class="adminBufferField">
			'.$Calendar->get('lnkDate').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(537,page::language()).':
		</td>
		<td>
			<select name="lnkURL" class="adminBufferField">
				'.$Menu->getSelectOptions($Data['lnk_URL']).'
			</select>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(517,page::language()).':
		</td>
		<td>
			<select name="lnkTarget">
				<option value="0"></option>
				<option value="1"'.checkDropDown(1,$Data['lnk_Target']).'>'.$Res->html(518,page::language()).'</option>
				<option value="2"'.checkDropDown(2,$Data['lnk_Target']).'>'.$Res->html(519,page::language()).'</option>
			</select>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(520,page::language()).':
		</td>
		<td>
			<textarea name="lnkDesc" cols="40" rows="3">'.$Data['lnk_Desc'].'</textarea>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="lnkActive" value="1"'.checkCheckbox(1,$Data['lnk_Active']).'> '.$Res->html(539,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(522,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(523,page::language()).':
		</td>
		<td>
			<span id="lnkClickValue">'.$Data['lnk_Clicks'].'</span> '.$Res->html(524,page::language()).' 
			(<a href="#" onclick="'.ajaxRequest::simple('ajax/resetLink.php?id='.page::menuID().'&link='.$nLink).'resetLink();">'.$Res->html(526,page::language()).'</a>)
		</td>
	</tr>
</table>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');