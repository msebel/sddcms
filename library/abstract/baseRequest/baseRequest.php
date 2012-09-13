<?php
/**
 * Basisklasse für Ajax Requests
 * @author Michael Sebel <michael@sebel.ch>
 */
abstract class baseRequest {
	
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	protected $Conn;
	/**
	 * Sprachressourcen
	 * @var resources
	 */
	protected $Res;
	
	/**
	 * Konstruktor, tut grundsätzlich nichts...
	 */
	public function __construct() {
		
	}
	
	/**
	 * Initialisiert den AJAX Request
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param resources Res, Referenz zum Sprachobjekt
	 */
	public function initialize(dbConn $Conn, resources $Res) {
		// Referenzieren der Objekte
		$this->Conn = $Conn;
		$this->Res = $Res;
	}
	
	/**
	 * Wird beim Aufruf des Requests ausgeführt.
	 * Ausgaben müssen direkt mit 'echo' erfolgen.
	 */
	abstract function output();
}