<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Klassen laden
library::loadRelative('library');
library::loadRelative('categories/library');
library::loadRelative('keywords/library');
library::load('editor');
// Modulbezogene Funktionsklasse
$Module = new moduleBlog();
$Module->loadObjects($Conn,$Res);
$Module->checkBlogentryAccess();

// Objekt der Blogkategorien
$Categories = new blogCategory($Conn);
$Categories->loadObjects($Conn,$Res);
// Objekt für Keywords
$Keywords = new keywords($Conn);
$Keywords->loadObjects($Conn,$Res);
// Objekte dem Hauptmodulobjekt zuweisen
$Module->setCategoryObject($Categories);
$Module->setKeywordsObject($Keywords);

// Daten laden und Kalender starten
$Data = array();
$Module->loadBlogentry($Data);

// Daten verändern
if (isset($_GET['save'])) $Module->saveBlogentry();

$Calendar = htmlControl::calendar();
$Calendar->add('date_date');
// Selektor für Kategorien
$Selector = htmlControl::selector();
$Module->configureSelector($Selector);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Medienmanager konfigurieren
if(isset($_SESSION['ActualElementID'])) unset($_SESSION['ActualElementID']);
if(isset($_SESSION['ActualOwnerID'])) unset($_SESSION['ActualOwnerID']);
$_SESSION['ActualContentID'] = getInt($_GET['entry']);
$_SESSION['ActualMenuID'] = getInt($_GET['id']);

// Toolbar und Einleitung
$out .= '
<form name="contentIndex" method="post" action="edit.php?id='.page::menuID().'&entry='.getInt($_GET['entry']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(600,page::language()).'</a></td>
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
				<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&entry='.getInt($_GET['entry']).'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="categories/index.php?id='.page::menuID().'&entry='.getInt($_GET['entry']).'">
				<img src="/images/icons/table_edit.png" alt="'.$Res->html(622,page::language()).'" title="'.$Res->html(622,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="comments/index.php?id='.page::menuID().'&entry='.getInt($_GET['entry']).'">
				<img src="/images/icons/user_comment.png" alt="'.$Res->html(623,page::language()).'" title="'.$Res->html(623,page::language()).'" border="0"></a>
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
// Kopfbereich, Listendefinition
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(616,page::language()).'</h1><br></td>
	</tr>
</table>
';

// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td width="150" valign="top">'.$Res->html(64,page::language()).':</td>
		<td valign="top">
			<input name="conTitle" type="text" maxlength="255" class="adminBufferField" value="'.$Data['con_Title'].'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(172,page::language()).':</td>
		<td valign="top">
			<input id="date_date" name="date_date" type="text" maxlength="10" style="width:100px;" value="'.$Data['date_date'].'"> / 
			<input name="date_time" type="text" maxlength="8" style="width:80px;" value="'.$Data['date_time'].'"> 
			'.$Calendar->get('date_date').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(173,page::language()).':</td>
		<td valign="top">
			<input id="modified_date" name="modified_date" type="text" maxlength="10" style="width:100px;" value="'.$Data['modified_date'].'" disabled> / 
			<input name="modified_time" type="text" maxlength="8" style="width:80px;" value="'.$Data['modified_time'].'" disabled> 
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(174,page::language()).':</td>
		<td valign="top">
			<input name="conShowName" type="checkbox" value="1"'.checkCheckbox(1,$Data['con_ShowName']).'> '.$Res->html(617,page::language()).'<br>
			<input name="conShowDate" type="radio" value="0"'.checkCheckbox(0,$Data['con_ShowDate']).'> '.$Res->html(619,page::language()).'<br>
			<input name="conShowDate" type="radio" value="1"'.checkCheckbox(1,$Data['con_ShowDate']).'> '.$Res->html(618,page::language()).'<br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(620,page::language()).':</td>
		<td>
			'.$Selector->get('categorySelector').'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(644,page::language()).':</td>
		<td>
			<textarea name="keywords" style="width:300px;height:70px;">'.$Module->getKeywords().'</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" valign="top">'.$Res->html(179,page::language()).':<br>
		<br>
		'.editor::get('Default','conContent',page::language(),$Data['con_Content']).'
		</td>
	</tr>
</table>
';

// Tabrow objekt
$TabRow = new tabRowExtender();
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
			<td><img src="/images/icons/table_edit.png" title="'.$Res->html(622,page::language()).'" alt="'.$Res->html(622,page::language()).'"></td>
			<td>'.$Res->html(624,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_comment.png" title="'.$Res->html(623,page::language()).'" alt="'.$Res->html(623,page::language()).'"></td>
			<td>'.$Res->html(625,page::language()).'.</td>
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
			<td width="120" valign="top"><em>'.$Res->html(620,page::language()).'</em></td>
			<td valign="top">'.$Res->html(621,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(644,page::language()).'</em></td>
			<td valign="top">'.$Res->html(645,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');