<?php
/**
 * Simple Klasse die eindeutige IDs (Systemweit) vergibt
 * Wird eingesetzt für Tabellen die in die Elementetabelle
 * Referenzieren oder systemweit eindeutig sein müssen.
 * @author Michael Sebel <michael@sebel.ch>
 */
class ownerID {
	
	/**
	 * Letzte zurückgegebene ID (0 wenn keine)
	 * @var integer
	 */
	private static $Last = 0;
	
	/**
	 * Neue ID Generieren
	 * @param dbConn Conn, Datenbankobjekt
	 * @return integer Die neue ownerID
	 */
	public static function get(dbConn &$Conn) {
		$sSQL = "INSERT INTO tbowner (owner_ID) VALUES (NULL)";
		$nNewID = $Conn->insert($sSQL);
		self::$Last = $nNewID;
		return($nNewID);
	}
	
	/**
	 * Letzte generierte nochmal holen
	 * @return integer LEtzte generierte ownerID
	 */
	public static function getLast() {
		return(self::$Last);
	}
}