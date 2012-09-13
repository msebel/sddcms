<?php
/**
 * Erstellt ein verlinktes Icon
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellIconID extends baseAdminCell {

	/**
	 * Link des Icons
	 * @var string
	 */
	private $ID = '';
	/**
	 * Icon URL
	 * @var string
	 */
	private $Image = '';
	/**
	 * Alternativtext
	 * @var string
	 */
	private $Alt = '';

	/**
	 * Erstellt die Zelle
	 * @param string $sLink Link des Icons
	 * @param string $sImage Icon URL
	 * @param string $sAlt Alternativtext
	 * @param bool $bCenter Zentrieren oder nicht
	 */
	public function __construct($sID,$sImage,$sAlt = '',$bCenter = false) {
		parent::__construct($bCenter);
		$this->ID = $sID;
		$this->Image = $sImage;
		$this->Alt = $sAlt;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$this->Content = '
			<img src="'.$this->Image.'" id="'.$this->ID.'" title="'.$this->Alt.'" alt="'.$this->Alt.'" border="0">
		';
	}
}