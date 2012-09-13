<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
// Datenbank / Zugriff / Errors
require_once(BP.'/system.php');

$Access = new access($Conn);

// Aufzurufende Menu ID holen
$nMenuID = getInt(page::menuID());
// Wenn nichts, Error 404 weiterleiten
if ($nMenuID == 0) {
	redirect('location: /error.php?type=FileNotFound');
}
// Zugriff testen
$hasAccess = $Access->checkAccess($nMenuID);

// Wenn Zugriff:
if ($hasAccess == true) {
	// Spezialmenupunkte abhandeln ... 
	// Externe Weiterleitung
	if (singleton::currentmenu()->Type == menuTypes::LINK_EXTERNAL) {
		$sSQL = 'SELECT mnu_External FROM tbmenu WHERE mnu_ID = '.$nMenuID;
		$sRedirect = $Conn->getFirstResult($sSQL);
		redirect('location: '.$sRedirect);
	}
	// Interne weiterleitung
	if (singleton::currentmenu()->Type == menuTypes::LINK_INTERNAL) {
		$sSQL = 'SELECT mnu_Redirect FROM tbmenu WHERE mnu_ID = '.$nMenuID;
		$nRedirect = $Conn->getFirstResult($sSQL);
		if ($nRedirect != $nMenuID) {
			$menu = menuObject::getInstance($nRedirect);
			redirect('location: '.$menu->getLink());
		} else {
			redirect('location: /error.php?type=redirector');
		}
	}
	// Pfade laden
	$sViewPath = '/index.php';
	$sAdminPath = '/index.php';
	$sClassName = '';
	loadPaths($sViewPath,$sAdminPath,$Conn,singleton::currentmenu()->Type,$sClassName);
	// Entscheiden welcher Pfad
	$nAccess = $Access->getControllerAccessType();
	$_SESSION['controller']['menu'.$nMenuID] = true;
	// Pfad aufrufen (Oder immer view, wenn es ein Preview ist
	if ($nAccess == 1 && !isset($_GET['cmspreview'])) {
		// Admin Teil
		redirect('location: '.$sAdminPath.'?id='.$nMenuID);
	} else {
		// Normale Ansicht, Klasse oder Redirect?
		if (strlen($sClassName) > 0) {
			require_once(BP.$sViewPath);
			$view = new $sClassName($tpl);
			$tpl->aC($view->getModule());
			require_once(BP.'/cleaner.php');
		} else {
			redirect('location: '.$sViewPath.'?id='.$nMenuID);
		}
	}
} else {
	// Fehlermeldung
	redirect('location: /error.php?type=controller');
}