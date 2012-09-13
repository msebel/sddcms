<?php
/**
 * Funktionen zum arbeiten mit den Mapping Tabellen
 * @author Michael Sebel <michael@sebel.ch
 */
class mapOps {
	
	/**
	 * Typ: Locations
	 * @var int
	 */
	const TYPE_LOCATION = 1;
	/**
	 * Typ: Routenstart
	 * @var int
	 */
	const TYPE_ROUTESTART = 2;
	/**
	 * Typ: Via für Routen
	 * @var int
	 */
	const TYPE_ROUTEVIA = 3;
	/**
	 * Typ: Routenende
	 * @var int
	 */
	const TYPE_ROUTEEND = 4;
	
	/**
	 * Erstellt eine Karte mit Menuverbindung
	 * @param int nMenuID, Zu verbindendes Menu
	 * @param dbConn Conn, Datenbankverbindung
	 * @return int ID der erstellten Karte
	 */
	public static function addMapWithMenu($nMenuID,dbConn &$Conn) {
		$nMenuID = getInt($nMenuID);
		// Karte erstellen
		$nMapID = self::addMap($Conn,'Map for Menu '.$nMenuID);
		// Höchsten Sortorder für dieses Menu holen
		$sSQL = "SELECT IFNULL(MAX(mam_Sortorder),0) 
		FROM tbmap_menu WHERE mnu_ID = $nMenuID";
		$nMaxSort = getInt($Conn->getFirstResult($sSQL))+1;
		// Verbindung zum Menu erstellen
		$sSQL = "INSERT INTO tbmap_menu (mnu_ID,map_ID,mam_Sortorder) 
		VALUES ($nMenuID,$nMapID,$nMaxSort)";
		$Conn->command($sSQL); 
		return($nMapID);
	}
	
	/**
	 * Erstellt eine neue Karte und gibt die ID zurück
	 * @param dbConn Conn, Datenbankverbindung
	 * @param string sName, Name der Karte (Optional)
	 * @return int ID der erstellten Karte
	 */
	public static function addMap(dbConn &$Conn,$sName = '') {
		$Conn->escape($sName);
		$sSQL = "INSERT INTO tbmap (map_Class,map_Name,map_Zoom) VALUES ('','$sName',70)";
		return($Conn->insert($sSQL));
	}
	
	/**
	 * Fügt eine Location hinzu und assoziiert mit der gegebenen Karte
	 * @param dbConn Conn, Datenbankverbindung
	 * @param int nMapID Zu verbindende Karte
	 * @param int nType Einer der vier Maptypen
	 * @return int ID der erstellten Location
	 */
	public static function addLocation(dbConn &$Conn,$nMapID,$nType) {
		$nMapID = getInt($nMapID);
		$nType = getInt($nType);
		$sSQL = "INSERT INTO tblocation (map_ID,mlc_Type) VALUES ($nMapID,$nType)";
		return($Conn->insert($sSQL));
	}
	
	/**
	 * Fügt eine Route hinzu und assoziiert mit der gegebenen Karte
	 * @param dbConn Conn, Datenbankverbindung
	 * @param int nMapID Zu verbindende Karte
	 * @return int ID der erstellten Route
	 */
	public static function addRoute(dbConn &$Conn,$nMapID) {
		$nMapID = getInt($nMapID);
		$sSQL = "INSERT INTO tbroute (map_ID) VALUES ($nMapID)";
		return($Conn->insert($sSQL));
	}
	
	/**
	 * Erstellt eine Routen/Location Verbindung
	 * @param dbConn Conn Datenbankverbindung
	 * @param int nRouteID ID der Route
	 * @param int nStartID ID der Location
	 * @return int ID der Verbindung
	 */
	public static function addRouteLocationConnection(dbConn &$Conn,$nRouteID,$nStartID) {
		$nRouteID = getInt($nRouteID);
		$nStartID = getInt($nStartID);
		$sSQL = "INSERT INTO tbroute_location (mrt_ID,mlc_ID) VALUES ($nRouteID,$nStartID)";
		$Conn->command($sSQL);
	}
	
	/**
	 * Speichert die gegebenen Felder in eine Location
	 * @param int nID, ID der Location
	 * @param array fields, Zu speichernde Felder
	 */
	public static function saveLocation($nID,$fields,dbConn &$Conn) {
		$sSQL = "UPDATE tblocation SET ";
		self::addFields($sSQL,$fields);
		// SQL auf ID Einschränken und abfeuern
		$sSQL .= " WHERE mlc_ID = $nID";
		$Conn->command($sSQL);
	}
	
