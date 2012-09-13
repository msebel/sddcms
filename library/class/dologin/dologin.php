<?php
/**
 * Einen Loginversuch starten.
 * @author Michael Sebel <michael@sebel.ch>
 */
class dologin {
	/**
	 * Benutzername der einzuloggen ist
	 * @var string
	 */
	private $Username;
	/**
	 * Gegebenes Passwort zum einloggen
	 * @var string
	 */
	private $Password;
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	
	/**
	 * Erstellt das Objekt und versucht ein Login.
	 * Als Parameter nimmt die Klasse indirekt die
	 * $_POST Variablen 'username' und 'password'
	 * @param dbConn Conn, Datenbankobjekt
	 */
	public function __construct(dbConn &$Conn) {
		$this->Conn = $Conn;
		$this->Username = $_POST['username'];
		$this->Password = $_POST['password'];
		$bCheck = $this->checkCredentials();
		// Login machen wenn möglich
		if ($bCheck == true) {
			// Security Hash zusammenbauen
			if (getInt($_POST['LeavePasswordUnencrypted']) == 0) {
				$sSecurity = $this->getSecurityHash();
			} else {
				$sSecurity = $this->Password;
			}
			// Login ausführen
			$this->doLogin($sSecurity);
		}
	}
	
	/**
	 * Login ausführen.
	 * - Prüft zuerst, ob der Benutzer eingeloggt werden kann
	 * - Danach wir die Startseite des Users oder fallback der Webseite genommen
	 * - Es findet ein Redirect zur gefundenen Seite statt wenn eingeloggt
	 * Ist das login nicht erfolgreich, werden keine Sessions gesetzt und
	 * die Seite wird auf keine Fehlerseite weiterleiten. Bei erfolgreichem
	 * Login werden die Menuobjekte und die Kontrollsession gelöscht.
	 * @param string sSecurity, Security String zum einloggen, per User eindeutig
	 */
	public function doLogin($sSecurity,$bRedirect = true) {
		$sSQL = "SELECT usr_ID,usr_Start,usr_Access FROM tbuser WHERE 
		man_ID = ".page::mandant()." AND usr_Security = '$sSecurity'";
		$Res = $this->Conn->execute($sSQL);
		$nResults = 0;
		$nStart = 0;
		$bLoginOk = false;
		while ($row = $this->Conn->next($Res)) {
			$nResults++;
			if ($nResults == 1) {
				// Login darf stattfinden
				$_SESSION['userid'] = (int) $row['usr_ID'];
				$bLoginOk = true;
				// Startseite definieren
				$nStart = $row['usr_Start'];
				// Prüfen ob die Seite existiert, sonst Startseite
				if ($nStart == NULL || !menuExists($nStart,$this->Conn)) {
					$nStart = page::start();
					$nStart = page::start();
				}
				// Admindesign, wenn vorhanden
				if (page::admindesign() > 0 && $row['usr_Access'] == 1) {
					$_SESSION['page']['design'] = page::admindesign();
				}
			} else {
				// Security scheint nicht unique, login verbieten
				unset($_SESSION['userid']);
				$bLoginOk = false;
				logging::error('login failed: non unique secure-key!');
			}
		}
		// Zur startseite des Users / der Seite weiterleiten
		// aber nur wenn das Login Ok war
		if ($bLoginOk == true) {
			// Menu Session Objekte löschen
			unset($_SESSION['menuObjects']);
			unset($_SESSION['controller']);
			session_write_close();
			// Nur weiterleiten, wenn so erwünscht
			if ($bRedirect) {
				redirect('location: /controller.php?id='.$nStart);
			}
		} else {
			logging::info('login failed');
		}
		// Wenn wir bis hier sind, den Status zurück geben
		return($bLoginOk);
	}
	
	// Benutzereingaben validieren
	private function checkCredentials() {
		$bCheck = false;
		// Beides muss min. 4 Zeichen sein
		if (strlen($this->Username) >= 4) {
			if (strlen($this->Password) >= 4) {
				$bCheck = true;
			}
		}
		return ($bCheck);
	}
	
	/**
	 * Security Hash erstellen, ist Unique innerhalb des Mandanten
	 * Wird zusammengebaut aus:
	 * 32 Zeichen = usr_Alias md5 hash +
	 * 32 Zeichen = Password md5 hash +
	 * 16 Zeichen = Zeichen 10,16 von 
	 * Zeichen 1,3 von usr_Alias, md5 hash
	 * = 80 Zeichen Security Hash
	 * @return string Generierter Security String aus lokalen User/Passwort
	 */
	private function getSecurityHash() {
		return(secureString::getSecurityString($this->Password,$this->Username));
	}
}