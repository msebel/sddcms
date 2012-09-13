<?php
/**
 * Diese Klasse regelt den Zugriff auf Seiten.
 * Sie verarbeitet ausserdem Logins von Benutzern / Administratoren. 
 * Es wird automatisch ein Objekt $Access als Instanz für dieses 
 * Objekt generiert. Dieses ist im gesamten globalen Scope vorhanden.
 * @author Michael Sebel <michael@sebel.ch>
 */
class access {
	/**
	 * ID des eingeloggten Users, default = 0
	 * @var integer
	 */
	private $nLoginUser = 0;
	/**
	 * Gibt an ob Benutzer eingeloggt ist, default = false
	 * @var boolean
	 */
	private $login = false;
	/**
	 * Gibt den Code für den Zugriffstyp an, default = 0
	 * @var integer
	 */
	private $myAccess = 0;
	/**
	 * Spezieller Zugriff, nicht immer gegeben
	 * @var integer
	 */
	private $myControllerAccess = NULL;
	/**
	 * Referenz zur Datenbankklasse
	 * @var dbConn
	 */
	private $Conn = NULL;
	/**
	 * Gibt den Zugriffstyp Admin an
	 * @var integer
	 */
	const ACCESS_ADMIN = 1;
	/**
	 * Gibt den Zugriffstyp CUG an
	 * @var integer
	 */
	const ACCESS_CUG = 0;
	
	/**
	 * Access Objekt erstellen.
	 * @param dbConn Conn, Datenbankobjekt
	 */
	public function __construct(dbConn &$Conn) {
		// Eingeloggten User checken
		$this->Conn = $Conn;
		// Schauen ob menu diesem Mandanten gehört, sonst error
		$this->checkMandantAccess();
		if (isset($_SESSION['userid'])) {
			$this->nLoginUser = (int) $_SESSION['userid'];
			$this->login = true;
			$this->setAccess();
		}
	}
	
	/**
	 * Setzt den Zugriffscode.
	 * Wenn ein User eingeloggt ist, dann wird hier seine Zugangsart
	 * also CUG Benutzer oder Administrator gesetzt
	 */
	private function setAccess() {
		if ($this->login == true) {
			$sSQL = 'SELECT usr_Access FROM tbuser WHERE usr_ID = '.$this->nLoginUser;
			$this->myAccess = (int) $this->Conn->getFirstResult($sSQL);
		} 
	}
	
	/**
	 * Setzt zusätzliche Spezialrechte, wenn es sich um einen
	 * CUG Benutzer handelt.
	 */
	private function setControllerAccess() {
		// Eventuell durch Menurechte überschreiben, wenn CUG
		if ($this->myAccess == 0 && $this->login == true) {
			if (!isset($_SESSION['useraccess']['m'.page::menuID()])) {
				$sSQL = "SELECT uac_Type FROM tbuseraccess 
				WHERE usr_ID = ".$this->nLoginUser." AND mnu_ID = ".page::menuID();
				$this->myControllerAccess = getInt($this->Conn->getFirstResult($sSQL));
				$_SESSION['useraccess']['m'.page::menuID()] = $this->myControllerAccess;
			} else {
				$this->myControllerAccess = $_SESSION['useraccess']['m'.page::menuID()];
			}
		}
	}
	
	/**
	 * Gibt den Zugangscode zurück: 1 = Admin, 0 = CUG User.
	 * @return integer Zugangscode
	 */
	public function getAccessType() {
		return($this->myAccess);
	}
	
	/**
	 * Accesstype für den Controller holen, zuvor setzen
	 * der Spezialrechte die eventuell vorhanden sind
	 */
	public function getControllerAccessType() {
		$this->setControllerAccess();
		if ($this->myControllerAccess != NULL) {
			return($this->myControllerAccess);
		} else {
			return($this->myAccess);
		}
	}
	
