<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleLog();
$Module->loadObjects($Conn,$Res);
// Javascripts
$Meta->addJavascript('/scripts/system/ajax.js',true);
$Meta->addJavascript('/admin/log/library.js',true);

// Suchfilter abändern
if (isset($_POST['filter'])) $Module->setFilter();
if (isset($_POST['reset'])) $Module->resetFilter();
if (isset($_GET['delete'])) $Module->deleteLog();
if (isset($_GET['sort'])) $Module->addSort($_GET['field'],$_GET['type']);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Kalender für Filter erstellen
$Calendar = htmlControl::calendar();
$Calendar->add('datefrom');
$Calendar->add('dateto');

// Daten laden
$Data = array();
$sPagingHtml = $Module->loadLog($Data);

$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(828,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(829,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp
				'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td colspan="4">
			<h1>'.$Res->html(830,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="140" valign="top">
			'.$Res->html(831,page::language()).':
		</td>
		<td colspan="3">
			<input type="text" style="width:288px;" name="searchword" value="'.$Module->getFilter('search').'">
		</td>
	</tr>
	<tr>
		<td width="140" valign="top">
			'.$Res->html(832,page::language()).':
		</td>
		<td width="140">
			<input type="text" style="width:100px;" name="datefrom" id="datefrom" value="'.$Module->getFilter('datefrom').'">
			'.$Calendar->get('datefrom').'
		</td>
		<td width="40">
			bis
		</td>
		<td>
			<input type="text" style="width:100px;" name="dateto" id="dateto" value="'.$Module->getFilter('dateto').'">
			'.$Calendar->get('dateto').'
		</td>
	</tr>
	<tr>
		<td width="140" valign="top">
			&nbsp;
		</td>
		<td colspan="3">
			<input type="submit" name="filter" value="'.$Res->html(833,page::language()).'" class="cButton"> 
			<input type="submit" name="reset" value="'.$Res->html(834,page::language()).'" class="cButton">
		</td>
	</tr>
</table>
</form>';

$TabRow = new tabRowExtender();
$Tooltip = htmlControl::tooltip();
$out .= $Tooltip->initialize();

$out .= '
	'.$sPagingHtml.'
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:70px;float:left;">
			<strong>'.$Res->html(836,page::language()).'</strong> 
			'.$Module->getSortIcon(moduleLog::FIELD_NR).'
		</div>
		<div style="width:90px;float:left;">
			<strong>'.$Res->html(652,page::language()).'</strong>
			'.$Module->getSortIcon(moduleLog::FIELD_CATEGORY).'
		</div>
		<div style="width:150px;float:left;">
			<strong>'.$Res->html(837,page::language()).'</strong>
			'.$Module->getSortIcon(moduleLog::FIELD_USER).'
		</div>
		<div style="width:120px;float:left;">
			<strong>'.$Res->html(838,page::language()).'</strong>
			'.$Module->getSortIcon(moduleLog::FIELD_MENU).'
		</div>
		<div style="width:150px;float:left;">
			<strong>'.$Res->html(839,page::language()).'</strong>
			'.$Module->getSortIcon(moduleLog::FIELD_ERROR).'
		</div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data as $row) {
	$nCount++;
	// Werte für Ausgabe bereit machen
	$sCategory = $Module->getCategoryByType($row['log_Type']);
	$sUser = stringOps::chopString($row['log_Userinfo'],12,true);
	stringOps::htmlViewEnt($sUser);
	$sMenu = stringOps::chopString($row['log_Menuinfo'],14,true);
	stringOps::htmlViewEnt($sMenu);
	$sError = stringOps::chopString($row['log_Error'],20,true);
	stringOps::htmlViewEnt($sMenu);
	// Tooltips erstellen
	$Tooltip->add('user_'.$nCount,$row['log_Userinfo'],$Res->html(837,page::language()),350,0);
	$out .= $Tooltip->get('user_'.$nCount);
	$Tooltip->add('menu_'.$nCount,$row['log_Menuinfo'],$Res->html(838,page::language()),350,0);
	$out .= $Tooltip->get('menu_'.$nCount);
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&error='.$row['log_ID'].'">
			<img src="/images/icons/bullet_magnifier.png" alt="'.$Res->html(835,page::language()).'" title="'.$Res->html(835,page::language()).'" border="0"></a>
		</div>
		<div style="width:16px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['log_ID'].'\',\'Log #'.$row['log_ID'].'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" border="0" alt="'.$Res->html(157,page::language()).'" title="'.$Res->html(157,page::language()).'"></a>
		</div>
		<div style="width:70px;float:left;">
			#'.$row['log_ID'].'
		</div>
		<div style="width:90px;float:left;">
			'.$sCategory.'
		</div>
		<div style="width:150px;float:left;" id="user_'.$nCount.'">
			<a href="javascript:updateUser('.$row['usr_ID'].','.$nCount.','.page::menuID().');">
				'.$sUser.'
			</a>
		</div>
		<div style="width:120px;float:left;" id="menu_'.$nCount.'">
			<a href="javascript:updateMenu('.$row['mnu_ID'].','.$nCount.','.page::menuID().');">
			'.$sMenu.'
			</a>
		</div>
		<div style="width:150px;float:left;">
			'.$sError.'
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
$out .= '</div>'.$sPagingHtml;

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');