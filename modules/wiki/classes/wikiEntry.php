<?php
// Implementiert einen Wiki Eintrag
class wikiEntry {
	
	/**
	 * ID der Impersonation
	 * @var int
	 */
	public $EntryID = 0;
	/**
	 * Impersonierter User
	 * @var int
	 */
	public $ContentID = 0;
	/**
	 * ID des Users, der diese Version bearbeitet hat
	 * @var int
	 */
	public $ImpersonationID = 0;
	/**
	 * Titel des Eintrags
	 * @var string
	 */
	public $Title = '';
	/**
	 * Inhalt des Eintrages
	 * @var string
	 */
	public $Content = '';
	/**
	 * Datum dieser Version
	 * @var string
	 */
	public $Date = '';
	/**
	 * Session ID welche diese Version erstellte
	 * @var string
	 */
	public $Session = '';
	/**
	 * Versionsnummer
	 * @var int
	 */
	public $Version = 0;
	/**
	 * ID des Vorgänger Eintrags
	 * @var int
	 */
	public $Parent = 0;
	/**
	 * Gibt an, ob der Eintrag aktiv ist
	 * @var bool
	 */
	public $Active = false;
	
	// Objekt laden
	public function __construct($nWkeID,dbConn &$Conn) {
		$nWkeID = getInt($nWkeID);
		// Daten in lokale Variablen laden
		$sSQL = "SELECT tbcontent.con_ID,imp_ID,con_Title,con_Date,con_Active, 
		con_Content,wke_Version,wke_Session,wke_Parent FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID WHERE wke_ID = $nWkeID";
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) {
			// Daten modifizieren
			$this->EntryID = $nWkeID;
			$this->ContentID = getInt($row['con_ID']);
			$this->ImpersonationID = getInt($row['imp_ID']);
			$this->Version = getInt($row['wke_Version']);
			$this->Parent = getInt($row['wke_Parent']);
			$this->Title = $row['con_Title'];
			$this->Content = $row['con_Content'];
			$this->Session = $row['wke_Session'];
			// Datum direkt in europäisches Format umbauen
			$this->Date = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATETIME,
				$row['con_Date']
			);
			// Aktiv Flag setzen
			if (getInt($row['con_Active']) == 1) {
				$this->Active = true;
			}
		}
	}
}