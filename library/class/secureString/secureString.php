<?php
/**
 * Bietet Funktionen um Sichere Strings zu generieren.
 * Derzeit wird die Klasse nur für das setzen von Passwörter
 * in der Usertabelle benutzt.
 * @author Michael Sebel <michael@sebel.ch
 */
class secureString {
	
	/**
	 * Einen Security String aus Passwort und Benutzername generieren.
	 * @param string sPass, Passwort des Users
	 * @param string sUser, Name des Users
	 * @return string Neuer SecurityString
	 */
	public static function getSecurityString($sPass,$sUser) {
		$sHash = '';
		// Passwort anhängen
		$sHash .= md5($sPass);
		// Benutzername anhängen
		$sHash .= md5($sUser);
		// Salzzusatz generieren
		$sSalt = substr($sUser,1,3);
		$sSaltHash = substr($sSalt,10,16);
		$sHash .= $sSaltHash;
		return($sHash);
	}
	
	/**
	 * Einem bestehenden String den Alias hinzufügen.
	 * @param string sSecurity, bestehender String
	 * @param string sAlias, Einzufügender Alias Salt
	 * @return string Neuer SecurityString
	 */
	public static function insertNewAlias($sSecurity,$sAlias) {
		$sHash = '';
		// Passwort extrahieren
		$sPassword = substr($sSecurity,0,32);
		$sHash .= $sPassword;
		// Benutzername anhängen
		$sHash .= md5($sAlias);
		// Salzzusatz generieren
		$sSalt = substr($sAlias,1,3);
		$sSaltHash = substr($sSalt,10,16);
		$sHash .= $sSaltHash;
		return($sHash);
	}
}