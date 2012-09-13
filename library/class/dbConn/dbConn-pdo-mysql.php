<?php
/**
 * Datenbank.
 * Abstraktionsklasse für Datenbankabfragen
 * mit MySQL 5.0 und der mysql-extension
 * @author Michael Sebel <michael@sebel.ch>
 */
class dbConnPdoMysql implements dbConn {
	/**
	 * Array für Konfigurationen
	 * @var array
	 */
	private $Config = array();
	/**
	 * Named Array aller DB Instanzen
	 * @var array
	 */
	private $Instances = array();
	/**
	 * Aktuelle Datenbankverbindung
	 * @var PDO
	 */
	private $Conn = NULL;
	
	/**
	 * Datenbankobjekt instanzieren
	 */
	public function __construct() {
		// Konfigurationsparameter füllen
		$this->Config['srvr'] = config::SQL_SERVER;
		$this->Config['user'] = config::SQL_PREFIX.config::SQL_USER;
		$this->Config['pass'] = config::SQL_PASSWORD;
		$this->Config['inst'] = config::SQL_PREFIX.config::SQL_INSTANCE;
		$this->Config['glob'] = config::SQL_PREFIX.config::SQL_GLOBAL;
		// Verbindung herstellen und Instanz auswählen
		$this->connect($this->Config['inst']);
	}
	
	/**
	 * Objekt zerstören indem die Verbindung geschlossen wird
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * Zum Datenbankserver verbinden
	 */
	private function connect($db) {
		// Zum Datenbankserver verbinden
		if (!isset($this->Instances[$db])) {
			try {
				$this->Instances[$db] = new PDO(
					'mysql:host='.$this->Config['srvr'].';dbname='.$db,
					$this->Config['user'],
					$this->Config['pass']
				);
			} catch (PDOException $pdoex) {
				// Throw errors only on main db, not on customers
				if (!stringOps::startsWith(config::SQL_PREFIX.$db,config::SQL_PREFIX.'page'))
					throw new sddStandardException($pdoex->getMessage());
			}
		}
		// Dies ins aktuelle Objekt referenzieren
		$this->Conn = $this->Instances[$db];
	}
	
	/**
	 * Mit der Instanzdatenbank arbeiten
	 */
	public function setInstanceDB() {
		$this->connect($this->Config['inst']);
	}
	
	/**
	 * Mit der globalen Datenbank arbeiten
	 */
	public function setGlobalDB() {
		$this->connect($this->Config['glob']);
	}
	
	/**
	 * Mit der Kundendatenbank arbeiten
	 */
	public function setCustomerDB() {
		$this->connect(config::SQL_PREFIX.'page'.page::ID());
	}
	
	/**
	 * Mit einer anderen Datenbank arbeiten
	 * @param string dbName, Name der zu verbindenden Datenbank
	 */
	public function setDB($dbName) {
		$this->connect($dbName);
	}
	
	/**
	 * Resultat löschen
	 * @param resource Resource, Abfrageressource
	 */
	private function release(dbStmt $stmt) {
		$stmt->release();
	}
	
	/**
	 * Query ausführen und Resource zurückgeben
	 * @param string sSQL, Datenbankabfrage String
	 * @return dbStmt Resultidentifier für das resultierende Recordset
	 */
	public function execute($sSQL) {
		$stmt = $this->Conn->query($sSQL) or die ($this->getError($sSQL));
		return(new dbStmtPdoMySql($this->Conn,$stmt));
	}
	
	/**
	 * Statement für folgendes Prepared Statement vorbereiren
	 * @param string $sSQL SQL Statement
	 * @return dbStmt Statement Objekt
	 */
	public function prepare($sSQL) {
		return(new dbStmtPdoMySql(
			$this->Conn, $this->Conn->prepare($sSQL)
		));
	}
	
