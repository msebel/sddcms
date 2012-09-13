<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
$Meta->addJavascript('/admin/links/functions.js',true);
$Meta->addJavascript('/scripts/system/ajax.js',true);
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleLink();
$Module->loadObjects($Conn,$Res);

// Konfiguration laden
$Config = array();
$nMenuID = page::menuID();
pageConfig::get($nMenuID,$Conn,$Config);

// Zeugs machen
if (isset($_GET['save'])) $Module->saveConfig($Config);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<form name="contentIndex" method="post" action="config.php?id='.page::menuID().'&link='.$nLink.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="140">
			<a href="index.php?id='.page::menuID().'">'.$Res->html(509,page::language()).'</a>
		</td>
		<td class="cNav" width="140">
			<a href="categories.php?id='.page::menuID().'">'.$Res->html(620,page::language()).'</a>
		</td>
		<td class="cNavSelected" width="140">'.$Res->html(329,page::language()).'</td>
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
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(329,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(527,page::language()).':
			<input type="hidden" value="'.$Config['viewType']['Value'].'" id="viewType" name="viewType">
		</td>
		<td valign="top">
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs1" src="images/linkview1.gif" 
					onClick="javascript:chooseType(1);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(528,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs2" src="images/linkview2.gif" 
					onClick="javascript:chooseType(2);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(529,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs3" src="images/linkview3.gif" 
					onClick="javascript:chooseType(3);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(530,page::language()).'
				</div>
			</div>
		</td>
	</tr>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(531,page::language()).':
		</td>
		<td valign="top">
			-> <a href="#" onClick="'.ajaxRequest::response('ajax/orderAlphabetic.php?id='.page::menuID(),'alphabeticOrderResult').'">'.$Res->html(532,page::language()).'</a> 
			<span id="alphabeticOrderResult">&nbsp;</span>
			<br>
			<br>
			-> <a href="#" onClick="'.ajaxRequest::response('ajax/orderAlphabeticCategories.php?id='.page::menuID(),'alphabeticOrderCategoriesResult').'">'.$Res->html(1158,page::language()).'</a> 
			<span id="alphabeticOrderCategoriesResult">&nbsp;</span>
			<br>
			<br>
			-> <a href="#" onClick="'.ajaxRequest::response('ajax/resetLinks.php?id='.page::menuID(),'resetLinkResult').'">'.$Res->html(533,page::language()).'</a> 
			<span id="resetLinkResult">&nbsp;</span>
		</td>
	<tr>
		<td colspan="2">
			<br>
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
';

// Event hinzufügen um den aktuellen viewType anzuzeigen
$out .= '
<script type="text/javascript">
	addEvent(window,\'load\',function(){chooseType('.$Config['viewType']['Value'].')},false);
</script>
';

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
		<td width="120" valign="top"><em>'.$Res->html(527,page::language()).'</em></td>
		<td valign="top">'.$Res->html(534,page::language()).'</td>
	</tr>
	</table>
</div>
';


// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');