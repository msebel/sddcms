<?php
/**
 * Sammlung aller Singletons im System
 * @author Michael Sebel <michael@sebel.ch>
 */
class singleton {

	/**
	 * Metadaten Objekt für CSS/JS etc.
	 * @var meta
	 */
	private static $Meta = NULL;
	/**
	 * Sprachressourcen Objekt
	 * @var resources
	 */
	private static $Resources = NULL;
	/**
	 * Datenbankverbindung zum CMS
	 * @var dbConn
	 */
	private static $Conn = NULL;
	/**
	 * Datenbankverbindung zu Kunde
	 * @var dbConn
	 */
	private static $CustConn = NULL;
	/**
	 * Zugriffsobjekt
	 * @var access
	 */
	private static $Access = NULL;
	/**
	 * Template Objekt des CMS
	 * @var template
	 */
	private static $Template = NULL;
	/**
	 * Menuklasse für das Primäre Menu
	 * @var menuInterface
	 */
	private static $Menu = NULL;
	/**
	 * CDN Domain, überschreibbar pro Mandant (in der Theorie)
	 * @var string
	 */
	private static $CDN = config::CDN_URL;

	/**
	 * Metadaten Objekt für CSS/JS etc.
	 * @return meta
	 */
	public static function meta() {
		if (self::$Meta == NULL) {
			$Template = self::template();
			self::$Meta = new meta($Template);
		}
		return(self::$Meta);
	}

	/**
	 * Sprachressourcen Objekt
	 * @return resources Sprachressourcen Objekt
	 */
	public static function resources() {
		if (self::$Resources == NULL) {
			$Conn = self::conn();
			self::$Resources = new resources($Conn);
		}
		return(self::$Resources);
	}

	/**
	 * Datenbankverbindung zu CMS
	 * @return dbConn Datenbankverbindung zu CMS
	 */
	public static function conn() {
		if (self::$Conn == NULL) {
			self::$Conn = database::instantiateConnection();
		}
		return(self::$Conn);
	}

	/**
	 * Datenbankverbindung zu Kunde
	 * @return dbConn Datenbankverbindung zu Kunde
	 */
	public static function custconn() {
		if (self::$CustConn == NULL) {
			self::$CustConn = database::instantiateConnection();
			self::$CustConn->setCustomerDB();
		}
		return(self::$CustConn);
	}

	/**
	 * Zugriffsobjekt
	 * @return access Zugriffsobjekt
	 */
	public static function access() {
		if (self::$Access == NULL) {
			$Conn = self::conn();
			self::$Access = new access($Conn);
		}
		return(self::$Access);
	}

	/**
	 * Template Objekt des CMS
	 * @return template Template Objekt des CMS
	 */
	public static function template() {
		if (self::$Template == NULL) {
			$Access = self::access();
			self::$Template = new template($Access);
		}
		return(self::$Template);
	}

	/**
	 * Gibt die Menuklasse für das Primäre Menu zurück
	 * @return menuInterface Menuklasse für das Primäre Menu
	 */
	public static function menu() {
		if (self::$Menu == NULL) {
			$menuclass = option::get('menuClass');
			if (strlen($menuclass) == 0) $menuclass = 'defaultMenu';
			self::$Menu = new $menuclass(
				singleton::access(),
				singleton::conn()
			);
		}
		return(self::$Menu);
	}

	/**
	 * Gibt das aktuelle Menuobjekt zurück
	 * @return menuObject Menuobjekt das aktuell am laufen ist
	 */
	public static function currentmenu() {
		return(self::menu()->CurrentMenu);
	}

	public static function cdn() {
		return(self::$CDN);
	}

	public static function setCdn($cdn) {
		if (stringOps::checkURL($cdn)) {
			self::$CDN = $cdn;
		}
	}
}