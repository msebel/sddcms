<?php
/**
 * Klasse zur Vereinfachung des Zugriffs auf die Page Session, nur lese Klasse.
 * @author Michael Sebel <michael@sebel.ch>
 */
class page {
		
	/**
	 * Zurückgeben, ob die Page Session existiert.
	 * @return boolean true wenn die Seitensession existiert
	 */
	public static function exists() {
		return (isset($_SESSION['page']));
	}
	
	/**
	 * Zurückgeben ob der Mandant gewechselt werden soll.
	 * @return boolean true wenn Mandant gewechselt werden soll
	 */
	public static function changeMandant() {
		return (isset($_GET['changeMandant']));
	}
	
	/**
	 * Validierten Mandanten speichern.
	 * @param integer nManID, ID des zu setzenden Mandanten
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public static function setMandant($nManID,dbConn &$Conn) {
		$_SESSION['page']['mandant'] = $nManID;
		// Sprache dieses Mandanten holen
		$sSQL = 'SELECT man_Language FROM tbmandant WHERE man_ID = '.$nManID;
		$nRes = $Conn->execute($sSQL);
		// Standard Deutsch
		$nLanguage = LANG_DE;
		while ($row = $Conn->next($nRes)) {
			$nLanguage = $row['man_Language'];
		}
		$_SESSION['page']['language'] = $nLanguage;
	}
	
	/**
	 * @return string Name der Domain der Webseite (www.example.com)
	 */
	public static function domain() {
		return($_SESSION['page']['domain']);
	}
	
	/**
	 * @return string Titel der Webseite
	 */
	public static function title() {
		return($_SESSION['page']['title']);
	}
	
	/**
	 * @return integer ID des Designs der Webseite
	 */
	public static function design() {
		return($_SESSION['page']['design']);
	}
	
	/**
	 * @return integer ID des alternative Admindesigns
	 */
	public static function admindesign($fallback = false) {
		if ($fallback && $_SESSION['page']['admindesign'] == 0) {
			return($_SESSION['page']['design']);
		} else {
			return($_SESSION['page']['admindesign']);
		}
	}
	
	/**
	 * @param dbConn Referenz zum Datenbankobjekt
	 * @return interger ID des Designs der Webseite von Datenbank
	 */
	public static function originaldesign(&$Conn) {
		// Von der Session holen, wenn schon mal geladen
		if (isset($_SESSION['page']['originaldesign'])) {
			return($_SESSION['page']['originaldesign']);
		} else {
			$sSQL = "SELECT design_ID FROM tbpage
			WHERE page_ID = ".page::id();
			$nDesign = $Conn->getFirstResult($sSQL);
			$_SESSION['page']['originaldesign'] = $nDesign;
			return($nDesign);
		}
	}
	
	/**
	 * @return integer ID des Website besitzenden Mandanten
	 */
	public static function mandant() {
		return($_SESSION['page']['mandant']);
	}
	
	/**
	 * @return integer ID der Webseite selbst
	 */
	public static function id() {
		return($_SESSION['page']['id']);
	}
	
	/**
	 * @return integer ID des Standardmandanten der Pages
	 */
	public static function standardmandant() {
		return($_SESSION['page']['standardmandant']);
	}
	
	/**
	 * @return string Absoluter Pfad zu einem Startseiten Template
	 */
	public static function individual() {
		return($_SESSION['page']['individual']);
	}
	
	/**
	 * @return string Name der Webseite
	 */
	public static function name() {
		return($_SESSION['page']['name']);
	}
	
	/**
	 * @return integer Menu ID der Startseite dieser Page
	 */
	public static function start() {
		return($_SESSION['page']['start']);
	}
	
	/**
	 * @return integer ID der Administratorengruppe
	 */
	public static function admingroup() {
		return($_SESSION['page']['admingroup']);
	}
	
	/**
	 * @return integer ID der Sprache der Webseite
	 */
	public static function language() {
		return($_SESSION['page']['language']);
	}
	
	/**
	 * @return string Description der Webseite für Meta Tag
	 */
	public static function metadesc() {
		return($_SESSION['page']['metadesc']);
	}
	
	/**
	 * @return string Keywords der Webseite für Meta Tag
	 */
	public static function metakeys() {
		return($_SESSION['page']['metakeys']);
	}
	
	/**
	 * @return string Name des Autoren der Webseite
	 */
	public static function author() {
		return($_SESSION['page']['author']);
	}
	
	/**
	 * @return string Google-v1 Verify Tag
	 */
	public static function verify() {
		return($_SESSION['page']['verify']);
	}
	
	/**
	 * @return integer 0 = Webseite ist aktiv, 1 = Webseite ist deaktiviert
	 */
	public static function inactive() {
		return($_SESSION['page']['inactive']);
	}
	
	/**
	 * @return integer ID des Standard Teasers der Webseite
	 */
	public static function teaserID() {
		return(getInt($_SESSION['page']['teaserID']));
	}
	
	/**
	 * @return integer Breite des Contents ohne Teaser
	 */
	public static function allwidth() {
		return(getInt($_SESSION['page']['allwidth']));
	}
	
	/**
	 * @return integer Breite des Contents mit Teaser
	 */
	public static function contentwidth() {
		return(getInt($_SESSION['page']['contentwidth']));
	}
	
	/**
	 * @return integer Aktuelle Menu ID
	 */
	public static function menuID() {
		return(getInt($_GET['id']));
	}
}