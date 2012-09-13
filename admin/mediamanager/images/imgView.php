<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Klassen Importieren für Mediamanager
require_once(BP.'/library/class/mediaManager/mediaConst.php');		// Konfigurationskonstanten und Arrays
require_once(BP.'/library/class/mediaManager/mediaLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/class/mediaManager/mediaInstance.php');		// Erzeugt $mediaInstance Objekt
require_once(BP.'/library/class/mediaManager/fileLib.php');			// Allgemeine Mediafunktionen
require_once(BP.'/library/deprecated/content/content.php');				// Allgemeine Mediafunktionen

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

// Toolbar erstellen
$out = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(276,page::language()).'</td>
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
				<a href="/admin/mediamanager/index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;
			</div>
		</td>
	</tr>
</table>
<br>
<br>
';

// Titel anzeigen
$out .= '
<h1>'.$Res->html(276,page::language()).' - '.$mediaInstance->Progress.'</h1>
<br>
<br>
';

// Floating definieren
$sCSSFloat = '';
if (strlen($mediaInstance->getProperty('align')) > 0) {
	$sCSSFloat = 'style="float:'.$mediaInstance->getProperty('align').'"'; 
}
$sRandom = stringOps::getRandom(50);
// Bild anzeigen
if (fileLib::hasXLImage(BP.$sMediapath)) {
	// Mit vergrösserung
	$out .= '
	<a href="'.fileLib::getXLVersion($sMediapath).'" rel="lightbox" target="_blank" title="'.$mediaInstance->getProperty('desc').'">
	<img '.$sCSSFloat.' src="'.$sMediapath.'?'.$sRandom.'" border="0" alt="'.$mediaInstance->getProperty('desc').'" title="'.$mediaInstance->getProperty('desc').'"></a>
	<br>
	<br>
	';
} else {
	// Ohne vergrösserung
	$out .= '
	<img '.$sCSSFloat.' src="'.$sMediapath.'?'.$sRandom.'" border="0" alt="'.$mediaInstance->getProperty('desc').'" title="'.$mediaInstance->getProperty('desc').'">
	<br>
	<br>
	';
}

// Ans Template weitergeben
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');