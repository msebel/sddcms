<?php
$Conn = singleton::conn();
// Dies der Logging Klasse zuweisen, damit diese ab hier funktioniert
logging::$Conn = $Conn;

// Resourcen Objekt erstellen
$Res = singleton::resources();

// Sessionres Array erstellen, wenns noch nicht gibt
if (!isset($_SESSION['sessionres'])) {
	$_SESSION['sessionres'] = array();
}

// Mandant ändern, wenn erwünscht. Nur möglich, wenn entsprechender
// Querystring vorhanden und Page Session schon vorhanden ist
if (page::exists() && page::changeMandant()) {
	// Neue Mandanten ID prüfen
	$nManID = getInt($_GET['changeMandant']);
	// Gibts den Mandanten für die aktuelle Page?
	$sSQL = 'SELECT COUNT(man_ID) FROM tbmandant 
	WHERE page_ID = '.page::id().' AND man_ID = '.$nManID;
	$nResult = $Conn->getCountResult($sSQL);
	// Wenn nicht ok, standard nehmen
	if ($nResult != 1) $nManID = page::standardmandant();
	// Menu Session Objekte löschen
	unset($_SESSION['menuObjects']);
	// Mandanten ID speichern
	page::setMandant($nManID,$Conn);
	// Auf Startseite weiterleiten
	redirect('location: /index.php');
}

// Wenns die Page Session schon gibt, nichts mehr machen
if (!page::exists()) {
	// Schauen um welche Page es sich handelt
	$sDomain = $_SERVER['HTTP_HOST'];
	$Conn->escape($sDomain);
	// Domain speichern
	$_SESSION['page']['domain'] = $sDomain;
	$sSQL = 'SELECT dom_Mandant,page_ID,dom_Redirect
	FROM tbdomain WHERE dom_Name = \''.$sDomain.'\'';
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		$_SESSION['page']['mandant'] = $row['dom_Mandant'];
		$_SESSION['page']['id'] = $row['page_ID'];
		// Redirect auf andere Domain
		if ($row['dom_Redirect'] > 0) {
			$sSQL = 'SELECT dom_Name FROM tbdomain WHERE dom_ID = '.$row['dom_Redirect'];
			$sRedirect = $Conn->getFirstResult($sSQL);
			unset($_SESSION['page']);
			session_write_close();
			redirect('location: http://'.$sRedirect.$_SERVER['REQUEST_URI']);
		}
	}
	// Page ID nutzen um Page informationen zu sammeln
	$sSQL = 'SELECT page_Individual,page_Name,page_Mandant,
	design_ID,page_Admindesign FROM tbpage 
	WHERE page_ID = '.$_SESSION['page']['id'];
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		$_SESSION['page']['standardmandant'] = $row['page_Mandant'];
		$_SESSION['page']['individual'] = $row['page_Individual'];
		$_SESSION['page']['name'] = $row['page_Name'];
		$_SESSION['page']['design'] = $row['design_ID'];
		$_SESSION['page']['admindesign'] = $row['page_Admindesign'];
	}
	// Mandant ID Nutzen um Mandantinfos zu sammeln
	$sSQL = 'SELECT man_Start,ugr_AdminID,man_Language,
	man_Metadesc,man_Allwidth,man_Contentwidth,man_Verify,man_Title,
	tas_ID,man_Metakeys,man_Metaauthor,man_Inactive
	FROM tbmandant WHERE man_ID = '.$_SESSION['page']['mandant'];
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		$_SESSION['page']['start'] 			= $row['man_Start'];
		$_SESSION['page']['title'] 			= $row['man_Title'];
		$_SESSION['page']['admingroup'] 	= $row['ugr_AdminID'];
		$_SESSION['page']['language'] 		= $row['man_Language'];
		$_SESSION['page']['metadesc'] 		= $row['man_Metadesc'];
		$_SESSION['page']['metakeys'] 		= $row['man_Metakeys'];
		$_SESSION['page']['author']	  		= $row['man_Metaauthor'];
		$_SESSION['page']['verify']	  		= $row['man_Verify'];
		$_SESSION['page']['inactive']		= $row['man_Inactive'];
		$_SESSION['page']['allwidth']		= $row['man_Allwidth'];
		$_SESSION['page']['contentwidth']	= $row['man_Contentwidth'];
		$_SESSION['page']['teaserID']		= $row['tas_ID'];
		// Inaktivität speichern
		$nInactive = getInt($row['man_Inactive']);
		$sName = $row['man_Title'];
	}
	// Wenn Mandant Inaktiv, nichts mehr tun
	if ($nInactive > 0) {
		echo 'the page \''.$sName.'\' is not active...<br>';
		echo '...but still powered by sddCMS '.VERSION;
		unset($_SESSION['page']);
		session_write_close();
		exit();
	}
}

// Wenn keine ID da, Request URI suchen
if (getInt($_GET['id']) == 0) {
	$path = $_SERVER['REQUEST_URI'];
	// Wenn Fragezeichen, bis dahin, sonst ganzer Pfad
	$qmp = strripos($path, '?');
	if ($qmp !== false) {
		$path = substr($path, 1, ($qmp - 1));
	} else {
		$path = substr($path, 1);
	}
	// Wenn Pfad vorhanden, anwenden
  if (is_string($path)) {
  	// Suchen
    $sSQL = 'SELECT mnu_ID FROM tbmenu
    WHERE man_ID = '.page::mandant().' AND mnu_Path = :path';
    $stmt = $Conn->prepare($sSQL);
    $stmt->bind('path',$path,PDO::PARAM_STR);
    $stmt->select();
    // GET Parameter simulieren
    if ($result = $stmt->next()) {
      $_GET['id'] = getInt($result['mnu_ID']);
    }
  }
}

// Optionen laden
option::load($Conn);

// Accessobjekt erstellen
$Access = singleton::access();

// Ausloggen, wenn nötig
if (isset($_GET['logout']) && $Access->isLogin() == true) $Access->logMeOut();
// Einloggen, wenn nötig
if (isset($_POST['cmdLogin']) && $Access->isLogin() == false) $Access->logMeIn();

// Template Objekt erstellen
$tpl = singleton::template();
$Meta = singleton::meta();

// Including der Frameworks$
$param = '&f[c]&f[j]';
if ($Meta->prototypeEnabled)
	$param .= '&f[p]&f[s]';
$Meta->addJavascript('/resource/js/framework.php?v'.JS_FRAMEWORK.$param);

// Menu Klasse(n) einbinden und ausführen
require_once(BP.'/library/interface/menuInterface/menuInterface.php');
require_once(BP.'/library/class/defaultMenu/defaultMenu.php');

// Menuobjekt erzeugen und Menu füllen
$Menu = singleton::menu();

$Access->checkLoginAccess(page::menuID());
$tpl->aMenu($Menu->getMenu());

// Menuobjekt im Template zuweisen, damit es am Ende das Objekt hat
$tpl->setMenu($Menu);
// Teaser Klasse einbinden und ausführen
require_once(BP.'/library/class/core/teaser.php');

// Teaser laden und anzeigen wenn nicht Admin
if ($Access->getControllerAccessType() != 1 || isset($_GET['showTeaser'])) {
	$Teaser = new teaserParser($Conn,$Res,$tpl,$Menu->CurrentMenu);
}