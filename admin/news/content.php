<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::load('content');
$Module = new moduleContent();
$Module->loadObjects($Conn,$Res);

// Speichern von Daten, zuvor zugriff checken
$Module->checkNewsAccess($Conn,$Res);
if (isset($_GET['save'])) $Module->saveContent('news');

// Kalender Controls erstellen
$Calendar 	= htmlControl::calendar();
$Calendar->add('date_date');
$Calendar->add('date_from');
$Calendar->add('date_to');

// Daten laden
$sData = array();
$_SESSION['ActualContentID'] = getInt($_GET['content']);
$_SESSION['ActualMenuID'] = getInt($_GET['id']);
$Module->loadContent($sData);
$FromTo = array();
$Module->loadFromTo($FromTo,$sData);
// Toolbar erstellen
$out = '
<form name="newsEdit" method="post" action="content.php?id='.page::menuID().'&content='.$_GET['content'].'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(366,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(367,page::language()).'</td>
		<td class="cNav" width="150"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
		<td class="cNav">&nbsp;</td>
	</tr>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbar">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="document.newsEdit.submit()">
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
				<a href="#" onClick="openWindow(\'/admin/news/preview.php?id='.page::menuID().'&content='.getInt($_GET['content']).'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700);">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
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
				&nbsp;'.$Module->checkErrorSession($Res).'
			</div>
		</td>
	</tr>
</table>
<br>
';

$out .= '
<table width="100%" cellspacing="0" border="0" cellpadding="3">
	<tr>
		<td><h1>'.$Res->html(367,page::language()).'</h1><br>
		'.$Module->showErrorSession().'
		</td>
	</tr>
</table>
';

// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="150" valign="top">'.$Res->html(64,page::language()).':</td>
		<td valign="top">
			<input name="title" type="text" maxlength="255" class="adminBufferField" value="'.$sData['con_Title'].'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(172,page::language()).':</td>
		<td valign="top">
			<input id="date_date" name="date_date" type="text" maxlength="10" style="width:100px;" value="'.$sData['date_date'].'"> / 
			<input name="date_time" type="text" maxlength="8" style="width:80px;" value="'.$sData['date_time'].'"> 
			'.$Calendar->get('date_date').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(375,page::language()).':</td>
		<td valign="top">
			<input id="date_from" name="date_from" type="text" maxlength="10" style="width:100px;" value="'.$FromTo['date_from'].'"> / 
			<input name="date_from_time" type="text" maxlength="8" style="width:80px;" value="'.$FromTo['date_from_time'].'"> 
			'.$Calendar->get('date_from').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(376,page::language()).':</td>
		<td valign="top">
			<input id="date_to" name="date_to" type="text" maxlength="10" style="width:100px;" value="'.$FromTo['date_to'].'"> / 
			<input name="date_to_time" type="text" maxlength="8" style="width:80px;" value="'.$FromTo['date_to_time'].'"> 
			'.$Calendar->get('date_to').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(173,page::language()).':</td>
		<td valign="top">
			<input id="modified_date" name="modified_date" type="text" maxlength="10" style="width:100px;" value="'.$sData['modified_date'].'" disabled> / 
			<input name="modified_time" type="text" maxlength="8" style="width:80px;" value="'.$sData['modified_time'].'" disabled> 
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(174,page::language()).':</td>
		<td valign="top">
			<input name="showname" type="checkbox" value="1"'.checkCheckbox(1,$sData['con_ShowName']).'> '.$Res->html(175,page::language()).'<br>
			<input name="showdate" type="radio" value="0"'.checkCheckbox(0,$sData['con_ShowDate']).'> '.$Res->html(176,page::language()).'<br>
			<input name="showdate" type="radio" value="1"'.checkCheckbox(1,$sData['con_ShowDate']).'> '.$Res->html(177,page::language()).'<br>
			<input name="showdate" type="radio" value="2"'.checkCheckbox(2,$sData['con_ShowDate']).'> '.$Res->html(178,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="top">'.$Res->html(179,page::language()).':<br>
		<br>
		'.editor::get('Default','content',page::language(),$sData['con_Content']).'
		</td>
	</tr>
</table>
';
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();
// Hilfe anzeigen
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
		<td width="120" valign="top"><em>'.$Res->html(173,page::language()).'</em></td>
		<td>'.$Res->html(180,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(64,page::language()).'</em></td>
		<td>'.$Res->html(181,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(377,page::language()).'</em></td>
		<td>'.$Res->html(378,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(82,page::language()).'</em></td>
		<td>'.$Res->html(379,page::language()).'.</td>
	</tr>
</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');