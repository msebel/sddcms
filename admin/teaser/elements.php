<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse / Javascript
library::load('teaser');
$Module = new moduleTeaser();
$Module->loadObjects($Conn,$Res);
$Meta->addJavascript('/admin/teaser/teaser.js',true);
$Meta->addJavascript('/scripts/system/formAdmin.js',true);

// Teaser prüfen
$nTeaserID = getInt($_GET['teaser']);
$Module->checkSectionAccess($nTeaserID);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveTeaserElements($nTeaserID);
if (isset($_GET['add'])) $Module->addTeaserElement($nTeaserID);
if (isset($_GET['delete'])) $Module->deleteTeaserElement($nTeaserID);
if (isset($_GET['deleteimported'])) $Module->deleteImportedTeaserElement($nTeaserID);
if (isset($_GET['import'])) $Module->importTeaserElement($nTeaserID);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Daten laden
$sData = array();
$Module->loadTeaserElements($sData,$nTeaserID);

// Toolbar erstellen
$out = '
<form name="teaserIndex" method="post" action="elements.php?id='.page::menuID().'&teaser='.$nTeaserID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(409,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(410,page::language()).'</td>
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
				<a href="#" onClick="document.teaserIndex.submit()">
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
				<a href="elements.php?id='.page::menuID().'&teaser='.$nTeaserID.'&add">
				<img src="/images/icons/page_add.png" alt="'.$Res->html(426,page::language()).'" title="'.$Res->html(426,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/basket_put.png" id="windowImport" alt="'.$Res->html(777,page::language()).'" title="'.$Res->html(777,page::language()).'">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&teaser='.$nTeaserID.'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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
			<h1>'.$Res->html(414,page::language()).'</h1><br>
		</td>
	</tr>
</table>
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:20px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="width:170px;float:left;"><strong>'.$Res->html(427,page::language()).'</strong></div>
		<div style="width:170px;float:left;"><strong>'.$Res->html(428,page::language()).'</strong></div>
		<div style="width:40px;float:left;text-align:center;"><strong>'.$Res->html(160,page::language()).'</strong></div>
		<div style="width:50px;float:left;">&nbsp;</div>
	</div>

	<div id="contentTable">
';

// Einträge anzeigen
$nCount = 0;
foreach ($sData as $row) {
	$nCount++;
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:20px;float:left;">
			'.$Module->getEditable($row,$nTeaserID,$row['tsa_Imported']).'
		</div>	
		<div style="width:30px;float:left;">
			'.$Module->getTeaserDeleteHtml($row).'
		</div>
		<div style="width:170px;float:left;">
			<input type="text" style="width:160px;" name="title[]" value="'.$row['tap_Title'].'">
		</div>
		<div style="width:170px;float:left;">
			<select name="type[]" style="width:160px;">
				'.$Module->getTypeOptions($row['tty_ID']).'
			</select>
		</div>
		<div style="width:40px;float:left;text-align:center;">
			<input type="checkbox" value="1" name="active_'.$row['tap_ID'].'"'.checkCheckBox(1,$row['tsa_Active']).'>
		</div>
		<div style="width:50px;float:left;">
			<input type="hidden" name="sort[]" value="'.$nCount.'">
			<input type="hidden" name="id[]" value="'.$row['tap_ID'].'">
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
		<div style="width:480px;padding:3px;">'.$Res->html(415,page::language()).' ...</div>
	</div>
	';
}
// Ende der Content Tabelle
$out .= '
</div>';

// Flying Window für import von Teaserelementen
$window = htmlControl::window();
$Title = $Res->html(777,page::language());
$HTML = $Module->getImportWindowHtml();
$window->add('windowImport',$HTML,$Title,410,340);
$out .= $window->get('windowImport');

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
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></td>
			<td>'.$Res->html(430,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(423,page::language()).'" alt="'.$Res->html(423,page::language()).'" border="0"></td>
			<td>'.$Res->html(431,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench_disabled.png" title="'.$Res->html(424,page::language()).'" alt="'.$Res->html(424,page::language()).'" border="0"></td>
			<td>'.$Res->html(432,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(429,page::language()).'" alt="'.$Res->html(429,page::language()).'" border="0"></td>
			<td>'.$Res->html(433,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/arrow_in.png" title="'.$Res->html(214,page::language()).'" alt="'.$Res->html(214,page::language()).'" border="0"></td>
			<td>'.$Res->html(434,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/basket_put.png" title="'.$Res->html(777,page::language()).'" alt="'.$Res->html(777,page::language()).'" border="0"></td>
			<td>'.$Res->html(778,page::language()).'.</td>
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