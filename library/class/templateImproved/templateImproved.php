<?php
/**
 * Erweitertes Template, dessen HTML man ins tpl Objekt pushen kann
 * @author Michael Sebel <michael@sebel.ch
 */
class templateImproved {

	/**
	 * Array von Key/Value Paaren
	 * @var array
	 */
	protected $Data = array();
	/**
	 * Listeobjekte die verarbeitet werden sollen
	 * @var array
	 */
	protected $List = array();
	/**
	 * Subtemplates die verarbeitet werden sollen
	 * @var array
	 */
	protected $Template = array();
	/**
	 * Inhalt des Templates
	 * @var string
	 */
	protected $Content = '';

	/**
	 * Erstellt das Template und liest die Datei ein
	 * @param string $path PFad zur Templatedatei
	 */
	public function __construct($path) {
		// Einlesen des Files
		$this->Content = file_get_contents ($path);
		$this->Content = utf8_decode($this->Content);
		stringOps::htmlViewEnt($this->Content);
	}

	/**
	 * Fügt Daten zur Verarbeitung hinzu
	 * @param string $name Name der Variable
	 * @param string $value Inhalt der Variable
	 */
	public function addData($name,$value) {
		array_push($this->Data,array(
			'Key' => $name,
			'Value' => $value
		));
	}
	
	public function addArray(array $data) {
		foreach ($data as $key => $value) {
			$this->addData($key,$value);
		}
	}

	/**
	 * Fügt eine Liste in das Template ein
	 * @param string $name Name der Variable
	 * @param templateList $list Liste die für Inhalt sorgt
	 */
	public function addList($name, templateList $list) {
		array_push($this->List,array(
			'Key' => $name,
			'List' => $list
		));
	}

	/**
	 * Erstellt ein Untertemplate
	 * @param string $name Name der Variable
	 * @param templateImproved $tpl Template welches für Inhalt sorgt
	 */
	public function addSubtemplate($name,templateImproved $tpl) {
		array_push($this->Template,array(
			'Key' => $name,
			'Tpl' => $tpl
		));
	}

	/**
	 * Löscht sämtliche Daten, damit das Template z.B. für eine mehrmalige
	 * Verwendung erneut mit anderen Daten gefüllt werden kann
	 */
	public function flush() {
		$this->Data = array();
		$this->List = array();
		$this->Template = array();
	}

	/**
	 * Gibt den gesamten Inhalt aus (Alle Daten, Listen und Subtemplates
	 */
	public function output() {
		$out = $this->Content;
		// Alle einfachen Daten ersetzen
		foreach ($this->Data as $set) {
			$out = str_replace('{'.$set[Key].'}', $set['Value'], $out);
		}
		// Alle Listen verarbeiten
		foreach ($this->List as $set) {
			$out = str_replace('{'.$set[Key].'}', $set['List']->output(), $out);
		}
		// Alle Subtemplates verarbeiten
		foreach ($this->Template as $set) {
			$out = str_replace('{'.$set[Key].'}', $set['Tpl']->output(), $out);
		}
		// Gesammelte Daten zurückgeben
		return($out);
	}
}
