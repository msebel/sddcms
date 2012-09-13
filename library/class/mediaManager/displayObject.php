<?php
/**
 * Bietet möglichkeiten ein File darzustellen.
 * Faktisch erstellt diese Klasse einfach eine Tabellenzelle
 * in der verschiedene Linsk und Informationen zum File 
 * bereitgestellt werden. Bei Bildern erscheint ein Thumbnail.
 * @author Michael Sebel <michael@sebel.ch>
 */
class displayObject {
	
	/**
	 * Link zum Script, welches die Datei verändern kann
	 * @var string
	 */
	public $editFile;
	/**
	 * Gibt an, ob die Datei veränderbar ist
	 * @var boolean
	 */
	public $editable = false;
	/**
	 * Speichert den auszugebenden HTML Code
	 * @var string
	 */
	public $displayHtml;
	/**
	 * Scriptfile um das gewünschte File in einer Vorschau zu sehen
	 * @var string
	 */
	public $viewFile;
	/**
	 * Eigentlicher Name der Datei ohne Pfad
	 * @var string
	 */
	public $fileName;
	/**
	 * Grösse des Files als String oder integer
	 * @var string
	 */
	public $fileSize;
	/**
	 * Zusätzliche Informationen zum File
	 * @var string
	 */
	public $additionalInfo;
	/**
	 * Javascript Code der beim Selektieren ausgeführt werden soll
	 * @var string
	 */
	public $selectActionJs;
	/**
	 * Gibt an, ob das File aktuell ausgewählt ist
	 * @var boolean
	 */
	public $isSelected = false;
	/**
	 * Name der Methode zum speichern des Files.
	 * Wird gebraucht für den doppelklick Event
	 * @var string
	 */
	public $SaveMethod = '';
	
	/**
	 * Instanziert das Objekt und speichert die SaveMethod
	 * @param string SaveMethod, Methode welche bei doppelklick ausgeführt wird (JS)
	 */
	public function __construct($SaveMethod) {
		$this->SaveMethod = $SaveMethod;
	}
	
	/**
	 * HTML Code für das File zurückgeben
	 * @param resource Res, Referenz zum Sprachobjekt
	 * @return string HTML Code für das File
	 */
	public function getHtml(resources &$Res) {
		// Selektion prüfen
		$myConn = database::getConnection();
		$sColor = option::get('mmCellBackground');
		if ($sColor == NULL) $sColor = '#eee';
		unset($myConn);
		if ($this->isSelected) $sColor = "#879DFF";
		// Editierbarkeit prüfen
		$sEditHtml = '';
		if ($this->editable == true) {
			$sEditHtml = '
			<a href="'.$this->editFile.'?id='.page::menuID().'&file='.$this->fileName.'" alt="'.$Res->html(191,page::language()).'" title="'.$Res->html(191,page::language()).'">
				<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(191,page::language()).'"></a>&nbsp;&nbsp;
			';
		}
		// Ausgabe buffer erweitern
		$out = '
			<td id="'.$this->fileName.'" style="vertical-align:bottom;background-color:'.$sColor.';width:33%;padding-top:10px;"
			onClick="mmTbCellClick(this)" onMouseover="mmTbCellIn(this);" onMouseout="mmTbCellOut(this);" onDblClick="mmTbCellClick(this);'.$this->SaveMethod.';">
				<table width="100%" cellspacing="0" cellpadding="3" border="0">
					<tr>
						<td style="text-align:center;" >
							'.$this->displayHtml.'
							<br>
							<br>
							<strong>'.$this->fileName.'</strong>
						</td>
					</tr>
					<tr>
						<td style="text-align:center;">
							<em>'.$this->getFileInfo().'</em>
						</td>
					</tr>
					<tr>
						<td style="text-align:center;">
							'.$sEditHtml.'
							<a href="'.$this->viewFile.'?id='.page::menuID().'&file='.$this->fileName.'" title="'.$Res->html(192,page::language()).'" alt="'.$Res->html(192,page::language()).'">
							<img src="/images/icons/bullet_magnifier.png" border="0" alt="'.$Res->html(192,page::language()).'"></a>&nbsp;&nbsp;
							<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$this->fileName.'\',\''.addslashes($this->fileName).'\','.page::language().')">
							<img src="/images/icons/bullet_delete.png" border="0" alt="'.$Res->html(193,page::language()).'"></a>&nbsp;&nbsp;
							<a href="#" onClick="javascript:mmTbCellClick($(\''.$this->fileName.'\'));'.$this->SaveMethod.'">
							<img src="/images/icons/accept.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
						</td>
					</tr>
				</table>
			</td>
		';
		return($out);
	}
	
	/**
	 * Erweiterte Dateiinformationen holen.
	 * Zudem grösse des Files in KB konvertieren (von bytes).
	 * @return string Die Dateigrösse in KB mit " KB" dahinter
	 */
	private function getFileInfo() {
		$sSize = (string) $this->fileSize / 1024;
		if (stristr($sSize,".") !== false) {
			$sSize = substr($sSize,0,strpos($sSize,".")+3);
		}
		$sSize .= ' KB';
		// Zusatzinfos
		$sAdd = $this->additionalInfo;
		if (strlen($sAdd) > 0) {
			$sSize .= ' / '.$sAdd;
		}
		// String erstellen
		return($sSize);
	}
}