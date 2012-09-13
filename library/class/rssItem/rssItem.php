<?php 
/**
 * Klasse, die rssItems für das rssDocument anbietet.
 * @author Michael Sebel <michael@sebel.ch>
 */
class rssItem {
	
	/**
	 * Titel des Eintrages
	 * @var string
	 */
	public $title = '';
	/**
	 * Link zum Originaleintrag
	 * @var string
	 */
	public $link = '';
	/**
	 * Beschreibung des RSS Elementes
	 * @var string
	 */
	public $description = '';
	/**
	 * Eindeutige ID (meist URL) des Eintrages
	 * @var string
	 */
	public $guid = '';
	/**
	 * Publikationsdatum (nur SQL_DATETIME akzeptiert)
	 * @var string
	 */
	public $date = '';
	/**
	 * Array für Kategoriendaten, muss mit Methode definiert werden
	 */
	public $category = null;
	
	/**
	 * Konstruktor, tut ansich nichts...
	 */
	public function __construct() {}
	
	/**
	 * Setzt die Kategorie für das Element.
	 * Muss nicht zwingend gesetzt werden
	 * @param string domain, Domain für die Kategorie
	 * @param string title, Titel der Kategorie
	 */
	public function setCategory($domain,$title) {
		$this->category = array();
		$this->category['domain'] = $domain;
		$this->category['title'] = $title;
	}
}