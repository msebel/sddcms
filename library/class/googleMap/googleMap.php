<?php
/**
 * Implementierung von Google Maps
 * @author Michael Sebel <michael@sebel.ch>
 */
class googleMap implements mapService {
	
	/**
	 * API Key für Google Maps
	 * @var string
	 */
	private $Key = '';
	/**
	 * Zoomfaktor der Karte, default 60%
	 * @var int
	 */
	private $ZoomFactor = 60;
	/**
	 * ID der Karte
	 * @var string
	 */
	private $ID = '';
	/**
	 * URL um Google Sprachressourcen zu laden
	 * @var string
	 */
	private $Language = '';
	/**
	 * Google Maps Code, welcher zusammengestellt wird aus div. Methoden
	 * @var string
	 */
	private $Code = '';
	/**
	 * Interne ID um Routen wiederzuerkennen
	 * @var int
	 */
	private $InternalID = 1;
	/**
	 * Gibt an, ob die Karte zentriert wurde
	 * @var bool
	 */
	private $Centered = false;
	/**
	 * Konfigurationen der Karte
	 * @var array
	 */
	private $Configuration = array();
	/**
	 * Array aller Anfangspunkte [route_id][punkt]
	 * @var array
	 */
	private $RouteStart = array();
	/**
	 * Array aller Vias [route_id]->array([punkt])
	 * @var array
	 */
	private $Via = array();
	/**
	 * Array aller Endpunkte [route_id][punkt]
	 * @var array
	 */
	private $EndRoute = array();
	
	/**
	 * Konstruieren des Objekts
	 */
	public function __construct() {
		// Eindeutige ID generieren
		$this->ID = md5('map'.time());
		// API Key konfigurieren
		$this->Key = option::get('GoogleApiKey');
		// Basiskonfiguration erstellen
		$this->Configuration['MapID'] = $this->ID;
		$this->Configuration['MapClass'] = 'cGoogleMap';
		$this->Configuration['RouteClass'] = 'cGoogleRoute';
		$this->Configuration['ApiUrl'] = 'http://www.google.com/jsapi?key=';
		$this->Configuration['DefaultPlace'] = 'Switzerland';
		$this->Configuration['MapType'] = 'G_PHYSICAL_MAP';
		// Sprache definieren
		switch(page::language()) {
			case LANG_DE: $this->setLanguage('de');	break;
			case LANG_EN: $this->setLanguage('en');	break;
		}
	}
	
	/**
	 * Funktion, welche am Ende den HTML Code zurückgeben soll
	 * @return string HTML output
	 */
	public function output() {
		// Routen erstellen, wenn vorhanden
		for ($i = 1;$i < $this->InternalID;$i++) {
			$this->routeOutput($i);
		}
		
		// Zoomen, wenn keine Routen vorhanden
		if (count($this->StartRoute) == 0) {
			// Zentrieren, wenn nicht geschehen
			if (!$this->Centered) {
				$coord = new googleCoordinate($this->getProperty('DefaultPlace'));
				$this->setCenter($coord);
			}
			// Umrechnen in Google Faktor (0 - 17)
			if ($this->ZoomFactor < 0.0 || $this->ZoomFactor > 100.0) {
				$this->ZoomFactor = 100;
			}
			$nZoom = (int) (($this->ZoomFactor / 100) * 17);
			$this->Code .= 'myMap.setZoom('.$nZoom.');';
		}
		
		// Basic HTML und spezialcode erstellen
		$sHtml = '<div class="'.$this->getProperty('MapClass').'" id="Map'.$this->ID.'"></div>
		<div class="'.$this->getProperty('RouteClass').'" id="Route'.$this->ID.'"></div>
		<script type="text/javascript" src="'.$this->getProperty('ApiUrl').$this->Key.'"></script>
		<script type="text/javascript" src="'.$this->Language.$this->Key.'"></script>
		<script type="text/javascript">
			google.load("maps","2")
			var myMap = null;
			var myRoute, myLatLng, myIcon, myLocation, myMarker;
			function GenerateMap() {
				myMap = new google.maps.Map2(document.getElementById("Map'.$this->ID.'"));
				var topLeft = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(10,40));
				myMap.addControl(new GLargeMapControl(), topLeft);
				myMap.addControl(new GMapTypeControl());
				myMap.setMapType('.$this->Configuration['MapType'].');
				myMap.enableContinuousZoom();
				myMap.enableScrollWheelZoom();
				'.$this->Code.'
			}
			google.setOnLoadCallback(GenerateMap)
		</script>';
		
