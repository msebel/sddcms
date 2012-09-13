<?php
// Implementiert ein Wiki Objekt
class wikiObject {
	
	/**
	 * ID des Wiki in der Datenbank
	 * @var int
	 */
	public $WikiID = 0;
	/**
	 * ID des impersonierenden Adminusers
	 * @var int
	 */
	public $Adminuser = 0;
	/**
	 * ID des impersonierenden CUG Users
	 * @var int
	 */
	public $Cuguser = 0;
	/**
	 * Gibt an, ob das Wiki offen ist (Ohne Registrierung)
	 * @var boolean
	 */
	public $Open = false;
	/**
	 * Titel des Wikis
	 * @var string
	 */
	public $Title = '';
	/**
	 * Einleitungstext für das Wiki
	 * @var string
	 */
	public $Text = '';
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	public $Conn = null;
	
	/**
	 * Erstellt das Wiki Objekt und lädt dessen Daten
	 * @param int nWkiID, Eindeutige ID des Wiki
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public function __construct($nWkiID,dbConn &$Conn) {
		$this->Conn = $Conn;
		$this->WikiID = $nWkiID;
		$sSQL = "SELECT wki_Adminuser,wki_Cuguser,wki_Open,
		wki_Title,wki_Text FROM tbwiki WHERE wki_ID = $nWkiID";
		$nRes = $Conn->execute($sSQL);
		// Wenn Daten vorhanden, diese laden
		if ($row = $Conn->next($nRes)) {
			$this->Adminuser = $row['wki_Adminuser'];
			$this->Cuguser = $row['wki_Cuguser'];
			$this->Open = $row['wki_Open'];
			$this->Title = $row['wki_Title'];
			$this->Text = $row['wki_Text'];
		}
	}
}