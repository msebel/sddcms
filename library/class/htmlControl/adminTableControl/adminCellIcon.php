<?php
/**
 * Erstellt ein verlinktes Icon
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellIcon extends baseAdminCell {

	/**
	 * Link des Icons
	 * @var string
	 */
	private $Text = '';
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
	public function __construct($sLink,$sImage,$sAlt = '',$bCenter = false) {
		parent::__construct($bCenter);
		$this->Link = $sLink;
		$this->Image = $sImage;
		$this->Alt = $sAlt;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$this->Content = '
			<a href="'.$this->Link.'" title="'.$this->Alt.'" alt="'.$this->Alt.'">
			<img src="'.$this->Image.'" title="'.$this->Alt.'" alt="'.$this->Alt.'" border="0"></a>
		';
	}
}