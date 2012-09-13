<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Bibliothek laden und Objekte übergeben
library::loadRelative('library');
$Module = new moduleGlossary();
$Module->loadObjects($Conn,$Res);

// Befehle ausführen
if (isset($_GET['save'])) $Module->saveItems();
if (isset($_GET['delete'])) $Module->deleteItem();
if (isset($_GET['add'])) $Module->addItem();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="formIndex" method="post" action="index.php?id='.page::menuID().'&page='.getInt($_GET['page']).'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(474,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(457,page::language()).'</td>
		<td class="cNav" width="150"><a href="config.php?id='.page::menuID().'&page='.getInt($_GET['page']).'">'.$Res->html(329,page::language()).'</a></td>
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
				<a href="#" onClick="document.formIndex.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'/modules/glossar/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
				<img src="/images/icons/magnifier.png" alt="'.$Res->html(169,page::language()).'" title="'.$Res->html(169,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(425,page::language()).'" title="'.$Res->html(425,page::language()).'" border="0">
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
';

// übersicht anzeigen
$TabRow = new tabRowExtender();
$GlossarEntries = array();
$sPageHtml = $Module->loadEntries($GlossarEntries);

$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(473,page::language()).'</h1>
		</td>
	</tr>
</table>
'.$sPageHtml.'

<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
	<div style="width:16px;float:left;">&nbsp;</div>
	<div style="width:16px;float:left;">&nbsp;</div>
	<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
	<div style="width:100px;float:left;"><strong>'.$Res->html(365,page::language()).'</strong></div>
	<div style="width:50px;float:left;text-align:center"><strong>'.$Res->html(160,page::language()).'</strong></div>
</div>
';

// Einträge anzeigen
$nCount = 0;
foreach ($GlossarEntries as $Entry) {
	// Tabellenzeilenwechsler
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&item='.$Entry['con_ID'].'&page='.getInt($_GET['page']).'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(457,page::language()).'" alt="'.$Res->html(457,page::language()).'" border="0"></a>
		</div>
		<div style="width:16px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$Entry['con_ID'].'&page='.getInt($_GET['page']).'\',\''.addslashes($Entry['con_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="hidden" name="conID[]" value="'.$Entry['con_ID'].'">
			<input type="text" name="conTitle[]" value="'.$Entry['con_Title'].'" class="adminBufferInput">
		</div>
		<div style="width:100px;float:left;text-align:center">
			<input type="text" name="conDate[]" value="'.$Entry['con_Date'].'" style="width:90px">
		</div>
		<div style="width:50px;float:left;text-align:center">
			<div name="divActive">
			<input type="checkbox" name="active_'.$nCount++.'" value="1"'.checkCheckbox(1,$Entry['con_Active']).'>
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
$out .= '</div>';

// Hilfedialog zeigen
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
			<td><img src="/images/icons/page_add.png" title="" alt=""></td>
			<td>'.$Res->html(475,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="" alt=""></td>
			<td>'.$Res->html(476,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/magnifier.png" title="" alt=""></td>
			<td>'.$Res->html(477,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="" alt=""></td>
			<td>'.$Res->html(478,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');