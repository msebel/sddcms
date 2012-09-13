<?php

class menuObject {
	
	/**
	 * Eindeutige ID des Menupunkte
	 * @var integer
	 */
	public $ID;
	/**
	 * Menutyp aus der Tabelle tbmenutyp
	 * @var integer
	 */
	public $Type;
	/**
	 * Sortierindex des Menupunktes
	 * @var integer
	 */
	public $Index;
	/**
	 * Gibt mit 0/1 an, ob der Menupunkt sichtbar ist
	 * @var integer
	 */
	public $Invisible;
	/**
	 * Gibt mit 0/1 an, ob der Menupunkt aktiv ist
	 * @var integer
	 */
	public $Active;
	/**
	 * Gibt mit 0/1 an, ob der Menupunkt gesichert ist
	 * @var integer
	 */
	public $Secured;
	/**
	 * Item im Stil einer Kapitelhierarchie (1.2.12 etc)
	 * @var string
	 */
	public $Item;
	/**
	 * Item des Übermenupunktes oder 0 wenn es Hierarchisch zuoberst ist
	 * @var string
	 */
	public $Parent;
	/**
	 * Name des Menupunktes
	 * @var string
	 */
	public $Name;
	/**
	 * Pfad zum Bild, wenn es ein Bildmenupunkt ist
	 * @var string
	 */
	public $Image;
	/**
	 * Numerierung der Hierarchie als Zahl
	 * @var integer
	 */
	public $Level;
	/**
	 * ID des Teasers dieses Menus
	 * @var integer
	 */
	public $Teaser;
	/**
	 * Keywords für Metatags
	 * @var string
	 */
	public $Metakeys = '';
	/**
	 * Description für Metatags
	 * @var string
	 */
	public $Metadesc = '';
	/**
	 * Titel des Menus
	 * @var string
	 */
	public $Title = '';
	/**
	 * Pfad des Menus (Individuelle URL)
	 * @var string
	 */
	public $Path = '';
	/**
	 * Gibt an, ob das Menu in neuem Fenster geöffnet wird
	 * @var int
	 */
	public $Blank = 0;
	
	/**
	 * Datenzeile aus der Menutabelle
	 * @param array $row, Menupunkt Datenzeile
	 */
	public function __construct($row) {
		$this->ID					= $row['mnu_ID'];
		$this->Type				= $row['typ_ID'];
		$this->Index			= $row['mnu_Index'];
		$this->Invisible	= $row['mnu_Invisible'];
		$this->Secured		= $row['mnu_Secured'];
		$this->Active			= $row['mnu_Active'];
		$this->Item				= $row['mnu_Item'];
		$this->Parent			= $row['mnu_Parent'];
		$this->Name				= $row['mnu_Name'];
		$this->Image			= $row['mnu_Image'];
		$this->Title			= $row['mnu_Title'];
		$this->Path				= $row['mnu_Path'];
		$this->Blank			= $row['mnu_Blank'];
		$this->getLevel();
	}

	/**
	 * Gibt die Instanz eines bestimmten Menus zurück
	 * @param $nMnuID Das Menu, von welchem eine Instanz benötigt wird
	 * @return menuObject Menuobjekt oder false wenn nichts gefunden
	 */
	public static function getInstance($nMnuID) {
		$Conn = singleton::conn();
		$nMnuID = getInt($nMnuID);
		$menus = singleton::menu()->getMenuObjects();
		foreach ($menus as $menu) {
			// Wenn es sichtbar ist im Menu, direkt die gecachete instanz nehmen
			if ($menu->ID == $nMnuID)	return($menu);
		}
		// Wenn nichts gefunden, von Datenbank holen
		$sSQL = 'SELECT mnu_ID,typ_ID,mnu_Index,mnu_Invisible,mnu_Secured,mnu_Blank,
		mnu_Active,mnu_Item,mnu_Parent,mnu_Name,mnu_Image,mnu_Title,mnu_Path
		FROM tbmenu WHERE man_ID = '.page::mandant().' AND mnu_ID ='.$nMnuID;
		$nRes = $Conn->execute($sSQL);
		if ($row = $Conn->next($nRes)) {
			return(new menuObject($row));
		}
		// Wenn nichts gefunden, Error
		return(false);
	}

	/**
	 * Holt den Pfad oder sonst den Controller Link für das Menu
	 * @return string Link zum aktuellen Menu (controller/pfad)
	 */
	public function getLink($param = '') {
		if (strlen($this->Path) > 0) {
			$link = '/'.$this->Path;
			if (strlen($param) > 0) $link.= '?'.$param;
		} else {
			$link = '/controller.php?id='.$this->ID;
			if (strlen($param) > 0) $link.= '&'.$param;
		}
		return($link);
	}
	
	/**
	 * Zusätzliche Daten laden für aktuellen Menupunkt
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public function loadAdditional(dbConn &$Conn) {
		// Metadaten holen
		$sSQL = "SELECT tas_ID,mnu_Metakeys,mnu_Metadesc FROM
		tbmenu WHERE mnu_ID = ".$this->ID;
		$nRes = $Conn->execute($sSQL);
		$row = $Conn->next($nRes);
		// Daten in Members schreiben
		$this->Metakeys = $row['mnu_Metakeys'];
		$this->Metadesc = $row['mnu_Metadesc'];
		$this->Teaser	= getInt($row['tas_ID']);
	}
	
	/**
	 * Berechnen in welchem Hierarchischen Level sich ein Menupunkt befindet
	 */
	private function getLevel() {
		$sItem = $this->Item;
		// Anzahl Punkte zählen
		$nCount = 0;
		$sItem = str_replace(".","",$sItem,$nCount);
		$this->Level = $nCount;
	}
}