<?php 
/**
 * Klasse für einfache Sessionkonfiguration.
 * @author Michael Sebel <michael@sebel.ch>
 */
class sessionConfig {
	
	/**
	 * Session Konfigurations Variable setzen
	 * @param string $Name, Name der Konfigurationsvariable
	 * @param mixed $Value, Inhalt der Konfiguration
	 * @param int $nMenuID, Alternative Menu ID, sonst aktuelle
	 */
	public static function set($Name,$Value,$nMenuID = 0) {
		if ($nMenuID == 0) $nMenuID = page::menuID();
		if (!isset($_SESSION['SessionConfig'])) {
			$_SESSION['SessionConfig'] = array();
		}
		$_SESSION['SessionConfig'][$nMenuID.'_'.$Name] = $Value;
	}
	
	/**
	 * Session Variable auslesen.
	 * Wenn Sie ungesetzt ist, wird der Standardwert Value eingesetzt.
	 * @param string $Name, Name der Konfigurationsvariable
	 * @param mixed $Value, Inhalt der Konfiguration (default Wert)
	 * @param int $nMenuID, Alternative Menu ID, sonst aktuelle
	 * @return mixed Inhalt der Konfiguration
	 */
	public static function get($Name,$Value,$nMenuID = 0) {
		if ($nMenuID == 0) $nMenuID = page::menuID();
		if (!isset($_SESSION['SessionConfig'])) {
			$_SESSION['SessionConfig'] = array();
		}
		if (!isset($_SESSION['SessionConfig'][$nMenuID.'_'.$Name])) {
			$_SESSION['SessionConfig'][$nMenuID.'_'.$Name] = $Value;
		}
		return($_SESSION['SessionConfig'][$nMenuID.'_'.$Name]);
	}
}