	/**
	 * Login Zugriff checken.
	 * Leitet auf die Errorseite/noAccess weitern, 
	 * wenn kein genügender Zugriff vorhanden ist
	 * @param integer nMenuID, ID des zu checkenden Menus
	 */
	public function checkLoginAccess($nMenuID) {
		$sSQL = 'SELECT mnu_Secured FROM tbmenu WHERE mnu_ID = '.$nMenuID;
		$nSecured = $this->Conn->getFirstResult($sSQL);
		if ($nSecured == 1 && $this->login == false) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	/**
	 * Access auf einen Menupunkt testen.
	 * @param integer nMenuID, ID des zu checkenden Menus
	 * @return boolean True wenn der Zugriff auf das Menu gewährt ist
	 */
	public function checkAccess($nMenuID) {
		$Conn = $this->Conn;
		$bReturn = false;
		// Prüfen ob das Menu gesichert ist
		$sSQL = 'SELECT mnu_Secured FROM tbmenu WHERE mnu_ID = '.$nMenuID;
		$nSecurity = $Conn->getFirstResult($sSQL);
		// Wenn Security und login vorhanden, checken auf Zugriff
		if ($nSecurity == 1 && $this->login == true) {
			$sSQL = 'SELECT COUNT(tbaccess.acc_ID) AS Counter FROM tbmenu
			INNER JOIN tbaccess ON tbmenu.mnu_ID = tbaccess.mnu_ID 
			INNER JOIN tbusergroup ON tbaccess.ugr_ID = tbusergroup.ugr_ID 
			INNER JOIN tbuser_usergroup ON tbusergroup.ugr_ID = tbuser_usergroup.ugr_ID 
			INNER JOIN tbuser ON tbuser_usergroup.usr_ID = tbuser.usr_ID WHERE 
			tbuser.usr_ID = '.$this->nLoginUser.' AND tbmenu.mnu_ID = '.$nMenuID;
			$nResult = $Conn->getCountResult($sSQL);
			// Zugriff gewähren wenn mehr als 0 Zugriffe
			if ($nResult > 0) $bReturn = true;
		}
		// Wenn keine Security, Zugriff erlauben
		if ($nSecurity == 0) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Loginstatus zurückgeben: true = eingeloggt / false = nicht eingeloggt.
	 * @return boolean true/false ob eingeloggt oder nicht
	 */
	public function isLogin() {
		return($this->login);
	}
	
	/**
	 * Einen Benutzer einloggen (zumindest versuchen).
	 * Erstellt eine Instanz von dologin($Conn), welche
	 * das Login durchführt, diese Funktion gibt keine
	 * Nachricht / Variable zurück ob erfolg oder nicht
	 */
	public function logMeIn() {
		// Leitet auf Userstartseite weiter, wenn Login OK ist
		$doLogin = new dologin($this->Conn);
	}
	
	/**
	 * Einen Benutzer ausloggen.
	 * Nach dem Login wird auf /index.php weitergeleiten,
	 * welche automatisch die Startseite der Webseite aufruft
	 * @param string location, Redirect nach Logout, optional
	 * @param bool true/false ob der Redirect stattfinden soll
	 */
	public function logMeOut($location = '/index.php',$bRedirect = true) {
		unset($_SESSION['userid']);
		unset($_SESSION['useraccess']);
		$this->login = false;
		$this->nLoginUser = 0;
		// Auch allfällige Impersonations löschen
		foreach ($_SESSION['SessionConfig'] as $key => $value) {
			if (stringOps::endsWith($key,'_ImpersonationSecurity'))
				unset($_SESSION['SessionConfig'][$key]);
		}
		// Menu Session Objekte löschen
		unset($_SESSION['menuObjects']);
		unset($_SESSION['controller']);
		// Design ID neu laden
		$sSQL = "SELECT design_ID FROM tbpage WHERE page_ID = ".page::ID();
		$_SESSION['page']['design'] = $this->Conn->getFirstResult($sSQL);
		session_write_close();
		// Auf die Startseite gehen
		if ($bRedirect)	redirect('location: '.$location);
	}
	
	/**
	 * Die Access-Session kontrollieren. 
	 * Diese Funktion sollte zumindest in allen Admin Modulen genutzt werden, 
	 * da sonst ein unerlaubter Zugriff theoretisch möglich wird.
	 */
	public function control() {
		// Aufzurufende Menuid
		$nMenuID = page::menuID();
		// Fehler wenn im Controller die MenuID nicht vorhanden ist
		if (!isset($_SESSION['controller']['menu'.$nMenuID]) || !$this->login) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	/**
	 * Checken ob das Menu dem Mandanten gehört. 
	 * Es wird das aktuelle Menu in $_GET['id'] gelesen. Leitet auf die
	 * Seite error/invalidContent weiter, wenn das Menu nicht dem 
	 * aktuellen Mandanten gehört und somit nicht angezeigt werden darf.
	 */
	public function checkMandantAccess() {
		$nMenuID = page::menuID();
		if ($nMenuID > 0) {
			$sSQL = 'SELECT COUNT(mnu_ID) FROM tbmenu 
			WHERE mnu_ID = '.page::menuID().' AND man_ID = '.page::mandant();
			$nResult = $this->Conn->getCountResult($sSQL);
			if ($nResult != 1) {
				redirect('location: /error.php?type=invalidContent');
			}
		}
	}
}