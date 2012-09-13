<?php
// Implementiert einen Wiki Benutzer
class wikiUser {
	
	/**
	 * ID der Impersonation
	 * @var int
	 */
	public $ImpersonationID = 0;
	/**
	 * Impersonierter User
	 * @var int
	 */
	public $UserID = 0;
	/**
	 * Gibt an, ob der User aktiv ist
	 * @var bool
	 */
	public $Active = false;
	/**
	 * Name des Users
	 * @var string
	 */
	public $Alias = '';
	/**
	 * Security String mit Passwort
	 * @var string
	 */
	public $Security = '';
	/**
	 * E-Mail Adresse des Benutzers
	 * @var string
	 */
	public $Email = '';
	
	// Objekt laden
	public function __construct($sSecurity,dbConn &$Conn) {
		// Benutzerdaten laden
		$sSQL = "SELECT imp_ID,usr_ID,imp_Active,imp_Alias,imp_Email FROM tbimpersonation
		WHERE imp_Security = '$sSecurity' AND man_ID = ".page::mandant();
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) {
			// Daten abfüllen
			$this->ImpersonationID = getInt($row['imp_ID']);
			$this->UserID = getInt($row['usr_ID']);
			$this->Alias = $row['imp_Alias'];
			$this->Email = $row['imp_Email'];
			$this->Security = $sSecurity;
			// Aktiv Flag
			$this->Active = false;
			if (getInt($row['imp_Active'])) $this->Active = true;
		}
	}
}