<?php
require_once(BP.'/library/abstract/baseAdminCell/baseAdminCell.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminTableHead.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellText.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellInput.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellCheckbox.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellIcon.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellJsIcon.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellDeleteIcon.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellRadio.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellDropdown.php');
require_once(BP.'/library/class/htmlControl/adminTableControl/adminCellIconID.php');

/**
 * Erweiterung des Basecontrol um ein e Tabelle.
 * Es ist eine spezielle evtl Sortier-/Pagbare Tabelle,
 * welche dem Standard Admin Design entspricht
 * @author Michael Sebel <michael@sebel.ch>
 */
class adminTableControl extends abstractControl {

	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	/**
	 * Definiert de Kopfzeilen
	 * @var array
	 */
	private $Head;
	/**
	 * Gibt an, ob die Tabelle sortierbar ist
	 * @var bool
	 */
	private $Sortable = false;
	/**
	 * Gibt an, ob hidden die Row IDs als id[] Array geprintet werden
	 * @var bool
	 */
	private $PrintIDs = true;
	/**
	 * Gibt an, dass die Tabelle mit Paging ausgestattet ist
	 * @var paging
	 */
	private $Paging;
	/**
	 * tabRowExtender Instanz, by default die normale Instanz
	 * @var tabRowExtender
	 */
	private $RowExtender;
	/**
	 * Fehlermeldung, wenn keine Daten vorhanden sind
	 * @var string
	 */
	private $Error = '';
	/**
	 * Offset für Fehlermeldung (So eine Art colspan)
	 * @var int
	 */
	private $ErrorOffset = 0;
	/**
	 * Anzahl Zeilen in der Tabelle (Wird intern gezählt)
	 * @var int
	 */
	private $RowCount = 0;
	/**
	 * Array aller Datenzeilen und deren Zellen
	 * @var array
	 */
	private $Rows = array();
	/**
	 * Array aller IDs für Hidden Felder
	 * @var array
	 */
	private $RowIDs = array();
	/**
	 * Zeilenhöhe in Pixeln (Head ist fix auf 25!), default = 20
	 * @var int
	 */
	private $LineHeight =  20;
	/**
	 * Angenommene Breite für eine gepufferte Zelle (Mit CSS)
	 * @var int
	 */
	const BUFFERCELL_SIZE = 150;

	
	/**
	 * Objekte laden, nichts zu laden
	 */
	public function loadObjects() {
		$this->Conn =& func_get_arg(0); // $Conn
		$this->Res =& func_get_arg(1); // $Res
		$this->RowExtender = new tabRowExtender();
	}
	
