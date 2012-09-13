<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleFaq();
$Module->loadObjects($Conn,$Res);

$Meta->addJavascript('/scripts/system/formAdmin.js',true);

// Zeugs machen
if (isset($_GET['add'])) $Module->addFaqEntry();
if (isset($_GET['save'])) $Module->saveFaqs();
if (isset($_GET['delete'])) $Module->deleteFaqEntry();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$Data = array();
$Module->loadFaqElements($Data);
// Konfiguration Initialisieren
$Config = array();
$Module->loadConfig($Config);

// Toolbar erstellen
$out = '
<form name="faqForm" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(456,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(457,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/page_add.png" alt="FAQ '.$Res->html(349,page::language()).'" title="FAQ '.$Res->html(349,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'/modules/faq/index.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(456,page::language()).'</h1><br>
		</td>
	</tr>
</table>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(459,page::language()).' ('.$Res->html(464,page::language()).')</strong></div>
		<div style="width:50px;float:left;"><strong>'.$Res->html(465,page::language()).'.</strong></div>
		<div style="width:40px;float:left;">&nbsp;</div>
		<div style="width:50px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($Data as $row) {
	$nCount++;
	$row['faq_Question'] = stringOps::chopString($row['faq_Question'],80,true);
	$sDesc = $row['faq_Question'];
	stringOps::htmlViewEnt($sDesc);
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:30px;padding-top:5px;">
		<div style="width:16px;float:left;padding-top:5px;">
			<a href="edit.php?id='.page::menuID().'&entry='.$row['faq_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(466,page::language()).'" alt="'.$Res->html(466,page::language()).'" border="0"></a>
		</div>
		<div style="width:20px;float:left;padding-top:5px;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['faq_ID'].'\',\''.addslashes($row['faq_Question']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="float:left;" class="adminBuffer">
			'.$sDesc.'&nbsp;
		</div>
		<div style="width:50px;float:left;padding-top:5px;">
			'.$Module->getAnswerHtml($row['faq_Answer']).'
		</div>
		<div style="width:40px;float:left;text-align:center;padding-top:5px;">
			<input type="checkbox" value="1" name="active_'.$row['faq_ID'].'"'.checkCheckBox(1,$row['faq_Active']).'>
		</div>
		<div style="width:50px;float:left;padding-top:5px;">
			<input type="hidden" name="sort[]" value="'.$nCount.'">
			<input type="hidden" name="id[]" value="'.$row['faq_ID'].'">
			<a href="#" id="tabRow_'.$nCount.'" onMouseover="SetPointer(this.id,\'move\')" onMouseout="SetPointer(this.id,\'default\')" title="'.$Res->html(214,page::language()).'">
			<img src="/images/icons/arrow_in.png" border="0" alt="'.$Res->html(214,page::language()).'" title="'.$Res->html(214,page::language()).'"></a>
		</div>
	</div>
	';
}
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px;padding:3px;">'.$Res->html(467,page::language()).' ...</div>
	</div>
	';
}
// Ende der Content Tabelle
$out .= '
</div>';

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(423,page::language()).'" alt="'.$Res->html(423,page::language()).'" border="0"></td>
			<td>'.$Res->html(468,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></td>
			<td>'.$Res->html(170,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(429,page::language()).'" alt="'.$Res->html(429,page::language()).'" border="0"></td>
			<td>'.$Res->html(164,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/action_go.gif" title="'.$Res->html(469,page::language()).'" alt="'.$Res->html(469,page::language()).'" border="0"></td>
			<td>'.$Res->html(469,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/action_notgo.gif" title="'.$Res->html(470,page::language()).'" alt="'.$Res->html(470,page::language()).'" border="0"></td>
			<td>'.$Res->html(470,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'" valign="top">
			<td><img src="/images/icons/arrow_in.png" title="'.$Res->html(214,page::language()).'" alt="'.$Res->html(214,page::language()).'" border="0"></td>
			<td>'.$Res->html(215,page::language()).'.</td>
		</tr>
	</table>
</div>
';

$out .= '
<script type="text/javascript">
	Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateSort});
</script>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');