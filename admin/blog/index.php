<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleBlog();
$Module->loadObjects($Conn,$Res);
// Objekt der Blogkategorien
library::loadRelative('categories/library');
$Categories = new blogCategory($Conn);
$Module->setCategoryObject($Categories);
// Objekt für Tooltip mit Infos
$Tooltip = htmlControl::tooltip();
$Tooltip->setTimeout(750);
// Kategorieneinschränkung
$nSearchCategory = sessionConfig::get('SearchCategory',0);
// Daten verändern
if (isset($_GET['add'])) $Module->addBlogentry();
if (isset($_GET['save'])) $Module->saveBlogentries();
if (isset($_GET['delete'])) $Module->deleteBlogentry();
if (isset($_GET['search'])) $Module->setSearchCategory();

// Daten laden
$Data = array();
$PagingHTML = $Module->loadBlogentries($Data);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar und Einleitung
$out .= '
<form name="contentIndex" method="post" action="index.php?id='.page::menuID().'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(600,page::language()).'</td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'&add">
				<img src="/images/icons/vcard_edit.png" alt="'.$Res->html(599,page::language()).'" title="'.$Res->html(599,page::language()).'" border="0">
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
$TabRow = new tabRowExtender();
// Kopfbereich, Listendefinition
$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td width="200"><h1>'.$Res->html(600,page::language()).'</h1></td>
		<td align="right">'.$Module->getCategorySearch($nSearchCategory).'</td>
	</tr>
</table>
	'.$PagingHTML.'
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:16px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="width:30px;float:left;">&nbsp;</div>
		<div style="float:left;" class="adminBuffer"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:90px;float:left;"><strong>'.$Res->html(365,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Daten anzeigen
$nCount = 0;
foreach ($Data as $Entry) {
	$nCount++;
	$sInfoPic = $Module->addTooltip($Tooltip,$Entry,$nCount);
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:16px;float:left;">
			<a href="edit.php?id='.page::menuID().'&entry='.$Entry['con_ID'].'">
			<img src="/images/icons/bullet_wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'" border="0"></a>
		</div>
		<div style="width:16px;float:left;">
			<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&entry='.$Entry['con_ID'].'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$Res->javascript(169,page::language()).'\',950,700);">
			<img src="/images/icons/bullet_magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$Entry['con_ID'].'\',\''.addslashes($Entry['con_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'" border="0"></a>
		</div>
		<div style="width:30px;float:left;">
			<img src="/images/icons/'.$sInfoPic.'" id="tooltipWin_'.$nCount.'">
		</div>
		<div style="float:left;" class="adminBuffer">
			<input type="text" name="title[]" value="'.$Entry['con_Title'].'" class="adminBufferInput">
		</div>
		<div style="width:90px;float:left;">
			<input type="text" disabled="disabled" value="'.$Entry['con_Date'].'" style="width:80px;">
		</div>
		<div style="width:30px;float:left;text-align:center">
			<div name="divActive">
			<input type="hidden" name="id[]" value="'.$Entry['con_ID'].'">
			<input type="checkbox" name="active_'.$nCount.'" value="1"'.checkCheckbox(1,$Entry['con_Active']).'>
			</div>
		</div>
	</div>
	';
}

// Wenn keine Daten
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(483,page::language()).' ...</div>
	</div>
	';
}
// Formular schliessen
$out .= '</div>
'.$PagingHTML.'
</form>';
// Formular für Suche
$out .= '
<form name="SearchCategoryForm" method="post" action="index.php?id='.page::menuID().'&search">
	<input type="hidden" value="" name="SearchCategoryID" id="SearchCategoryID">
	<script type="text/javascript">
		function SearchCategory() {
			var id = document.getElementById("SearchCategorySelect").value;
			document.getElementById("SearchCategoryID").value = id;
			document.forms.SearchCategoryForm.submit();
		}
	</script>
</form>
';

// Tooltip JS einfüllen
$out .= $Tooltip->initialize();
for ($i = $nCount;$i > 0;$i--) {
	$out .= $Tooltip->get('tooltipWin_'.$i);
}

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
			<td><img src="/images/icons/vcard_edit.png" title="'.$Res->html(599,page::language()).'" alt="'.$Res->html(599,page::language()).'"></td>
			<td>'.$Res->html(608,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/information.png" title="'.$Res->html(602,page::language()).'" alt="'.$Res->html(602,page::language()).'"></td>
			<td>'.$Res->html(609,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/icon_alert.gif" title="'.$Res->html(603,page::language()).'" alt="'.$Res->html(603,page::language()).'"></td>
			<td>'.$Res->html(610,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" title="'.$Res->html(156,page::language()).'" alt="'.$Res->html(156,page::language()).'"></td>
			<td>'.$Res->html(611,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/magnifier.png" title="'.$Res->html(169,page::language()).'" alt="'.$Res->html(169,page::language()).'"></td>
			<td>'.$Res->html(612,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/delete.png" title="'.$Res->html(157,page::language()).'" alt="'.$Res->html(157,page::language()).'"></td>
			<td>'.$Res->html(613,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');