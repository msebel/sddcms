<?php
/**
 * Klasse die eine Verteilung von Records auf mehrere Seiten anbietet.
 * Die Seitenzahl wird seit sddCMS 2.0 in der Session gespeichert, sodass
 * man nicht mehr für alle Links den 'page' Parameter führen muss.
 * @author Michael Sebel <michael@sebel.ch>
 */
class paging {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Pfad für die Seiten-Wechsel-Links
	 * @var string
	 */
	private $Path;
	/**
	 * Aktuell angezeigte Seite
	 * @var integer
	 */
	private $Page = 1;
	/**
	 * Anzahl Records im verteilten Recordset
	 * @var integer
	 */
	private $Records = 0;
	/**
	 * Anzahl Records pro Seite
	 * @var integer
	 */
	private $Limit = 10;
	/**
	 * Anzahl Seiten für das Recordset
	 * @var integer
	 */
	private $Pages = 1;
	/**
	 * HTML Code für Navigation
	 * @var string
	 */
	private $HTML = '';
	/**
	 * Grundlegende SQL Abfrage
	 * @var string
	 */
	private $sSQL;
	
	/**
	 * Paging Objekt erstellen.
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param string Path, Pfad für die Seiten-Wechsel-Links
	 */
	public function __construct(dbConn &$Conn,$Path) {
		$this->Conn = $Conn;
		$this->Path = $Path;
	}
	
	/**
	 * Paging starten.
	 * Dem SQL String wird das LIMIT Wort angehängt, vorher Wird die
	 * Gesamtzahl der Records gezählt und in die Session gespeichert
	 * @param string sSQL, Zu verteilende SQL Abfrage
	 * @param integer nLimit, Anzahl Records pro Seite
	 * @param boolean Preserve, Wenn False, werden Rows immer neu gezählt
	 * @return string Bearbeitetes SQL Statement für die effektive Abfrage
	 */
	public function start($sSQL,$nLimit = NULL,$Preserve = true) {
		// Limit anpassen wenn vorhanden
		if ($nLimit != NULL) {
			$this->Limit = $nLimit;
		}
		// Lesen wie viele Records das Statement bringt
		if (!isset($_SESSION['paging'][''.page::menuID()]) || !$Preserve) {
			$this->Records = $this->Conn->getColumnCount($sSQL);
			// Session speichern
			$_SESSION['paging'][''.page::menuID()]['rows'] = $this->Records;
		} else {
			$this->Records = $_SESSION['paging'][''.page::menuID()]['rows'];
		}
		// Wenn kein Record, einen anfügen damits keine Zero Divisons gibt
		if ($this->Records == 0) $this->Records++;
		// Maximale Anzahl Seiten berechnen
		$this->calcPages();
		// Aktuelle Page holen aus session oder query
		if (!$this->getSessionPaging()) {
			$this->Page = getInt($_GET['page']);
			$_SESSION['paging'][''.page::menuID()]['page'] = $this->Page;
		}
		// wenn zu hoch, maximum nehmen, wenn zu niedrig = 1
		if ($this->Page < 1) $this->Page = 1;
		if ($this->Page > $this->Pages) $this->Page = $this->Pages;
		// Entsprechend das SQL Statement erstellen
		$this->setSQL($sSQL);
	}
	
	/**
	 * Page vom Paging aus der Session holen.
	 * Alternativ wird versucht die Seite vom 'page' Parameter zu lesen
	 * @return boolean True, wenn das Paging aus der Session geholt wird
	 */
	private function getSessionPaging() {
		$bGotFromSession = false;
		$bSessExists = isset($_SESSION['paging'][''.page::menuID()]['page']);
		$bGetExists = isset($_GET['page']);
		// Wenn Session vorhanden und Parameter nicht
		if ($bSessExists && !$bGetExists) {
			$this->Page = $_SESSION['paging'][''.page::menuID()]['page'];
			$bGotFromSession = true;
		}
		return($bGotFromSession);
	}
	
	/**
	 * SQL Statement mit LIMIT generieren.
	 * @param string sSQL, Datenbank Abfrage
	 */
	private function setSQL($sSQL) {
		// Rows und Offset fürs Statement generieren
		$nRows = $this->Limit;
		$nOffs = ($this->Page * $this->Limit) - $this->Limit;
		// Limit klausel anhängen
		$sSQL .= " LIMIT $nOffs,$nRows";
		$this->sSQL = $sSQL;
	}
	
	/**
	 * Berechnet anhand des Limits und den Datensätzen die Anzahl der Seiten.
	 */
	private function calcPages() {
		// Rest berechnen
		$nAdd = 1;
		$nMuch = $this->Records % $this->Limit;
		// Die Rechnung geht ohne Rest auf
		if ($nMuch == 0) $nAdd = 0;
		// Rest subtrahieren und 1x limit hinzu
		$nAbsoluteRec = $this->Records - $nMuch;
		// Ergibt eine Seite zuwenig, +1
		$this->Pages = ($nAbsoluteRec / $this->Limit) + $nAdd;
	}
	
