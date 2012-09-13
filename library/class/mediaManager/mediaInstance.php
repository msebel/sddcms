<?php
class mediaInstance extends commonModule {
	/**
	 * Aufrufer des Mediamanager
	 * @var string
	 */
	public $Caller;
	/**
	 * Name der aktuellen Instanz
	 * @var string
	 */
	public $Instance;
	/**
	 * ID des anzuzeigenden Elementes
	 * @var integer
	 */
	public $Element;
	/**
	 * Typ der selektierten Datei (Konstanten)
	 * @var integer
	 */
	public $Type;
	/**
	 * Selektierte Datei
	 * @var string
	 */
	public $Selected;
	/**
	 * Das momentan bearbeitete File
	 * @var string
	 */
	public $Progress;
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	public $Conn;
	
	/**
	 * Objekte laden, überschrieben von Mutterklasse
	 */
	public function loadObjects() {
		// Nichts tun, zwecks kompatibilität
	}
	
	/**
	 * Objekt erstellen und alle set Methodn ausführen.
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public function __construct(dbConn &$Conn) {
		$this->Conn = $Conn;
		$this->setInstance();
		$this->setElement();
		$this->setNoCache();
		$this->setCaller();
		$this->setSelected();
		$this->setType();
		$this->setProgress();
		// Pfad des Files
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{ELE_ID}",$this->Element,$sPath);
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		$this->setProperty('path',$sPath);
	}
	
	/**
	 * Eine Option setzen
	 * @param string sProp, Name des zu setzenden Properties
	 * @param string sValue, Wert für die zu setzende Option
	 */
	public function setProperty($sProp,$sValue) {
		$_SESSION[$this->Instance][$sProp] = $sValue;
	}
	
	/**
	 * Eine Option bekommen, die mit setProperty gesetzt wurde
	 * @param string sProp, Name des zu holenden Properties
	 * @return string Wert der Option oder NULL wenn nicht gefunden
	 */
	public function getProperty($sProp) {
		$Return = NULL;
		if (isset($_SESSION[$this->Instance][$sProp])) {
			$Return = $_SESSION[$this->Instance][$sProp];
		}
		return($Return);
	}
	
