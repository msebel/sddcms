<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('form');
$Module = new moduleForm();
$Module->loadObjects($Conn,$Res);

require_once(BP.'/library/class/mediaManager/formCode.php');

// Zugriff testen
$nConID = getInt($_GET['content']);
$nFieldID = getInt($_GET['field']);
$Module->checkDropdownAccess($nConID,$nFieldID);
// Laden der aktuellen Optionen
$Options = array();
$Module->loadSelectOptions($Options);

// Speichern
if(isset($_GET['save']))  $Module->saveDropdownOptions($Options);
if(isset($_GET['delete'])) $Module->deleteDropdownOption($Options);
if(isset($_GET['add'])) $Module->addDropdownOption();

// Toolbar erstellen
$out = '
<form name="formEdit" method="post" action="select.php?id='.page::menuID().'&content='.$nConID.'&field='.$nFieldID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="200"><a href="form.php?id='.page::menuID().'&content='.$nConID.'">'.$Res->html(37,page::language()).'</a></td>
		<td class="cNavSelected" width="200">'.$Res->html(764,page::language()).'</a></td>
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
				<a href="#" onClick="document.formEdit.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="form.php?id='.page::menuID().'&content='.$nConID.'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(765,page::language()).'" title="'.$Res->html(765,page::language()).'" border="0">
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
				<a href="select.php?id='.page::menuID().'&content='.$nConID.'&field='.$nFieldID.'&add">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
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

$TabRow = new tabRowExtender();
// Liste ausgeben
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
<tr>
	<td colspan="4"><h1>'.$Res->html(770,page::language()).'</h1><br></td>
</tr>
<tr class="tabRowHead">
	<td width="20">&nbsp;</td>
	<td width="200"><strong>'.$Res->html(766,page::language()).'</strong></td>
	<td><strong>'.$Res->html(767,page::language()).'</strong></td>
</tr>
';

$nCount = 0;
foreach ($Options as $Option) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td width="20">
			<a href="select.php?id='.page::menuID().'&content='.$nConID.'&field='.$nFieldID.'&delete='.$nCount.'">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</td>
		<td width="200">
			<input type="hidden" value="" name="option_'.$nCount.'">
			<input type="text" style="width:190px;" maxlength="50" value="'.$Option['value'].'" name="value_'.$nCount.'">
		</td>
		<td>
			<input type="text" style="width:190px;" maxlength="50" value="'.$Option['text'].'" name="text_'.$nCount.'">
		</td>
	</tr>
	';
	$nCount++;
}

// Wenn keine Records, anzeigen
if ($nCount == 0) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td width="16">&nbsp;</td>
		<td width="16">&nbsp;</td>
		<td colspan="2">'.$Res->html(483,page::language()).'</td>
	</tr>
	';
}
$out .= '</table>';

// Hilfe anzeigen
$out .= '
<br>
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(766,page::language()).'</em></td>
		<td valign="top">'.$Res->html(771,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(767,page::language()).'</em></td>
		<td valign="top">'.$Res->html(772,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');