	/**
	 * Template laden (Hier nicht nötig)
	 * @param meta Meta, Referenz zum Metadaten Objekt
	 */
	public function loadMeta($Meta) {
		
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @param string $Name Egal bei diesem Control
	 * @return string HTML Code für das Control
	 */
	public function get($Name) {
		// Tabellenkopf erstellen
		$out .= $this->getPaging();
		$out .= $this->getHead();
		// Beginn der Tabelle
		$out .= '<div id="contentTable">';
		// Zeilen darstellen
		if (!$this->createContent($out)) {
			// Falls nicht erfolgreich, Meldung
			$out .= $this->createError();
		}
		// Ende der Tabelle
		$out .= '</div>';
		$out .= $this->getPaging();
		// Wenn nötig, HTML Code für Sortierung
		if ($this->Sortable) {
			$out .= '
			<script type="text/javascript">
				Sortable.create("contentTable", { tag:"div", containment:["contentTable"],onChange:updateSort});
			</script>';
		}
		return($out);
	}

	/**
	 * Akzeptiert als Tabellenkopf ein Array von adminTableHead Elementen
	 * @param array $Head Array von adminTableHead Elementen
	 */
	public function setHead(array $Head) {
		$this->Head = $Head;
	}

	/**
	 * Paging definieren, wenn vorhanden, wird es verwendet
	 * @param paging $Paging Instanz eines Pagings zur fertigen Verwendung
	 */
	public function setPaging(paging $Paging) {
		$this->Paging = $Paging;
	}

	/**
	 * Fehlermeldung, welche bei leerem Recordset ausgegeben wird
	 * @param string $sError Meldung
	 */
	public function setErrorMessage($sError,$nOffset) {
		$this->Error = $sError;
		$this->ErrorOffset = getInt($nOffset);
	}

	/**
	 * Definiert die Zeilenhöhe für Inhalte
	 * @param int $nSize Zeilenhöhe in PX
	 */
	public function setLineHeight($nSize) {
		$this->LineHeight = getInt($nSize);
	}

	/**
	 * Definiert ob die Tabelle sortierbar ist
	 * @param inboolt $bSortable true/false ob sortierbar oder nicht
	 */
	public function setSortable($bSortable) {
		$this->Sortable = $bSortable;
	}

	/**
	 * Definiert ob die ID's geprintet werden am Zeilenende
	 * @param bool $bValue true/false
	 */
	public function setPrintIDs($bValue) {
		$bValue = ($bValue ? true : false);
		$this->PrintIDs = $bValue;
	}

	/**
	 * Fügt eine Zeile hinzu. Sollte mit den definitionen
	 * in der setHead Methode übereinstimmen
	 * @param array $Row Datenzeile, Array von baseAdminCells
	 */
	public function addRow($nID,array $Row) {
		$this->RowCount++;
		$this->Rows[$nID] = $Row;
	}

	/**
	 * Erstellt den Tabelleninhalt anhand der vorhandenen Daten
	 * @param string $out Direkter Output Buffer, HTML hier anhängen
	 * @return bool true/false ob Daten vorhanden oder nicht
	 */
	private function createContent(&$out) {
		// Alle Zeilen durchgehen
		$nCountRows = 0;
		foreach ($this->Rows as $key => $Cells) {
			$nCountRows++;
			// Start der Zeile
			$out .= '
			<div class="'.$this->RowExtender->get().'" name="tabRow[]"
				style="width:100%;height:'.$this->LineHeight.'px;padding-top:5px;">
			';
			// Zelleninhalte holen
			for ($iC = 0;$iC < count($Cells);$iC++) {
				$out .= $Cells[$iC]->printCell($this->Head[$iC]);
			}
			// ID Array printen, wenn erwünscht
			if ($this->PrintIDs) {
				$out .= '<input type="hidden" name="id[]" value='.$key.'">';
			}
			// Sortierfunktions-Zelle anhängen wenn nötig
			if ($this->Sortable) {
				$out .= '
				<div style="width:20px;float:left;">
					<input type="hidden" name="sort[]" value="'.$nCountRows.'">
					<a href="#" id="tabRow_'.$key.'"
						onMouseover="SetPointer(this.id,\'move\')"
						onMouseout="SetPointer(this.id,\'default\')"
						title="'.$this->Res->html(214, page::language()).'"
						alt="'.$this->Res->html(214, page::language()).'">
					<img src="/images/icons/arrow_in.png" border="0"
						alt="'.$this->Res->html(214, page::language()).'"
						title="'.$this->Res->html(214, page::language()).'"></a>
				</div>
				';
			}
			// Zeile schliessen
			$out .= '</div>';
		}
		return(($this->RowCount > 0));
	}

	/**
	 * Erstellt eine Fehlerzeile über die ganze Breite
	 * @return string HTML Code für eine Fehlerzeile
	 */
	private function createError() {
		// Breite aller Zellen holen
		$nSize = $this->getHeadSize(count($this->Head));
		$nOffset = $this->getHeadSize($this->ErrorOffset);
		// Nur wenn keine Zeilen vorhanden sind
		if ($this->RowCount == 0) {
			return('
			<div class="'.$this->RowExtender->get().'" style="width:100%;height:'.$this->LineHeight.'px;padding-top:5px;">
				<div style="width:'.($nOffset).'px;float:left;">&nbsp;</div>
				<div style="width:'.($nSize - $nOffset).'px;float:left;">'.$this->Error.'</div>
			</div>
			');
		}
	}

	/**
	 * Gibt die Gesamtgrösse aller Zellen an und nimmt für eine
	 * gebufferte Zeile eine Breite von 150px.
	 * @return int Pixelwert
	 */
	private function getHeadSize($nMax) {
		$nSize = 0;
		$nCells = 0;
		foreach ($this->Head as $Cell) {
			if ($Cell->Size == 0) {
				$nSize += self::BUFFERCELL_SIZE;
			} else {
				$nSize += $Cell->Size;
			}
			$nCells++;
			if ($nCells == $nMax) break;
		}
		return($nSize);
	}

	/**
	 * Gibt den Tabellenkopf zurück
	 * @return string HTML Code für gesamten Tabellenkopf
	 */
	private function getHead() {
		// Standard Kopf erstellen
		$out = '<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">';
		// Schlaufe über alle Kopfeinträge
		foreach ($this->Head as $Cell) {
			$out .= '
			<div style="float:left;'.$Cell->CssSize.'" class="'.$Cell->CssClass.'">
				<strong>'.$Cell->Text.'</strong>
			</div>';
		}
		// Leeren Eintrag für Verschieber Control, wenn erwünscht
		if ($this->Sortable) {
			$out .= '<div style="float:left;width:20px;">&nbsp;</div>';
		}
		// Zusammen mit schliessendem Div zurückgeben
		return($out.'</div>');
	}

	/**
	 * Paging HTML zurückgeben, sofern vorhanden
	 * @return string HTML Code für Paging
	 */
	private function getPaging() {
		if ($this->Paging instanceof paging) {
			return($this->Paging->getHtml());
		}
	}
}