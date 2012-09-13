<?php
/**
 * Interface für Kartenkoordinaten
 * @author Michael Sebel <michael@sebel.ch>
 */
interface mapCoordinate {
	
	/**
	 * Erstellt das Objekt und sucht Koordinaten
	 * @param string sSearch, Suchanfrage an Kartendienst
	 */
	public function __construct($sSearch);
	
	/**
	 * Lat-Koordinate zurück bekommen
	 * @return double Koordinate
	 */
	public function getLatitude();
	
	/**
	 * Lng-Koordinate zurück bekommen
	 * @return double Koordinate
	 */
	public function getLongitude();
	
}