	/**
	 * Speichert die gegebenen Felder in eine Route
	 * @param int nID, ID der Location
	 * @param array fields, Zu speichernde Felder
	 */
	public static function saveRoute($nID,$fields,dbConn &$Conn) {
		$sSQL = "UPDATE tbroute SET ";
		self::addFields($sSQL,$fields);
		// SQL auf ID Einschränken und abfeuern
		$sSQL .= " WHERE mrt_ID = $nID";
		$Conn->command($sSQL);
	}
	
	/**
	 * Löscht eine einzelne Location
	 * @param int nMlcID, ID der Location
	 */
	public static function deleteLocation($nMlcID,dbConn &$Conn) {
		$nMlcID = getInt($nMlcID);
		$sSQL = "DELETE FROM tblocation WHERE mlc_ID = $nMlcID";
		$Conn->command($sSQL);
	}
	
	/**
	 * Entfernt eine Location und deren Routenverbindung
	 * @param int nDeleteID, zu löschende Location
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public static function deleteRouteLocation($nDeleteID,dbConn &$Conn) {
		// Location löschen
		self::deleteLocation($nDeleteID,$Conn);
		// Verbindung löschen
		$sSQL = "DELETE FROM tbroute_location WHERE mlc_ID = $nDeleteID";
		$Conn->command($sSQL);
	}
	
	/**
	 * Löscht eine einzelne Route
	 * @param int nMrtID, ID der Route
	 */
	public static function deleteRoute($nMrtID,dbConn &$Conn) {
		$nMrtID = getInt($nMrtID);
		// Über Verbindungen die Locations löschen
		$sSQL = "SELECT mrl_ID,mlc_ID FROM tbroute_location WHERE mrt_ID = $nMrtID";
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			// Location löschen
			self::deleteLocation($row['mlc_ID'],$Conn);
			// Verbindung löschen
			$sSQL = "DELETE FROM tbroute_location WHERE mrl_ID = ".$row['mrl_ID'];
			$Conn->command($sSQL);
		}
		// Am Ende die Route selbst löschen
		$sSQL = "DELETE FROM tbroute WHERE mrt_ID = $nMrtID";
		$Conn->command($sSQL);
	}
	
	/**
	 * Lädt eine Location aus der Datenbank
	 * @param int nMlcID, ID der Location
	 * @return array Daten der Location
	 */
	public static function loadLocation($nMlcID,dbConn &$Conn) {
		$Data = NULL;
		// Daten suchen
		$sSQL = "SELECT mlc_ID,map_ID,mlc_Type,mlc_Sortorder,mlc_Name,mlc_Latitude,
		mlc_Longitude, mlc_Query,mlc_Icon,mlc_Html FROM tblocation WHERE mlc_ID = $nMlcID";
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) $Data = $row;
		// Daten zurückgeben
		return($Data);
	}
	
	/**
	 * Lädt alle Locations der genannten Route
	 * @param int nMrtID, ID der zu ladenden Route
	 * @param dbConn Conn, Datenbankverbindung
	 * @return array Liste der Locations
	 */
	public static function loadRouteLocations($nMrtID,dbConn &$Conn) {
		$Data = array();
		$Res = getResources::getInstance($Conn);
		$nMrtID = getInt($nMrtID);
		$sSQL = "SELECT tblocation.mlc_ID,map_ID,mlc_Type,mlc_Sortorder,mlc_Name,
		mlc_Latitude,mlc_Longitude, mlc_Query,mlc_Icon,mlc_Html FROM tblocation
		INNER JOIN tbroute_location ON tbroute_location.mlc_ID = tblocation.mlc_ID
		WHERE mrt_ID = $nMrtID ORDER BY mlc_Type ASC, mlc_Sortorder ASC";
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			// Wenn leer, "Neuer Eintrag" rein schreiben
			if (strlen($row['mlc_Name']) == 0) {
				$row['mlc_Name'] = '< '.$Res->html(425,page::language()).' >';
			}
			array_push($Data,$row);
		}
		return($Data);
	}
	
	/**
	 * Erstellt assoziative Arrays aus den primitiven Routendaten
	 * @param array RouteData, primitive Routen Daten
	 * @param array Data, Array in welches die verständlichen Daten kommen
	 */
	public function prepareRouteArray($RouteData, &$Data) {
		$nEntries = count($RouteData);
		// Start und Ziel definieren (Wie Location Übersicht)
		$Data['start'] = $RouteData[0];
		$Data['goal'] = $RouteData[$nEntries-1];
		// Vias definieren (Wie Location Übersicht)
		$Data['vias'] = array();
		if ($nEntries > 2) {
			for ($i = 1;$i < ($nEntries-1);$i++) {
				array_push($Data['vias'],$RouteData[$i]);
			}
		}
	}
	
	/**
	 * Korrigiert die Sortierung der Routenlocations
	 * @param int nMrtID, ID der zu sortierenden Route
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public static function fixRouteorder($nMrtID,dbConn &$Conn) {
		// Gesamte Route laden
		$Route = array();
		$RouteData = self::loadRouteLocations($nMrtID,$Conn);
		self::prepareRouteArray($RouteData,$Route);
		// Sortorder für Start / Ende definieren
		$Route['start']['mlc_Sortorder'] = 1;
		$Route['goal']['mlc_Sortorder'] = count($Route['vias']) + 2;
		// Vias sortieren / Durchgehen
		usort($Route['vias'], 'sortViasByOrder');
		for ($i = 0;$i < count($Route['vias']);$i++) {
			$Route['vias'][$i]['mlc_Sortorder'] = ($i+2);
		}
		return($Route);
	}
	
	/**
	 * Speichert ein Routenarray, speichert aber nur übersichtsfelder.
	 * Diese sind: mlc_Name, mlc_Sortorder
	 * @param array Route, Routenarray mit 'prepareRouteArray' behandelt
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public static function saveRouteArray(&$Route,dbConn &$Conn) {
		// Speichern von Start / Ziel
		self::saveLocationShort($Route['start'],$Conn);
		self::saveLocationShort($Route['goal'],$Conn);
		// Speichern der Vias
		foreach ($Route['vias'] as $Location) {
			self::saveLocationShort($Location,$Conn);
		}
	}
	
	/**
	 * Speichert von der gegebenen Location den Sortorder und den Namen
	 * @param array Location, Array mit Location Daten
	 * @param dbConn Conn, Datenbankverbindung
	 */
	public static function saveLocationShort($Location,dbConn &$Conn) {
		$Fields = array();
		// Sortorder als Zahl validieren und hinzufügen
		$Fields['mlc_Sortorder'] = getInt($Location['mlc_Sortorder']);
		// Namen als String validieren und hinzufügen
		$sName = $Location['mlc_Name'];
		$Conn->escape($sName);
		$Fields['mlc_Name'] = "'$sName'";
		// Speicherfunktion durchführen
		self::saveLocation($Location['mlc_ID'],$Fields,$Conn);
	}
	
	/**
	 * Fügt ein Speicherbares Feld hinzu
	 * @param string name, Name des Feldes
	 * @param mixed value, Inhalt des Feldes
	 * @param array fields, Array aller Felder
	 */
	public static function addSaveable($name,$value,&$fields) {
		$fields[$name] = $value;
	}
	
	/**
	 * Fügt die speicherbaren Felder in den SQL String ein
	 * @param string sSQL, Zu verarbeitender SQL String
	 * @param array fields, Zu speichernde Felder
	 */
	private static function addFields(&$sSQL,$fields) {
		// Felder einfügen
		foreach ($fields as $key => $value) {
			$sSQL .= "$key = $value,";
		}
		// Letztes Komma entfernen
		$sSQL = substr($sSQL,0,strlen($sSQL)-1);
	}
}

/**
 * Sortiert ein Array anhand des Sortorder.
 * Sortiert normal aufsteigen, nur Vias mit 0
 * kommen ganz am Ende
 * @param array a, Routeninformationen (Location)
 * @param array b, Routeninformationen (Location)
 * @return Zahl für usort Sortierung
 */
function sortViasByOrder($a,$b) {
	$nA = getInt($a['mlc_Sortorder']);
	$nB = getInt($b['mlc_Sortorder']);
	$nReturn = ($nA > $nB) ? +1 : -1;
	if ($nA == 0) $nReturn = +1;
	if ($nB == 0) $nReturn = -1;
	return $nReturn;
}