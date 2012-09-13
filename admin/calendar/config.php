<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
$Meta->addJavascript('/admin/calendar/functions.js',true);
library::load('editor');
library::loadRelative('library');
$Module = new moduleCalendar();
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
$out .= '
<form name="contentIndex" method="post" action="config.php?id='.page::menuID().'&item='.$nCalID.'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(546,page::language()).'</a></td>
		<td class="cNavDisabled" width="150">'.$Res->html(212,page::language()).'</td>
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
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
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

$out .= '
<input type="hidden" value="1" name="viewType">
<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(329,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(803,page::language()).':
			<input type="hidden" value="'.$Config['viewType']['Value'].'" id="viewType" name="viewType">
		</td>
		<td valign="top">
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs1" src="images/calview1.gif" 
					onClick="javascript:chooseType(1);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(731,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs2" src="images/calview2.gif" 
					onClick="javascript:chooseType(2);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(732,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs5" src="images/calview5.gif" 
					onClick="javascript:chooseType(5);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(848,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs6" src="images/calview6.gif" 
					onClick="javascript:chooseType(6);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(1160,page::language()).'
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td width="120" valign="top">
			'.$Res->html(804,page::language()).':
		</td>
		<td valign="top">
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs3" src="images/calview3.gif" 
					onClick="javascript:chooseType(3);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(805,page::language()).'
				</div>
			</div>
			<div style="width:100%;overflow:auto;">
				<div style="width:40px;height:25px;float:left;margin:3px;">
					<img id="vs4" src="images/calview4.gif" 
					onClick="javascript:chooseType(4);"
					onMouseout="SetPointer(this.id,\'default\');"
					onMouseover="SetPointer(this.id,\'pointer\');">
				</div>
				<div style="width:300px;height:25px;line-height:25px;float:left;margin:3px;">
					'.$Res->html(806,page::language()).'
				</div>
			</div>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(563,page::language()).':
		</td>
		<td valign="top">
			<input type="radio" value="0"'.checkCheckbox(0,$Config['calendarStart']['Value']).' name="calendarStart"> '.$Res->html(564,page::language()).'<br>
			<input type="radio" value="1"'.checkCheckbox(1,$Config['calendarStart']['Value']).' name="calendarStart"> '.$Res->html(565,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(566,page::language()).':
		</td>
		<td valign="top">
			<input type="radio" value="0"'.checkCheckbox(0,$Config['showOldDates']['Value']).' name="showOldDates"> '.$Res->html(567,page::language()).'<br>
			<input type="radio" value="1"'.checkCheckbox(1,$Config['showOldDates']['Value']).' name="showOldDates"> '.$Res->html(568,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(850,page::language()).':
		</td>
		<td valign="top">
			<input type="checkbox" value="1"'.checkCheckbox(1,$Config['pdfPrint']['Value']).' name="pdfPrint"> '.$Res->html(851,page::language()).'
		</td>
	</tr>
	'.$Module->getRegisterHtml($Config).'
	<tr>
		<td colspan="2">
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

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');