<?php
/**
 * Klasse um verschiedenfarbige Tabellenzeilen zu generieren.
 * @author Michael Sebel <michael@sebel.ch>
 */
class tabRowExtender {
	
	/**
	 * Name der dünkleren Tabellenzeile (CSS Standard)
	 * @var string
	 */
	const ODD_ROW = "tabRowDark";
	/**
	 * Name der helleren Tabellenzeile (CSS Standard)
	 * @var string
	 */
	const EVEN_ROW = "tabRowLight";
	/**
	 * Name der Zeilenlinien Klasse (CSS Standard)
	 * @var string
	 */
	const LINE = "tabRowLine";
	/**
	 * Letzte zurückgegebene Klasse (Konstante)
	 * @var string
	 */
	private $Actual;
	/**
	 * Spezielle CSS Klasse, anstelle ODD_ROW
	 * @var string
	 */
	private $SpecialOdd;
	/**
	 * Spezielle CSS Klasse, anstelle EVEN_ROW
	 * @var string
	 */
	private $SpecialEven;
	/**
	 * Letzte zurückgegebene Klasse (Spezialwerte)
	 * @var string
	 */
	private $SpecialActual;
	/**
	 * Zählt, wie viele Zeilen bereits verarbeitet wurden
	 * @var int
	 */
	private $Count = 0;
	
	/**
	 * Tabellenzeilen Objekt erstellen.
	 * Es werden die Standard Klasse in Konstanten verwendet,
	 * sofern sOdd und sEven nicht übergeben werden.
	 * @param string sOdd, Name der dünkleren CSS Klasse (optional)
	 * @param string sEven, Name der helleren CSS Klasse (optional)
	 */
	public function __construct($sOdd = null, $sEven = null) {
		// Startklasse definieren
		$this->Actual = self::ODD_ROW;
		// Eventuell Special nutzen
		$this->SpecialOdd = $sOdd;
		$this->SpecialEven = $sEven;
	}
	
	/**
	 * Nächsten Klassennamen aus Konstanten zurückgeben
	 * @return string CSS Klasse für Tabellenzeile
	 */
	public function get() {
		// Tabellenzeilen Klasse wechseln
		if ($this->Actual == self::ODD_ROW) {
			$this->Actual = self::EVEN_ROW;
		} else {
			$this->Actual = self::ODD_ROW;
		}
		$this->Count++;
		return($this->Actual);
	}
	
	/**
	 * Klasse für eine Tabellenlinie zurückgeben
	 * @return string CSS Klasse für Tabellenlinie
	 */
	public function getLine() {
		$this->Count++;
		return(self::LINE);
	}
	
	/**
	 * Nächsten Klassennamen aus Spezialwerten zurückgeben
	 * @return string CSS Klasse für Tabellenzeile
	 */
	public function getSpecial() {
		// Tabellenzeilen Klasse wechseln
		if ($this->SpecialActual == $this->SpecialOdd) {
			$this->SpecialActual = $this->SpecialEven;
		} else {
			$this->SpecialActual = $this->SpecialOdd;
		}
		$this->Count++;
		return($this->SpecialActual);
	}

	/**
	 * Gibt die Anzahl Zeilen der Tabelle aus
	 * @return int Anzahl Zeilen > 0
	 */
	public function getCount() {
		return($this->Count);
	}
}