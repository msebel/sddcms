<?php
/**
 * Klasse um eine Serviceantwort zu vereinfachen. Generiert automatisch
 * ein mehrdimensionales Konstrukt welches als JSON ausgeliefert wird
 * @author Michael Sebel <michael@sebel.ch>
 */
class serviceResponse {

	/**
	 * Antwortdaten, assoziatives Array, wird mit Add() gefüttert
	 * @var array
	 */
	private $Answer = array();

	/**
	 * Initialisiert ein leeres Antwortobjekt
	 */
	public function __construct() {
		$this->Answer['ServiceResponse'];
	}

	/**
	 * Fügt Daten hinzu
	 * @param string key, Key für den übergebenen Wert
	 * @param mixed value, Datenwert, Assoziatives Array oder primitiver Typ
	 */
	public function Add($key,$value) {
		$this->Answer['ServiceResponse'][$key] = $value;
	}

	/**
	 * Gibt die Antwort in JSON aus
	 */
	public function Send() {
		$this->SendJson();
	}

	/**
	 * Antwort als JSON ausgeben
	 */
	private function SendJson() {
		header('Content-type: text/plain');
		echo json_encode($this->Answer);
	}
}