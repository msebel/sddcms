<?php
/**
 * Erstellt ein Lösch-Icon mit Bestätigung
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellDeleteIcon extends baseAdminCell {

	/**
	 * Link des Icons
	 * @var string
	 */
	private $Text = '';
	/**
	 * Name des zu löschenden Objekts für Abfrage
	 * @var string
	 */
	private $ObjectName = '';
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
	public function __construct($sLink,$sObject,$sAlt = '',$bCenter = false) {
		parent::__construct($bCenter);
		$this->Link = $sLink;
		$this->ObjectName = $sObject;
		$this->Alt = $sAlt;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		$this->Content = '
			<a href="javascript:deleteConfirm(\''.$this->Link.'\',\''.addslashes($this->ObjectName).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$this->Alt.'" alt="'.$this->Alt.'" border="0"></a>
		';
	}
}