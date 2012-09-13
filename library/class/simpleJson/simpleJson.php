<?php
/**
 * TODO simpleJson: UTF8 entfernen wenn umstellung erfolgt
 * Klasse um einfache (eindimensionale Json Abfragen zu generieren
 * @author Michael Sebel <michael@sebel.ch>
 */
class simpleJson {

	/**
	 * Assoziative JSON Daten
	 * @var array
	 */
	private $Json = array();

	/**
	 * Initialisiert das Objekt
	 */
	public function __construct() {

	}

	/**
	 * Fügt einen Wert hinzu und kodiert diesen zu UTF8
	 * @param string $key Schlüssel für Zugriff
	 * @param string $value Datenwert
	 */
	public function addValue($key,$value) {
		$this->Json[$key] = utf8_encode($value);
	}

	/**
	 * Gibt die Daten unter dekodiertung von UTF8 wieder aus
	 */
	public function output() {
		echo utf8_decode(json_encode($this->Json));
	}
}
