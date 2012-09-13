<?php
/**
 * Erstellt ein Icon mit Javascript
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellJsIcon extends baseAdminCell {

	/**
	 * Link des Icons
	 * @var string
	 */
	private $Code = '';
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
	 * @param string $sCode Code des Icons
	 * @param string $sImage Icon URL
	 * @param string $sAlt Alternativtext
	 * @param bool $bCenter Zentrieren oder nicht
	 */
	public function __construct($sCode,$sImage,$sAlt = '',$bCenter = false) {
		parent::__construct($bCenter);
		$this->Code = $sCode;
		$this->Image = $sImage;
		$this->Alt = $sAlt;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$this->Content = '
			<a href="javascript:'.$this->Code.'" title="'.$this->Alt.'" alt="'.$this->Alt.'">
			<img src="'.$this->Image.'" title="'.$this->Alt.'" alt="'.$this->Alt.'" border="0"></a>
		';
	}
}