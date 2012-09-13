<?php
// Funktionsklasse für das Menu
class moduleMenu extends commonModule {
	
	Const MAX_HIERARCHY = 7;
	Const MAX_CHARS = 36;
	Const MENU_OFFSET = 20;
	Const MENU_ADDITION = 10;
	
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
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	// Neuen Menupunkt erstellen
	public function addMenu() {
		$nMenu = stringOps::getGetEscaped('newitem',$this->Conn);
		$nMenuID = getInt($_GET['menu']);
		$sNewItem = $this->getNextItem($nMenu);
		$nNewIndex = $this->getNextIndex($nMenu);
		// Checken in welcher Hierarchie das Menu ist
		$nLevel = 0;
		$sTemp = str_replace(".","",$nMenu,$nLevel);
		if ($nLevel <= self::MAX_HIERARCHY && strlen($sNewItem) <= self::MAX_CHARS) {
			// Neuen Menueintrag erfassen
			$nNewID = ownerID::get($this->Conn);
			$sSQL = "INSERT INTO tbmenu (mnu_ID,man_ID,typ_ID,tas_ID,mnu_Index,
			mnu_Active, mnu_Invisible,mnu_Secured,mnu_Item,mnu_Parent,mnu_Name,mnu_Title)
			VALUES ($nNewID,".page::mandant().",100,".page::teaserID().",$nNewIndex,
			0,0,0,'$sNewItem','$nMenu','< ".$this->Res->html(113,page::language())." >','')";
			$this->Conn->command($sSQL);
		}
		// Menu Session Objekte löschen
		unset($_SESSION['menuObjects']);
		// Zur Startseite zurück
		redirect('location: /admin/menu/index.php?id='.page::menuID().'&menu='.$nMenuID); ;
	}
	
	// Menupunktdaten speichern
	public function saveMenuProperties() {
		// Menu ID holen
		$nMenuID = getInt($_GET['menu']);
		// Fehler Array
		$Errors = array();
		// Eingaben des Users validieren
		$sName = $this->validateMenuname($Errors);
		$nType = $this->validateMenutype($Errors);
		$isActive = $this->validateCheckbox($Errors,'active');
		$isInvisible = $this->validateCheckbox($Errors,'invisible');
		$isSecured = $this->validateCheckbox($Errors,'secured');
		$blank = $this->validateCheckbox($Errors,'blank');
		$sExternal = $this->validateExternal($Errors);
		$nInternal = $this->validateInternal($Errors);
		$nTeaserID = $this->validateTeaser($Errors);
		$sParent = $this->validateParent($Errors);
		$sItem = $this->getNextItem($sParent);
		$sShorttag = $this->validateShorttag($Errors);
		$sPath = $this->validatePath($Errors);
		// Ungeprüfte Eingabefeldes
		$nIndex = getInt($_POST['index']);
		$sMetakeys = stringOps::getPostEscaped('metakeys',$this->Conn);
		$sTitle = stringOps::getPostEscaped('title',$this->Conn);
		$sMetadesc = stringOps::getPostEscaped('metadesc',$this->Conn);
		// HTML aus Metas entfernen
		stringOps::noHtml($sMetakeys);
		stringOps::noHtml($sMetadesc);
		// Wenn keine Errors, loslegen
		if (count($Errors) == 0) {
			// Parent / Item ab der Hierarchie anpassen
			$this->cascadeItemUpdate($nMenuID,$sItem);
			// SQL generieren und abfeuern
			$sSQL = "UPDATE tbmenu SET
			mnu_Name = '$sName', typ_ID = $nType, tas_ID = $nTeaserID,
			mnu_Invisible = $isInvisible, mnu_Active = $isActive, 
			mnu_Secured = $isSecured, mnu_Index = $nIndex, 
			mnu_External = $sExternal, mnu_Redirect = $nInternal, 
			mnu_Metadesc = '$sMetadesc', mnu_Metakeys = '$sMetakeys',
			mnu_Shorttag = '$sShorttag', mnu_Parent = '$sParent',
			mnu_Title = '$sTitle', mnu_Item = '$sItem',
			mnu_Path = '$sPath', mnu_Blank = $blank
			WHERE mnu_ID = $nMenuID";
			$this->Conn->command($sSQL);
			// Menu Session Objekte löschen
			unset($_SESSION['menuObjects']);
			// Erfolg ausgeben und weiterleiten
			logging::debug('saved menu properties');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/menu/menu.php?id='.page::menuID().'&menu='.$nMenuID); 
		} else {
			// Misserfolg ausgeben und weiterleiten
			logging::error('error saving menu properties');
			$this->setErrorSession($Errors);
		}
	}
	
	// Einen Menupunkt löschen
	public function deleteMenu() {
		$nMenuID = getInt($_GET['delete']);
		// ID von seinem Parent holen
		$nParentID = getInt($_GET['menu']);
		$nResult = $this->isLastItem($nMenuID);
		// Löschen wenn kein Ergebnis
		if ($nResult == 0) {
			$sSQL = "DELETE FROM tbmenu WHERE mnu_ID = $nMenuID";
			$this->Conn->command($sSQL);
			logging::debug('deleted menu');
		}
		// Menu Session Objekte löschen
		unset($_SESSION['menuObjects']);
		// Zurück zur Startseite
		redirect('location: /admin/menu/index.php?id='.page::menuID().'&menu='.$nParentID); 
	}
	
	public function isLastItem($nMenuID) {
		// Schauen um welches Item es sich handelt
		$sSQL = "SELECT mnu_Item FROM tbmenu WHERE mnu_ID = $nMenuID";
		$sItem = $this->Conn->getFirstResult($sSQL);
		// Nur wenn das Menu nirgendwo Parent ist, kann man es löschen
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
		WHERE mnu_Parent = '$sItem' AND man_ID = ".page::mandant();
		return($this->Conn->getCountResult($sSQL));
	}
	
	public function getMenuInfo(menuObject &$menuObject) {
		$sHtml = '';
		// Prüfen ob gesichert
		if ($menuObject->Secured == 1) {
			$sHtml .= '&nbsp;<img src="/images/icons/key.png" alt="'.$this->Res->html(106,page::language()).'" title="'.$this->Res->html(106,page::language()).'" border="0">&nbsp;';
		}
		// Prüfen ob unsichtbar
		if ($menuObject->Invisible == 1) {
			$sHtml .= '&nbsp;<img src="/images/icons/magifier_zoom_out.png" alt="'.$this->Res->html(107,page::language()).'" title="'.$this->Res->html(107,page::language()).'" border="0">&nbsp;';
		}
		// Prüfen ob inaktiv
		if ($menuObject->Active == 0) {
			$sHtml .= '&nbsp;<img src="/images/icons/page_inactive.png" alt="'.$this->Res->html(443,page::language()).'" title="'.$this->Res->html(443,page::language()).'" border="0">&nbsp;';
		}
		// HTML zurückgeben
		return($sHtml);
	}
	
	// Prüfen ob das Menu bearbeitet werden darf
	public function checkEditable() {
		// Benutzer abholen
		$nMenuID = getInt($_GET['menu']);
		// Checken ob der User von diesem Mandanten ist
		$allowEdit = false;
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE 
		mnu_ID = $nMenuID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $allowEdit = true;
		// Benutzer effektiv löschen
		if ($allowEdit == false) {
			// Fehler ausgeben und auf Startseite
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/menu/index.php?id='.page::menuID());
		} 		
	}
	
	// Globale Menupunkte als Options holen
	public function getGlobalTypes($nType) {
		$this->Conn->setGlobalDB();
		$sHtml = '<optgroup label="----------------">';
		$sSQL = "SELECT typ_Name,typ_ID FROM tbmenutyp ORDER BY typ_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			stringOps::htmlEnt($row['typ_Name']);
			$sHtml .= '<option value="'.$row['typ_ID'].'"'.checkDropDown($row['typ_ID'],$nType).'>'.$row['typ_Name'].'</option>'."\n";
		}
		$sHtml .= '</optgroup>';
		$this->Conn->setInstanceDB();
		return($sHtml);
	}
	
	// Menudaten holen
	public function loadData($nMenuID) {
		$sMenuData = NULL;
		$sSQL = "SELECT typ_ID,tas_ID,mnu_Index,mnu_Redirect,mnu_Active,
		mnu_Shorttag,mnu_Invisible,mnu_Secured,mnu_Blank,mnu_Name,mnu_Parent,mnu_Path,
		mnu_External,mnu_Metakeys,mnu_Metadesc,mnu_Title
		FROM tbmenu WHERE mnu_ID = $nMenuID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sMenuData = $row;
		}
		return($sMenuData);
	}
	
	// Teaser Options bekommen
	public function getTeaserOptions($nTeaserID) {
		$out = '';
		// Kein Teaser Option einfügen
		$out .= '<option value="0"'.checkDropDown(0,$nTeaserID).'>'.$this->Res->html(402,page::language()).'</option>'."\n";
		// Teaser des Mandanten lesen
		$sSQL = "SELECT tas_ID,tas_Desc FROM tbteasersection WHERE man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['tas_ID'].'"'.checkDropDown($row['tas_ID'],$nTeaserID).'>'.$row['tas_Desc'].'</option>'."\n";
		}
		return($out);
	}
	
	// Spezifische Menupunkte als Options holen
	public function getCustomTypes($nType) {
		$sHtml = '';
		$sSQL = "SELECT typ_Name,typ_ID FROM tbmenutyp
		WHERE page_ID = ".page::ID()." ORDER BY typ_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			stringOps::htmlEnt($row['typ_Name']);
			$sHtml .= '<option value="'.$row['typ_ID'].'"'.checkDropDown($row['typ_ID'],$nType).'>'.$row['typ_Name'].'</option>'."\n";
		}
		// Optgroups dazwischen, wenn Einträge vorhanden
		if (strlen($sHtml) > 0) {
			$sHtml = '<optgroup label="----------------">'.$sHtml.'</optgroup>';
			//$sHtml = $sTemp;
		}
		return($sHtml);
	}
	
	// Gibt das aktuelle Menuobjekt zurück
	public function getCurrentMenu(&$menuObjects) {
		$nMenuID = getInt($_GET['menu']);
		$currentMenu = NULL;
		foreach ($menuObjects as $menuObject) {
			if ($nMenuID == $menuObject->ID) {
				$currentMenu = $menuObject;
			}
		}
		return($currentMenu);
	}
	
	// Menuhierarchie abchecken
	// Die Funktion geht davon aus, dass der Menupunkt nicht anzeigbar ist
	// Dies wird aber umgekehrt/ermöglicht wenn:
	// - Der gecheckte Menupunkt den gleichen Parent wie der aktuelle hat
	// - Der gecheckte Menupunkt ein überobjekt des aktuellen ist
	// - Der gecheckte Menupunkt in der ersten Hierarchie (0) ist
	public function checkHierarchy(&$menuObject,&$currentMenu) {
		$bVisible = false;
		// Checken ob gleicher Parent
		if ($menuObject->Parent == $currentMenu->Parent) {
			$bVisible = true;
		}
		if (stristr($currentMenu->Item,$menuObject->Parent) !== false) {
			// Wenn vergleichsparent keinen Punkt hat (Level 1)
			if ($menuObject->Level == 1) {
				// Nur Anzeigen, wenn der vergleichparent am Anfang ist
				if ($currentMenu->Level == 0) {
					if ($currentMenu->Item == $menuObject->Parent) {
						$bVisible = true;
					}
				} else {
					if (substr($currentMenu->Item,0,strpos($currentMenu->Item,'.')) == $menuObject->Parent) {
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
	
	// Status der Menubilder anzeigen
	public function getPictureState() {
		$out = '';
		// Prüfen ob Bilder vorhanden und entsprechend anzeigen
		$nMenuID = getInt($_GET['menu']);
		$sPath = BP.'/design/'.page::design().'/menu/';
		$bPic1 = false; $sPic1 = $nMenuID.'.gif';
		$bPic2 = false; $sPic2 = $nMenuID.'-over.gif';
		if (file_exists($sPath.$sPic1)) $bPic1 = true;
		if (file_exists($sPath.$sPic2)) $bPic2 = true;
		// Erstes Bild wenn vorhanden
		if ($bPic1) {
			$out .= '
			<div style="float:left; margin:5px;">
				<img style="border:1px solid #ccc;" src="/design/'.page::design().'/menu/'.$sPic1.'">
			</div>
			';
		}
		// Zweites Bild und Pfeil
		if ($bPic2) {
			$out .= '
			<div style="float:left; margin:5px;">
				<img style="border:1px solid #ccc;" src="/design/'.page::design().'/menu/'.$sPic2.'">
			</div>
			';
		}
		// Wenn Bilder vorhanden, am Ende lösch-Icon zeigen
		if ($bPic2 || $bPic2) {
			$out .= '
			<div style="float:left; margin:5px;">
				<a href="menu.php?id='.page::menuID().'&menu='.$nMenuID.'&delete">
				<img src="/images/icons/delete.png" border="0" alt="'.$this->Res->html(213,page::language()).'" title="'.$this->Res->html(213,page::language()).'"></a>
			</div>
			';
		}
		// Meldung wenn keine Files
		if (!$bPic1 && !$bPic2) {
			$out = $this->Res->html(446,page::language()).'...';
		}
		return($out);
	}
	
	// Menubilder löschen
	public function deleteMenuImages() {
		$nMenuID = getInt($_GET['menu']);
		$sPath = BP.'/design/'.page::design().'/menu/';
		// Beide Bilder löschen wenn sie existieren
		if (file_exists($sPath.$nMenuID.'.gif')) unlink($sPath.$nMenuID.'.gif');
		if (file_exists($sPath.$nMenuID.'-over.gif')) unlink($sPath.$nMenuID.'-over.gif');
		// Datenbank anpassen
		$sSQL = "UPDATE tbmenu SET mnu_Image = 0 WHERE mnu_ID = $nMenuID";
		$this->Conn->command($sSQL);
		logging::debug('menu images deleted');
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		// Menu Session Objekte löschen
		unset($_SESSION['menuObjects']);
		session_write_close();
		redirect('location: /admin/menu/menu.php?id='.page::menuID().'&menu='.$nMenuID);
	}
	
	// Menubilder hochladen
	public function uploadMenuImages() {
		$bUploadFirst = false;
		$Errors = array();
		$nMenuID = getInt($_GET['menu']);
		$sPath = BP.'/design/'.page::design().'/menu/';
		// Ordner erstellen wenn nicht vorhanden
		if (!file_exists($sPath)) {
			mkdir($sPath, 0755, true);
		}
		// Erstes File verarbeiten
		if ($_FILES['menuPicture']['error'] == UPLOAD_ERR_OK) {
			$sFile = $_FILES['menuPicture']['name'];
			move_uploaded_file($_FILES['menuPicture']['tmp_name'],$sPath.$sFile);
			// File prüfen
			$sExt = $this->checkUploadFile($sFile);
			// File konvertieren wenn nötig
			if (strlen($sExt) > 0) {
				// File konvertieren, umbenennen original löschen
				$bUploadFirst = true;
				switch ($sExt) {
					case '.jpg':
					case '.png': 
						$ObjConv = new imageConverter($sPath,$sFile,'gif');
						unlink($sPath.$sFile);
						$sFile = str_replace('.jpg','.gif',$sFile);
						$sFile = str_replace('.png','.gif',$sFile);
				}
				// Bild umbenennen in Schlussendliches File
				$sMenuPicture = $sPath.$nMenuID.'.gif';
				if (file_exists($sMenuPicture)) unlink($sMenuPicture);
				rename($sPath.$sFile,$sMenuPicture);
			} else {
				// Fehler generieren
				array_push($Errors,$this->Res->html(447,page::language()));
				// Datei löschen
				unlink($sPath.$sFile);
			}
		}
		// Zweites File verarbeiten
		if ($_FILES['mousePicture']['error'] == UPLOAD_ERR_OK) {
			// Prüfen, ob das erste Bild schon vorher hochgeladen wurde
			if (!$bUploadFirst) {
				if (file_exists($sPath.$nMenuID.'.gif')) {
					$bUploadFirst = true;
				}
			}
			// Nur wenns auch das erste File gibt
			if ($bUploadFirst) {
				$sFile = $_FILES['mousePicture']['name'];
				move_uploaded_file($_FILES['mousePicture']['tmp_name'],$sPath.$sFile);
				// File prüfen
				$sExt = $this->checkUploadFile($sFile);
				// File konvertieren wenn nötig
				if (strlen($sExt) > 0) {
					// File konvertieren, umbenennen original löschen
					$bUploadFirst = true;
					switch ($sExt) {
						case '.jpg':
						case '.png': 
							$ObjConv = new imageConverter($sPath,$sFile,'gif');
							unlink($sPath.$sFile);
							$sFile = str_replace('.jpg','.gif',$sFile);
							$sFile = str_replace('.png','.gif',$sFile);
					}
					// Bild umbenennen in Schlussendliches File
					$sMousePicture = $sPath.$nMenuID.'-over.gif';
					if (file_exists($sMousePicture)) unlink($sMousePicture);
					rename($sPath.$sFile,$sMousePicture);
				} else {
					// Fehler generieren
					array_push($Errors,$this->Res->html(447,page::language()));
					// Datei löschen
					unlink($sPath.$sFile);
				}
			}
		} elseif ($bUploadFirst && count($Errors) == 0) {
			// Erstes File für zweites kopieren
			$sMousePicture = $sPath.$nMenuID.'-over.gif';
			if (file_exists($sMousePicture)) unlink($sMousePicture);
			copy($sMenuPicture,$sMousePicture);
		}
		// Fehler melden oder Erfolg
		if (count($Errors) == 0) {
			// Datenbankmässig "mnu_Image" auf true stellen
			$sSQL = "UPDATE tbmenu SET mnu_Image = 1 WHERE mnu_ID = $nMenuID";
			$this->Conn->command($sSQL);
			logging::debug('saved menu images');
			// Erfolg melden und weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			// Menu Session Objekte löschen
			unset($_SESSION['menuObjects']);
			session_write_close();
			redirect('location: /admin/menu/menu.php?id='.page::menuID().'&menu='.$nMenuID);
		} else {
			logging::error('error saving menu images');
			$this->setErrorSession($Errors);
			session_write_close();
			redirect('location: /admin/menu/menu.php?id='.page::menuID().'&menu='.$nMenuID);
		}
	}
	
	// Dropdown Optionen für Parent auswähl zurückgeben
	public function getParentDropdown(menuInterface &$menu, $nParentItem) {
		// Keiner (Hauptmenupunkt) einfüllen
		$nCurrentMenu = getInt($_GET['menu']);
		$out = '<option value="0">'.$this->Res->html(775,page::language()).'</option>';
		$menuObjects = $menu->getMenuObjects();
		foreach ($menuObjects as $menuObject) {
			if ($nCurrentMenu != $menuObject->ID) {
				$out .= '<option value="'.$menuObject->ID.'"';
				if ($nParentItem == $menuObject->Item) {
					$out .= ' selected="selected"';
				}
				$out .= '>';
				for ($i = 0;$i < $menuObject->Level;$i++) {
					$out .= '- - ';
				}
				$out .= $menuObject->Name.'</option>';
			}
		}
		// Optionen String zurückgeben
		return($out);
	}
	
	// File prüfen und Endung zurückgeben
	private function checkUploadFile($sFile) {
		$sExt = '';
		$FileExt = substr($sFile,strripos($sFile,'.'));
		switch (strtolower($FileExt)) {
			case '.jpg':
			case '.png':
			case '.gif':
				$sExt = $FileExt; break;
		}
		return($sExt);
	}
	
	// Nächstes Item anhand eines Items holen
	private function getNextIndex($nMenu) {
		// Höchsten Index +1
		$sSQL = "SELECT MAX(mnu_Index)+1 AS Result FROM tbmenu 
		WHERE mnu_Parent = '$nMenu' AND man_ID = ".page::mandant();
		if (!$nResult = $this->Conn->getFirstResult($sSQL)) {
			$nResult = 1;
		}
		return($nResult);
	}
	
	// Nächstes Item anhand eines Items holen
	private function getNextItem($nMenu) {
		// Das hächste Item mit diesem Parent abfragen
		$sSQL = "SELECT mnu_Item FROM tbmenu WHERE mnu_Parent = '$nMenu' 
		AND man_ID = ".page::mandant()." ORDER BY mnu_Item DESC";
		$nRes = $this->Conn->execute($sSQL);
		// Alles in ein Array einlesen, sortieren und letzten Index nehmen
		$CurrentValue = 0;
		while ($row = $this->Conn->next($nRes)) {
			$Value = (string) $row['mnu_Item'];
			// Bis und mit zum letzten Punkt entfernen
			$nPos = strripos($Value,'.');
			if ($nPos !== false) {
				$Value = substr($Value,$nPos+1);
			}
			// Prüfen ob grösster Index
			if ($CurrentValue <= (int) $Value) {
				$CurrentValue = (int) $Value;
				$nHighestItem = $row['mnu_Item'];
			}
		}
		if (strlen($nHighestItem) == 0) $nHighestItem = $nMenu.".0";
		// Item keinen Punkt hat, voranhängen
		if (stristr($nHighestItem,".") === false) $nHighestItem = ".".$nHighestItem;
		// Trennen nach "vor dem letzten Punkt" und "nach letzten Punkt"
		$nLastDot = strrpos($nHighestItem,".");
		$sBefore = substr($nHighestItem,0,$nLastDot);
		$nAfter = substr($nHighestItem,$nLastDot+1);
		if (strlen($sBefore) > 0) $sBefore .= ".";
		$sNewItem = $sBefore.($nAfter+1);
		// Ausgeben
		return($sNewItem);
	}
	
	private function checkMenutypeAccess($nType) {
		$bAccess = false;
		// Prüfen ob Spezialtyp
		if ($this->isSpecialMenu($nType)) $bAccess = true;
		
		// Prüfen, ob der Menupunkt global erreichbar ist
		if ($bAccess == false) {
			$this->Conn->setGlobalDB();
			$sSQL = "SELECT COUNT(typ_ID) FROM tbmenutyp 
			WHERE typ_ID = $nType";
			$nResult = $this->Conn->getCountResult($sSQL);
			$this->Conn->setInstanceDB();
			// Wenn das Resultat 1 ist, ist der Menupunkt erlaubt
			if ($nResult == 1) $bAccess = true;
		}
		
		// Instanz nur prüfen, nicht schon ok
		if ($bAccess == false) {
			$sSQL = "SELECT COUNT(typ_ID) FROM tbmenutyp 
			WHERE page_ID <= ".page::id()." AND typ_ID = $nType";
			$nResult = $this->Conn->getCountResult($sSQL);
			// Wenn das Resultat 1 ist, ist der Menupunkt erlaubt
			if ($nResult == 1) $bAccess = true;
		}
		// Resultat zurückgeben
		return($bAccess);
	}

	// Spezialmenus prüfen
	private function isSpecialMenu($nType) {
		$isSpecial = false;
		switch ($nType) {
			case menutypes::LINK_EXTERNAL: $isSpecial = true;
			case menutypes::LINK_INTERNAL: $isSpecial = true;
		}
		return($isSpecial);
	}

	// Gibt an, ob der automatische Link ausfüllen aktiv ist
	public function isBlurActive($sPath) {
		$active = 'false';
		// Ist der Pfad leer, dann gilt es als neu und wird autom. ausgefüllt
		if (strlen($sPath) == 0) {
			$active = 'true';
		}
		// Ist es deaktiviert? Falls ja, ausschalten
		if (option::get('deactivateMenulinkBlur') == 1) {
			$active = 'false';
		}
		return($active);
	}

	// Validieren des Menunamens
	private function validateMenuname(&$Errors) {
		$sName = $_POST['name'];
		$this->Conn->escape($sName);
		stringOps::noHtml($sName);
		if (stringOps::minLength($sName,1) == false) {
			array_push($Errors,$this->Res->html(142,page::language()));
		}
		return($sName);
	}

  // Validiert die individuelle Menu URL
	private function validatePath(&$Errors) {
		$sPath = stringOps::getPostEscaped('path',$this->Conn);
		$sPath = $this->sanitizePath($sPath);
		// Checken ob es schon existiert
		if ($this->pathExists($sPath,getInt($_GET['menu']))) {
			array_push($Errors,$this->Res->html(1162,page::language()));
		}
		return($sPath);
	}

	// Löscht oder ersetzt ungültige Zeichen für den URL Pfad
	public function sanitizePath($sPath) {
		$sPath = strtolower($sPath);
		$sPath = stringOps::replaceWellKnownChars($sPath);
		$sPath = preg_replace(stringOps::ALPHA_PATH,"",$sPath);
		return($sPath);
	}

	// Prüft ob es die individuelle URL schon gibt
	public function pathExists($sPath,$nMnuID) {
		// Wenn kein Pfad da, alles OK, gar nicht erst prüfen
		if (strlen($sPath) == 0) {
			return(false);
		}
		$sSQL = "SELECT COUNT(mnu_ID) CountResult FROM tbmenu
    WHERE man_ID = ".page::mandant()." AND mnu_Path = '$sPath'
		AND mnu_ID != $nMnuID";
    $nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult > 0) {
			return(true);
		}
		return(false);
	}

	// Validieren des Menutyps
	private function validateMenutype(&$Errors) {
		$nType = getInt($_POST['type']);
		$bAccess = $this->checkMenutypeAccess($nType);
		// Wenn kein Zugriff, Fehler
		if ($bAccess == false) {
			array_push($Errors,$this->Res->html(143,page::language()));
		}
		return($nType);
	}

	// Validieren einer Checkbox mit 1 value
	private function validateCheckbox(&$Errors, $sName) {
		$nValue = getInt($_POST[$sName]);
		if ($nValue != 1) $nValue = 0;
		return($nValue);
	}

	// Validieren des Menunamens
	private function validateExternal(&$Errors) {
		$sExternal = $_POST['external'];
		stringOps::noHtml($sExternal);
		// Prüfen ob es eine URL sein kann
		$bOk = stringOps::checkURL($sExternal);
		// Wenn nicht ok, schauen ob es mit / beginnt, das geht jetzt auch
		if (!$bOk && stringOps::startsWith($sExternal,'/')) $bOk = true;
		// Wenn nicht leer und ok, mit '' ausstatten, sonst "NULL"
		if (strlen($sExternal) > 0) {
			if ($bOk == true) {
				$this->Conn->escape($sExternal);
				$sExternal = "'".$sExternal."'";
			} else {
				array_push($Errors,$this->Res->html(144,page::language()));
			}
		} else {
			$sExternal = 'NULL';
		}
		return($sExternal);
	}

	// Validieren des Menunamens
	private function validateInternal(&$Errors) {
		$nRedirect = getInt($_POST['redirect']);
		if (!menuExists($nRedirect,$this->Conn) && $nRedirect > 0) {
			array_push($Errors,$this->Res->html(145,page::language()));
		}
		if ($nRedirect == 0) $nRedirect = 'NULL';
		return($nRedirect);
	}

	// Standard Teaser ID prüfen
	private function validateTeaser(&$Errors) {
		$nTeaserID  = getInt($_POST['teaser']);
		// Prüfen ob Startseite dem Mandanten gehört
		$sSQL = "SELECT COUNT(tas_ID) AS tas_ID FROM tbteasersection
		WHERE man_ID = ".page::mandant()." AND tas_ID = $nTeaserID";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1 && $nTeaserID != 0)  {
			// Fehler
			array_push($Errors,$this->Res->html(403,page::language()));
		}
		// Teaser ID zurückgeben
		return($nTeaserID);
	}

	// Shorttag validieren
	private function validateShorttag(&$Errors) {
		$Shorttag = stringOps::getPostEscaped('shorttag',$this->Conn);
		// Nichts tun, wenn gar kein Tag eingegeben wurde
		if (strlen($Shorttag) > 0) {
			// Ungültige Zeichen aussortieren
			stringOps::alphaNumOnly($Shorttag);
			// Schauen ob der String noch lang genug ist
			if (strlen($Shorttag) == 0) {
				logging::error('invalid chars for shorttag');
				array_push($Errors,$this->Res->html(503,page::language()));
			} else {
				// Schauen ob Tag schon gebraucht wird
				$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
				WHERE man_ID = ".page::mandant()."
				AND mnu_Shorttag = '$Shorttag' AND mnu_ID != ".getInt($_GET['menu']);
				$nUsed = $this->Conn->getCountResult($sSQL);
				if ($nUsed > 0) {
					logging::error('shorttag is already used');
					array_push($Errors,$this->Res->html(504,page::language()));
				}
			}
		}
		return($Shorttag);
	}

	// Parent validieren
	private function validateParent(&$Errors) {
		$nMenuID = getInt($_GET['menu']);
		$sParent = '0';
		$nMenuParent = stringOps::getPostEscaped('parent',$this->Conn);
		// Schauen, ob der Elter-Menupunkt vorhanden ist
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
		WHERE man_ID = ".page::mandant()." AND mnu_ID = '$nMenuParent'";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn Resultat vorhanden oder Parent Null, OK
		if ($nResult == 1) {
			$sSQL = "SELECT mnu_Item FROM tbmenu
			WHERE mnu_ID = $nMenuParent";
			$sParent = $this->Conn->getFirstResult($sSQL);
		}
		return($sParent);
	}

	// Kaskatierted Updaten der Menuhierarchie anhand Items
	private function cascadeItemUpdate($nMenuID,$sItem) {
		// Aktuelles Item des MEnus holen
		$sSQL = "SELECT mnu_Item FROM tbmenu WHERE mnu_ID = $nMenuID";
		$sOldItem = $this->Conn->getFirstResult($sSQL);
		// Alle Menus mit diesem Item als Parent updaten
		$sSQL = "SELECT mnu_ID FROM tbmenu WHERE
		man_ID = ".page::mandant()." AND mnu_Parent = '$sOldItem'";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Neues Item generieren
			$sNewItem = $this->getNextItem($sItem);
			// Kaskadiertes ausführen
			$this->cascadeItemUpdate($row['mnu_ID'],$sNewItem);
			// Updaten sItem ist Parent, neues Item
			$sSQL = "UPDATE tbmenu SET mnu_Item = '$sNewItem',
			mnu_Parent = '$sItem' WHERE mnu_ID = ".$row['mnu_ID'];
			$this->Conn->command($sSQL);
			logging::debug('cascaded item update');
		}
	}
}