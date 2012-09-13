<?php
/**
 * Impersonation Klasse.
 * Handelt adminfunktionen, add,remove,alter
 * und das login über den impersonation user
 * @author Michael Sebel <michael@sebel.ch>
 */
class impersonation {
	
	/**
	 * Erstellt einen neuen Impersonierungs User
	 * @param string user, Name des Benutzers
	 * @param string password, Passwort des Benutzers
	 * @param string original, Impersonierter Benutzer
	 * @param dbConn Conn, Datenbankverbindung
	 * @return int, Gibt die ID des erstellten Users (oder 0 wenn schon existent)
	 */
	public static function addUser($user,$password,$original,dbConn &$Conn) {
		// Security String erstellen
		$sSecurity = secureString::getSecurityString($password,$user);
		// Daten validieren
		$nImpID = 0;
		$original = getInt($original);
		$Conn->escape($user);
		// Neuen User erstellen, wenn noch keiner Vorhanden
		if (!self::exists($sSecurity,$Conn)) {
			$sSQL = " INSERT INTO tbimpersonation (usr_ID,man_ID,imp_Access,imp_Alias,
			imp_Security,imp_Active,imp_Email,imp_Activation)
			VALUES ($original,".page::mandant().",0,'$user','$sSecurity',0,'','')";
			$nImpID = $Conn->insert($sSQL);
		}
		return($nImpID);
	}
	
	/**
	 * Stellt eine Verbindung zwischen Impersonierung und Menu her
	 * @param int impID, Impersonierung der Verbindung
	 * @param int menuID, Menu der Verbindung
	 * @param dbConn Conn, Datenbankverbindung
	 * @return int, Verbindungskennung, wenn Datensatz erstellt wurde
	 */
	public static function addConnection($impID,$menuID,dbConn &$Conn) {
		$nMniID = 0;
		$menuID = getInt($menuID);
		$impID = getInt($impID);
		$sSQL = "SELECT COUNT(mni_ID) FROM tbmenu_impersonation
		WHERE mnu_ID = $menuID AND imp_ID = $impID";
		if ($Conn->getCountResult($sSQL) == 0) {
			$sSQL = "INSERT INTO tbmenu_impersonation
			(mnu_ID,imp_ID) VALUES ($menuID,$impID)";
			$nMniID = $Conn->insert($sSQL);
		}
		return($nMniID);
	}
	
	/**
	 * Löscht einen Impersionierungs User
	 * @param string security, Eindeutiger Security String
	 * @param dbConn Conn, Datenbankverbindung
	 * @return boolean, ob das Löschen klappte oder nicht
	 */
	public static function removeUser($security,dbConn &$Conn) {
		// User suchen anhand der Security
		$nImpID = self::getIdBySecurity($security,$Conn);
		// Wenn etwas gefunden wurde, Daten löschen
		if ($nImpID > 0) {
			// Verbindungen löschen
			$Conn->command("DELETE FROM tbmenu_impersonation WHERE imp_ID = $nImpID");
			// User selbst löschen
			$Conn->command("DELETE FROM tbimpersonation WHERE imp_ID = $nImpID");
			// Erfolg melden
			return(true);
		}
		// Wenn wir bis hierher kommen, wurde nichts gelöscht
		return(false);
	}
	
	/**
	 * Ändert die Daten eines Users anhand der gegebenen Felder
	 * @param string security, Eindeutiger Security String
	 * @param string fields, Felder Array
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public static function changeUser($security,$fields,dbConn &$Conn) {
		$nImpID = self::getIdBySecurity($security,$Conn);
		if ($nImpID > 0) {
			$sSQL = "UPDATE tbimpersonation SET ";
			foreach ($fields as $key => $value) {
				$sSQL .= "$key = $value,";
			}
			// Letztes Komma entfernen
			$sSQL = substr($sSQL,0,strlen($sSQL)-1).' ';
			$sSQL.= "WHERE imp_ID = $nImpID";
			$Conn->command($sSQL);
		}
	}
	
	/**
	 * Ein Update Feld in eine Collection laden.
	 * Achtung: Wird imp_Alias geupdated, muss auch
	 * der imp_Security String geupdated werden
	 * @param array collection, Collection die befüllt wird
	 * @param string field, Feldname für die Collection
	 * @param mixed value, Feldinhalt für die Collections
	 * @param dbConn Conn,
	 */
	public static function addField(&$collection,$field,$value,dbConn &$Conn) {
		// Felddaten validieren
		switch ($field) {
			case 'usr_ID':
			case 'imp_Access':
				$value = getInt($value);
				$collection[$field] = $value;
				break;
			case 'imp_Alias':
			case 'imp_Security':
			case 'imp_Email':
			case 'imp_Activation':
				$Conn->escape($value);
				$value = "'$value'";
				$collection[$field] = $value;
				break;
		}
	}
	
	/**
	 * Versucht einen Impersonierungs User einzuloggen. Achtung, sofern
	 * es klappt wird der User auf die Startseite des Original Users
	 * weitergeleitet, wenn nich, kommt ein return
	 * @param string $user, Benutzername
	 * @param string $password, Passwort
	 * @param int $menuID, Menupunkt für den eingeloggt werden soll
	 * @param dbConn $Conn, Datenbankverbindung
	 * @param bool $bRedirect true/false ob Weiterleiten nach login
	 * @return false, wenn Login nicht klappte, sonst gibts Redirect
	 */
	public static function login($user,$password,$menuID,dbConn &$Conn,$bRedirect = true) {
		// Security String generieren und Menu validieren
		$menuID = getInt($menuID);
		$sSecurity = secureString::getSecurityString($password,$user);
		// Nach der impersonation über Menu Join suchen
		$sSQL = "SELECT usr_ID FROM tbimpersonation
		INNER JOIN tbmenu_impersonation ON tbmenu_impersonation.imp_ID = tbimpersonation.imp_ID
		WHERE imp_Active = 1 AND imp_Security = '$sSecurity' AND tbmenu_impersonation.mnu_ID = $menuID";
		// Wenn Resultat vorhanden, kann eingeloggt werden
		$nUsrID = getInt($Conn->getFirstResult($sSQL));
		if ($nUsrID > 0) {
			// Security des impersonierten Users holen
			$sSQL = "SELECT usr_Security FROM tbuser
			WHERE usr_ID = $nUsrID AND man_ID = ".page::mandant();
			$sOriginalSecurity = $Conn->getFirstResult($sSQL);
			// Wenn das Login klappt, wird weitergeleitet auf User Startseite
			sessionConfig::set('ImpersonationSecurity',$sSecurity,$menuID);
			$login = new dologin($Conn);
			return($login->doLogin($sOriginalSecurity,$bRedirect));
		}
		// Wenn wir bis hier kommen, false zurückgeben
		return(false);
	}
	
	/**
	 * Loggt den aktuellen Impersonation User aus
	 * @param access Access, Aktuelles Access Objekt
	 * @param int $nMenuID, ID des Menus an dem ausgeloggt wird
	 * @param string $location, String für den location Header (Redirect)
	 */
	public static function logout(access &$Access,$nMenuID,$location = '') {
		sessionConfig::set('ImpersonationSecurity','',$nMenuID);
		$Access->logMeOut($location,!(strlen($location)==0));
	}
	
	/**
	 * Impersonation ID anhand des Security Strings holen
	 * @param string security, Sicherheitsstring
	 * @param dbConn Conn, Datenbankverbindung
	 * @return int, ID des Users oder 0, wenn nicht gefunden
	 */
	public static function getIdBySecurity($security,dbConn &$Conn) {
		$Conn->escape($security);
		$sSQL = "SELECT imp_ID FROM tbimpersonation 
		WHERE imp_Security = '$security' AND man_ID = ".page::mandant();
		return(getInt($Conn->getFirstResult($sSQL)));
	}
	
	/**
	 * Impersonation Security anhand der ID holen
	 * @param int, ID des Users
	 * @param dbConn Conn, Datenbankverbindung
	 * @return string security, Sicherheitsstring
	 */
	public static function getSecurityById($nImpID,dbConn &$Conn) {
		$nImpID = getInt($nImpID);
		$sSQL = "SELECT imp_Security FROM tbimpersonation 
		WHERE imp_ID = $nImpID AND man_ID = ".page::mandant();
		return($Conn->getFirstResult($sSQL));
	}
	
	/**
	 * Prüfen, ob der User schon existiert
	 * @param string $sSecurity
	 * @param dbConn $Conn
	 * @return bool true/false ob ein User existiert
	 */
	public static function exists($sSecurity,dbConn &$Conn) {
		$sSQL = "SELECT COUNT(imp_ID) FROM tbimpersonation
		WHERE imp_Security = '$sSecurity' AND man_ID = ".page::mandant();
		$nCount = $Conn->getCountResult($sSQL);
		if ($nCount > 0) return(true);
		return(false);
	}
}