<?php
/**
 * Input mit Texteingabe
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellInput extends baseAdminCell {

	/**
	 * Name des Input Feldes
	 * @var string
	 */
	private $Name = '';
	/**
	 * Inhalt des Feldes (Bleibt unverändert)
	 * @var string
	 */
	private $Value = '';

	/**
	 * Erstellt die Zelle
	 * @param string $sName Name des Input Feldes
	 * @param string $sValue Inhalt des Feldes (Bleibt unverändert)
	 * @param bool $bCenter Zentrieren oder nicht
	 */
	public function __construct($sName,$sValue,$bCenter = false) {
		parent::__construct($bCenter);
		$this->Name = $sName;
		$this->Value = $sValue;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		// Klasse definieren, wenn der Head eine hat
		$sClass = '';
		if (strlen($Head->CssClass) > 0) {
			$sClass = ' class="adminBufferInput"';
		}
		// Wenn noch keine Klasse, Grösse berechnen
		if (strlen($sClass) == 0) {
			$sClass = ' style="width:'.($Head->Size-10).'px;"';
		}
		// Inhalt aufbereiten
		$this->Content = '
		<input type="text" name="'.$this->Name.'" value="'.$this->Value.'"'.$sClass.'>
		';
	}
}