<?php
/**
 * Speichert alle Teaserelemente des Standardteasers in die Session (beim ersten Laden).
 * Holt sich das HTML aller gegebener Teaserelemente des aktuellen Menupunktes
 * @author Michael Sebel <michael@sebel.ch>
 */
class teaserParser {
	
	/**
	 * Referenz zum Datenbank Objekt
	 * @var dbConn
	 */
	private $Conn;
	
	/**
	 * Referenz zum aktuellen Menuobjekt
	 * @var menu
	 */
	private $Menu;
	
	/**
	 * Referenz zum Sprachressourcen Objekt
	 * @var resources
	 */
	private $Res;
	
	/**
	 * Variable in der HTML Code eingefüllt (anhängen) wird
	 * @var string
	 */
	private $out;
	
	/**
	 * Referenz zum Objekt der Template Engine
	 * @var template
	 */
	private $tpl;
	
	/**
	 * Array aller Teasertypen
	 * @var array
	 */
	private $Types;
	
	/**
	 * Anzahl der Teaserelemente
	 * @var array
	 */
	public $Elements;
	
	/**
	 * Sekunden nach der, der Standardteaser wieder neugeladen wird
	 * @var integer
	 */
	const EXPIRE = 60;
	
	/**
	 * Objekt mit div. Nutzobjekten erstellen.
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param resources Res, Referenz zu den Sprachressourcen
	 * @param template tpl, Referenz zum Templateobjekt
	 * @param menu Menu, Referenz zum Menuobjekt
	 */
	public function __construct(dbConn &$Conn,resources &$Res,&$tpl,&$Menu) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->tpl = $tpl;
		$this->Menu = $Menu;
		$this->Types = array();
		$this->Elements = 0;
		// Nur wenn Menuobjekt vorhanden
		if ($Menu instanceof menuObject) {
			$this->constructTeaser();
		}
	}
	
	/**
	 * Teaser aufbauen.
	 */
	private function constructTeaser() {
		// Schauen ob der Teaser aus der Session geholt wird
		//if ($this->standardTeaserAvailable()) {
		//	$this->out = $_SESSION['standardteaser'];
		//	$this->Elements = 1; // Damit was gezeigt wird
		//} else {
			$this->loadTeaser();
			$this->saveToSession();
		//}
		// Ins Teaser Template füllen und Code darum
		if ($this->Elements > 0) {
			stringOps::htmlViewEnt($this->out);
			$this->out = '
			<div class="teaserContainer">
				'.$this->out.'
			</div>';
			$this->tpl->aTeaser($this->out);
		}
	}
	
	/**
	 * Schauen ob der Standardteaser aus der Session geholt werden kann.
	 * @return boolean true wenn standard teaser verfügbar
	 */
	private function standardTeaserAvailable() {
		$isAvailable = false;
		// Wenn Session vorhanden
		if (isset($_SESSION['standardteaser'])) {
			// Wenn Standardteaser aufzurufen
			if (page::teaserID() == $this->Menu->Teaser) {
				$isAvailable = true;
			}
			// Doch nicht available, wenn Zeit abgelaufen
			if ($this->sessionExpired()) {
				$isAvailable = false;
			}
		}
		return($isAvailable);
	}
	
	/**
	 * Prüfen ob der Standardteaser abgelaufen ist.
	 * @return boolean true wenn Standardteaser veraltet ist
	 */
	private function sessionExpired() {
		$isExpired = false;
		// Wenn die Time Session nicht existiert (th. n. mögl.)
		if (!isset($_SESSION['standardteaser_time'])) {
			$isExpired = true;
		}
		// Aktuelle Zeit nehmen
		$nNow = time();
		$nDiff = $nNow - $_SESSION['standardteaser_time'];
		// Expired wenn Differenz grösser als EXPIRED
		if ($nDiff > self::EXPIRE) {
			$isExpired = true;
		}
		// Status melden
		return($isExpired);
	}
	
	/**
	 * HTML in Session speichern, wenn Standardteaser.
	 */
	private function saveToSession() {
		// Wenn Standardteaser
		if ($this->Menu->Teaser == page::teaserID()) {
			$_SESSION['standardteaser'] = $this->out;
			// Und Timestamp speichern
			$_SESSION['standardteaser_time'] = time();
		}
	}
	
	/**
	 * Teaser Laden und in die Session, sofern es der Standard Teaser ist.
	 */
	private function loadTeaser() {
		// Teasertypen laden
		$this->loadTeaserTypes();
		// Teaser Elemente der Reihe nach laden
		$sSQL = 'SELECT tbteaser.tap_Title,tbteaser.tty_ID,tbteaser.tap_ID FROM tbteaser 
		INNER JOIN tbteasersection_teaser ON tbteaser.tap_ID = tbteasersection_teaser.tap_ID
		WHERE tbteasersection_teaser.tas_ID = '.$this->Menu->Teaser.' 
		AND tbteasersection_teaser.tsa_Active = 1
		ORDER BY tbteasersection_teaser.tsa_Sortorder ASC';
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
					// Trenn DIVs für Teaser erstellen
					if (option::available('enhancedTeaser')) {
						// Teaser Header
						$this->out .= '<div class="teaserHeader"></div>';
					} else {
						// Divider vorschalten, wenn nicht erstes Element
						if ($this->Elements >= 1) {
							$this->out .= '<div class="teaserDivider"></div>';
						}
					}
					// Code vor Teaserelement
					$this->out .= '<div class="teaserElement '.$row['tty_Classname'].'">';
					$Teaser->appendHtml($this->out);
					$this->out .= '</div>';
					// End Element je nach Konfiguration
					if (option::available('enhancedTeaser')) {
						// Teaser Header
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
				// Error Loggen
				logging::error($e->getMessage());
				// Etwas für den User ausgeben
				if (!DEBUG) {
					$this->out .= '
					<div class="teaserElement">
						Error loading the teaser:<br>
						'.$row['tap_Title'].'
					</div>';
				} else {
					// Oder für den Admin, im Debug Modus
					$this->out .= 
					$e->getMessage().'<br>'.
					$e->getTraceAsString();
				}
			}
		}
	}
	
	/**
	 * Teasertypen zuordnen.
	 */
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
	
	/**
	 * Teasertypen laden.
	 */
	private function loadTeaserTypes() {
		// Alle Teasertypen der Webseite laden
		$sSQL = 'SELECT tty_ID,tty_Name,tty_Viewpath,tty_Classname 
		FROM tbteasertyp WHERE page_ID = '.page::ID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Types,$row);
		}
		// Alle globalen Teasertypenladen
		$this->Conn->setGlobalDB();
		$sSQL = 'SELECT tty_ID,tty_Name,
		tty_Viewpath,tty_Classname FROM tbteasertyp';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Types,$row);
		}
		$this->Conn->setInstanceDB();
	}
}