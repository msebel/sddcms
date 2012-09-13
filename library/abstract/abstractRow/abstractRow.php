<?php
/**
 * Abstrakte Datenzeile um ORM Objekte zu implementieren
 * @author Michael Sebel <michael@sebel.ch>
 */
abstract class abstractRow {
	
	/**
	 * Gibt an, ob die Datenzeile bereits initialisiert wurde
	 * @var boolean
	 */
	protected $isInitialized = false;
	/**
	 * Datenbankverbindung zum CMS
	 * @var dbConn
	 */
	protected $Conn = NULL;
	/**
	 * Datenbankverbindung zum Kunden
	 * @var dbConn
	 */
	protected $CustConn = NULL;
	/**
	 * Ressourcen Objekt
	 * @var resources
	 */
	protected $Res = NULL;
	
	/**
	 * Definitiver Konstruktor, welchem eine ID übergeben werden kann.
	 * Macht man das, wird der Datensatz direkt über load() geladen
	 * @param int nID, Eindeutige ID des Datensatzes
	 */
	public function __construct($nID = 0) {
		// Datenbankverbindungen holen
		$this->Conn = database::getConnection();
		$this->CustConn = database::getCustomerConnection();
		$this->Res = getResources::getInstance($this->Conn);
		// Daten laden, wenn möglich
		if ($nID > 0) {
			$this->load(getInt($nID));
			$this->isInitialized = true;
		}
	}
	
	/**
	 * Speichern der Daten, entscheidet selbst ob ein Insert
	 * oder ein Update geschieht und gibt die ID zurück
	 * @return int ID des gespeicherten Datensatzes
	 */
	public function save() {
		if ($this->isInitialized) {
			$nID = $this->update($nID);
		} else {
			$nID = $this->insert($nID);
			$this->isInitialized = true;
		}
		return($nID);
	}
	
	/**
	 * Lädt den Datensatz ins lokale Objekt
	 * @param int nID, ID des Datensatzes
	 */
	public abstract function load($nID);
	
	/**
	 * Speichert einen bestehenden Datensatz anhand der internen ID
	 * @return int ID des gespeicherten Datensatzes
	 */
	public abstract function update();
	
	/**
	 * Erstellt einen neuen Datensatz und gibt die ID zurück
	 * @return int ID des gespeicherten Datensatzes
	 */
	public abstract function insert();
	
	/**
	 * Löscht einen Datensatz und allfällige zugehörigkeiten
	 */
	public abstract function delete();
}