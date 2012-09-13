<?php
/**
 * 
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminCellText extends baseAdminCell {

	/**
	 * Text für den Inhalt (Auch HMTL Erlaubt)
	 * @var string
	 */
	private $Text = '';

	/**
	 * Erstellt die Zelle
	 * @param string $sText Textinhalt (oder HTML)
	 * @param bool $bCenter Zentrieren oder nicht
	 */
	public function __construct($sText,$bCenter = false) {
		parent::__construct($bCenter);
		$this->Text = $sText;
	}

	/**
	 * Erstellt den Inhalt, kodiert den Code und füllt Ihn ein
	 */
	public function createContent(adminTableHead $Head) {
		stringOps::htmlViewEnt($this->Text);
		$this->Content = $this->Text;
	}
}