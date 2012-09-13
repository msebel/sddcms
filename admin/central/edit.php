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
$TabRow = new tabRowExtender();

// Zugriff prüfen
$nMcsID = getInt($_GET['entity']);
$Module->checkAccess($nMcsID);

// Daten lesen
$Data = NULL;
$Module->loadEntity($nMcsID,$Data);

// Aktuelles Sourcemenu holen
$nSourceMenu = $Module->getSourceMenu($Data);

// Daten verändern
if (isset($_GET['source'])) $Module->changeSource($nSourceMenu);
if (isset($_GET['save'])) $Module->saveEntity($nMcsID);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar und Einleitung
$out .= '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(809,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(810,page::language()).'</td>
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
				<a href="#" onClick="document.contentForm.submit()">
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
				<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&entity='.$nMcsID.'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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
<form name="menuForm" method="post" action="edit.php?id='.page::menuID().'&entity='.$nMcsID.'&source">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(816,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(815,page::language()).':
		</td>
		<td valign="top">
			<select name="sourceMenu" onChange="document.menuForm.submit()" class="adminBufferInput">
				'.$Module->getSourceMenuOptions($nSourceMenu).'
			</select>
		</td>
	</tr>
</table>
</form>
<br>
<form name="contentForm" method="post" action="edit.php?id='.page::menuID().'&entity='.$nMcsID.'&save">
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td width="150" valign="top">
			'.$Res->html(817,page::language()).':
		</td>
		<td valign="top">
			<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
				<div style="width:30px;float:left;">&nbsp;</div>
				<div style="width:30px;float:left;">&nbsp;</div>
				<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
				<div style="width:50px;float:left;"><strong>'.$Res->html(160,page::language()).'</strong></div>
			</div>
			'.$Module->getContentList($Data, $nSourceMenu).'
		</td>
	</tr>
</table>
</form>
';

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
			<td width="120" valign="top"><em>'.$Res->html(815,page::language()).'</em></td>
			<td valign="top">'.$Res->html(818,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td width="120" valign="top"><em>'.$Res->html(817,page::language()).'</em></td>
			<td valign="top">'.$Res->html(819,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');