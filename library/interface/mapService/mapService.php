<?php
/**
 * Interface für alle Kartenfeatures
 * @author Michael Sebel <michael@sebel.ch>
 */
interface mapService {
	
	/**
	 * Funktion, welche am Ende den HTML Code zurückgeben soll
	 * @return string HTML output
	 */
	public function output();
	
	/**
	 * Sucht nach einem Ort und gibt eine Koordinate zurück
	 * @param string sSearch, Suchanfrage für Kartendienst
	 * @return mapCoordinate Koordinatenobjekt
	 */
	public function search($sSearch);
	
	/**
	 * Erstellt einen Marker auf der Karte
	 * @param mapCoordinate coord, Koordinate an der der Marker erscheint
	 * @param string sHtml, HTML Code für die Bezeichnung des Markers
	 * @param string sIcon, Link zum Icon des Markers
	 */
	public function addLocation(mapCoordinate $coord,$sHtml = '',$sIcon = '');
	
	/**
	 * Erstellt eine neue Route, auf die man per ID referenzieren kann
	 * @return int ID der Route
	 */
	public function createRoute();
	
	/**
	 * Startpunkt für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function setStart(mapCoordinate $coord, $nRouteID);
	
	/**
	 * Endpunkt für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function setEnd(mapCoordinate $coord, $nRouteID);
	
	/**
	 * Via für eine Route festlegen
	 * @param mapCoordinate coord, Koordinatenobjekt
	 * @param int nRouteID, Zugehörende Route
	 */
	public function addVia(mapCoordinate $coord, $nRouteID);
	
	/**
	 * Zoom für die Karte setzen (Default ist 70%)
	 * @param double dZoomfactor, Zoomfaktor zwischen 0 und 100
	 */
	public function setZoom($dZoomfactor);
	
	/**
	 * Karte auf einen bestimmten Punkt zentrieren
	 * @param mapCoordinate coord, Koordinatenobjekt
	 */
	public function setCenter(mapCoordinate $coord);
	
	/**
	 * Konfigurationseinstellung setzen (Name/Value)
	 * @param string sName, Name der Einstellung
	 * @param string sValue, Inhalt der Einstellung
	 */
	public function setProperty($sName,$sValue);
	
	/**
	 * Gibt einen Einstellungswert zurück
	 * @param string sName, Name der gewünschten Einstellung
	 * @return string Wert der Einstellung
	 */
	public function getProperty($sName);
	
	/**
	 * Lädt alle Kartenobjekte anhand der Datenbank
	 * @param int nMapID, zu ladende Karte
	 */
	public function load($nMapID,dbConn &$Conn);
	
}