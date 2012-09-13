<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('editor');
library::loadRelative('library');
$Module = new moduleGallery();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();
// Konfiguration initialisieren
$Config = array();
$Module->initConfig($Config);

// Operationen durchführen
if (isset($_GET['save'])) $Module->saveConfig($Config);
if (isset($_POST['thumbs'])) $Module->makeThumbs($Res);
if (isset($_POST['resize'])) $Module->resizeImages($Res);
// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<script type="text/javascript">
	function submitSave() {
		document.galleryIndex.action = \'index.php?id='.page::menuID().'&save\';
		document.galleryIndex.submit();
	}
</script>
<form name="galleryIndex" method="post" action="index.php?id='.page::menuID().'">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(386,page::language()).'</td>
		<td class="cNav" width="150"><a href="text.php?id='.page::menuID().'">'.$Res->html(747,page::language()).'</a></td>
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
				<a href="#" onClick="submitSave()">
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
				<a href="#" onClick="openWindow(\'/controller.php?id='.page::menuID().'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser&cmspreview\',\''.$Res->javascript(169,page::language()).'\',950,700)">
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
';

// Bilder Upload
$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(400,page::language()).'</h1><br>
			<a href="/admin/mediamanager/index.php?id='.page::menuID().'&element='.galleryConst::getMenuElement($Conn).'" target="_blank">
			<img style="border:0px none;float:left;margin-right:20px;" src="images/uploadButton'.page::language().'.gif" alt="'.$Res->html(400,page::language()).'" title="'.$Res->html(400,page::language()).'"></a>
			<br>
			'.$Res->html(387,page::language()).' 
			<a href="/admin/mediamanager/index.php?id='.page::menuID().'&element='.galleryConst::getMenuElement($Conn).'" target="_blank">'.$Res->html(388,page::language()).'</a> 
			'.$Res->html(389,page::language()).'.
		</td>
	</tr>
</table>
';

// Null bei höhe/breite leeren für Darstellung
if ($Config['thumbWidth']['Value'] == 0)
	$Config['thumbWidth']['Value'] = '';
if ($Config['thumbHeight']['Value'] == 0)
	$Config['thumbHeight']['Value'] = '';


// Bilder bearbeiten
$out .= '
<br>
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(390,page::language()).'</h1><br>
			<strong>'.$Res->html(391,page::language()).'</strong>
			'.$Res->html(392,page::language()).'
			<div style="margin-top:10px;margin-bottom:10px;width:100%;vertical-align:middle;">
				'.$Res->html(394,page::language()).' 
				<input type="text" style="width:50px;" maxlength="4" name="pictureWidth" value="800"> 
				'.$Res->html(395,page::language()).' 
				<input type="text" style="width:50px;" maxlength="4" name="pictureHeight" value=""> 
				<input type="submit" class="cButton" name="resize" value="'.$Res->html(391,page::language()).'"> 
			</div>
			<br>
			<strong>'.$Res->html(393,page::language()).'</strong>
			'.$Res->html(392,page::language()).'
			<div style="margin-top:10px;margin-bottom:10px;width:100%;vertical-align:middle;">
				'.$Res->html(394,page::language()).' 
				<input type="text" style="width:50px;" maxlength="4" name="thumbWidth" value="'.$Config['thumbWidth']['Value'].'"> 
				'.$Res->html(395,page::language()).' 
				<input type="text" style="width:50px;" maxlength="4" name="thumbHeight" value="'.$Config['thumbHeight']['Value'].'">
				<input type="submit" class="cButton" name="thumbs" value="'.$Res->html(393,page::language()).'"> 
			</div>
		</td>
	</tr>
</table>
';

// Konfiguration
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(329,page::language()).'</h1><br>
			<input type="hidden" name="mode" value="1">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(669,page::language()).':
		</td>
		<td>
			<input type="radio" name="mode" value="1" '.checkCheckbox(1,$Config['mode']['Value']).'> '.$Res->html(670,page::language()).'<br>
			<input type="radio" name="mode" value="2" '.checkCheckbox(2,$Config['mode']['Value']).'> '.$Res->html(671,page::language()).'<br>
			<input type="radio" name="mode" value="3" '.checkCheckbox(3,$Config['mode']['Value']).'> '.$Res->html(672,page::language()).'
		</td>
	</tr>
	<tr>
		<td colspan="2">
			'.$Res->html(396,page::language()).':<br>
			<br>
			'.editor::getSized('Config','htmlCode',page::language(),$Config['htmlCode']['Value'],'100%','250').'
		</td>
	</tr>
</table>
</form>
';


// Hilfetexte
$TabRow = new tabRowExtender();

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(393,page::language()).'</em></td>
		<td>'.$Res->html(397,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(391,page::language()).'</em></td>
		<td>'.$Res->html(398,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="180" valign="top"><em>'.$Res->html(400,page::language()).'</em></td>
		<td>'.$Res->html(399,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');