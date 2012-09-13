<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/flashCode.php');		// Erzeugt $mediaInstance Objekt

$Meta->addJavascript('/scripts/system/mediamanager.js',true);		// Mediamanager Javascripts

// Zugriff testen und Fehler melden
$Access->control();
$tpl->setPopup();

// Medieninstanz erstellen
$mediaInstance = new mediaInstance($Conn);

// Gesamter Pfad
$sMediapath = $mediaInstance->getProperty('path').$mediaInstance->Progress;

// Gibts Zugriff auf das Element?
$bAccess = mediaLib::checkElementAccess($Conn,$mediaInstance);
$nElementID = $mediaInstance->Element;
if ($bAccess == false) {
	redirect('location: /error.php?type=noAccess');
}

// FLV Daten speichern
if (isset($_POST['cmdSave'])) {
	$sSkin = stringOps::getPostEscaped('skin',$Conn);
	$mediaInstance->setProperty('skin',$sSkin);
	// Erfolg melden und zurück
	mediaLib::updateElementData($Conn,$Res,$mediaInstance,'audio/audioSkin.php');
}
// Skinvar setzen zum prüfen der Checkboxen
$sSkin = $mediaInstance->getProperty('skin');

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($mediaInstance->hasErrorSession() == true) {
	$sMessage = $mediaInstance->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="audioEdit.php?id='.page::menuID().'">'.$Res->html(310,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(299,page::language()).'</td>
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
				<a href="#" onClick="document.audioform.submit();">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="/admin/mediamanager/index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>
<br>
';


$out .= '
<form name="audioform" method="post" action="audioSkin.php?id='.page::menuID().'">
<input type="hidden" name="cmdSave" value="1">
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td>
			<h1>'.$Res->html(305,page::language()).'</h1><br>
		</td>
	</tr>
</table>
<br>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'standard','none',$out);
		$out .= '
		<input type="radio" name="skin" value="standard" style="margin-top:6px"'.checkCheckbox('standard',$sSkin).'> Standard Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'light','none',$out);
		$out .= '
		<input type="radio" name="skin" value="light" style="margin-top:6px"'.checkCheckbox('light',$sSkin).'> Light Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'pink2','none',$out);
		$out .= '
		<input type="radio" name="skin" value="pink2" style="margin-top:6px"'.checkCheckbox('pink2',$sSkin).'> Creamy Pink Skin<br><br><br>
		</td>
	</tr>
	<tr>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'pink','none',$out);
		$out .= '
		<input type="radio" name="skin" value="pink" style="margin-top:6px"'.checkCheckbox('pink',$sSkin).'> Outrageous Pink Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'lightblue','none',$out);
		$out .= '
		<input type="radio" name="skin" value="lightblue" style="margin-top:6px"'.checkCheckbox('lightblue',$sSkin).'> Lightblue Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'dark','none',$out);
		$out .= '
		<input type="radio" name="skin" value="dark" style="margin-top:6px"'.checkCheckbox('dark',$sSkin).'> Dark Skin<br><br><br>
		</td>
	</tr>
	<tr>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'silver','none',$out);
		$out .= '
		<input type="radio" name="skin" value="silver" style="margin-top:6px"'.checkCheckbox('silver',$sSkin).'> Silver Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'green','none',$out);
		$out .= '
		<input type="radio" name="skin" value="green" style="margin-top:6px"'.checkCheckbox('green',$sSkin).'> Lightgreen Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'yellow','none',$out);
		$out .= '
		<input type="radio" name="skin" value="yellow" style="margin-top:6px"'.checkCheckbox('yellow',$sSkin).'> Yellow Skin<br><br><br>
		</td>
	</tr>
	<tr>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'darkblue','none',$out);
		$out .= '
		<input type="radio" name="skin" value="darkblue" style="margin-top:6px"'.checkCheckbox('darkblue',$sSkin).'> Darkblue Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'darkred','none',$out);
		$out .= '
		<input type="radio" name="skin" value="darkred" style="margin-top:6px"'.checkCheckbox('darkred',$sSkin).'> Darkred Skin<br><br><br>
		</td>
		<td style="text-align:center">';
		flashCode::getMp3PlayerCode('/scripts/flvplayer/music.mp3',200,'darkgreen','none',$out);
		$out .= '
		<input type="radio" name="skin" value="darkgreen" style="margin-top:6px"'.checkCheckbox('darkgreen',$sSkin).'> Darkgreen Skin<br><br><br>
		</td>
	</tr>
</table>
</form>
';

// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');