	/**
	 * HTML Des Paging zurückgeben
	 * @return string HTML Code für Seitennavigation
	 */
	public function getHtml() {
		// Nur wenn mehr als eine Seite
		if (strlen($this->HTML) == 0) {
			if ($this->Pages > 1) {
				// Fünfmal die selbe Seite in ein Array einfügen
				$arrPages = array(
					$this->Page,$this->Page,$this->Page,$this->Page,$this->Page
				);
				// Mehr und weniger Seiten berechnen
				$this->getPageArray($arrPages);
				// Totalinkrementation bis stelle null >= 1 ist
				while ($arrPages[0] < 1) {
					for ($i = 0;$i < 5;$i++) {
						$arrPages[$i]++;
					}
				}
				// Alles auf 0, was grösser ist als Maximum
				for ($i = 0;$i < 5;$i++) {
					if ($arrPages[$i] > $this->Pages) {
						$arrPages[$i] = 0;
					}
				}
				// HTML generieren
				$this->HTML = $this->generateHtml($arrPages);
			} else {
				$this->HTML = '
				<div class="divPagingTable">
					<table class="pagingTable" width="100%" cellpadding="3" cellspacing="0" border="0">
						<tr>
							<td>&nbsp;</td>
						</tr>
					</table>
				</div>
				';
			}
		} 
		return($this->HTML);
	}
	
	/**
	 * Definiert die zu bearbeitenden Seiten.
	 * @var array arrPages, Referenziertes Array mit Seitenzahlen
	 */
	private function getPageArray(&$arrPages) {
		// Herausfinden an welcher Stelle die Page ist
		$nStelle = 2; 		// Mitte
		$nMaxStellen = 4;	// Höchster Index
		// Spezialstellen definieren
		if ($this->Page == 1) $nStelle = 0; // Anfang
		if ($this->Page == 2) $nStelle = 1; // Zweite stelle
		if ($this->Page == $this->Pages - 1) 	$nStelle = 3; // Zweitletzte
		if ($this->Page == $this->Pages) 		$nStelle = 4; // Letzte Stelle
		// Referenzzahl
		$nReference = $arrPages[$nStelle];
		// Seiten darunter rückwärts feststellen
		$nHelper = 0;
		for ($i = $nStelle;$i >= 0;$i--) {
			$arrPages[$i] = $nReference + $nHelper;
			// Nächstesmal eins weniger
			$nHelper--; 
		}
		// Seiten darüber feststellen
		$nHelper = 0;
		for ($i = $nStelle;$i <= $nMaxStellen;$i++) {
			$arrPages[$i] = $nReference + $nHelper;
			// Nächstesmal eins mehr
			$nHelper++; 
		}
	}
	
	/**
	 * Generiert HTML Code für das Paging.
	 * @param array arrPages, Array mit Seitenzahlen drin
	 * @return string HTML Code für Navigation
	 */
	private function generateHtml($arrPages) {
		$sHTML = '';
		$sign = '?';
		if (stristr($this->Path,'?'))
			$sign = '&';
		// Tabellenkopf generieren
		$sHTML .= '
		<div class="divPagingTable">
		<table class="pagingTable" width="100%" cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td>';
		// Link zum Anfang setzen
		$sHTML .= '<a class="cLinkPaging" href="'.$this->Path.$sign.'page=1">&lt;&lt;</a>&nbsp;&nbsp;';
		// Link zurücksetzen
		$nBack = $this->Page - 1;
		if ($nBack < 1) $nBack = 1;
		$sHTML .= '<a class="cLinkPaging" href="'.$this->Path.$sign.'page='.$nBack.'">&lt;</a>&nbsp;&nbsp;&nbsp;';
		// Seiten angeben, markierte highlighten
		for ($i = 0;$i < 5;$i++) {
			if ($arrPages[$i] != 0) {
				if ($arrPages[$i] == $this->Page) {
					$sHTML .= '<a class="cLinkPaging" href="'.$this->Path.$sign.'page='.$arrPages[$i].'"><strong>'.$arrPages[$i].'</strong></a> ';
				} else {
					$sHTML .= '<a class="cLinkPaging" href="'.$this->Path.$sign.'page='.$arrPages[$i].'">'.$arrPages[$i].'</a> ';
				}
			}
		}
		// Link zurücksetzen
		$nNext = $this->Page + 1;
		if ($nNext > $this->Pages) $nNext = $this->Pages;
		$sHTML .= '&nbsp;&nbsp;<a class="cLinkPaging" href="'.$this->Path.$sign.'ppage='.$nNext.'">&gt;</a>';
		// Link zum Ende setzen
		$sHTML .= '&nbsp;&nbsp;<a class="cLinkPaging" href="'.$this->Path.$sign.'ppage='.$this->Pages.'">&gt;&gt;</a>';
		// Tabelle abschliessen
		$sHTML .= '
				</td>
			</tr>
		</table>
		</div>
		';
		// Ergebnisse liefern
		return($sHTML);
	}
	
	/**
	 * SQL String zurückgeben
	 * @return string Verarbeitetes SQL Statement
	 */
	public function getSQL() {
		return($this->sSQL);
	}
}