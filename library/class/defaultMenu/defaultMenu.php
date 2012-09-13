<?php
/**
 * Diese Klasse stellt grundlegende Menufunktionen.
 * Wird verwendet zum darstellen und verarbeiten des Menus.
 * Eine Instanz $Menu wird automatisch erstellt und ist
 * im globalen Scope jederzeit verfügbar
 * @author Michael Sebel <michael@sebel.ch>
 */
class defaultMenu implements menuInterface {
	/**
	 * Menuobjekte, array aus instanten von menuObject
	 * @var array
	 */
	private $menuObjects = array();
	/**
	 * Referenz zum Zugriffsobjekt $Access
	 * @var access
	 */
	private $Access = NULL;
	/**
	 * Referenz zum Datenbankobjekt $Conn
	 * @var dbConn
	 */
	private $Conn = NULL;
	/**
	 * Objekt, welches die Verarbeitung von Menutypen übernimmt
	 * @var menuTypes
	 */
	private $Menutypes = NULL;
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
		$sSQL = 'SELECT mnu_ID,typ_ID,mnu_Index,mnu_Redirect,mnu_Invisible,mnu_Image,mnu_Path,
		mnu_Secured,mnu_Active,mnu_Item,mnu_Blank,mnu_Parent,mnu_Name,mnu_Title FROM tbmenu WHERE
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
	private function loadMenuObjects($sParent) {
		$sSQL = 'SELECT mnu_ID,typ_ID,mnu_Index,mnu_Redirect,mnu_Invisible,mnu_Image,mnu_Path,
		mnu_Secured,mnu_Item,mnu_Parent,mnu_Name,mnu_Blank,mnu_Title,mnu_Active FROM tbmenu WHERE
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
		$sHtml['menuStart'] = '<ul class="cMenuContainer">';
		$sHtml['itemStart'] = '<li class="{CLASS}">';
		$sHtml['itemEnd'] = '</li>';
		$sHtml['menuEnd'] = '</ul>';
		// Schauen ob anderer HTML Code in Optionen vorhanden
		$this->getCustomMenu($sHtml);
		// Menuanfang ausgeben
		$out = $sHtml['menuStart'];
		foreach ($this->menuObjects as $menuObject) {
			$bShow = false;
			if ($menuObject->Invisible == 0) $bShow = true;
			if ($this->Access->getAccessType() == 1) $bShow = true;
			// Nur was tun, wenn anzeigbar
			if ($bShow && $this->checkHierarchy($menuObject)) {
				// Klasse des Menupunktes definieren
				$nLevel = ($menuObject->Level)+1;
				// Klasse definieren
				$sClass = 'cMenuItem'.$nLevel;
				// Wenn ausgewähltes Menu, spezielle Klasse
				if ($menuObject->ID == $this->CurrentMenu->ID) {
					$sClass = 'cMenuItemSel'.$nLevel;
				} elseif ($this->CurrentMenu->Level > 0 && option::available('SuperiorHierarchy')) {
					// Wenn Aktuelles menu grösser als 1 hierarchie
					// Prüfen, ob das aktuelle ein untermenu sein könnte
					if ($this->isSuperior($menuObject)) {
						$sClass = 'cMenuItemSel'.$nLevel;
					}
				}
        $attributes = '';
        if ($menuObject->Blank == 1) {
          $attributes .= ' target="_blank"';
        }
				// Item generieren
				if ($menuObject->Image == 1) {
					$sPathAlt = '/design/'.page::design().'/menu/'.$menuObject->ID.'-over.gif';
					if ($menuObject->ID == $this->CurrentMenu->ID) {
						$out .= str_replace("{CLASS}",$sClass,$sHtml['itemStart']);
						$out .= '<a href="'.$this->getLink($menuObject).'" id="menu_'.$menuObject->ID.'"'.$attributes.'>
						<img src="'.$sPathAlt.'" alt="'.$menuObject->Name.'" title="'.$menuObject->Name.'" border="0"></a>'.$sHtml['itemEnd'];
					} else {
						$sPath = '/design/'.page::design().'/menu/'.$menuObject->ID.'.gif';
						$sMouseover = 'onMouseover="imageOver(\'menu_'.$menuObject->ID.'\',\'\',\''.$sPathAlt.'\',1);"';
						$sMouseout = 'onMouseout="imageRestore();"';
						$out .= str_replace("{CLASS}",$sClass,$sHtml['itemStart']);
						$out .= '<a href="'.$this->getLink($menuObject).'" id="menu_'.$menuObject->ID.'"'.$attributes.' '.$sMouseover.' '.$sMouseout.'>
						<img src="'.$sPath.'" alt="'.$menuObject->Name.'" title="'.$menuObject->Name.'" border="0" name="menu_'.$menuObject->ID.'"></a>'.$sHtml['itemEnd'];
					}
				} else {
					$out .= str_replace('{CLASS}',$sClass,$sHtml['itemStart']);
					// Link generieren mit Klasse
					$out .= '<a href="'.$this->getLink($menuObject).'" class="aMenuItem'.$nLevel.'" id="menu_'.$menuObject->ID.'"'.$attributes.'>';
					// Name des Links und abschliessen, wenn inaktiver Punkt
					$sMenuName = $menuObject->Name;
					stringOps::htmlEnt($sMenuName);
					if ($menuObject->Active == 0) {
						$out .= '<span class="inactiveMenu">'.$sMenuName.'</span></a>'.$sHtml['itemEnd'];
					} else {
						$out .= $sMenuName.'</a>'.$sHtml['itemEnd'];
					}
				}
				// Umbruch des Quellcodes
				$out .= "\n";
			}
		}
		// Wenn eingeloggt, logout link zeigen
		if ($this->Access->isLogin() == true) {
			$out .= str_replace('{CLASS}','cMenuItem1',$sHtml['itemStart']);
			// Link generieren mit Klasse
			$out .= '<a href="/?logout" class="aMenuItem1">';
			// Name des Links und abschliessen
			$out .= 'Logout</a>'.$sHtml['itemEnd'];
		}
		// Menuende ausgeben
		$out .= $sHtml['menuEnd'];
		return($out);
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
	 * Prüft ob das Menu Aufgrund der Hierarchie angezeigt wird.
	 * Die Funktion geht davon aus, dass der Menupunkt nicht anzeigbar ist
	 * Dies wird aber umgekehrt/ermöglicht wenn:
	 * - Der gecheckte Menupunkt den gleichen Parent wie der aktuelle hat
	 * - Der gecheckte Menupunkt ein überobjekt des aktuellen ist
	 * - Der gecheckte Menupunkt in der ersten Hierarchie (0) ist
	 * @return boolean true = anzeigbar
	 */
	private function checkHierarchy(&$menuObject) {
		$bVisible = false;
		// Checken ob gleicher Parent
		if ($menuObject->Parent == $this->CurrentMenu->Parent) {
			$bVisible = true;
		}
		
		// Unterobjekt(e) checken
		if (stristr($this->CurrentMenu->Item,$menuObject->Parent) !== false) {
			// Wenn vergleichsparent keinen Punkt hat (Level 1)
			if ($menuObject->Level == 1) {
				// Nur Anzeigen, wenn der vergleichparent am Anfang ist
				if ($this->CurrentMenu->Level == 0) {
					if ($this->CurrentMenu->Item == $menuObject->Parent) {
						$bVisible = true;
					}
				} else {
					if (substr($this->CurrentMenu->Item,0,strpos($this->CurrentMenu->Item,'.')) == $menuObject->Parent) {
						$bVisible = true;
					}
				}
			} else {
				// Wenn nicbt Level 1, ist soweit anzeigebar
				$bVisible = true;
			}
		}
		
		// 1. Hierarchie ist über alle Zweifel erhaben
		if ($menuObject->Level == 0) {
			$bVisible = true;
		}
		return($bVisible);
	}
	
	/**
	 * Spezielle HTML Codes für das Menu.
	 * Die bestehenden Codes im $Html Array werden durch vorhandene Options ersetzt
	 * @param array Html, Array der menu/item start/end codes
	 */
	private function getCustomMenu(&$sHtml) {
		// In den Optionen alternativen HTML Code suchen
		if (option::available('menuStart')) $sHtml['menuStart'] = option::get('menuStart');
		if (option::available('itemStart')) $sHtml['itemStart'] = option::get('itemStart');
		if (option::available('itemEnd')) 	$sHtml['itemEnd'] 	= option::get('itemEnd');
		if (option::available('menuEnd')) 	$sHtml['menuEnd'] 	= option::get('menuEnd');
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
	 * Prüft, ob das zu checkende Element ein Übergeordnetes
	 * Element des aktuell sichtbaren Menus ist
	 */
	private function isSuperior(menuObject &$menuChecked) {
		$Last = false;
		$isSelected = false;
		$ParentObject = $this->CurrentMenu;
		// Solange durchgehen bis erste Hierarchie durchsucht oder Abbruch
		while (true) {
			// Alle Menuobjekte durchgehen
			foreach ($this->menuObjects as $menuObject) {
				// Wenn wir in der ersten Hierarchie sind
				if ($ParentObject->Parent == 0) $Last = true;
				// Prüfen ob Item mit letzten Parent übereinstimmt
				if ($ParentObject->Parent == $menuObject->Item) {
					if ($menuObject->ID == $menuChecked->ID) {
						$isSelected = true;
						// Durchbrechen des For/des While
						break 2;
					} else {
						$ParentObject = $menuObject;
						break;
					}
				} 
			}
			// Wenn letzter durchgang, beenden
			if ($Last) break;
		}
		return($isSelected);
	}
	
	/**
	 * Array der Menuobjekte für spezielle Verarbeitung zurückgeben.
	 */
	public function getMenuObjects() {
		return($this->menuObjects);
	}
}