<?php
/**
 * Input Feld mit Dropdown
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellDropdown extends baseAdminCell {

	/**
	 * Name des Select Feldes
	 * @var string
	 */
	private $Name = '';
	/**
	 * Wert zum vorselektieren
	 * @var mixed
	 */
	private $SelectedValue = NULL;
	/**
	 * Array von Key/Value Paaren für Inhalt
	 * @var int
	 */
	private $Data = NULL;

	/**
	 *Erstellt das Dropdown
	 * @param string $sName Name des Dropdowns
	 * @param array $Data Array mit Key/Value paaren (Array: 0=KeyWert 1=Darstellungswert
	 * @param mixed $sSelected Selektierter Wert, wird mit 0=KeyWert verglichen
	 */
	public function __construct($sName,$Data,$sSelected = '') {
		parent::__construct($bCenter);
		$this->Name = $sName;
		$this->SelectedValue = $sSelected;
		$this->Data = $Data;
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
		// Select erstellen
		$this->Content .= '<select name="'.$this->Name.'" '.$sClass.'>';
		// Alle Optionen darstellen
		foreach ($this->Data as $option) {
			$sSelected = checkDropDown($this->SelectedValue, $option[0]);
			// Inhalt aufbereiten
			$this->Content .= '
			<option value="'.$option[0].'"'.$sSelected.'>'.$option[1].'</option>
			';
		}
		$this->Content .= '</select>';
	}
}