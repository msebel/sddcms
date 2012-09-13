<?php
/**
 * Basis für Zellen der adminTable
 * @author Michael Sebel <michael@sebel.ch
 */
abstract class baseAdminCell {

	/**
	 * Content für die Zelle
	 * @var string
	 */
	protected $Content = '';
	/**
	 * Gibt an, ob die Zelleninhalte zentriert werden sollen
	 * @var bool
	 */
	protected $Centered = false;

	/**
	 * Erstellt die Basis (Centered oder nicht)
	 * @param bool $bCenter true/false ob zentriert oder nicht
	 */
	public function __construct($bCenter) {
		$this->Centered = $bCenter;
	}

	/**
	 * Zelle ausgeben
	 * @param adminTableHead $Head Entsprechende Kopfzelle
	 * @return string HTML Code für die Zelle
	 */
	final public function printCell(adminTableHead $Head) {
		$this->createContent($Head);
		return($this->getContent($Head));
	}

	/**
	 * Erstellt den Inhalt (Basis um die Zelle)
	 * @param adminTableHead $Head Entsprechende Kopfzelle
	 * @return string HTML Code für die Zelle
	 */
	final public function getContent(adminTableHead $Head) {
		// Zentrieren wenn nötig
		$sCenter = '';
		if ($this->Centered) $sCenter = 'text-align:center;';
		// Content validieren, wenn leer
		if (strlen($this->Content) == 0) $this->Content = '&nbsp;';
		// Klasse oder Style
		$sAttribute = '';
		if (strlen($Head->CssClass) > 0) {
			$sAttribute = ' style="float:left;'.$sCenter.'" " class="'.$Head->CssClass.'"';
		} else {
			$sAttribute = ' style="'.$Head->CssSize.'float:left;'.$sCenter.'"';
		}
		// Direkt HTML zurückgeben
		return('<div'.$sAttribute.'>'.$this->Content.'</div>');
	}

	/**
	 * Erstellt aus den lokalen Daten den Inhalt der Zelle
	 */
	abstract public function createContent(adminTableHead $Head);
}