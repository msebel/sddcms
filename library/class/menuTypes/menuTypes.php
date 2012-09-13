<?php
/**
 * Verarbeitet Menutypen.
 * Zudem bietet die Klasse Funktionen um den
 * View oder Admintyp zu definieren
 * @TODO Switch Ding mit Datenbank lösen
 * @author Michael Sebel <michael@sebel.ch>
 */
class menuTypes {
	
	/**
	 * Referenz zum Datenbank Objekt
	 * @var dbConn
	 */
	private $Conn = NULL;
	/**
	 * Referenz zum Zugriffsobjekt
	 * @var access
	 */
	private $Access = NULL;
	/**
	 * Array der Menutyp Datenzeilen
	 * @var array
	 */
	private $Types = array();
	/**
	 * Gibt an ob Typen geladen sind
	 * @var boolean
	 */
	private $TypesLoaded = false;
	
	const ADMIN_START 	= 1;	// Startseite des Admin
	const ADMIN_MENU 	= 2;	// Menuverwaltung
	const ADMIN_USER 	= 3;	// Benutzerverwaltung
	const ADMIN_GROUP 	= 4;	// Gruppenverwaltung
	const ADMIN_PAGE 	= 6;	// Seitenverwaltung
	// Standardmodule
	const CONTENT 		= 100;	// Gewöhnlicher Inhalt
	const LOGIN 		= 101;	// Loginseite
	// Spezialtypen
	const LINK_INTERNAL	= 50;	// Interne Weiterleitung
	const LINK_EXTERNAL	= 51;	// Externe Weiterleitung
	
	// Sonstige Konstanten
	const ADMIN_LOGON	= 1;	// Login als Admin
	const ANONYMOUS		= 0;	// Login als User oder Anonym
	
	/**
	 * Menutypen Objekt erstellen
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param access Access, Zugriffsobjekt
	 */
	public function __construct(dbConn &$Conn, access &$Access) {
		$this->Conn = $Conn;
		$this->Access = $Access;
	}
	
	/**
	 * Checken ob ein Menu im Admin/View nicht angezeigt werden soll
	 * @param integer nType, Menutyp der zu prüfen ist
	 * @return boolean True, wenn Menupunkt anzeigbar
	 */
	public function checkOneSideType($nType) {
		$bResult = true; // Menutyp grundsätzlich ok
		// Je nach Loginart
		switch ($this->Access->getAccessType()) {
			case self::ADMIN_LOGON:
				// View only Punkte prüfen
				$bResult = $this->checkViewOnly($nType);	break;
			case self::ANONYMOUS:
				// Admin only Punkte prüfen
				$bResult = $this->checkAdminOnly($nType);	break;
			default:
				// Admin only Punkte prüfen
				$bResult = $this->checkAdminOnly($nType);	break;
		}
		return($bResult);
	}
	
	/**
	 * Schauen ob ein Menupunkt im Admin nicht angezeigt wird
	 * @param integer nType, Menupunkt zu prüfen
	 * @return boolean True, wenn Menupunkt anzeigbar
	 */
	public function checkViewOnly ($nType) {
		$bShow = true; $bFound = false;
		// Zuerst alle bekannten Menutypen switchen
		switch ($nType) {
			case typeID::MENU_ADMINSTART: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_MENUADMIN: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_USERADMIN: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GROUPADMIN: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_PAGEADMIN: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_FILELIBRARY: 		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_LOGVIEW: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_DIRECTLINK:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_CACHE:		$bFound = true;		$bShow = true; break;
			case self::LINK_INTERNAL: 			$bFound = true; 	$bShow = true;	break;
			case self::LINK_EXTERNAL: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_CONTENT: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_LOGIN: 			$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_GUESTBOOK:		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_NEWS:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GALLERY:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_FAQ:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GLOSSARY:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_LINK:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_UPDATES:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_CALENDAR:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_BLOGADMIN:		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_BLOGCATEGORY:		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_BLOGRECENT:		$bFound = true; 	$bShow = true;	break;	
			case typeID::MENU_BLOGOVERVIEW:		$bFound = true; 	$bShow = true;	break;	
			case typeID::MENU_CENTRALCONTENT:	$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOWCENTRAL:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_FINDUS:			$bFound = true;		$bShow = true;	break;
			case typeID::MENU_LOCATION:			$bFound = true;		$bShow = true;	break;
			case typeID::MENU_FILEEXCHANGE:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_WIKI:				$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_GROUPS:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_ARTICLES:	$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_STARTPAGE:	$bFound = true;		$bShow = true;	break;
		}
		// Wenn noch nicht gefunden, lokale Menutypen abfragen
		if ($bFound == false) {
			// Menutypen laden, wenn noch nicht vorhanden
			if ($this->TypesLoaded == false) {
				$this->loadTypes();
				$this->TypesLoaded = true;
			}
			// Menutypen durchgehen
			foreach ($this->Types as $row) {
				if ($row['typ_ID'] == $nType && strlen($row['typ_Viewpath']) == 0) {
					$bFound = true;
				}
			}
		}
		return($bShow);
	}
	
