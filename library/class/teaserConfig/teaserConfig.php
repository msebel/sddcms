<?php
/**
 * Konfigurations-Schnittstelle für Teaser.
 * Bietet eine Schnittstelle um Teaser mit numerischen,
 * character oder grossen Textfeldern zu konfigurieren. Das
 * Erstellen, Speichern und Löschen übernimmt die Klasse
 * @author Michael Sebel <michael@sebel.ch>
 */
class teaserConfig {
	
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
	 * Konfigurationsdaten für einen Teasereintrag laden.
	 * Ist die Konfiguration schon geladen, wird diese
	 * aus der Session geholt. Ist keine Konfiguration
	 * vorhanden, geschieht ein error-redirect
	 * @param integer nTeaserID, ID des konfigurerten Teasers
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, leeres Array, wird mit Konfiguration befüllt
	 */
	public static function get($nTeaserID,dbConn &$Conn,&$Config) {
		if (!isset($_SESSION['TeaserConfig']['tap'.$nTeaserID]) || count($_SESSION['TeaserConfig']['tap'.$nTeaserID]) == 0) {
			$sSQL = "SELECT cfg_ID,cfg_Type,cfg_Numeric,cfg_Name,cfg_Value,cfg_Text
			FROM tbteaserkonfig WHERE tap_ID = $nTeaserID";
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
			// Wenn keine Konfig gezählt error Redirect
			if ($nCount == 0) {
				$Config = false;
			}
			// Das alles in die Session packen wenn etwas vorhanden
			$_SESSION['TeaserConfig']['tap'.$nTeaserID] = $Config;
		} else {
			// Direkt von der Session laden
			$Config = $_SESSION['TeaserConfig']['tap'.$nTeaserID];
			// Auf jede Text-Value Stripslashes anwenden
			if ($Config !== false) self::stripSlashes($Config);
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
	 * @param integer nTeaserID, Teaserkonfiguration welche zurückzusetzen ist
	 */
	public static function reset($nTeaserID) {
		unset($_SESSION['TeaserConfig']['tap'.$nTeaserID]);
	}
	
	/**
	 * Einen Konfigurationsparameter erstellen.
	 * @param integer nTeaserID, ID des Teasers, welches die Konfiguration bekommt
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param mixed Value, Inhalt der Konfiguration
	 * @param integer Type, Art der Konfiguration (TYPE_ Konstanten!)
	 * @param array Config, Array welches Konfiguration enthält
	 */
	public static function setConfig($nTeaserID,dbConn &$Conn,$Value,$Type,$Name,&$Config) {
		// Alle Konfigurationen mit diesem Namen unter dieser ID löschen
		$sSQL = "SELECT COUNT(cfg_ID) FROM tbteaserkonfig 
		WHERE tap_ID = $nTeaserID AND cfg_Name = '$Name'";
		$nResult = $Conn->getCountResult($sSQL);
		// Nur wenn kein Resultat neue erstellen
		if ($nResult == 0) {
			// Value für SQL formatieren anhand Typ
			$ValueFormated = $Value;
			self::formatValue($ValueFormated,$Type,$Conn);
			// Konfigurationsfeld in der Datenbank erstellen
			$sSQL = "INSERT INTO tbteaserkonfig 
			(tap_ID,cfg_Type,cfg_Name,".self::getColumn($Type).")
			VALUES ($nTeaserID,$Type,'$Name',$ValueFormated)";
			$nNewID = $Conn->insert($sSQL);
			// Equivalent in Config speichern und Session
			$Config[$Name]['Value'] = $Value;
			$Config[$Name]['Type'] = $Type;
			$Config[$Name]['ID'] = $nNewID;
			$_SESSION['TeaserConfig']['tap'.$nTeaserID] = $Config;
		} else {
			// Konfiguration laden
			$sSQL = "SELECT cfg_ID,cfg_Type,cfg_Numeric,cfg_Name,cfg_Value,cfg_Text 
			FROM tbteaserkonfig WHERE tap_ID = $nTeaserID AND cfg_Name = '$Name'";
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
			$_SESSION['TeaserConfig']['tap'.$nTeaserID] = $Config;
		}
	}
	
	/**
	 * Gesamte Konfiguration in die Datenbank speichern.
	 * @param integer nTeaserID, Teaserkonfiguration die zu speichern ist
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param array Config, Array, welches Konfiguration enthält
	 */
	public static function saveConfig($nTeaserID,dbConn &$Conn,&$Config) {
		// Gesamtes Config Array durchgehen
		foreach ($Config as $Setting) {
			// Value absichern
			self::formatValue($Setting['Value'],$Setting['Type'],$Conn);
			// SQL Erstellen zum updaten
			$sSQL = "UPDATE tbteaserkonfig SET
			".self::getColumn($Setting['Type'])." = ".$Setting['Value']." 
			WHERE tap_ID = $nTeaserID AND cfg_ID = ".$Setting['ID'];
			$Conn->command($sSQL);
		}
		// Das Array wieder in die Session schreiben
		$_SESSION['TeaserConfig']['tap'.$nTeaserID] = $Config;
	}
	
	/**
	 * Schauen ob alle Parameter für die Konfiguration vorhanden sind.
	 * @param integer nTeaserID, Teaserkonfiguration die zu prüfen ist
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param integer Params, Anzahl Konfigurationen die das Menu haben muss
	 */
	public static function hasConfig($nTeaserID,dbConn &$Conn,$Params) {
		$sSQL = "SELECT COUNT(cfg_ID) AS CfgCount 
		FROM tbteaserkonfig WHERE tap_ID = $nTeaserID";
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