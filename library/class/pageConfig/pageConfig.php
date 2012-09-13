<?php
/**
 * Konfigurations-Schnittstelle für Menupunkte.
 * Bietet eine Schnittstelle um Menupunkte mit numerischen,
 * character oder grossen Textfeldern zu konfigurieren. Das
 * Erstellen, Speichern und Löschen übernimmt die Klasse
 * @author Michael Sebel <michael@sebel.ch>
 */
class pageConfig {
	
	/**
	 * Typ für kurzen String Varchar(255)
	 * @var integer
	 */
	const TYPE_VALUE = 1;
	/**
	 * Typ für numerischen Wert
	 * @var integer
	 */
	const TYPE_NUMERIC = 2;
	/**
	 * Typ für langen Text, Textfeld
	 * @var string
	 */
	const TYPE_TEXT = 3;
	
	/**
	 * Option für das laden der Konfiguration.
	 * Diese option bewirkt ein error Redirect, wenn
	 * die Konfiguration nicht geladen werden kann.
	 * @var integer
	 */
	const GET_TYPE_REDIRECT = 1;
	/**
	 * Option für das laden der Konfiguration.
	 * Diese option wirft eine Exception, wenn
	 * die Konfiguration nicht geladen werden kann.
	 * @var integer
	 */
	const GET_TYPE_EXCEPTION = 2;
	
	/**
	 * Konfigurationsdaten für einen Menupunkt laden.
	 * Ist die Konfiguration schon geladen, wird diese
	 * aus der Session geholt. Ist keine Konfiguration
	 * vorhanden, geschieht ein error-redirect
	 * @param integer nMenuID, ID des konfigurerten Menupunktes
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, leeres Array, wird mit Konfiguration befüllt
	 */
	public static function get($nMenuID,dbConn &$Conn,&$Config) {
		self::getByType($nMenuID,$Conn,$Config,self::GET_TYPE_REDIRECT);
	}
	
	/**
	 * Konfigurationsdaten für einen Menupunkt laden.
	 * Ist die Konfiguration schon geladen, wird diese
	 * aus der Session geholt. Ist keine Konfiguration
	 * vorhanden, wird eine Exception geworfen
	 * @param integer nMenuID, ID des konfigurerten Menupunktes
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, leeres Array, wird mit Konfiguration befüllt
	 */
	public static function getWithException($nMenuID,dbConn &$Conn,&$Config) {
		self::getByType($nMenuID,$Conn,$Config,self::GET_TYPE_EXCEPTION);
	}
	
	/**
	 * Konfigurationsdaten für einen Menupunkt laden.
	 * Ist die Konfiguration schon geladen, wird diese
	 * aus der Session geholt.
	 * @param integer nMenuID, ID des konfigurerten Menupunktes
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, leeres Array, wird mit Konfiguration befüllt
	 * @param integer nType, Art der Fehlermeldung, wenn keine Konfiguration
	 */
	private static function getByType($nMenuID,dbConn &$Conn,&$Config,$nType) {
		if (!isset($_SESSION['PageConfig']['mnu'.$nMenuID]) || count($_SESSION['PageConfig']['mnu'.$nMenuID]) == 0) {
			$sSQL = "SELECT cfg_ID,cfg_Type,cfg_Numeric,cfg_Name,cfg_Value,cfg_Text
			FROM tbkonfig WHERE mnu_ID = $nMenuID";
			$nRes = $Conn->execute($sSQL);
			$nCount = 0;
			while ($row = $Conn->next($nRes)) {
				$nCount++;
				$Value = NULL;
				$sName = $row['cfg_Name'];
				switch (getInt($row['cfg_Type'])) {
					case self::TYPE_NUMERIC: $Value = $row['cfg_Numeric']; break;
					case self::TYPE_VALUE: $Value = (string) $row['cfg_Value']; break;
					case self::TYPE_TEXT: $Value = (string) $row['cfg_Text']; break;
				}
				// Wenn etwas vorhanden, in das Array
				if (strlen($sName) > 0) {
					$Config[$sName]['Value'] = $Value;
					$Config[$sName]['Type']  = getInt($row['cfg_Type']);
					$Config[$sName]['ID'] 	 = getInt($row['cfg_ID']);
				}
			}
			// Wenn keine Konfig gezählt Exception oder Fehler generieren
			if ($nCount == 0) {
				if ($nType == self::GET_TYPE_REDIRECT) {
					redirect('location: /error.php?type=noConfig&page='.page::menuID());
				}
				if ($nType == self::GET_TYPE_EXCEPTION) {
					throw new Exception('Error while reading configuration');
				}
			}
			// Das alles in die Session packen wenn etwas vorhanden
			$_SESSION['PageConfig']['mnu'.$nMenuID] = $Config;
		} else {
			// Direkt von der Session laden
			$Config = $_SESSION['PageConfig']['mnu'.$nMenuID];
			// Auf jede Text-Value Stripslashes anwenden
			self::stripSlashes($Config);
		}
	}
	
	/**
	 * Stripslashes auf die gesamte Konfiguration anwenden.
	 * Wichtig für Werte die aus der Session kommen. Es werden
	 * nur Text und Value Werte gestript.
	 * @param array Config, Array mit der Konfiguration
	 */
	public static function stripSlashes(&$Config) {
		foreach ($Config as $Key => $Setting) {
			// Ist es kein numerischer Wert?
			if ($Setting['Type'] != self::TYPE_NUMERIC) {
				$Config[$Key]['Value'] = stripslashes($Setting['Value']);
			}
		}
	}
	
	/**
	 * Konfigurationssession zurücksetzen.
	 * @param integer nMenuID, Menukonfiguration welche zurückzusetzen ist
	 */
	public static function reset($nMenuID) {
		unset($_SESSION['PageConfig']['mnu'.$nMenuID]);
	}
	
