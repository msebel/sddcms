<?php
/**
 * Teaser zur Anzeige des Warenkorbs
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopUserTeaser implements teaser {

	/**
	 * Ressourcen
	 * @var resources
	 */
	private $Res;
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Titel des Teasers
	 * @var string
	 */
	private $Title;
	/**
	 * Metadaten Objekt
	 * @var meta
	 */
	private $Meta;

	// Konstruieren
	public function __construct() {
		$this->Meta = singleton::meta();
		// Direkt ein Skript hinzufügen, welches den Korb aktualisiert
		$this->Meta->addJavascript('/modules/shop/js/cart.js');
	}

	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
		// Mit dem ClassLoader die nötigen Klassen laden
		require_once(BP.'/modules/shop/system.php');
	}

	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}

	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}

	// HTML Code einfüllen
	public function appendHtml(&$out) {
		// Controller, falls Postback oder Links gedrückt
		$this->control();
		// Prüfen was anzuzeigen ist
		if (shopStatic::isUserLogin()) {
			$this->showUserarea($out);
		} else {
			$this->showLogin($out);
		}
	}

	// Zeigt das Login Formular und Fehlermeldungen an
	private function showLogin(&$out) {
		$tPath = shopStatic::getTemplate('login-teaser-main');
		$tpl = new templateImproved($tPath);
		// Subtemplate für Login form
		$tPath = shopStatic::getTemplate('login-teaser-form');
		$form = new templateImproved($tPath);
		// POST URL erstellen
		$sUrl = $_SERVER['SCRIPT_NAME'].'?id='.page::menuID().'&shoplogin';
		$form->addData('POST_URL',$sUrl);
		// Fehlermeldung von vorherigen einloggen
		$sMessage = sessionConfig::get('LoginMessage', '');
		$form->addData('LOGIN_ERROR',$sMessage);
		sessionConfig::set('LoginMessage','');
		// Template hinzufügen und ausgeben
		$tpl->addSubtemplate('TEASER_CONTENT', $form);
		$out .= $tpl->output();
	}

	// Zeigt die User Area an
	private function showUserarea(&$out) {
		$tPath = shopStatic::getTemplate('login-teaser-main');
		$tpl = new templateImproved($tPath);
		// Subtemplate für Login form
		$tPath = shopStatic::getTemplate('login-teaser-user');
		$userarea = new templateImproved($tPath);
		// Variablen ersetzen
		$user = shopStatic::getLoginUser();
		$userarea->addData('USER_NAME',$this->getUsername());
		$userarea->addData('CURRENT_URL',$_SERVER['SCRIPT_NAME']);
		$userarea->addData('MENU_ID',page::menuID());
		// Dies in den Output spitzen
		$tpl->addSubtemplate('TEASER_CONTENT', $userarea);
		$out .= $tpl->output();
	}

	// Behandelt Login/Logout und macht Redirects
	private function control() {
		if (isset($_GET['shoplogin'])) {
			$sUser = stringOps::getPostEscaped('shopUsername', $this->Conn);
			$sPwd = stringOps::getPostEscaped('shopPassword', $this->Conn);
			$nMenu = shopConfig::LoginMenuID();
			if (!impersonation::login($sUser,$sPwd,$nMenu,$this->Conn,false)) {
				sessionConfig::set('LoginMessage', '<br>'.$this->Res->html(1101,page::language()));
			}
			// Auf aktuelle Seite ohne Action weiterleiten
			session_write_close();
			redirect('location: '.$_SERVER['SCRIPT_NAME'].'?id='.page::menuID());
		}
		if (isset($_GET['shoplogout'])) {
			impersonation::logout(singleton::access(),shopConfig::LoginMenuID());
			// Auf aktuelle Seite ohne Action weiterleiten
			session_write_close();
			redirect('location: /controller.php?id='.shopConfig::LoginMenuID());
		}
	}

	// Den Usernamen des eingeloggten Users holen
	private function getUsername() {
		$user = shopStatic::getLoginUser();
		$addr = $user->getPrimaryAddress();
		// Anonymer Standard User (Erstmal)
		$sName = $this->Res->html(1103,page::language());
		// Mit E-Mail initialisieren
		if ($addr instanceof shopAddress) {
			// E-Mail Adresse
			if (strlen($addr->getEmail()) > 0) {
				$sName = $addr->getEmail();
			}
			// Vorname allein (Wenn kein Nachname)
			if (strlen($addr->getFirstname()) > 0) {
				$sName = $addr->getFirstname();
			}
			// Vorname und Nachname versuchen
			if (strlen($addr->getFirstname()) > 0 && strlen($addr->getLastname()) > 0) {
				$sName = $addr->getFirstname().' '.$addr->getLastname();
			}
		}
		// Name zurückgeben fürs Template
		return($sName);
	}
}