<?php
/**
 * Kann dynamisch Klassennamen aus verschiedenen Orten importieren
 * Die erste gefundene Klasse wird verwendet. Es kann in Basispfaden
 * und darin in Unterordnern gesucht werden.
 * @author Michael Sebel <michael@sebel.ch>
 */
class dynamicClassLoader {

	/**
	 * Basispfade, werden in gegebener Reihenfolge durchsucht
	 * @var array
	 */
	private $myBase = array();
	/**
	 * In diesen Ordnern wird innerhalb der Basispfade gesucht
	 * @var array
	 */
	private $mySearch = array();
	/**
	 * Zwischenspeicher, der aufzeigt, welche Klassen von wo geladen wurden
	 * @var array
	 */
	private $myData = array();

	/**
	 * Konstruktor, erstellt genau gar nichts.
	 */
	public function __construct() {
		
	}

	/**
	 * Definiert ein Array von Basispfaden in deren
	 * übergebener Reihenfolge gesucht wird
	 * @param array $paths Array von Absolute Filesystem Pfaden
	 */
	public function setBasepaths(array $paths) {
		$this->myBase = $paths;
	}

	/**
	 * In diesen Ordnern innerhalb der Basispfade wird
	 * nach Klassen gesucht. Üblicherweise ist dies ein
	 * Array welches nur aus dem Ordner 'classes' besteht
	 * @param array $paths Array von Absolute Filesystem Pfaden
	 */
	public function setSearchFolders(array $paths) {
		$this->mySearch = $paths;
	}

	/**
	 * Lädt eine Klasse einmalig und wirft eine Exception
	 * wenn der Klassenpfad nicht gefunden wurde. Für eine
	 * Debug ausgabe werden die geladenen Daten gespeichert
	 * um zu sehen was von wo geladen wurde.
	 * @param string $className
	 */
	public function load($className) {
		// Alle Basispfade durchgehen
		foreach ($this->myBase as $path) {
			// Und nun alle Ordner durchsuchen
			foreach ($this->mySearch as $ext) {
				$file = $path.$ext.$className.'.php';
				if (file_exists($file)) {
					require_once($file);
					array_push($this->myData,array(
						'ClassName' => $className,
						'FileName' => $file
					));
					// Reicht schon, sofort aufhören ;-)
					return;
				}
			}
		}
	}

	/**
	 * Gibt alle geladenen Klassen und deren Locations aus
	 */
	public function debug() {
		debug($this->myData);
	}
}