	/**
	 * Einen Konfigurationsparameter erstellen.
	 * @param integer nMenuID, ID des Menus, welches die Konfiguration bekommt
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param mixed Value, Inhalt der Konfiguration
	 * @param integer Type, Art der Konfiguration (TYPE_ Konstanten!)
	 * @param array Config, Array welches Konfiguration enthält
	 */
	public static function setConfig($nMenuID,dbConn &$Conn,$Value,$Type,$Name,&$Config) {
		// Alle Konfigurationen mit diesem Namen unter dieser ID löschen
		$sSQL = "SELECT COUNT(cfg_ID) FROM tbkonfig 
		WHERE mnu_ID = $nMenuID AND cfg_Name = '$Name'";
		$nResult = $Conn->getCountResult($sSQL);
		// Nur wenn kein Resultat neue erstellen
		if ($nResult == 0) {
			// Value für SQL formatieren anhand Typ
			$ValueFormated = $Value;
			self::formatValue($ValueFormated,$Type,$Conn);
			// Konfigurationsfeld in der Datenbank erstellen
			$sSQL = "INSERT INTO tbkonfig 
			(mnu_ID,cfg_Type,cfg_Name,".self::getColumn($Type).")
			VALUES ($nMenuID,$Type,'$Name',$ValueFormated)";
			$nNewID = $Conn->insert($sSQL);
			// Equivalent in Config speichern und Session
			$Config[$Name]['Value'] = $Value;
			$Config[$Name]['Type'] = $Type;
			$Config[$Name]['ID'] = $nNewID;
			$_SESSION['PageConfig']['mnu'.$nMenuID] = $Config;
		} else {
			// Konfiguration laden
			$sSQL = "SELECT cfg_ID,cfg_Type,cfg_Numeric,cfg_Name,cfg_Value,cfg_Text 
			FROM tbkonfig WHERE mnu_ID = $nMenuID AND cfg_Name = '$Name'";
			$nRes = $Conn->execute($sSQL);
			while ($row = $Conn->next($nRes)) {
				$Value = NULL;
				$sName = $row['cfg_Name'];
				switch (getInt($row['cfg_Type'])) {
					case self::TYPE_NUMERIC: $Value = $row['cfg_Numeric']; break;
					case self::TYPE_VALUE: $Value = $row['cfg_Value']; break;
					case self::TYPE_TEXT: $Value = $row['cfg_Text']; break;
				}
				// Wenn etwas vorhanden, in das Array
				if ($Value != NULL && strlen($sName) > 0) {
					$Config[$sName]['Value'] = $Value;
					$Config[$sName]['Type']  = getInt($row['cfg_Type']);
					$Config[$sName]['ID'] 	 = getInt($row['cfg_ID']);
				}
			}
			$_SESSION['PageConfig']['mnu'.$nMenuID] = $Config;
		}
	}
	
	/**
	 * Gesamte Konfiguration in die Datenbank speichern.
	 * @param integer nMenuID, Menukonfiguration die zu speichern ist
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, Array, welches Konfiguration enthält
	 */
	public static function saveConfig($nMenuID,dbConn &$Conn,&$Config) {
		// Gesamtes Config Array durchgehen
		foreach ($Config as $Setting) {
			// Value absichern
			self::formatValue($Setting['Value'],$Setting['Type'],$Conn);
			// SQL Erstellen zum updaten
			$sSQL = "UPDATE tbkonfig SET
			".self::getColumn($Setting['Type'])." = ".$Setting['Value']." 
			WHERE mnu_ID = $nMenuID AND cfg_ID = ".$Setting['ID'];
			$Conn->command($sSQL);
		}
		// Das Array wieder in die Session schreiben
		$_SESSION['PageConfig']['mnu'.$nMenuID] = $Config;
	}
	
	/**
	 * Schauen ob alle Parameter für die Konfiguration vorhanden sind.
	 * @param integer nMenuID, Menukonfiguration die zu prüfen ist
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param integer Params, Anzahl Konfigurationen die das Menu haben muss
	 */
	public static function hasConfig($nMenuID,dbConn &$Conn,$Params) {
		$sSQL = "SELECT COUNT(cfg_ID) AS CfgCount 
		FROM tbkonfig WHERE mnu_ID = $nMenuID";
		$nResult = $Conn->getCountResult($sSQL);
		$bResult = false;
		// True zurückgeben, wenn mehr als 0 Elemente vorhanden
		if ($nResult == $Params) $bResult = true;
		return($bResult);
	}
	
	/**
	 * Value für SQL Formatieren und absichern
	 * @param mixed Value, Inhalt der zu sichernden Konfiguration
	 * @param integer Type, Art der Konfiguration (TYPE_ Konstanten!)
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	private function formatValue(&$Value,&$Type,dbConn &$Conn) {
		if ($Type != self::TYPE_NUMERIC) {
			$Conn->escape($Value);
			$Value = '\''.$Value.'\'';
		} else {
			$Value = getInt($Value);
		}
	}
	
	/**
	 * Feldnamen herausfinden anhand Typ.
	 * @param integer nType, Art der Konfiguration (TYPE_ Konstanten!)
	 * @return string Name des entsprechenden Datenbankfeldes
	 */
	private function getColumn($nType) {
		// Textfeld als Standard
		$sReturn = 'cfg_Value'; 
		switch ($nType) {
			case self::TYPE_NUMERIC: $sReturn = 'cfg_Numeric'; break;
			case self::TYPE_VALUE: $sReturn = 'cfg_Value'; break;
			case self::TYPE_TEXT: $sReturn = 'cfg_Text'; break;
		}
		return($sReturn);
	}
}