		return($sHtml);
	}
	
	/**
	 * Sucht nach einem Ort und gibt eine Koordinate zurück
	 * @param string sSearch, Suchanfrage für Kartendienst
	 * @return mapCoordinate Koordinatenobjekt
	 */
	public function search($sSearch) {
		return(new googleCoordinate($sSearch));
	}
	
	/**
	 * Erstellt einen Marker auf der Karte
	 * @param mapCoordinate coord, Koordinate an der der Marker erscheint
	 * @param string sHtml, HTML Code für die Bezeichnung des Markers
	 * @param string sIcon, Link zum Icon des Markers
	 */
	public function addLocation(mapCoordinate $coord,$sHtml = '',$sIcon = '') {
		$nIconWidth = 50;
		$nIconHeight = 50;
		
		// HTML Validieren (Muss alles auf einer Zeile sein
		$sHtml = str_replace("\r\n","",$sHtml);
		$sHtml = str_replace("'","\'",$sHtml);
		
		// Grösse des alternativen Icons herausfinden
		if (strlen($sIcon) > 0) {
			// Basispfad, wenn es kein HTTP File ist
			if (substr($sIcon,0,4) != 'http') {
				$sLocalIcon = BP.$sIcon;
			}
			// Icon mit der grösse erstellen
			$size = getimagesize($sLocalIcon);
			$nIconWidth = $size[0];
			$nIconHeight = $size[1];
			// Icon erstellen oder default, wenn nichts gelesen wurde
			if ($nIconWidth > 0 && $nIconHeight > 0) {
				$this->Code .= '
				mySize = new google.maps.Size('.$nIconWidth.','.$nIconHeight.');
				myIcon = new google.maps.Icon(G_DEFAULT_ICON,\''.$sIcon.'\');
				myIcon.iconSize = mySize;
				myIcon.imageMap = [
					0,0, 
					0,'.$nIconWidth.',
					'.$nIconHeight.','.$nIconWidth.',
					'.$nIconHeight.',0
				];';
			} else {
				$this->Code .= 'myIcon = G_DEFAULT_ICON;';
			}
		} else {
			// Default Icon nehmen
			$this->Code .= 'myIcon = G_DEFAULT_ICON;';
		}
		
		// Location mit Koordinaten erstellen
		if ($coord->getLatitude() > 0 && $coord->getLongitude() > 0) {
			$this->Code .= '
				myLocation = new google.maps.LatLng('.$coord->getLatitude().','.$coord->getLongitude().');
				myMarker = new google.maps.Marker(myLocation,myIcon);
				myMap.addOverlay(myMarker);
				myMarker.bindInfoWindowHtml(\''.$sHtml.'\');	
			';
			// Zentrieren auf diesen Punkt
			$this->setCenter($coord);
		}
	}
	
	/**
	 * Erstellt eine neue Route, auf die man per ID referenzieren kann
	 * @return int ID der Route
	 */
	public function createRoute() {
		$this->Via['Route_'.$this->InternalID] = array();
		return($this->InternalID++);
	}
	
	/**
	 * Startpunkt für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function setStart(mapCoordinate $coord, $nRouteID) {
		$this->StartRoute['Route_'.$nRouteID] = $coord;
	}
	
	/**
	 * Endpunkt für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function setEnd(mapCoordinate $coord, $nRouteID) {
		$this->EndRoute['Route_'.$nRouteID] = $coord;
	}
	
	/**
	 * Via für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function addVia(mapCoordinate $coord, $nRouteID) {
		array_push($this->Via['Route_'.$nRouteID],$coord);
	}
	
	/**
	 * Zoom für die Karte setzen (Default ist 70%)
	 * @param double dZoomfactor, Zoomfaktor zwischen 0 und 100
	 */
	public function setZoom($dZoomfactor) {
		$this->ZoomFactor = $dZoomfactor;
	}
	
	/**
	 * Karte auf einen bestimmten Punkt zentrieren
	 * @param mapCoordinate coord, Koordinatenobjekt
	 */
	public function setCenter(mapCoordinate $coord) {
		if ($coord->getLatitude() > 0 && $coord->getLongitude() > 0) {
			$this->Centered = true;
			$this->Code .= 'myMap.setCenter(new google.maps.LatLng('.$coord->getLatitude().','.$coord->getLongitude().'));';
		}
	}
	
	/**
	 * Konfigurationseinstellung setzen (Name/Value)
	 * @param string sName, Name der Einstellung
	 * @param string sValue, Inhalt der Einstellung
	 */
	public function setProperty($sName,$sValue) {
		$this->Configuration[$sName] = $sValue;
	}
	
	/**
	 * Gibt einen Einstellungswert zurück
	 * @param string sName, Name der gewünschten Einstellung
	 * @return string Wert der Einstellung
	 */
	public function getProperty($sName) {
		return($this->Configuration[$sName]);
	}
	
	/**
	 * Lädt alle Kartenobjekte anhand der Datenbank
	 * @param int nMapID, zu ladende Karte
	 */
	public function load($nMapID,dbConn &$Conn) {
		// Kartendaten laden
		$sSQL = "SELECT map_Zoom FROM tbmap WHERE map_ID = $nMapID";
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) {
			$this->setZoom(getInt($row['map_Zoom']));
		}
		// Alle Locations anzeigen
		$sSQL = "SELECT mlc_Latitude,mlc_Longitude,mlc_Icon,mlc_Html FROM tblocation
		WHERE map_ID = $nMapID AND mlc_Type = ".mapOps::TYPE_LOCATION;
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) {
			$coord = new googleCoordinate('');
			$coord->setLatitude($row['mlc_Latitude']);
			$coord->setLongitude($row['mlc_Longitude']);
			$this->addLocation($coord,$row['mlc_Html'],$row['mlc_Icon']);
		}
		// Routen laden
		$sSQL = "SELECT mrt_ID FROM tbroute WHERE map_ID = $nMapID";
		$nResRt = $Conn->execute($sSQL);
		while ($rowrt = $Conn->next($nResRt)) {
			// Daten über Verbindungstabelle laden
			$sSQL = "SELECT mlc_Latitude, mlc_Longitude, mlc_Type FROM tblocation INNER JOIN
			tbroute_location ON tbroute_location.mlc_ID = tblocation.mlc_ID
			WHERE tbroute_location.mrt_ID = ".$rowrt['mrt_ID']." 
			ORDER BY mlc_Type ASC, mlc_Sortorder ASC";
			$nRes = $Conn->execute($sSQL);
			// Routen ID erstellen
			$nRouteID = $this->createRoute();
			while ($row = $Conn->next($nRes)) {
				// Koordinate erstellen
				$coord = new googleCoordinate('');
				$coord->setLatitude($row['mlc_Latitude']);
				$coord->setLongitude($row['mlc_Longitude']);
				// Zuweisen an Funktion je nach Typ
				switch(getInt($row['mlc_Type'])) {
					case mapOps::TYPE_ROUTESTART: $this->setStart($coord,$nRouteID); break;
					case mapOps::TYPE_ROUTEVIA: $this->addVia($coord,$nRouteID); break;
					case mapOps::TYPE_ROUTEEND: $this->setEnd($coord,$nRouteID); break;
				}
			}
		}
	}
	
	/**
	 * Definiert die Sprach URL
	 */
	private function setLanguage($sLang) {
		$this->Language = 'http://maps.google.com/maps?file=api&v=2&hl='.$sLang.'&key=';
	}
	
	/**
	 * Gibt den Code für eine Route aus
	 * @param int nRouteID, interne ID der Route
	 */
	private function routeOutput($nRouteID) {
		$nLatLangIdx = 0;
		// Koordinaten in ein Array pringen
		$coords = array();
		// Startpunkt
		if ($this->StartRoute['Route_'.$nRouteID] instanceOf googleCoordinate) {
			array_push($coords,$this->StartRoute['Route_'.$nRouteID]);
		}
		// Vias
		foreach ($this->Via['Route_'.$nRouteID] as $coord) {
			if ($coord instanceOf googleCoordinate) {
				array_push($coords,$coord);
			}
		}
		// Endpunkt
		if ($this->EndRoute['Route_'.$nRouteID] instanceOf googleCoordinate) {
			array_push($coords,$this->EndRoute['Route_'.$nRouteID]);
		}
		// JS LatLang Array initialisieren
		$this->Code .= 'myLatLngArr = [];';
		// Array durchgehen und Code herstellen
		foreach ($coords as $coord) {
			$this->Code .= '
			myLatLng = new google.maps.LatLng(
				'.$coord->getLatitude().',
				'.$coord->getLongitude().'
			)
			myLatLngArr['.$nLatLangIdx++.'] = myLatLng;';
		}
		// Route in Google Maps erstellen
		$this->Code .= '
			myRoute = new google.maps.Directions(myMap,document.getElementById(\'Route'.$this->ID.'\'));
			myRoute.loadFromWaypoints(myLatLngArr);
		';
	}
}