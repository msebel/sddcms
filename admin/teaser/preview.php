<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

class teaserParserPreview {
	
	private $Conn;		// Datenbankverbindung
	private $Teaser;	// Aktuelle TeaserID
	private $Res;		// Ressourcen Objekt
	private $out;		// Output Variable
	private $tpl;		// Templateengine
	private $Types;		// Teasertypen Array
	public $Elements;	// Anzahl Teaserelemente
	
	// Objekt mit div. Nutzobjekten erstellen
	public function __construct(dbConn &$Conn,resources &$Res,&$tpl,&$Teaser) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->tpl = $tpl;
		$this->Teaser = $Teaser;
		$this->Types = array();
		$this->Elements = 0;
		$this->checkAccess();
		$this->constructTeaser();
	}
	
	// Prüft ob der aufgerufene Teaser dem Mandanten gehört
	private function checkAccess() {
		$sSQL = "SELECT COUNT(man_ID) FROM tbteasersection
		WHERE tas_ID = ".$this->Teaser." AND man_ID = ".page::mandant();
		$nReturn = $this->Conn->getCountResult($sSQL);
		// Wenn nicht Result = 1, error
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Teaser aufbauen
	private function constructTeaser() {
		// Schauen ob der Teaser aus der Session geholt wird
		$this->loadTeaser();
		// Ins Teaser Template füllen und Code darum
		if ($this->Elements > 0) {
			$this->out = '
			<div class="teaserContainer">
				'.$this->out.'
			</div>';
			$this->tpl->aTeaser($this->out);
		}
	}
	
	// Teaser Laden und in die Session, sofern es der Standard Teaser ist
	private function loadTeaser() {
		// Teasertypen laden
		$this->loadTeaserTypes();
		// Teaser Elemente der Reihe nach laden
		$sSQL = "SELECT tbteaser.tap_Title,tbteaser.tty_ID,tbteaser.tap_ID FROM tbteaser 
		INNER JOIN tbteasersection_teaser ON tbteaser.tap_ID = tbteasersection_teaser.tap_ID
		WHERE tbteasersection_teaser.tas_ID = ".$this->Teaser." 
		AND tbteasersection_teaser.tsa_Active = 1
		ORDER BY tbteasersection_teaser.tsa_Sortorder ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Teasertyp matchen
			$this->matchTypes($row);
			// Teaser laden 
			try {
				require_once(BP.$row['tty_Viewpath']);
				$Teaser = new $row['tty_Classname'];
				$Teaser->setID($row['tap_ID']);
				$Teaser->setData($this->Conn,$this->Res,$row['tap_Title']);
				// Teaserelement darstellen wenn Output vorhanden
				if ($Teaser->hasOutput()) {
					// Teaser je nach Konfiguration anzeigen
					if (option::available('enhancedTeaser')) {
						// Teaser Header erstellen
						$this->out .= '<div class="teaserHeader"></div>';
					} else {
						// Divider vorschalten, wenn nicht erstes Element
						if ($this->Elements >= 1) {
							$this->out .= '<div class="teaserDivider"></div>';
						}
					}
					// Code vor Teaserelement
					$this->out .= '<div class="teaserElement">';
					$Teaser->appendHtml($this->out);
					$this->out .= '</div>';
					// Teaser je nach Konfiguration abschliessen
					if (option::available('enhancedTeaser')) {
						// Teaser Header erstellen
						$this->out .= '<div class="teaserFooter"></div>';
					}
					// Element zählen
					$this->Elements++;
				} else {
					// Trotzdem ausführen, pseudo Variable übergeben
					$BLACKHOLE = '';
					$Teaser->appendHtml($BLACKHOLE);
				}
			} catch (Exception $e) {
				$this->out .= '
				<div class="teaserElement">
					ERROR WHILE LOADING:<br>
					'.$row['tap_Title'].'
				</div>';
			}
		}
	}
	
	// Teasertypen zuordnen
	private function matchTypes(&$row) {
		// Alle Typen durchgehen
		foreach ($this->Types as $Type) {
			// Prüfen ob Match zwischen Element / Type
			if ($row['tty_ID'] == $Type['tty_ID']) {
				$row['tty_Viewpath'] = $Type['tty_Viewpath'];
				$row['tty_Name'] = $Type['tty_Name'];
				$row['tty_Classname'] = $Type['tty_Classname'];
				break; // Loop verlassen
			}
		}
	}
	
	// Teasertypen laden
	private function loadTeaserTypes() {
		// Alle Teasertypen der Webseite laden
		$sSQL = "SELECT tty_ID,tty_Name,tty_Viewpath,tty_Classname 
		FROM tbteasertyp WHERE page_ID = ".page::ID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Types,$row);
		}
		// Alle globalen Teasertypenladen
		$this->Conn->setGlobalDB();
		$sSQL = "SELECT tty_ID,tty_Name,
		tty_Viewpath,tty_Classname FROM tbteasertyp";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Types,$row);
		}
		$this->Conn->setInstanceDB();
	}
}

// Teaser laden und anzeigen wenn nicht Admin
$TeaserID = getInt($_GET['teaser']);
$Teaser = new teaserParserPreview($Conn,$Res,$tpl,$TeaserID);

// System abschliessen
require_once(BP.'/cleaner.php');