	/**
	 * Schauen ob ein Menupunkt in der View nicht angezeigt wird
	 * @param integer nType, Menupunkt zu prüfen
	 * @return boolean True, wenn Menupunkt anzeigbar
	 */
	public function checkAdminOnly ($nType) {
		$bShow = true; $bFound = false;
		// Zuerst alle bekannten Menutypen switchen
		switch ($nType) {
			case typeID::MENU_ADMINSTART: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_MENUADMIN: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_USERADMIN: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_GROUPADMIN: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_PAGEADMIN: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_FILELIBRARY: 		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_LOGVIEW: 			$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_DIRECTLINK:		$bFound = true;		$bShow = false; break;
			case typeID::MENU_CACHE:		$bFound = true;		$bShow = false; break;
			case self::LINK_INTERNAL: 			$bFound = true; 	$bShow = true;	break;
			case self::LINK_EXTERNAL: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_CONTENT: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_LOGIN: 			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GUESTBOOK:		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_NEWS:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GALLERY:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_FAQ:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_GLOSSARY:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_LINK:				$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_UPDATES:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_CALENDAR:			$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_BLOGADMIN:		$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_BLOGCATEGORY:		$bFound = true; 	$bShow = true;	break;
			case typeID::MENU_BLOGRECENT:		$bFound = true; 	$bShow = true;	break;	
			case typeID::MENU_BLOGOVERVIEW:		$bFound = true; 	$bShow = true;	break;	
			case typeID::MENU_CENTRALCONTENT:	$bFound = true; 	$bShow = false;	break;
			case typeID::MENU_SHOWCENTRAL:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_FINDUS:			$bFound = true;		$bShow = true;	break;
			case typeID::MENU_LOCATION:			$bFound = true;		$bShow = true;	break;
			case typeID::MENU_FILEEXCHANGE:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_WIKI:				$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_GROUPS:		$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_ARTICLES:	$bFound = true;		$bShow = true;	break;
			case typeID::MENU_SHOP_STARTPAGE:	$bFound = true;		$bShow = true;	break;
		}
		// Wenn noch nicht gefunden, lokale Menutypen abfragen
		if ($bFound == false) {
			// Menutypen laden, wenn noch nicht vorhanden
			if ($this->TypesLoaded == false) {
				$this->loadTypes();
				$this->TypesLoaded = true;
			}
			// Menutypen durchgehen
			foreach ($this->Types as $row) {
				if ($row['typ_ID'] == $nType && strlen($row['typ_Adminpath']) == 0) {
					$bFound = true;
				}
			}
		}
		return($bShow);
	}
	
	/**
	 * Daten in lokale Arrays laden
	 */
	public function loadTypes() {
		$sSQL = "SELECT typ_ID,typ_Adminpath,typ_Viewpath 
		FROM tbmenutyp WHERE page_ID = ".page::ID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Types,$row);
		}
	}
}