	/**
	 * Query ausführen, welches nichts selektiert aber Daten verändert
	 * @param string sSQL, Abfrage, Update, Delete oder Insert
	 * @return integer ANzahl betroffene Datenzeilen
	 */
	public function command($sSQL) {
		// Query ausführen
		$stmt = $this->Conn->query($sSQL) or die ($this->getError($sSQL));
		return(getInt($stmt->rowCount()));
	}
	
	/**
	 * Insert Query ausführen und die eingefügte ID danach zurückgeben
	 * @param string sSQL, Abfragestring nur INSERT Statements erlaubt
	 * @return integer Letzte eingefügte ID des Insert Statements
	 */
	public function insert($sSQL) {
		$stmt = $this->Conn->query($sSQL) or die ($this->getError($sSQL));
		return($this->Conn->lastInsertId());
	}
	
	/**
	 * String für SQL Connection escapen
	 * @param mixed value, Zu escapender Wert (String, integer etc.)
	 */
	public function escape(&$value) {
		if (!get_magic_quotes_gpc()) {
			$value = addslashes($value);
		}
	}
	
	/**
	 * Nächsten Datensatz einer Ressource zurückgeben
	 * @param resource Resource, SQL Abfrageressource
	 * @return array Datenressource oder NULL wenn keine vorhanden
	 */
	public function next(dbStmt $stmt) {
		$row = $stmt->fetch();
		// Wenn letztes ergebnis
		if ($row == NULL) {
			$stmt->release();
		} 
		return($row);
	}
	
	/**
	 * Erstes gefundenes Resultatfeld zurückgeben
	 * @param string sSQL, Datenbankabfrage
	 * @return mixed, Erster Wert des ersten Ergebnissatzes
	 */
	public function getFirstResult($sSQL) {
		$stmt = $this->Conn->query($sSQL) or die ($this->getError($sSQL));
		// Egal was, erstes Feld nehmen
		$numericRecords = $stmt->fetch(PDO::FETCH_NUM);
		// Prüfen ob was vorhanden ist
		if ($numericRecords != false) {
			$sResult = $numericRecords[0];
		} else {
			$sResult = NULL;
		}
		$stmt->closeCursor();
		return($sResult);
	}
	
	/**
	 * Resultat eines SQL Count Queries zurückgeben
	 * @param string sSQL, Datenbankabfrage SELECT COUNT(
	 * @return integer Anzahl gezählter Datensätze des Counts
	 */
	public function getCountResult($sSQL) {
		$stmt = $this->Conn->query($sSQL) or die ($this->getError($sSQL));
		$numericRecords = $stmt->fetch(PDO::FETCH_NUM);
		$nReturn = getInt($numericRecords[0]);
		$stmt->closeCursor();
		return($nReturn);
	}
	
	/**
	 * Zeilen einer Abfrage zählen und zurückgeben.
	 * Abfrage ist danach nicht mehr zugänglich
	 * @param string sSQL, Datenbankabfrage
	 * @return integer Gezählte Datensätze
	 */
	public function getColumnCount($sSQL) {
		$stmt = $this->Conn->query($sSQL);
		return($this->numRows($stmt));
	}
	
	/**
	 * Ergebnisse einer Ressource zählen
	 * @param PDOStatement $stmt, Ressouce einer Datenbankabfrage
	 * @return integer Gezählte Datensätze der Ressource
	 */
	public function numRows($stmt) {
		return($stmt->rowCount());
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
	
	/**
	 * Datenbankverbindung schliessen
	 */
	private function close() {
		unset($this->Conn);
		unset($this->Instances);
	}
	
	/**
	 * Beginnt eine Transaktion
	 */
	public function beginTransaction() {
		$this->beginTransaction();
	}
	
	/**
	 * Beendet eine Transaktion, Skripte werden ausgeführt und
	 * die Datenbank geht zurück in den AutoCommit Mode
	 */
	public function commit() {
		$this->commit();
	}
	
	/**
	 * Führt ein Rollback auf der aktuellen Transaktion aus und
	 * die Datenbank geht zurück in den AutoCommit Mode
	 */
	public function rollback() {
		$this->rollback();
	}
}