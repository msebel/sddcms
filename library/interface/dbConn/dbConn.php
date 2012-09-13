<?php
/**
 * Datenbank.
 * Interface für Datenbankabfragen
 * @author Michael Sebel <michael@sebel.ch>
 */
interface dbConn {
	
	/**
	 * Mit der Instanzdatenbank arbeiten
	 */
	public function setInstanceDB();
	
	/**
	 * Mit der globalen Datenbank arbeiten
	 */
	public function setGlobalDB();
	
	/**
	 * Mit der Kundendatenbank arbeiten
	 */
	public function setCustomerDB();
	
	/**
	 * Mit einer anderen Datenbank arbeiten
	 * @param string dbName, Name der zu verbindenden Datenbank
	 */
	public function setDB($dbName);
	
	/**
	 * Query ausführen und Resource zurückgeben
	 * @param string sSQL, Datenbankabfrage String
	 * @return resource Resultidentifier für das resultierende Recordset
	 */
	public function execute($sSQL);
	
	/**
	 * Query ausführen, welches nichts selektiert aber Daten verändert
	 * @param string sSQL, Abfrage, Update, Delete oder Insert
	 * @return integer ANzahl betroffene Datenzeilen
	 */
	public function command($sSQL);
	
	/**
	 * Insert Query ausführen und die eingefügte ID danach zurückgeben
	 * @param string sSQL, Abfragestring nur INSERT Statements erlaubt
	 * @return integer Letzte eingefügte ID des Insert Statements
	 */
	public function insert($sSQL);
	
	/**
	 * String für SQL Connection escapen
	 * @param mixed value, Zu escapender Wert (String, integer etc.)
	 */
	public function escape(&$value);
	
	/**
	 * Nächsten Datensatz einer Ressource zurückgeben
	 * @param resource Resource, SQL Abfrageressource
	 * @return array Datenressource oder NULL wenn keine vorhanden
	 */
	public function next(dbStmt $Resource);
	
	/**
	 * Erstes gefundenes Resultatfeld zurückgeben
	 * @param string sSQL, Datenbankabfrage
	 * @return mixed, Erster Wert des ersten Ergebnissatzes
	 */
	public function getFirstResult($sSQL);
	
	/**
	 * Resultat eines SQL Count Queries zurückgeben
	 * @param string sSQL, Datenbankabfrage SELECT COUNT(
	 * @return integer Anzahl gezählter Datensätze des Counts
	 */
	public function getCountResult($sSQL);
	
	/**
	 * Zeilen einer Abfrage zählen und zurückgeben.
	 * Abfrage ist danach nicht mehr zugänglich
	 * @param string sSQL, Datenbankabfrage
	 * @return integer Gezählte Datensätze
	 */
	public function getColumnCount($sSQL);
	
	/**
	 * Ergebnisse einer Ressource zählen
	 * @param resource Res, Ressouce einer Datenbankabfrage
	 * @return integer Gezählte Datensätze der Ressource
	 */
	public function numRows($Res);
	
	/**
	 * Beginnt eine Transaktion
	 */
	public function beginTransaction();
	
	/**
	 * Beendet eine Transaktion, Skripte werden ausgeführt und
	 * die Datenbank geht zurück in den AutoCommit Mode
	 */
	public function commit();
	
	/**
	 * Führt ein Rollback auf der aktuellen Transaktion aus und
	 * die Datenbank geht zurück in den AutoCommit Mode
	 */
	public function rollback();
	/**
	 * Statement für folgendes Prepared Statement vorbereiren
	 * @param string $sSQL SQL Statement
	 * @return dbStmt Statement Objekt
	 */
	public function prepare($sSQL);
}