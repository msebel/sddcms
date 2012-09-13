<?php
/**
 * Interface Teaser.
 * - Definiert wie ein Teaser min. aufgebaut sein muss
 * - Konstruktur kann nach eigenem ermessen definiert werden
 * @author Michael Sebel <michael@sebel.ch>
 */
interface teaser {
	
	/**
	 * HTML Code für das Teaserelement generieren.
	 * - fügt der Output Variable den HTML Code des Teasers an
	 * - wird direkt nach setID aufgerufen
	 * - stellt den gesamten Teaserinhalt zur Verfügung
	 * - kann dafür beliebige private Funktionen nutzen
	 * @param string out, Output variable in die HTML angehängt wird
	 */
	public function appendHtml(&$out);
	
	/**
	 * definieren ob ein Output daherkommen wird.
	 */
	public function hasOutput();
	
	/**
	 * setzt die Teaser Element ID für die Verarbeitung.
	 * @param integer ID eines Teasereintrages
	 */
	public function setID($tapID);
	/**
	 * Daten für den Teaser setzen.
	 * @param dbConn Conn, Datenbank Objekt
	 * @param resources Res, Sprachressourcen Objekt
	 * @param string Title, Titel des Teasereintrags
	 */
	public function setData(dbconn &$Conn,resources &$Res,&$Title);
}