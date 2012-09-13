<?php
// Handelt einige Funktionen um Benutzer und deren Gruppen
class moduleGroup extends commonModule {
	
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
	
	// Neue Gruppe erstellen
	public function addGroup() {
	// Array für Fehlermeldungen
		$Errors = array();
		
		$sDesc = $this->validateGroupdesc($Errors); // Alias prüfen	
		$nStart = $this->validateStart($Errors);	// Startseite prüfen
		
		// Benutzer erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			$this->Conn->escape($sDesc);
			stringOps::noHtml($sDesc);
			$sSQL = "INSERT INTO tbusergroup (ugr_Desc,ugr_Start,man_ID)
			VALUES ('$sDesc',$nStart,".page::mandant().")";
			$this->Conn->command($sSQL);
			// Weiterleiten zum Index, Erfolg ausgeben
			$this->setErrorSession($this->Res->html(57,page::language()));
			// Paging für Userübersicht zurücksetzen
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/group/index.php?id='.page::menuID());
		} else {
			$this->setErrorSession($Errors);
		}
	}
	
	// Löscht zuordnungen von Benutzern zu einer Gruppe
	public function deleteUserRelations($nGroupID) {
		$sSQL = "DELETE FROM tbuser_usergroup WHERE ugr_ID = $nGroupID";
		$this->Conn->command($sSQL);
		logging::debug('user relations to group deleted');
	}
	
	// Löscht zuordnungen von Gruppen zu einem User
	public function deleteGroupRelations($nUserID) {
		$sSQL = "DELETE FROM tbuser_usergroup WHERE usr_ID = $nUserID";
		$this->Conn->command($sSQL);
		logging::debug('group relations to user deleted');
	}
	
	// Eine Gruppe löschen
	public function deleteGroup() {
		// Benutzer abholen
		$nGroupID = getInt($_GET['delete']);
		// Checken ob der User von diesem Mandanten ist
		$allowDelete = false;
		$sSQL = "SELECT COUNT(ugr_ID) FROM tbusergroup WHERE 
		ugr_ID = $nGroupID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $allowDelete = true;
		// Benutzer effektiv löschen
		if ($allowDelete == true) {
			// Query absetzen
			$sSQL = "DELETE FROM tbusergroup WHERE ugr_ID = $nGroupID";
			$this->Conn->command($sSQL);
			logging::debug('deleted group');
			// Abhängige User der Gruppe löschen
			$this->deleteUserRelations($nGroupID);
			// Paging für Userübersicht zurücksetzen
			$this->setErrorSession($this->Res->html(99,page::language()));
			$this->resetPaging();
		} else {
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Weiterleiten auf Startseite, zuvor Session sicher abschliessen
		session_write_close();
		redirect('location: /admin/group/index.php?id='.page::menuID());
	}
	
	// Such SQL setzen
	public function setSearch() {
		$search = stringOps::getPostEscaped('search',$this->Conn);
		$sSQL = "SELECT tbusergroup.ugr_ID,tbusergroup.ugr_Desc,tbmenu.mnu_Name
		FROM tbusergroup LEFT JOIN tbmenu ON tbusergroup.ugr_Start = tbmenu.mnu_ID
		WHERE tbusergroup.man_ID = ".page::mandant()." AND 
		(tbusergroup.ugr_Desc LIKE '%$search%' OR tbmenu.mnu_Name LIKE '%$search%') 
		ORDER BY tbusergroup.ugr_ID ASC";
		sessionConfig::set('SearchSQL',$sSQL);
	}
	
	// Bestehende Gruppe editieren
	public function editGroup() {
	// Array für Fehlermeldungen
		$Errors = array();
		
		$sDesc = $this->validateGroupdesc($Errors); // Alias prüfen	
		$nStart = $this->validateStart($Errors); // Startseite prüfen
		
		// Benutzer erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			$nGroupID = getInt($_GET['group']);
			$this->Conn->escape($sDesc);
			stringOps::noHtml($sDesc);
			$sSQL = "UPDATE tbusergroup SET 
			ugr_Desc = '$sDesc', ugr_Start = $nStart
			WHERE ugr_ID = $nGroupID";
			$this->Conn->command($sSQL);
			logging::debug('saved group');
			// Weiterleiten zur Editierseite
			$this->setErrorSession($this->Res->html(57,page::language()));
			// Paging für Userübersicht zurücksetzen
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/group/edit.php?id='.page::menuID()."&group=".$nGroupID); 
		} else {
			$this->setErrorSession($Errors);
		}
	}
	
	// Checken ob Gruppe dem Mandanten gehört
	public function checkEditable() {
		// Benutzer abholen
		$nGroupID = getInt($_GET['group']);
		// Checken ob der User von diesem Mandanten ist
		$allowEdit = false;
		$sSQL = "SELECT COUNT(ugr_ID) FROM tbusergroup WHERE 
		ugr_ID = $nGroupID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $allowEdit = true;
		// Benutzer effektiv löschen
		if ($allowEdit == false) {
			// Fehler ausgeben und auf Startseite
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/group/index.php?id='.page::menuID());
		} 		
	}
	
	// Users einer Gruppe laden
	public function loadUsers(&$UsersIn,&$UsersNotIn,$nGroupID) {
		// Array aller Users
		$Users = array();
		// Alle Gruppen des Mandante lesen
		$sSQL = "SELECT usr_ID,usr_Alias FROM tbuser
		WHERE man_ID = ".page::mandant()." ORDER BY usr_Alias ASC";
		$nRes = $this->Conn->execute($sSQL);
		// Alle Gruppen in das Array verschieben
		while ($row = $this->Conn->next($nRes)) {
			// Gruppe reinpushen
			array_push($Users,$row);
		}
		// Mit dem gleichen vorgehen die Benutzer der Gruppe füllen
		$this->loadUsersOfGroup($UsersIn,$nGroupID);
		// Anhand dieser Ergebnisse die Gruppen suchen
		// in denen der Benutzer noch nicht drin ist
		$this->loadIncurrentUsers($UsersIn,$UsersNotIn,$Users);
	}
	
	// User einer Gruppe laden
	public function loadUsersOfGroup(&$UsersIn,$nGroupID) {
		$sSQL = "SELECT tbuser.usr_ID, tbuser.usr_Alias FROM tbuser 
		INNER JOIN tbuser_usergroup ON tbuser.usr_ID = tbuser_usergroup.usr_ID
		INNER JOIN tbusergroup ON tbuser_usergroup.ugr_ID = tbusergroup.ugr_ID
		WHERE tbuser.man_ID = ".page::mandant()." AND tbusergroup.ugr_ID = $nGroupID
		ORDER BY tbuser.usr_Alias";
		$nRes = $this->Conn->execute($sSQL);
		// Alle Gruppen in das Array verschieben
		while ($row = $this->Conn->next($nRes)) {
			// Gruppe reinpushen
			array_push($UsersIn,$row);
		}
	}
	
	// Anhand der User und der UsersIn, die UsersNotIn berechnen
	public function loadIncurrentUsers(&$UsersIn,&$UsersNotIn,&$Users) {
		// Alle User durchgehen
		foreach ($Users as $User) {
			$bFound = false;
			// Alle UsersIn anschauen
			foreach ($UsersIn as $UserIn) {
				if ($UserIn['usr_ID'] == $User['usr_ID']) {
					// Benutzer ist in der aktuellen Gruppe drin
					$bFound = true;
				}
			}
			// Wenn User nicht gefunden, diese in das UsersNotIn Array tun
			if ($bFound == false) {
				array_push($UsersNotIn,$User);
			}
		}
	}
	
	// Gruppen des Users und Gruppen denen er nicht zugehört laden
	public function loadGroups(&$GroupsIn,&$GroupsNotIn,$nUserID) {
		// Array aller Gruppen
		$Groups = array();
		// Alle Gruppen des Mandante lesen
		$sSQL = "SELECT ugr_ID,ugr_Desc FROM tbusergroup
		WHERE man_ID = ".page::mandant()." ORDER BY ugr_Desc ASC";
		$nRes = $this->Conn->execute($sSQL);
		// Alle Gruppen in das Array verschieben
		while ($row = $this->Conn->next($nRes)) {
			// Gruppe reinpushen
			array_push($Groups,$row);
		}
		
		// Mit dem gleichen vorgehen die Gruppen des Benutzer befüllen
		$this->loadGroupsOfUser($GroupsIn,$nUserID);
		// Anhand dieser Ergebnisse die Gruppen suchen
		// in denen der Benutzer noch nicht drin ist
		$this->loadIncurrentGroups($GroupsIn,$GroupsNotIn,$Groups);
	}
	
	// Gruppen eines Users laden
	public function loadGroupsOfUser(&$GroupIn,$nUserID) {
		$sSQL = "SELECT tbusergroup.ugr_ID,tbusergroup.ugr_Desc FROM tbusergroup
		INNER JOIN tbuser_usergroup ON tbusergroup.ugr_ID = tbuser_usergroup.ugr_ID
		INNER JOIN tbuser ON tbuser_usergroup.usr_ID = tbuser.usr_ID
		WHERE tbuser.usr_ID = $nUserID ORDER BY tbusergroup.ugr_Desc ASC";
		$nRes = $this->Conn->execute($sSQL);
		// Alle Gruppen in das Array verschieben
		while ($row = $this->Conn->next($nRes)) {
			// Gruppe reinpushen
			array_push($GroupIn,$row);
		}
	}
	
	// Anhand der Gruppen und der GruppenIn, die GruppenNotIn berechnen
	public function loadIncurrentGroups(&$GroupsIn,&$GroupsNotIn,&$Groups) {
		// Alle Gruppen durchgehen
		foreach ($Groups as $Group) {
			$bFound = false;
			// Alle GruppenIn anschauen
			foreach ($GroupsIn as $GroupIn) {
				if ($GroupIn['ugr_ID'] == $Group['ugr_ID']) {
					// Benutzer ist in der aktuellen Gruppe drin
					$bFound = true;
				}
			}
			// Wenn Gruppe nicht gefunden, diese in das GroupNotIn Array tun
			if ($bFound == false) {
				array_push($GroupsNotIn,$Group);
			}
		}
	}
	
	// Gruppendaten laden
	public function loadData($nGroupID) {
		// Die Gruppe wird nicht gecheckt, da dies schon die
		// Funktion checkEditable vorher machen sollte
		$sGroupData = NULL;
		// SQL abfeuern
		$sSQL = "SELECT ugr_Desc,ugr_Start FROM tbusergroup WHERE ugr_ID = $nGroupID";
		$nRes = $this->Conn->execute($sSQL);
		// Rows durchgehen und jeweils neuste zwischenspeichern
		while ($row = $this->Conn->next($nRes)) {
			$sGroupData = $row;
		}
		// Resultrow zurückgeben
		return($sGroupData);
	}
	
	// GroupsIn und GroupsNotIn speichern für einzelnen Benutzer
	public function saveGroupsUser($nUserID) {
		// GroupsNotIn für den User hinzufügen
		if (isset($_POST['GroupsNotIn'])) {
			foreach ($_POST['GroupsNotIn'] as $Group) {
				$sSQL = "INSERT INTO tbuser_usergroup (ugr_ID,usr_ID)
				VALUES ($Group,$nUserID)";
				$this->Conn->command($sSQL);
			}
		}
		// GroupsIn für den User löschen
		if (isset($_POST['GroupsIn'])) {
			foreach ($_POST['GroupsIn'] as $Group) {
				$sSQL = "DELETE FROM tbuser_usergroup WHERE
				ugr_ID = $Group AND usr_ID = $nUserID";
				$this->Conn->command($sSQL);
			}
		}
		// Erfolg ausgeben und zur Adresseite zurück
		logging::debug('saved user/group relations');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/user/groups.php?id='.page::menuID().'&user='.$nUserID); 
	}
	
	// UsersIn und UsersNotIn speichern für einzelne Gruppe
	public function saveUsersGroup($nGroupID) {
		// GroupsNotIn für den User hinzufügen
		if (isset($_POST['UsersNotIn'])) {
			foreach ($_POST['UsersNotIn'] as $User) {
				$sSQL = "INSERT INTO tbuser_usergroup (ugr_ID,usr_ID)
				VALUES ($nGroupID,$User)";
				$this->Conn->command($sSQL);
			}
		}
		// GroupsIn für den User löschen
		if (isset($_POST['UsersIn'])) {
			foreach ($_POST['UsersIn'] as $User) {
				$sSQL = "DELETE FROM tbuser_usergroup WHERE
				ugr_ID = $nGroupID AND usr_ID = $User";
				$this->Conn->command($sSQL);
			}
		}
		// Erfolg ausgeben und zur Adresseite zurück
		logging::debug('saved user/group relations');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/group/users.php?id='.page::menuID().'&group='.$nGroupID); 
	}
	
	// HTML Options für ein Gruppenarray ausgeben
	public function getHtml(&$Groups) {
		$sHtml = '';
		// Alle Gruppen durchgehen
		foreach ($Groups as $Group) {
			$sHtml .= '<option value="'.$Group['ugr_ID'].'">'.stringOps::htmlEnt($Group['ugr_Desc']).'</option>'."\r";
		}
		return($sHtml);
	}
	
	// HTML Options für ein Userarray ausgeben
	public function getHtmlUsers(&$Users) {
		$sHtml = '';
		// Alle Gruppen durchgehen
		foreach ($Users as $User) {
			$sHtml .= '<option value="'.$User['usr_ID'].'">'.stringOps::htmlEnt($User['usr_Alias']).'</option>'."\r";
		}
		return($sHtml);
	}
	
	// Gruppenname validieren
	private function validateGroupdesc(&$Errors) {
		$sDesc = $_POST['desc'];
		if (stringOps::minLength($sDesc,4) == false) {
			array_push($Errors,$this->Res->html(98,page::language()));
		}
		return($sDesc);
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
}