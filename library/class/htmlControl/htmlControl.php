<?php 
/**
 * Bietet verschiedene Singleton Objekte an
 * um mit HTML einfache und komplexe Controls
 * zu verwenden. Etwa Tooltips, flying Windows,
 * Kalender oder multi-selektoren.
 * @author Michael Sebel <michael@sebel.ch>
 */
class htmlControl {
	
	/**
	 * Singleton Objekt für Tooltip Control
	 * @var tooltipControl
	 */
	private static $tooltip = NULL;
	/**
	 * Singleton Objekt für Kalender Control
	 * @var calendarControl
	 */
	private static $calendar = NULL;
	/**
	 * Singleton Objekt für Multi-Selektor Control
	 * @var selectorControl
	 */
	private static $selector = NULL;
	/**
	 * Singleton Objekt für Tagcloud Control
	 * @var tagcloudControl
	 */
	private static $tagcloud = NULL;
	/**
	 * Singleton Objekt für flying Window Control
	 * @var windowControl
	 */
	private static $window = NULL;
	/**
	 * Objektreferenz zum Metadaten objekt.
	 * @var meta
	 */
	public static $meta = NULL;
	
	/**
	 * Tooltip Objekt erstellen
	 * @return tooltipControl Tooltip Objekt
	 */
	public static function tooltip() {
		self::load('tooltipControl');
		if (self::$tooltip == NULL) {
			self::$tooltip = new tooltipControl();
			self::$tooltip->loadMeta(self::$meta);
		}
		return(self::$tooltip);
	}
	
	/**
	 * Kalender Objekt erstellen
	 * @return calendarControl Kalender Objekt
	 */
	public static function calendar() {
		self::load('calendarControl');
		if (self::$calendar == NULL) {
			self::$calendar = new calendarControl();
			self::$calendar->loadMeta(self::$meta);
		}
		return(self::$calendar);
	}
	
	/**
	 * Selektor Objekt erstellen
	 * @return selectorControl Selektor Objekt
	 */
	public static function selector() {
		self::load('selectorControl');
		if (self::$selector == NULL) {
			self::$selector = new selectorControl();
			self::$selector->loadMeta(self::$meta);
		}
		return(self::$selector);
	}
	
	/**
	 * Tagcloud Objekt erstellen
	 * @return tagcloudControl Tagcloud Objekt
	 */
	public static function tagcloud() {
		self::load('tagcloudControl');
		if (self::$tagcloud == NULL) {
			self::$tagcloud = new tagcloudControl();
			self::$tagcloud->loadMeta(self::$meta);
		}
		return(self::$tagcloud);
	}
	
	/**
	 * Window Objekt erstellen
	 * @return windowControl Window Objekt
	 */
	public static function window() {
		self::load('windowControl');
		if (self::$window == NULL) {
			self::$window = new windowControl();
			self::$window->loadMeta(self::$meta);
		}
		return(self::$window);
	}

	/**
	 * AdminTable Objekt erstellen
	 * @return adminTableControl Window Objekt
	 */
	public static function admintable() {
		self::load('adminTableControl');
		$admintable = new adminTableControl();
		$admintable->loadMeta(self::$meta);
		$admintable->loadObjects(
			database::getConnection(),
			getResources::getInstance(
				database::getConnection()
			)
		);
		return($admintable);
	}
	
	/**
	 * Komponente einmal laden, da diese Methode öfter aufgerufen werden könnte.
	 */
	private static function load($file) {
		$base = BP.'/library/class/htmlControl/';
		require_once($base.$file.'/'.$file.'.php');
	}
}

// Metadaten assignen (dazu Meta Objekt globalisieren)
global $Meta; 
htmlControl::$meta = $Meta;
// Basiscontrol einbinden
require_once(BP.'/library/abstract/abstractControl/abstractControl.php');