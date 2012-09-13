<?php
/**
 * Diese Klasse stellt grundlegende Menufunktionen.
 * Wird verwendet zum darstellen und verarbeiten des Menus.
 * Eine Instanz $Menu wird automatisch erstellt und ist
 * im globalen Scope jederzeit verfügbar.
 * Dieses Menu bietet Javascript Dropdowns.
 * @author Michael Sebel <michael@sebel.ch>
 */
class listMenu implements menuInterface {
	/**
	 * Menuobjekte, array aus instanten von menuObject
	 * @var array
	 */
	protected $menuObjects = array();
	/**
	 * Referenz zum Zugriffsobjekt $Access
	 * @var access
	 */
	protected $Access = NULL;
	/**
	 * Referenz zum Datenbankobjekt $Conn
	 * @var dbConn
	 */
	protected $Conn = NULL;
	/**
	 * Objekt, welches die Verarbeitung von Menutypen übernimmt
	 * @var menuTypes
	 */
	protected $Menutypes = NULL;
	/**
	 * Referenz aus dem menuObjects array und repräsentiert das selektierte Menu
	 * @var menuObject
	 */
	public $CurrentMenu;
	
	/**
	 * Konstruktor, welcher alle Objekte lädt. 
	 * Das Menu wird nur beim ersten mal geladen und ist dann in der Session gelagert
	 * @param access Access, Referenz zum Zugriffsobjekt
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public function __construct(access &$Access,dbConn &$Conn) {
		// Zugriffsobjekt / Datenbankobjekt / Menutypen
		$this->Access = $Access;
		$this->Conn = $Conn;
		$this->Menutypes = new menuTypes($Conn,$Access);
		// Laden aller Menupunkte in korrekter Reihenfolge
		if (isset($_SESSION['menuObjects'])) {
			$this->menuObjects = $_SESSION['menuObjects'];
			// Aktuelles Menu setzen
			foreach ($this->menuObjects as $menuObject) {
				if ($menuObject->ID == page::menuID()) {
					$this->CurrentMenu = $menuObject;
					// Weitere Daten für das Menu laden
					$this->CurrentMenu->loadAdditional($this->Conn);
				}
			}
		} else {
			$this->loadMenuObjects("0");
			$_SESSION['menuObjects'] = $this->menuObjects;
		}
	}
	
	/**
	 * Alle Menuobjekte laden (für die Menuverwaltung).
	 * Zugriffe und inaktive/unsichtbare Menus werden nicht beachtet
	 * @param string sParent, Übergeordneter Item String
	 */
	public function loadAllMenuObjects($sParent) {
		$sSQL = 'SELECT mnu_ID,typ_ID,mnu_Index,mnu_Redirect,mnu_Invisible,mnu_Image,
		mnu_Path,mnu_Secured,mnu_Blank,mnu_Active,mnu_Item,mnu_Parent,mnu_Name FROM tbmenu WHERE
		man_ID = '.page::mandant().' AND mnu_Parent = \''.$sParent.'\' 
		ORDER BY mnu_Index DESC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$menuObject = new menuObject($row);
			array_push($this->menuObjects,$menuObject);
			$this->loadAllMenuObjects($row['mnu_Item']);
		}
	}
	
	/**
	 * Menuobjekte löschen und das menuObjects array neu initialisieren.
	 */
	public function reset() {
		unset($this->menuObjects);
		$this->menuObjects = array();
	}
	
	/**
	 * Menuobjekte laden unter beachtung von Zugriffen und inaktiven Menus.
	 * Zudem werden untergeordnete Punkte nur geladen wenn, das übergeordnete
	 * Menu auch ausgewählt wurden (in irgend einer Hierarchie)
	 * @param string sParent, Übergeordneter Item String
	 */
	protected function loadMenuObjects($sParent) {
		$sSQL = 'SELECT mnu_ID,typ_ID,mnu_Index,mnu_Redirect,mnu_Invisible,mnu_Image,
		mnu_Path,mnu_Secured,mnu_Blank,mnu_Item,mnu_Parent,mnu_Name,mnu_Active FROM tbmenu WHERE
		man_ID = '.page::mandant().'
		AND mnu_Parent = \''.$sParent.'\' ORDER BY mnu_Index DESC';
		$Res = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($Res)) {
			$bShow = false;
			// Schauen ob Secured
			if ($row['mnu_Secured'] == 1) {
				// Zugriff des aktuellen Users checken
				if ($this->Access->checkAccess($row['mnu_ID'])) $bShow = true;
			} elseif ($row['mnu_Active'] == 0) {
				if ($this->Access->getAccessType() == 1) {
					$bShow = true;
				}
			} else {
				// Nur admin und nur View Menupunkte checken
				$bShow = $this->Menutypes->checkOneSideType($row['typ_ID']);
			}
			// Wenn anzeigbar Menuobjekt erzeugen und in die Sammlung
			if ($bShow) {
				$menuObject = new menuObject($row);
				array_push($this->menuObjects,$menuObject);
				// Aktuelles Objekt?
				if ($menuObject->ID == page::menuID()) {
					$this->CurrentMenu = $menuObject;
					// Weitere Daten für das Menu laden
					$this->CurrentMenu->loadAdditional($this->Conn);
				}
				// Nächste hierarchie holen
				$this->loadMenuObjects($row['mnu_Item']);
			}
		}
	}
	
	/**
	 * Das Menu als HTML laden.
	 * Hier werden auch die Optionen für individuelle menu-HTML-Daten geladen
	 * @return string HTML Code mit dem Menu drin
	 */
	public function getMenu() {
		$out = '';
		$nLastLevel = 1;
		// Menu Rekursiv laden
		$this->getMenuRecursive('0',$out);
		// Wenn eingeloggt, logout link zeigen (letztes ul ersetzen)
		if ($this->Access->isLogin() == true) {
			$out = substr($out,0,strlen($out)-5).'<li><a href="/?logout">Logout</a></li></ul>';
		}
		return($out);
	}
	
	/**
	 * Holt das Menu rekursiv aus den Menuobjekte für eine Baumstruktur
	 * @param string $sParent Items mit diesem Parent laden
	 * @param string $out Referenz auf den Output
	 * @param string $attr Attribut für menu_XX Wert
	 */
	protected function getMenuRecursive($sParent,&$out,$attr = 'class') {
		$bHasUL = false;
		for ($i = 0;$i < count($this->menuObjects);$i++) {
			$menuObject = $this->menuObjects[$i];
			$bShow = false;
			if ($menuObject->Invisible == 0) $bShow = true;
			if ($this->Access->getAccessType() == 1) $bShow = true;
			// Nur was tun, wenn anzeigbar
			if ($bShow && ($menuObject->Parent == $sParent)) {
				// Prüfen ob UL daher kommmt
				if (!$bHasUL) {
					$out .= '<ul>';
					$bHasUL = true;
				}
				// Selektion bestimmen
				$sSelected = '';
				if ($menuObject->ID == page::menuID() || $this->isSubOf($menuObject->Item)) {
					// Klasse durch eindeutigkeit erweitern / allgemeine Klasse hinzufügen
					$sSelected = '_sel menu_selected';
				}
        $attributes = '';
        if ($menuObject->Blank == 1) {
          $attributes .= ' target="_blank"';
        }
				// Link generieren mit Klasse
				$out .= '<li><a href="'.$this->getLink($menuObject).'" '.$attr.'="menu_'.$menuObject->ID.$sSelected.'"'.$attributes.'>';
				// Name des Links und abschliessen, wenn inaktiver Punkt
				$sMenuName = $menuObject->Name;
				stringOps::htmlEnt($sMenuName);
				if ($menuObject->Active == 0) {
					$out .= '<span class="inactiveMenu">'.$sMenuName.'</span></a>';
				} else {
					$out .= $sMenuName.'</a>';
				}
				// Untermenus laden
				$this->getMenuRecursive($menuObject->Item,$out,$attr);
				// Listeneintrag schliessen / Umbruch des Quellcodes
				$out .= "</li>\n";
			}
		}
		if ($bHasUL) $out .= '</ul>';
	}

	/**
	 * Gets the link for a menu (old controller or name)
	 * @param $menuObject The Menuobject that is printed
	 * @return string A link to the desired module
	 */
	public function getLink(&$menuObject) {
		if (strlen($menuObject->Path) > 0) {
			return('/'.$menuObject->Path);
		} else {
			return('/controller.php?id='.$menuObject->ID);
		}
	}

	/**
	 * Prüft ob das gegebene Menu ein Folgemenu des aktuellen ist
	 * @param int $nMnuID Zu prüfendes Menu
	 */
	protected function isSubOf($sItem) {
		// Parent Item holen
		$sSQL = "SELECT mnu_ID,mnu_Item FROM tbmenu WHERE
		man_ID = ".page::mandant()." AND mnu_Parent = '$sItem'";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Schauen ob dessen Parent das ausgewählte ist
			if ($row['mnu_ID'] == page::menuID()) {
				return(true);
			} 
		}
		return(false);
	}
	
	/**
	 * Das Menu in Form von select Options bekommen.
	 * @param integer sSelected, ID des selektierten Menupunktes
	 * @return string <options> HTML Code für Selectbox output
	 */
	public function getSelectOptions($sSelected = NULL) {
		$sOutput = '';
		foreach ($this->menuObjects as $menuObject) {
			// Option erstellen
			$sOutput .= '<option value="'.$menuObject->ID.'"';
			// Selektierung machen
			if ($sSelected == $menuObject->ID) {
				$sOutput .= ' selected';
			}
			$sOutput .= '>';
			// Vorzeichen anzeigen
			for ($nCount = 0;$nCount < $menuObject->Level;$nCount++) {
				$sOutput .= '- - ';
			}
			// Wenn Level vorhanden, abschliessen
			if ($menuObject->Level > 0) $sOutput .= '&nbsp;&nbsp;';
			// Inhalt der Option und abschluss
			$sOutput .= htmlentities($menuObject->Name).'</option>'."\n";
		}
		return($sOutput);
	}
	
	/**
	 * Array der Menuobjekte für spezielle Verarbeitung zurückgeben.
	 */
	public function getMenuObjects() {
		return($this->menuObjects);
	}
}