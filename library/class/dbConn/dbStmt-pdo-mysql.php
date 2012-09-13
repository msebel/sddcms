<?php

class dbStmtPdoMySql implements dbStmt {
	
	/**
	 * PDO Statement Objekt
	 * @var PDOStatement
	 */
	private $Stmt = NULL;
	/**
	 * Datenbankverbindung, für Inserts nötig
	 * @var PDO 
	 */
	private $Conn = NULL;
	
	/**
	 * PDF Statement Objekt zuweisen
	 * @param PDOStatement $stmt 
	 */
	public function __construct(PDO $conn,PDOStatement $stmt) {
		$this->Conn = $conn;
		$this->Stmt = $stmt;
	}

	/**
	 * Einen typisierten Parameter binden
	 * @param string $name Name der Bindung
	 * @param mixed $value Gebundene Variable
	 * @param int $type PDO_PARAM Typ
	 */
	public function bind($name,$value,$type = NULL) {
		if ($type != NULL) {
			$this->Stmt->bindValue($name,$value,$type);
		} else {
			$this->Stmt->bindValue($name,$value);
		}
	}
	
	/**
	 * Ein Select Statement ausführen, nachdem die
	 * entsprechenden Parameter gebunden wurden. Danach
	 * kann mit next() gefetch't werden
	 */
	public function select() {
		$this->Stmt->execute() or die (
			$this->getError($this->Stmt->queryString)
		);
	}
	
	/**
	 * Nächsten Record aus aktivem Recordset holen
	 */
	public function next() {
		$row = $this->fetch();
		// Wenn letztes ergebnis
		if ($row == NULL) {
			$this->release();
		} 
		return($row);
	}
	
	/**
	 * Insert Statement ausführen und die Autoincrement
	 * ID des Statements zurückbekommen
	 */
	public function insert() {
		$this->Stmt->execute() or die (
			$this->getError($this->Stmt->queryString)
		);
		return($this->Conn->lastInsertId());
	}
	
	/**
	 * Update oder Delete Statement ausführen
	 */
	public function command() {
		$this->Stmt->execute() or die (
			$this->getError($this->Stmt->queryString)
		);
		return($this->Stmt->rowCount());
	}
	
	/**
	 * Eine Resource wieder freigeben
	 */
	public function release() {
		$this->Stmt->closeCursor();
	}
	
	/**
	 * Gibt die nächste Zeile als assoziatives Array zurück
	 * @return array Datenzeile aus SQL Server (Assoc)
	 */
	public function fetch() {
		return($this->Stmt->fetch(PDO::FETCH_ASSOC));
	}
	
	/**
	 * Gibt die nächste Zeile als indiziertes Array zurück
	 * @return array Datenzeile aus SQL Server (Index)
	 */
	public function fetchIndexed() {
		return($this->Stmt->fetch(PDO::FETCH_NUM));
	}
	
	/**
	 * Error ausgeben, wenn Debug eingeschaltet
	 * @param string sSQL, Datenbankabfrage die Fehler verursachte
	 */
	private function getError($sSQL) {
		$info = $this->Conn->errorInfo();
		$newEx = new sddDbException($info[0].': '.$info[2]);
		$newEx->setSql($sSQL);
		throw $newEx;
	}
}