	/**
	 * Prüfen ob Option vorhanden ist
	 * @param string sProp, Name der Option
	 * @return boolean True, wenn das Property einen Wert hat
	 */
	public function issetProp($sProp) {
		$bReturn = false;
		if (isset($_SESSION[$this->Instance][$sProp])) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Selektionswerte resetten.
	 * Die Selektionswerte bestimmen, welche Datei
	 * aktuell ausgewählt ist (sowie deren Dateityp)
	 */
	public function resetSelection() {
		unset($_SESSION[$this->Instance]['selected']);
		unset($_SESSION[$this->Instance]['type']);
	}
	
	/**
	 * Cachen ausschalten.
	 * - Expires: -1
	 * - Cache-Control: post-check=0, pre-check=0
	 * - Pragma: no-cache
	 * - Last-Modified: D, d M Y H:i:s GMT
	 */
	private function setNoCache() {
		header("Expires: -1");
		header("Cache-Control: post-check=0, pre-check=0");
		header("Pragma: no-cache");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	}
	
	/**
	 * Eine neue Instanz erstellen oder eine alte weiterführen
	 */
	private function setInstance() {
		$sInstance = 'mediamanager_'.$_SESSION['mediamanager']['element'];
		if (isset($_GET['element'])) {
			$sInstance = 'mediamanager_'.$_GET['element'];
		}
		// Wenn Instanz noch nicht korrekt, Menu Element erstellen
		if ($sInstance == 'mediamanager_') {
			$sInstance =  'mediamanager_'.self::getMenuElement();
		}
		if (!isset($_SESSION[$sInstance])) {
			$_SESSION[$sInstance] = array();
		}
		// Dies auch in den Member schreiben
		$this->Instance = $sInstance;
	}
	
	/**
	 * Den Caller des Mediamanager definieren
	 */
	private function setCaller() {
		// Caller aus der Session holen ...
		if (isset($_SESSION[$this->Instance]['caller'])) {
			$this->Caller = $_SESSION[$this->Instance]['caller'];
		} else {
			// Oder setzen
			$_SESSION[$this->Instance]['caller'] = $_GET['caller'];
			$this->Caller = $_SESSION[$this->Instance]['caller'];
		}
	}
	
	/**
	 * Das Element des Mediamanager definieren
	 */
	private function setElement() {
		// Element aus der Session holen ...
		if (isset($_SESSION['mediamanager']['element'])) {
			// Nur wenn kein $_GET['element'] vorhanden ist
			if (!isset($_GET['element'])) {
				$this->Element = $_SESSION['mediamanager']['element'];
			} elseif (isset($_GET['element'])) {
				$_SESSION['mediamanager']['element'] = $_GET['element'];
				$this->Element = $_SESSION['mediamanager']['element'];
			} else {
				// Menuelement holen
				$_SESSION['mediamanager']['element'] = self::getMenuElement();
				$this->Element = $_SESSION['mediamanager']['element'];
			}
		} else {
			// Oder setzen
			if (isset($_GET['element'])) {
				$_SESSION['mediamanager']['element'] = $_GET['element'];
			} else {
				$_SESSION['mediamanager']['element'] = self::getMenuElement();
			}
			$this->Element = $_SESSION['mediamanager']['element'];
		}
	}
	
	/**
	 * Das selektierte File herausfinden
	 */
	private function setSelected() {
		if (isset($_SESSION[$this->Instance]['selected'])) {
			$this->Selected = $_SESSION[$this->Instance]['selected'];
		} else {
			// Oder setzen
			$sSelected = '';
			// Wenn nicht editor der Caller ist, aus DB holen
			if ($this->Caller != 'editor') {
				$sSQL = "SELECT ele_File FROM tbelement
				WHERE ele_ID = ".$this->Element;
				$sSelected = $this->Conn->getFirstResult($sSQL);
			}
			// Ergebnisse speichern
			$_SESSION[$this->Instance]['selected'] = $sSelected;
			$this->Selected = $_SESSION[$this->Instance]['selected'];
		}
	}
	
	/**
	 * Typ der selektierten Datei wechseln
	 */
	private function setType() {
		if (isset($_SESSION[$this->Instance]['type'])) {
			$this->Type = $_SESSION[$this->Instance]['type'];
		} else {
			// Oder setzen
			$nType = mediaLib::getType($this->Selected);
			// Ergebnisse speichern
			$_SESSION[$this->Instance]['type'] = $nType;
			$this->Type = $_SESSION[$this->Instance]['type'];
		}
	}
	
	/**
	 * Bearbeitendes Element setzen
	 */
	private function setProgress() {
		// Progress aus der Session holen
		if (isset($_SESSION[$this->Instance]['progress']) && !isset($_GET['file'])) {
			$this->Progress = $_SESSION[$this->Instance]['progress'];
		} else {
			// Oder setzen
			$_SESSION[$this->Instance]['progress'] = $_GET['file'];
			$this->Progress = $_SESSION[$this->Instance]['progress'];
		}
	}
	
	/**
	 * Element anhand der menuID erstellen / holen
	 */
	private function getMenuElement() {
		$Conn = $this->Conn;
		$nMenuElement = 0;		// Menu Element
		$nResult = 0;			// Anzahl Menu Elemente
		// Zählen wie viele Elemente die MenuID hat
		$sSQL = "SELECT COUNT(ele_ID) FROM tbelement WHERE
		owner_ID = ".page::menuID();
		$nResult = $Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			// Menu Element auslesen
			$sSQL = "SELECT ele_ID FROM tbelement WHERE
			owner_ID = ".page::menuID();
			$nMenuElement = $Conn->getFirstResult($sSQL);
		} else {
			// Menu Element erstellen
			$sSQL = "INSERT INTO tbelement (owner_ID) VALUES (".page::menuID().")";
			$nMenuElement = $Conn->insert($sSQL);
		}
		return($nMenuElement);
	}
}