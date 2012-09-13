<?php
/**
 * Definiert einen Container für eine Verlinkung
 * der aktuellen Seite mit sozialen Netzwerken
 */
interface socialBookmark {
	
	/**
	 * Gibt eine Verlinkung zum sozialen Netzwerk an
	 * @param string $sURL, URL die verlinkt wird
	 * @param string $sTitle, Titel für die URL (optional)
	 * @return string HTML Code mit Verlinkung
	 */
	public function get($sURL,$sTitle);
}