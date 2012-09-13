<?php
/**
 * Statement Objekt nach Prepared Statements
 * @author Michael Sebel <michael@sebel.ch>
 */
interface dbStmt {
	
	/**
	 * Einen typisierten Parameter binden
	 * @param string $name Name der Bindung
	 * @param mixed $value Gebundene Variable
	 * @param int $type PDO_PARAM Typ
	 */
	public function bind($name,$value,$type = NULL);
	/**
	 * Ein Select Statement ausführen, nachdem die
	 * entsprechenden Parameter gebunden wurden. Danach
	 * kann mit next() gefetch't werden
	 */
	public function select();
	/**
	 * Nächsten Record aus aktivem Recordset holen
	 */
	public function next();
	/**
	 * Insert Statement ausführen und die Autoincrement
	 * ID des Statements zurückbekommen
	 */
	public function insert();
	/**
	 * Update oder Delete Statement ausführen
	 */
	public function command();
	/**
	 * Eine Resource wieder freigeben
	 */
	public function release();
	/**
	 * Gibt die nächste Zeile als assoziatives Array zurück
	 * @return array Datenzeile aus SQL Server (Assoc)
	 */
	public function fetch();
	/**
	 * Gibt die nächste Zeile als indiziertes Array zurück
	 * @return array Datenzeile aus SQL Server (Index)
	 */
	public function fetchIndexed();
}