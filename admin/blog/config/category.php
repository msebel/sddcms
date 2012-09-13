<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleBlogConfig();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$Config = array();
$Module->initConfigCategory($Config);
// Konfiguration speichern
if (isset($_GET['save'])) $Module->saveConfigCategory($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Register und Toolbar
$out .= '
<form name="contentIndex" method="post" action="category.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(329,page::language()).'</td>
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
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
';

// Formular
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(650,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(651,page::language()).':
		</td>
		<td>
			'.$Module->getBlogSelector($Config['blogID']['Value']).'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(652,page::language()).':
		</td>
		<td>
			'.$Module->getCategorySelector($Config['blogID']['Value'],$Config['categoryID']['Value']).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">'.$Res->html(755,page::language()).':</td>
		<td>
			'.$Module->getRssLink(1).'
		</td>
	</tr>
</table>
</form>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');