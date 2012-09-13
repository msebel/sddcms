<?php 
/**
 * Klasse welche die Optionen der Webseite handelt.
 * Alles wird in eine Session geladen und danach nicht
 * mehr verändert. Readonly statische Klasse.
 * @author Michael Sebel <michael@sebel.ch>
 */
class option {
	
	/**
	 * Options in die Session laden, wenn nicht vorhanden.
	 * Diese Funktion wird automatisch aufgerufen
	 * @param dbConn Conn, Datenbank Objekt
	 */
	public static function load(dbConn &$Conn) {
		// Session noch nicht vorhanden?
		if (!isset($_SESSION['option'])) {
			$_SESSION['option'] = array();
			// Daten für diese Seite laden
			$sSQL = 'SELECT opt_Field,opt_Value FROM tboptions
			WHERE man_ID = '.page::mandant();
			$nRes = $Conn->execute($sSQL);
			while ($row = $Conn->next($nRes)) {
				$_SESSION['option'][$row['opt_Field']] = $row['opt_Value'];
			}
		}
	}

	/**
	 * Speichern einer Option
	 * @param $name Name der Option
	 * @param $value Definierter Wert der Option
	 */
	public static function set($name,$value,$mandant = 0) {
		$Conn = singleton::conn();
		$Conn->escape($value);
		if ($mandant == 0)
			$mandant = page::mandant();
		// Speichern oder neu erstellen
		$sSQL = 'SELECT COUNT(opt_ID) FROM tboptions
		WHERE man_ID = '.$mandant.' AND opt_Field = "'.$name.'"';
		$nResult = $Conn->getCountResult($sSQL);
		// Wenn schon da, update, sonst insert
		if ($nResult == 1) {
			$sSQL = 'UPDATE tboptions SET opt_Value = :opt_Value
			WHERE man_ID = :man_ID AND opt_Field = :opt_Field';
		} else {
			$sSQL = 'INSERT INTO tboptions (man_ID,opt_Field,opt_Value)
			VALUES (:man_ID,:opt_Field,:opt_Value)';
		}
		$Stmt = $Conn->prepare($sSQL);
		$Stmt->bind('man_ID',$mandant,PDO::PARAM_INT);
		$Stmt->bind('opt_Field',$name,PDO::PARAM_STR);
		$Stmt->bind('opt_Value',$value,PDO::PARAM_STR);
		$Stmt->command();
		// Auch direkt in der Session setzen
		$_SESSION['option'][$name] = $value;
	}
	
	/**
	 * Prüfen, ob Option vorhanden ist.
	 * @param string Name, Name der Option
	 */
	public static function available($Name) {
		$Isset = false;
		if (isset($_SESSION['option'][$Name])) $Isset = true;
		return($Isset);
	}
	
	/**
	 * Option zurückgeben
	 * @param string Name, Name der Option
	 */
	public static function get($Name, $Value = NULL) {
		if (self::available($Name)) {
			$Value = $_SESSION['option'][$Name];
		}
		return($Value);
	}


	public static function get_by_mandant($name,$mandant) {
		// Aus der Session, wenn aktueller Mandant
		if ($mandant == page::mandant()) {
			return self::get($name);
		}
		// Anderer Mandant, aus der Datenbank nehmen
		$sSQL = "SELECT opt_Value FROM tboptions
		WHERE man_ID = $mandant AND opt_Field = '$name'";
		return singleton::conn()->getFirstResult($sSQL);
	}
}