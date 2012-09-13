<?php
/**
 * Aufzurufen bei 404-Errors.
 * Schaut, ob der eingegebene Pfad allenfalls ein
 * Shortlink zu einem Menupunkt ist und leitet auf
 * diesen weiter. Wenn nicht, tut er nichts und somit
 * erscheint in aller Regel die normale Error Seite.
 * @author Michael Sebel <michael@sebel.ch>
 */
class pathFinder {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	public static $Conn;
	
	/**
	 * Validiert das eventuelle Keyword aus der URL.
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public static function find(dbConn &$Conn) {
		self::$Conn = $Conn;
		// Hinterstes Wort der nicht gefundenen URL filtern
		$Word =  $_SERVER['REDIRECT_URL'];
		$nDot = strrpos($Word,'/');
		$Word = substr($Word,$nDot+1);
		// Filtern auf nur a-zA-Z0-9
		stringOps::alphaNumOnly($Word);
		if (strlen($Word) > 0) {
			self::searchMenu($Word);
			self::searchLink($Word);
		}
	}
	
	/**
	 * Sucht das gegebene Keyword in der Datenbank.
	 * Falls es gefunden wird innerhalb des Mandanten, wird direkt
	 * auf die entsprechende Menu ID weitergeleitet.
	 * @param string Word, Eingegebenes Keyword
	 */
	private static function searchMenu($Word) {
		// Word escapen und suchen
		self::$Conn->escape($Word);
		$sSQL = "SELECT mnu_ID FROM tbmenu WHERE 
		man_ID = ".page::mandant()." AND mnu_Shorttag = '".$Word."'";
		$nRes = self::$Conn->execute($sSQL);
		while ($row = self::$Conn->next($nRes)) {
			// Auf den ersten Fund weiterleiten
			session_write_close();
			redirect('location: /controller.php?id='.$row['mnu_ID']);
		}
	}
	
	/**
	 * Sucht das gegebene Keyword in den Directlinks
	 * Falls es gefunden wird, wird auf den Link weitergeleitet
	 * @param string Word, Eingegebenes Keyword
	 */
	private static function searchLink($Word) {
		// Word escapen und suchen
		self::$Conn->escape($Word);
		$sSQL = "SELECT drl_Url FROM tbdirectlink WHERE 
		man_ID = ".page::mandant()." AND drl_Name = '".$Word."'";
		$nRes = self::$Conn->execute($sSQL);
		while ($row = self::$Conn->next($nRes)) {
			// Auf den ersten Fund weiterleiten
			session_write_close();
			redirect('location: '.$row['drl_Url']);
		}
	}
}