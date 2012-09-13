<?php
/**
 * Koordinatenimplementation von Google Maps
 * @author Michael Sebel <michael@sebel.ch>
 */
class googleCoordinate implements mapCoordinate {
	
	/**
	 * API Schlüssel für die Google Suche
	 * @var string
	 */
	private $Key = '';
	/**
	 * Latitude der Koordinate
	 * @var double
	 */
	private $Latitude = 0.0;
	/**
	 * Longitude der Koordinate
	 * @var double
	 */
	private $Longitude = 0.0;
	
	/**
	 * Erstellt das Objekt und sucht Koordinaten
	 * @param string sSearch, Suchanfrage an Kartendienst
	 */
	public function __construct($sSearch,$long = 0.0,$lat = 0.0) {
		$this->Key = option::get('GoogleApiKey');
		if (strlen($sSearch) > 0) {
			$this->search($sSearch);
		} else {
			// Sonst koodrinaten setzen, wenn vorhanden
			$this->Latitude = $lat;
			$this->Longitude = $long;
		}
	}
	
	/**
	 * Lat-Koordinate zurück bekommen
	 * @return double Koordinate
	 */
	public function getLatitude() {
		return($this->Latitude);
	}
	
	/**
	 * Lng-Koordinate definieren
	 * @return double Koordinate
	 */
	public function setLongitude($nLng) {
		$this->Longitude = $nLng;
	}
	
	/**
	 * Lat-Koordinate definieren
	 * @return double Koordinate
	 */
	public function setLatitude($nLat) {
		$this->Latitude = $nLat;
	}
	
	/**
	 * Lng-Koordinate zurück bekommen
	 * @return double Koordinate
	 */
	public function getLongitude() {
		return($this->Longitude);
	}
	
	/**
	 * Suchen einer Koordinate anhand der Suche
	 * @param string sSearch Suchanfrage an Dienst
	 */
	private function search($sSearch) {
		// Koordinaten initialisieren
		$this->Longitude = 0.0;
		$this->Latitude = 0.0;
		// Parameter HTML kodieren, dann URL kodieren
		$sSearch = urlencode(stringOps::htmlEnt($sSearch));
		// URL mit Anfrage vorbereiten
		$sUrl = 'http://maps.google.com/maps/geo?q='.$sSearch.'&output=xml'.
		'&oe=utf8&sensor=false&key='.$this->Key;
		try {
			$Answer = simplexml_load_file($sUrl);
			if (getInt($Answer->Response->Status->code) == 200) {
				$coords = explode(',',(string)$Answer->Response->Placemark->Point->coordinates);
				$this->Longitude = $coords[0];
				$this->Latitude = $coords[1];
			}
		} catch (Exception $e) {
			logging::error('Fehler beim suchen nach Koordinaten in Google Maps');
		}
	}
}