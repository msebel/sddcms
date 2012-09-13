<?php
/**
 * Input Feld mit Radio
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellRadio extends baseAdminCell {

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
	public function __construct($sName,$nValue = 0,$nCheck = 1,$sValue = '',$bCenter = false) {
		parent::__construct($bCenter);
		$this->Name = $sName;
		$this->CheckValue = $nValue;
		$this->Value = $sValue;
		$this->Check = $nCheck;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$sChecked = checkCheckBox($this->Check, $this->CheckValue);
		// Inhalt aufbereiten
		$this->Content = '
		<input type="radio" name="'.$this->Name.'" value="'.$this->Value.'"'.$sChecked.'>
		';
	}
}