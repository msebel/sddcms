<?php
/**
 * Logging Klasse.
 * Bietet verschiedene Levels von Logging an, 
 * dessen Ergebnis im Log Modul angesehen werden kann
 * @author Michael Sebel <michael@sebel.ch>
 */
class logging {
	
	/**
	 * Datenbank Verbindung
	 * @var dbConn
	 */
	public static $Conn;
	/**
	 * Fehlertyp "Info"
	 * @var integer
	 */
	const TYPE_INFO = 1;
	/**
	 * Fehlertyp "Debug"
	 * @var integer
	 */
	const TYPE_DEBUG = 2;
	/**
	 * Fehlertyp "Error"
	 * @var integer
	 */
	const TYPE_ERROR = 3;
	/**
	 * Fehlertyp "Fatal"
	 * @var integer
	 */
	const TYPE_FATAL = 4;
	
	/**
	 * Fehler von Typ "Info" erfassen
	 * @param string message Fehlermeldung
	 */
	public static function info($message) {
		self::insertError($message,self::TYPE_INFO);
	}
	
	/**
	 * Fehler von Typ "Debug" erfassen
	 * @param string message Fehlermeldung
	 */
	public static function debug($message) {
		self::insertError($message,self::TYPE_DEBUG);
	}
	
	/**
	 * Fehler von Typ "Error" erfassen
	 * @param string message Fehlermeldung
	 */
	public static function error($message) {
		self::insertError($message,self::TYPE_ERROR);
	}
	
	/**
	 * Fehler von Typ "Fatal" erfassen
	 * @param string message Fehlermeldung
	 */
	public static function fatal($message) {
		$nID = self::insertError($message,self::TYPE_FATAL);
		echo '
		<pre>'."\r\n\r\n\r\n\r\n\r\n\r\n".'
		Ein schwerer Fehler ist aufgetreten. Bitte melden Sie sich
		umgehend mit der Nummer #'.$nID.' beim Administrator. Die Ausf&uuml;hrung 
		der Webseite wurde aus Sicherheitsgr&uuml;nden abgebrochen.
		
		A fatal error has occured. Please contact your system
		administrator with the number #'.$nID.'. The execution of
		your website has stopped for security reasons.
		</pre>
		';
		exit();
	}
	
	/**
	 * Erstellt den Fehler mit gegebenem Typ/Nachricht
	 * @param string message Fehlernachricht
	 * @param int type Typ anhand interner Konstanten
	 * @return int ID des geloggten Eintrages
	 */
	private static function insertError($message,$type) {
		// Daten zusammensuchen
		$nManID = getInt($_SESSION['page']['mandant']);
		$nMenuID = getInt($_GET['id']);
		$nUserID = getInt($_SESSION['userid']);
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		// Benutzerdaten holen
		$sSQL = "SELECT usr_Alias,usr_Name FROM tbuser WHERE usr_ID = $nUserID";
		$nRes = self::$Conn->execute($sSQL);
		$sUserinfo = 'Standard web user';
		while ($row = self::$Conn->next($nRes)) {
			$sUserinfo.= $row['usr_Alias'].' ('.$row['usr_Name'].'), ';
			$sUserinfo = 'User #'.$nUserID;
		}
		// Menudaten holen
		$sSQL = "SELECT mnu_Name,typ_ID FROM tbmenu WHERE mnu_ID = $nMenuID";
		$nRes = self::$Conn->execute($sSQL);
		$sMenuinfo = 'No Menu info';
		while ($row = self::$Conn->next($nRes)) {
			$sMenuinfo = $row['mnu_Name'].' (typ_ID #'.$row['typ_ID'].')';
		}
		// Aufrufdaten generieren
		$sReferer = $_SERVER['HTTP_REFERER'];
		$sUrlinfo = $_SERVER['REQUEST_URI'].' ('.$_SERVER['REQUEST_METHOD'].')';
		$postdata = addslashes(stringOps::getVarDump($_POST));
		$getdata = addslashes(stringOps::getVarDump($_GET));
		if ($type == self::TYPE_FATAL) {
			$sessiondata = addslashes(stringOps::getVarDump($_SESSION));
		} else {
			$sessiondata = 'session data store disabled';
		}
		// Daten escapen
		self::$Conn->escape($message);
		self::$Conn->escape($sMenuinfo);
		self::$Conn->escape($sUserinfo);
		self::$Conn->escape($sReferer);
		self::$Conn->escape($sUrlinfo);
		self::$Conn->escape($postdata);
		self::$Conn->escape($getdata);
		self::$Conn->escape($sessiondata);
		
		// Daten im Log speichern
		$sSQL = "INSERT INTO tblogging (man_ID,mnu_ID,usr_ID,log_Type,log_Date,
		log_Userinfo,log_Menuinfo,log_Error,log_Referer,log_Urlinfo,log_Postdata,
		log_Getdata,log_Sessiondata) VALUES
		($nManID,$nMenuID,$nUserID,$type,'$sDate','$sUserinfo','$sMenuinfo',
		'$message','$sReferer','$sUrlinfo','$postdata','$getdata','$sessiondata')";
		$nID = self::$Conn->insert($sSQL);
		return($nID);
	}
}