<?php
/**
 * Bietet Funktionen zum hantieren mit Den Resourcen in der Session.
 * Wird nur von der Resourcen Klasse und nicht 
 * von weiteren Instanzen direkt verwendet.
 * @author Michael Sebel <michael@sebel.ch>
 */
class sessionRes {
	
	/**
	 * Prüft ob eine bestimmte Ressource schon existiert.
	 * @param integer sResID, ID der Ressource
	 * @param integer sLang, Sprache der Ressource
	 * @return boolean True, wenn die Ressource existiert
	 */
	public static function checkSessionRes($sResID,$sLang) {
		$bReturn = false;
		$sResID = (string) $sResID;
		$sLang  = (string) $sLang;
		if (isset($_SESSION['sessionres'][$sResID][$sLang])) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Ressource aus der Session holen.
	 * @param integer sResID, ID der Ressource
	 * @param integer sLang, Sprache der Ressource
	 * @return string Inhalt der Ressource
	 */
	public static function getSessionRes($sResID,$sLang) {
		$sResID = (string) $sResID;
		$sLang  = (string) $sLang;
		return($_SESSION['sessionres'][$sResID][$sLang]);
	}
	
	/**
	 * Ressource in die Session einfüllen.
	 * @param integer sResID, ID der Ressource
	 * @param integer sLang, Sprache der Ressource
	 * @param string sResource, Inhalt der Ressour
	 */
	public static function addSessionRes($sResID,$sLang,$sResource) {
		$sResID = (string) $sResID;
		$sLang  = (string) $sLang;
		$_SESSION['sessionres'][$sResID][$sLang] = $sResource;
	}
}