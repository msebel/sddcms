<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleGuestbook();
$Module->loadObjects($Conn,$Res);

// Konfiguration initialisieren
$GBConfig = array();
pageConfig::get(page::menuID(),$Conn,$GBConfig);
// Schauen ob Konfig schon vorhanden. Wenn nicht, kommt
// eine Fehlermeldung, dass man das GB konfigurieren soll
$bError = false;
if (count($GBConfig) == 0) $bError = true;
// Prüfen ob Das Submit von der Vor-Seite vorhanden ist
if (!isset($_POST['cmdEntry']) && !isset($_POST['cmdSave'])) $bError = true;
// Prüfen ob Abbrechen gewählt
if (isset($_POST['cmdCancel'])) $bError = true;
// Prüfen ob die Session vorhanden ist
if (!isset($_SESSION['gb_'.page::menuID()])) $bError = true;
// Wenn Fehler, auf Index der ID weiterleiten
if ($bError == true) {
	if (!isset($_POST['cmdCancel'])) {
		$Module->setErrorSession($Res->html(348,page::language()));
		session_write_close();
	}
	redirect('location: index.php?id='.page::menuID());
}

// Ansonsten den Post erstellen
$sComContent = ''; $sComName = '';
if (isset($_POST['cmdSave'])) {
	$Module->saveEntry($GBConfig,$sComName,$sComContent);
}

// Gästebuch Session nutzen zum Zeit speichern
$_SESSION['gb_'.page::menuID()] = time();

// Formular zum erstellen des Eintrages anzeigen
$out .= '
<form action="new.php?id='.page::menuID().'" method="post">
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(360,page::language()).'</h1>
			<br>
			'.$Module->showErrorSession().'<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(85,page::language()).':
		</td>
		<td valign="top">
			<input type="text" style="width:70%;" maxlength="255" name="comName" value="'.$sComName.'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(361,page::language()).':
		</td>
		<td valign="top">
			<textarea style="width:80%;height:100px;" rows="5" name="comContent">'.$sComContent.'</textarea>
		</td>
	</tr>
	'.$Module->getCaptchaCode($GBConfig).'
	<tr>
		<td width="150" valign="top">
			<div style="display:block;visibility:hidden;">
				<input type="text" name="comAdditional" value="">
			</div>
		</td>
		<td valign="top">
			<input type="submit" class="cButton" name="cmdSave" value="'.$Res->html(362,page::language()).'"> 
			<input type="submit" class="cButton" name="cmdCancel" value="'.$Res->html(234,page::language()).'">
		</td>
	</tr>
</table>
</post>
';
// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');