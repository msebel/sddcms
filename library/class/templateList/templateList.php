<?php
/**
 * Nutzt das Erweiterte Template um Listen zu generieren
 * @author Michael Sebel <michael@sebel.ch
 */
class templateList {

	/**
	 * Mehrdimensionales Array mit den Listendaten
	 * @var array
	 */
	protected $Data = array();
	/**
	 * Template, welches Listeneinträge verarbeiten
	 * @var templateImproved
	 */
	protected $Template = NULL;
	/**
	 * Output Buffer
	 * @var string
	 */
	protected $Buffer = '';

	/**
	 * Erstellt eine Templateliste
	 * @param templateImproved $tpl Referenz zu einem Template
	 */
	public function __construct(templateImproved $tpl) {
		$this->Template = $tpl;
	}

	/**
	 * Fügt Daten hinzu. Diese müssen mit den über
	 * @param array $dataset Set eines Records anhand Fields
	 */
	public function addData(array $dataset) {
		array_push($this->Data,$dataset);
	}

	/**
	 * Gibt zurück ob die Liste Daten enthält
	 * @return boolean true/false ob Daten vorhanden
	 */
	public function hasData() {
		return(count($this->Data) > 0);
	}

	/**
	 * Gibt die gesamte Liste aus, wendet dazu den
	 * flush Befehl des inenren Templates an
	 */
	public function output() {
		// Alle Daten durchgehen
		foreach($this->Data as $set) {
			// Variablen ins Template füllen
			foreach($set as $key => $value) {
				$this->Template->addData($key, $value);
			}
			// Template so in den Buffer nehmen
			$this->Buffer .= $this->Template->output();
			// Inhalt des Template löschen
			$this->Template->flush();
		}
		// Buffer zurückgeben
		return($this->Buffer);
	}
}
