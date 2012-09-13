<?php
/**
 * Eine Zelle im Tabellenkopf für adminTableControl
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminTableHead {

	/**
	 * Grösse in Pixeln
	 * @var int
	 */
	public $Size = 0;
	/**
	 * CSS Daten für die Zelle
	 * @var string
	 */
	public $CssSize = '';
	/**
	 * CSS Klasse für die Zelle
	 * @var string
	 */
	public $CssClass = '';
	/**
	 * Text der Kopfzelle
	 * @var string
	 */
	public $Text = '';

	/**
	 * Erstellt eine Kopfzelle
	 * @param int $nSize Grösse in PX, 0 wenn Buffer angewendet werden soll
	 * @param string $sText Text für die Zellenüberschrift
	 * @param int $nRes ID einer Ressource (Alternative zum direkten Text)
	 */
	public function __construct($nSize,$sText,$nRes = 0) {
		// Grösse Verarbeiten
		$this->setSize($nSize);
		// Text verarbeiten
		$this->setText($sText,$nRes);
	}

	/**
	 * Bestimmt die Grösse und entsprechende Styles / CSS Klassen
	 * @param int $nSize Grösse der Zeile in Pixel
	 */
	private function setSize($nSize) {
		$this->Size = getInt($nSize);
		// CSS Text je nach grösse
		if ($nSize == 0) {
			$this->CssClass = 'adminBuffer';
		} else {
			$this->CssSize = 'width:'.$nSize.'px;';
		}
	}

	/**
	 * Definiert den Text oder wendet eine Ressource an
	 * @param string $sText Text für Zelleninhalt
	 * @param int $nRes Ressource als Alternative zum Text
	 */
	private function setText($sText,$nRes) {
		$this->Text = $sText;
		// Wenn Resource, von dieser laden
		if ($nRes > 0) {
			$Res = getResources::getInstance(database::getConnection());
			$this->Text = $Res->html($nRes,page::language());
		}
	}
}
