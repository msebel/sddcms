<?php
// Handelt einige Funktionen die in
// der Benutzerverwaltung vorkommen
class moduleUser extends commonModule {
	
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
	
	// Einen Benutzer Speichern
	public function saveUser() {
		// Array für Fehlermeldungen
		$Errors = array();
		
		$sAlias = $this->validateAlias($Errors);
		$bPassOk = $this->validatePass($Errors,$_POST['pass1'],$_POST['pass2']);		
		$nStart = $this->validateStart($Errors);
		$nAccess = $this->validateAccess($Errors);
		
		// Security String generieren und Schauen ob der Mandant diese Security schon hat
		$sSecurity = secureString::getSecurityString($_POST['pass1'],$sAlias);
		$this->validateSecurity($sSecurity,$Errors);
		
		// Benutzer erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			$sName = $_POST['name'];
			// Escapen und HTML entfernen
			$this->Conn->escape($sAlias); stringOps::noHtml($sAlias);
			$this->Conn->escape($sName);  stringOps::noHtml($sName);
			$sSQL = "INSERT INTO tbuser (man_ID,usr_Start,usr_Access,usr_Name,usr_Alias,usr_Security) 
			VALUES (".page::mandant().",$nStart,$nAccess,'$sName','$sAlias','$sSecurity')";
			$this->Conn->command($sSQL);
			// Weiterleiten zum Index, Erfolg ausgeben
			$this->setErrorSession($this->Res->html(53,page::language()));
			// Paging für Userübersicht zurücksetzen
			logging::debug('added user');
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/user/index.php?id='.page::menuID());
		} else {
			$this->setErrorSession($Errors);
		}
	}
	
	// Einen Benutzer prüfen und löschen
	public function deleteUser() {
		// Benutzer abholen
		$nUserID = getInt($_GET['delete']);
		// Checken ob der User von diesem Mandanten ist
		$allowDelete = false;
		$sSQL = "SELECT COUNT(usr_ID) FROM tbuser WHERE 
		usr_ID = $nUserID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $allowDelete = true;
		// Benutzer effektiv löschen
		if ($allowDelete == true) {
			// Query absetzen
			$sSQL = "DELETE FROM tbuser WHERE usr_ID = $nUserID";
			$this->Conn->command($sSQL);
			// Abhängigkeiten für Gruppen löschen mit Library
			library::load('group');
			$GroupModule = new moduleGroup();
			$GroupModule->loadObjects($this->Conn,$this->Res);
			$GroupModule->deleteGroupRelations($nUserID);
			// Paging für Userübersicht zurücksetzen
			logging::debug('deleted user');
			$this->setErrorSession($this->Res->html(54,page::language()));
			$this->resetPaging();
		} else {
			logging::error('error deleting user');
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Weiterleiten auf Startseite, zuvor Session sicher abschliessen
		session_write_close();
		redirect('location: /admin/user/index.php?id='.page::menuID());
	}
	
	// Such SQL setzen
	public function setSearch() {
		$search = stringOps::getPostEscaped('search',$this->Conn);
		$sSQL = "SELECT usr_Alias,usr_ID,usr_Name,usr_Access FROM tbuser
		WHERE man_ID = ".page::mandant()." AND (usr_Alias LIKE '%$search%' 
		OR usr_Name LIKE '%$search%') ORDER BY usr_Alias ASC";
		sessionConfig::set('SearchSQL',$sSQL);
	}
	
	// Einen Benutzer editieren
	public function editUser() {
		// Welcher User wird bearbeitet?
		$nUserID = getInt($_GET['user']);
		// Security String für änderungen holen
		$sSecurity = $this->getSecurity();
		// Array für Fehlermeldungen
		$Errors = array();
		
		$sAlias = $this->validateAlias($Errors);
		$nStart = $this->validateStart($Errors);
		$nAccess = $this->validateAccess($Errors);
		
		// Security String generieren und Schauen ob der Mandant 
		// diese Security schon hat. Grundsätzlich kein neues Passwort setzen
		$bNewPass = false; 
		if (strlen($_POST['pass1']) > 0 && strlen($_POST['pass2']) > 0) {
			$bNewPass = $this->validatePass($Errors,$_POST['pass1'],$_POST['pass2']);
		}
		
		// Wenn neues Passwort, ganz neuen String, sonst nur Alias einfügen
		if ($bNewPass == true) {
			$sSecurity = secureString::getSecurityString($_POST['pass1'],$sAlias);
		} else {
			$sSecurity = secureString::insertNewAlias($sSecurity,$sAlias);
		}
		
		// Validieren, ob der Security String schon vergeben ist
		$this->validateSecurityEdit($sSecurity,$nUserID,$Errors);
		
		// Benutzer erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			$sName = $_POST['name'];
			$this->Conn->escape($sAlias); stringOps::noHtml($sAlias);
			$this->Conn->escape($sName);  stringOps::noHtml($sName);
			// Update SQL generieren
			$sSQL = "UPDATE tbuser SET usr_Alias = '$sAlias', usr_Start = '$nStart', usr_Name = '$sName',
			usr_Access = '$nAccess', usr_Security = '$sSecurity' WHERE usr_ID = $nUserID";
			$this->Conn->command($sSQL);
			// Weiterleiten zum Index, Erfolg ausgeben
			$this->setErrorSession($this->Res->html(57,page::language()));
			// Paging für Userübersicht zurücksetzen
			logging::debug('saved user');
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/user/edit.php?id='.page::menuID().'&user='.$nUserID); 
		} else {
			$this->setErrorSession($Errors);
		}
	}
	
	// Benutzerdaten laden
	public function loadData($nUserID) {
		// Der Benutzer wird nicht gecheckt, da dies schon die
		// Funktion checkEditable vorher machen sollte
		$sUserData = NULL;
		// SQL abfeuern
		$sSQL = "SELECT usr_Alias,usr_Name,usr_Security,usr_Start,usr_Access
		FROM tbuser WHERE usr_ID = $nUserID";
		$nRes = $this->Conn->execute($sSQL);
		// Rows durchgehen und jeweils neuste zwischenspeichern
		while ($row = $this->Conn->next($nRes)) {
			$sUserData = $row;
		}
		// Resultrow zurückgeben
		return($sUserData);
	}
	
	// Prüfen ob der Benutzer bearbeitet werden darf
	public function checkEditable() {
		// Benutzer abholen
		$nUserID = getInt($_GET['user']);
		// Checken ob der User von diesem Mandanten ist
		$allowEdit = false;
		$sSQL = "SELECT COUNT(usr_ID) FROM tbuser WHERE 
		usr_ID = $nUserID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $allowEdit = true;
		// Benutzer effektiv löschen
		if ($allowEdit == false) {
			// Fehler ausgeben und auf Startseite
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/user/index.php?id='.page::menuID());
		} 		
	}
	
	// Zugriffsrechte speichern
	public function saveAccess() {
		// Gewählte Menupunkte validieren
		$MenuIDs = array();
		foreach ($_POST['accessibleMenus'] as $MenuID) {
			if ($this->validateAccessibleMenu($MenuID)) {
				array_push($MenuIDs,$MenuID);
			}
		}
		// Aktuelle Zugriffe holen
		$nUserID = getInt($_GET['user']);
		$OldIDs = array();
		$this->getUseraccessMenuIds($nUserID,$OldIDs);
		// Daten inserten/deleten wo nötig
		$this->saveAccessData($OldIDs,$MenuIDs);
		// Erfolg melden und Weiterleiten
		logging::debug('saved user access');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/user/edit.php?id='.page::menuID().'&user='.$nUserID);
	}
	
	// HTML Code für die Zuweisung von Benutzerrechten holen
	public function getUseraccessHtml(menuInterface &$menu) {
		$nUserID = getInt($_GET['user']);
		$out = '';
		// Formular und Tabellenkopf erstellen
		$out .= '
		<form action="edit.php?id='.page::menuID().'&user='.$nUserID.'&access" method="post">
		<div style="float:left;overflow:auto;width:620px;height:400px;padding:3px;">
		<table width="600" cellpadding="2" cellspacing="0" border="0">
		<tr>
			<td colspan="2">
				<p>'.$this->Res->javascript(761,page::language()).'.</p><br>
			</td>
		</tr>
		';
		// Laden der aktuellen Userrechte
		$UserAccess = array();
		$this->getUseraccessData($nUserID,$UserAccess);
		// Liste der Menus ausgeben
		$menuObjects = $menu->getMenuObjects();
		foreach ($menuObjects as $menuObject) {
			$out .= '
			<tr>
				<td width="15">
					<input type="checkbox" name="accessibleMenus[]" value="'.$menuObject->ID.'"'.$this->checkUseraccessChecked($UserAccess,$menuObject->ID).'>
				</td>
				<td width="585">
					<div style="width:'.(($menuObject->Level+1)*20).'px;float:left;">&nbsp;</div>
					<div style="float:left">'.$menuObject->Name.'</div>
				</td>
			</tr>
			';
		}
		// Abschliessen mit Submit Button
		$out .= '
		</table>
		</div>
		<div style="float:left;overflow:auto;width:620px;height:30px;padding:3px;margin-top:10px;">
			<input style="float:right;" class="cButton" type="submit" value="'.$this->Res->javascript(233,page::language()).'">
		</div>
		</form>
		';
		return($out);
	}
	
	// Icon für Zuordnung von Benutzerrechten erhalten
	public function getUseraccessIcon($nType) {
		// Wenn es nur ein CUG Mitglied ist
		$out = '';
		if ($nType == 0) {
			$out = '
			<img src="/images/icons/key_add.png" 
			alt="'.$this->Res->html(758,page::language()).'"
			title="'.$this->Res->html(758,page::language()).'"
			id="windowUseraccess">';
		}
		return($out);
	}
	
	// Useraccess Daten ins Array spitzen
	private function getUseraccessData($nUserID, &$UserAccess) {
		$sSQL = "SELECT uac_ID,mnu_ID,uac_Type 
		FROM tbuseraccess WHERE usr_ID = $nUserID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($UserAccess,$row);
		}
	}
	
	// Useraccess MenuIDs
	private function getUseraccessMenuIds($nUserID, &$MenuIDs) {
		$sSQL = "SELECT mnu_ID FROM tbuseraccess WHERE usr_ID = $nUserID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($MenuIDs,getInt($row['mnu_ID']));
		}
	}
	
	// Checken, ob ein Accesstype für die gegebene Menu ID vorhanden ist
	private function checkUseraccessChecked(&$UserAccess,$nMenuID) {
		$sChecked = '';
		$doBreak = false;
		foreach ($UserAccess as $Access) {
			if ($Access['mnu_ID'] == $nMenuID) {
				$sChecked = ' checked="checked"';
				$doBreak = true;
			}
			if ($doBreak) break;
		}
		return($sChecked);
	}
	
	// Zugriffsdaten anhand vorheriger löschen/erstellen
	private function saveAccessData(&$OldMenus,&$NewMenus) {
		$nUserID = getInt($_GET['user']);
		// Einträge löschen die im alten vorhanden sind
		// und im neuen Array nicht vorkommen
		foreach ($OldMenus as $MenuID) {
			if (!$this->isAvailableAccess($NewMenus,$MenuID)) {
				$sSQL = "DELETE FROM tbuseraccess WHERE
				usr_ID = $nUserID AND mnu_ID = $MenuID";
				$this->Conn->command($sSQL);
			}
		}
		// Einträge erstellen, die nur im neuen Array vorkommen
		foreach ($NewMenus as $MenuID) {
			if (!$this->isAvailableAccess($OldMenus,$MenuID)) {
				$sSQL = "INSERT INTO tbuseraccess (mnu_ID,usr_ID,uac_Type)
				VALUES ($MenuID,$nUserID,1)";
				$this->Conn->command($sSQL);
			}
		}
	}
	
	// Prüfen, ob der gegebene Wert im gegebenen Array drin ist
	private function isAvailableAccess(&$Menus,$MenuID) {
		$bAvailable = false;
		foreach ($Menus as $Find) {
			if ($Find == $MenuID) {
				$bAvailable = true;
			}
			if ($bAvailable) break;
		}
		return($bAvailable);
	}
	
	// Validieren einer einzelnen MenuID für Zugriffe
	private function validateAccessibleMenu($nMenuID) {
		$nMenuID = getInt($nMenuID);
		$bValid = false;
		if ($nMenuID > 0) {
			$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
			WHERE man_ID = ".page::mandant()." AND mnu_ID = $nMenuID";
			$nResult = $this->Conn->getCountResult($sSQL);
			if ($nResult == 1) $bValid = true;
		}
		return($bValid);
	}
	
	// Security String holen
	private function getSecurity() {
		$nUserID = getInt($_GET['user']);
		// SQL für ein Ergebniss
		$sSQL = "SELECT usr_Security FROM tbuser WHERE usr_ID = $nUserID";
		$sSecurity = $this->Conn->getFirstResult($sSQL);
		// Security String zurückgeben
		return($sSecurity);
	}
	
	// Benutzeralias prüfen
	private function validateAlias(&$Errors) {
		$sAlias = $_POST['alias'];
		if (stringOps::minLength($sAlias,4) == false) {
			array_push($Errors,$this->Res->html(48,page::language()));
		}
		// Alias zurückgeben
		return($sAlias);
	}
	
	// Passwörter validieren
	private function validatePass(&$Errors,$sPass1,$sPass2) {
		$bReturn = true;
		// --> Gleichheit
		if ($sPass1 != $sPass2) {
			array_push($Errors,$this->Res->html(49,page::language())); $bReturn = false;
		}
		// --> Länge
		if (stringOps::minLength($sPass2,4) == false || stringOps::minLength($sPass1,4) == false) {
			array_push($Errors,$this->Res->html(50,page::language())); $bReturn = false;
		}
		return($bReturn);
	}
	
	// Startseite prüfen
	private function validateStart(&$Errors) {
		$nStart  = getInt($_POST['start']);
		// Prüfen ob Startseite dem Mandanten gehört
		$sSQL = "SELECT COUNT(mnu_ID) AS mnu_ID FROM tbmenu 
		WHERE man_ID = ".page::mandant()." AND mnu_ID = $nStart";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1)  {
			// Einfach die Seitenstartseite nehmen
			$nStart = page::start();
		}
		// Startseite zurückgeben
		return($nStart);
	}
	
	// Schauen ob der Security String schon existierts
	private function validateSecurity($sSecurity, &$Errors) {
		$sSQL = "SELECT COUNT(usr_ID) AS usr_ID FROM tbuser 
		WHERE man_ID = ".page::mandant()." AND usr_Security = '$sSecurity'";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 0) {
			array_push($Errors,$this->Res->html(51,page::language()));
		}
	}
	
	// Schauen ob der Security String schon existiert
	// Aber einen gegebenen User aussortieren fürs editieren
	private function validateSecurityEdit($sSecurity, $nUser, &$Errors) {
		$sSQL = "SELECT COUNT(usr_ID) AS usr_ID FROM tbuser 
		WHERE man_ID = ".page::mandant()." 
		AND usr_Security = '$sSecurity'
		AND usr_ID != $nUser";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 0) {
			array_push($Errors,$this->Res->html(51,page::language()));
		}
	}
	
	// Zugriff validieren
	private function validateAccess(&$Errors) {
		$nAccess = getInt($_POST['access']);
		if ($nAccess < 0 || $nAccess > 4) {
			array_push($Errors,$this->Res->html(52,page::language()));
		}
		// Rechte zurückgeben, in jedem Fall
		return($nAccess);
	}
}