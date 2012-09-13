<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleFaq();
$Module->loadObjects($Conn,$Res);

// Zugriff auf FAQ Eintrag testen
$Module->checkAccessRedirect();
// Daten holen, werden erstellt wenn nicht vorhanden
$Data = array();
$Module->loadData($Data);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveFaq($Data);

// Mediamanager Element vorbereiten
if(isset($_SESSION['ActualContentID'])) unset($_SESSION['ActualContentID']);
$_SESSION['ActualMenuID'] = page::menuID();
$_SESSION['ActualOwnerID'] = getInt($Data['faq_Answer']);
$_SESSION['ActualElementID'] = getInt($Data['ele_ID']);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="faqForm" method="post" action="edit.php?id='.page::menuID().'&entry='.$Data['faq_ID'].'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(456,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(457,page::language()).'</td>
		<td class="cNav" width=150"><a href="config.php?id='.page::menuID().'">'.$Res->html(329,page::language()).'</a></td>
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
				<a href="#" onClick="document.faqForm.submit()">
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
				<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&entry='.$Data['faq_ID'].'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2">
			<h1>FAQ '.$Res->html(457,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(458,page::language()).':
		</td>
		<td>
			<input type="radio" name="faqActive" value="1"'.checkCheckbox(1,$Data['faq_Active']).'> '.$Res->html(231,page::language()).' &nbsp;&nbsp;&nbsp;
			<input type="radio" name="faqActive" value="0"'.checkCheckbox(0,$Data['faq_Active']).'> '.$Res->html(230,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(459,page::language()).':
		</td>
		<td>
			<textarea name="faqQuestion" style="height:50px;" class="adminBufferField">'.$Data['faq_Question'].'</textarea>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(460,page::language()).':
		</td>
		<td>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<br>
			'.editor::getSized('Default','conContent',page::language(),$Data['con_Content'],'100%','300').'
		</td>
	</tr>
</table>
';

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120" valign="top">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(458,page::language()).'</em></td>
		<td>'.$Res->html(461,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(459,page::language()).'</em></td>
		<td>'.$Res->html(462,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(460,page::language()).'</em></td>
		<td>'.$Res->html(463,page::language()).'.</td>
	</tr>
</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');