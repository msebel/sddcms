<?php
/**
 * Input Feld mit Checkbox
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellCheckbox extends baseAdminCell {

	/**
	 * Name des Input Feldes
	 * @var string
	 */
	private $Name = '';
	/**
	 * Inhalt des Feldes (Bleibt unverändert)
	 * @var int
	 */
	private $Value = 0;
	/**
	 * Check, für "checked" Attribut
	 * @var int
	 */
	private $Check = 1;

	/**
	 * Erstellt die Zelle
	 * @param string $sName Name des Input Feldes
	 * @param string $sValue Inhalt des Feldes (Bleibt unverändert)
	 * @param bool $bCenter Zentrieren oder nicht
	 */
	public function __construct($sName,$nValue = 0,$nCheck = 1,$bCenter = false) {
		parent::__construct($bCenter);
		$this->Name = $sName;
		$this->Value = $nValue;
		$this->Check = $nCheck;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$sChecked = checkCheckBox($this->Check, $this->Value);
		// Inhalt aufbereiten
		$this->Content = '
		<input type="checkbox" name="'.$this->Name.'" value="'.$this->Check.'"'.$sChecked.'>
		';
	}
}