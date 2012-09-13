<?php 
// Verwandlte Libraries laden
require_once(BP.'/modules/wiki/classes/wikiObject.php');
require_once(BP.'/modules/wiki/classes/wikiUser.php');
require_once(BP.'/modules/wiki/classes/wikiEntry.php');

// Implementiert ein Wiki Modul
class moduleWiki extends commonModule {
	
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
	/**
	 * ID des Wiki
	 * @var int
	 */
	private $WikiID = 0;
	/**
	 * Wiki Objekt (Erst nach initialize geladen)
	 * @var wikiObject
	 */
	private $Wiki = null;
	/**
	 * Eingeloggter User
	 * @var wikiUser
	 */
	private $User = null;
	/**
	 * Anzahl neuste einträge auf Startseite
	 * @var int
	 */
	const NEWEST_ENTRIES = 3;
	/**
	 * Anzahl bearbeitete einträge auf Startseite
	 * @var int
	 */
	const UPDATE_ENTRIES = 4;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Initialisierung des Moduls
	public function initialize() {
		if (!sessionConfig::get('wikiID',false)) {
			// Wiki Eintrag erstellen, wenn nicht vorhanden
			$nWkiID = $this->getWikiID();
			// Konfiguration setzen
			sessionConfig::set('wikiID',$nWkiID);
			$this->WikiID = $nWkiID;
		} else {
			$this->WikiID = sessionConfig::get('wikiID',0);
		}
		// Wiki Datenobjekt laden
		$this->Wiki = new wikiObject($this->WikiID,$this->Conn);
		// Eingeloggen User laden
		$sSecurity = sessionConfig::get('ImpersonationSecurity','');
		if (strlen($sSecurity) > 0) {
			$this->User = new wikiUser($sSecurity,$this->Conn);
		}
	}
	
	// Gibt das Menu aus, anhand des aktuellen Zugriffs
	public function loadTopmenu(access &$Access, &$out) {
		$nAccess = $Access->getControllerAccessType();
		// Wenn nicht eingeloggt, -1 stellen
		if (!$Access->isLogin()) $nAccess = -1;
		$out .= '<div class="cWikiTopmenu">';
		// Admin / CUG Menus
		if ($nAccess == access::ACCESS_ADMIN || $nAccess == access::ACCESS_CUG) {
			$out .= '
				<div><a href="/modules/wiki/logout.php?id='.page::menuID().'">'.$this->Res->html(917,page::language()).'</a></div>
			';
			if (strlen(sessionConfig::get('ImpersonationSecurity','')) > 0) {
				$out .= '
					<div><a href="/modules/wiki/cug/config.php?id='.page::menuID().'">'.$this->Res->html(919,page::language()).'</a></div>
				';
			} else if ($nAccess == access::ACCESS_ADMIN) {
				$out .= '
					<div><a href="/modules/wiki/cug/user.php?id='.page::menuID().'">'.$this->Res->html(918,page::language()).'</a></div>
				';
			}
		} else {
			// Registrieren und einloggen
			if ($this->Wiki->Open == 0) {
				$out .= '
					<div><a href="/modules/wiki/login.php?id='.page::menuID().'">'.$this->Res->html(920,page::language()).'</a></div>
					<div><a href="/modules/wiki/register.php?id='.page::menuID().'">'.$this->Res->html(921,page::language()).'</a></div>
				';
			}
		}
		// Eintrag bearbeiten wenn anzeigt
		$entryname = stringOps::getGetEscaped('article',$this->Conn);
		if (strlen($entryname) > 0) {
			// Wenn entweder Admin ODER offenes Wiki
			if ($this->Wiki->Open == 1 || $nAccess == access::ACCESS_ADMIN || $nAccess == access::ACCESS_CUG) {
				$entry = $this->getEntryByName($entryname);
				// Wenn etwas geladen wurde
				if ($entry instanceOf wikiEntry) {
					$out .= '
						<div><a href="/modules/wiki/writer/edit.php?id='.page::menuID().'&entry='.$entry->EntryID.'">'.$this->Res->html(212,page::language()).'</a></div>
					';
				} 
			}
		}
		// Nur Admin Menus
		if ($nAccess == access::ACCESS_ADMIN) {
			$out .= '
				<div><a href="/modules/wiki/admin/useradmin.php?id='.page::menuID().'">'.$this->Res->html(922,page::language()).'</a></div>
				<div><a href="/modules/wiki/admin/config.php?id='.page::menuID().'">'.$this->Res->html(923,page::language()).'</a></div>
			';
		}
		$out .= '<div><a href="/modules/wiki/index.php?id='.page::menuID().'">'.$this->Res->html(938,page::language()).'</a></div>';
		$out .= '</div>';
	}
	
	// Benutzer registrieren
	public function registerUser() {
		$errormsg = array();
		// Versuchen einen neuen User zu erstellen
		$username = stringOps::getPostEscaped('username',$this->Conn);
		$email = stringOps::getPostEscaped('email',$this->Conn);
		$pass1 = stringOps::getPostEscaped('password1',$this->Conn);
		$pass2 = stringOps::getPostEscaped('password2',$this->Conn);
		// Prüfen ob Passwörter gleich sind
		if ($pass1 != $pass2) {
			array_push($errormsg,$this->Res->html(929,page::language()));
		}
		// Wenn keine Fehler, prüfen ob die Security schon vorhanden ist
		if (count($errormsg) == 0) {
			$sSecurity = secureString::getSecurityString($pass1,$username);
			if (impersonation::exists($sSecurity,$this->Conn)) {
				array_push($errormsg,$this->Res->html(964,page::language()));
			}
		}
		// Versuchen den User zu erstellen
		if (count($errormsg) == 0) {
			$nImpID = impersonation::addUser(
				$username,
				$pass1,
				$this->Wiki->Cuguser,
				$this->Conn
			);
			// User mit E-Mail und Activation Code updaten
			if ($nImpID > 0) {
				// Mit dem aktuellen Menu verbinden
				$menuID = page::menuID();
				impersonation::addConnection($nImpID,$menuID,$this->Conn);
				// Aktivierung und Mail vorbereiten
				$activation = md5(time() . $email);
				$security = impersonation::getSecurityById($nImpID,$this->Conn);
				$update = array();
				impersonation::addField($update,'imp_Activation',$activation,$this->Conn);
				impersonation::addField($update,'imp_Email',$email,$this->Conn);
				impersonation::changeUser($security,$update,$this->Conn);
				// Mail versenden
				if ($this->sendUserActivation($email,$activation,$nImpID)) {
					array_push($errormsg,$this->Res->html(931,page::language()));
				} else {
					array_push($errormsg,$this->Res->html(932,page::language()));
				}
			} else {
				// Fehler, wenn es den User schon gibt
				array_push($errormsg,$this->Res->html(930,page::language()));
			}
		}
		// Fehlermeldung / Erfolgsmeldung in die Session geben
		$error = '';
		foreach ($errormsg as $msg) {
			$error .= '- '.$msg.'<br>';
		}
		sessionConfig::set('wikiMessage',$error);
		redirect('location: /modules/wiki/register.php?id='.page::menuID());
	}
	
	// Benutzer einloggen
	public function loginUser() {
		// Daten aus dem Request holen
		$username = stringOps::getPostEscaped('username',$this->Conn);
		$password = stringOps::getPostEscaped('password',$this->Conn);
		$nMenuID = page::menuID();
		// Versuchen einzuloggen
		if (!impersonation::login($username,$password,$nMenuID,$this->Conn)) {
			sessionConfig::set('wikiMessage',$this->Res->html(937,page::language()));
		}
		redirect('location: /modules/wiki/login.php?id='.page::menuID());
	}
	
	// Zugriff für Admin prüfen oder Fehlerseite anzeigen
	public function checkAdminAccess(access &$Access) {
		$nAccess = $Access->getControllerAccessType();
		if (!($Access->isLogin() && $nAccess == access::ACCESS_ADMIN)) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Zugriff für CUG prüfen oder Fehlerseite anzeigen
	public function checkCugAccess(access &$Access) {
		$nAccess = $Access->getControllerAccessType();
		if (!($Access->isLogin() && ($nAccess == access::ACCESS_CUG || $nAccess == access::ACCESS_ADMIN))) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Zugriff für Schreiber prüfen. Wenn das wiki geschlossen ist,
	// muss ein User eingeloggt sein, wenn nicht, ist der Zugriff offen
	public function checkWriterAccess(access &$Access) {
		$nAccess = $Access->getControllerAccessType();
		if ($this->Wiki->Open == 0) {
			if (!($Access->isLogin() && ($nAccess == access::ACCESS_CUG || $nAccess == access::ACCESS_ADMIN))) {
				redirect('location: /error.php?type=noAccess');
			}
		}
	}
	
	// Gibt das Wiki Datenobjekt zurück
	public function getWiki() {
		return($this->Wiki);
	}
	
	// Gibt das Wiki User Datenobjekt zurück
	public function getUser() {
		return($this->User);
	}
	
	// Gibt ein Dropdown aller sddCMS User aus
	public function getUserDropdown($nSelected,$nAccess) {
		$out = '';
		$sSQL = "SELECT usr_ID,usr_Name,usr_Alias FROM tbuser
		WHERE man_ID = ".page::mandant()." AND usr_Access = $nAccess
		ORDER BY usr_Name";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sSelected = '';
			if ($nSelected == $row['usr_ID']) $sSelected = ' selected';
			$out .= '<option value="'.$row['usr_ID'].'"'.$sSelected.'>';
			$out .= $row['usr_Name'].' ('.$row['usr_Alias'].')</option>';
		}
		return($out);
	}
	
	// Speichern der Admin Konfiguration
	public function saveConfig() {
		$sError = '';
		// Daten holen und validieren
		$nWkiAdminuser = getInt($_POST['wkiAdminuser']);
		$nWkiCuguser = getInt($_POST['wkiCuguser']);
		$nWkiOpen = stringOps::getBoolInt($_POST['wkiOpen']);
		$sTitle = stringOps::getPostEscaped('wkiTitle',$this->Conn);
		$sText = stringOps::getPostEscaped('wkiText',$this->Conn);
		// Wenn geschlossenes Wiki, müssen User vorhanden sein
		if ($nWkiOpen == 0 && ($nWkiAdminuser == 0 || $nWkiCuguser == 0)) {
			$sError = $this->Res->html(950,page::language());
		}
		// Wenn kein Fehler, speichern
		if (strlen($sError) == 0) {
			$sSQL = "UPDATE tbwiki SET
			wki_Title = '$sTitle', wki_Text = '$sText',
			wki_Open = $nWkiOpen, wki_Adminuser = $nWkiAdminuser,
			wki_Cuguser = $nWkiCuguser WHERE wki_ID = ".$this->WikiID;
			$this->Conn->command($sSQL);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/admin/config.php?id='.page::menuID());
		} else {
			// Fehler melden und Weiterleiten
			$this->setErrorSession($sError);
			session_write_close();
			redirect('location: /modules/wiki/admin/config.php?id='.page::menuID());
		}
	}
	
	// Einen Wiki Eintrag speichern
	public function saveWikiEntry() {
		// Daten holen und validieren
		$Entry = new wikiEntry(getInt($_GET['entry']),$this->Conn);
		$sText = stringOps::getPostEscaped('conContent',$this->Conn);
		stringOps::noHtml($sText);
		// Links parsen, neue Seiten erstellen für nicht vorhandene Begriffe
		$this->parseLinks($sText);
		// Schauen ob in dieser Session schon bearbeitet wurde
		if (session_id() == $Entry->Session) {
			// Wenn ja, aktuelle Version updaten
			$sSQL = "UPDATE tbcontent SET
			con_Content = '$sText'
			WHERE con_ID = ".$Entry->ContentID;
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/writer/edit.php?id='.page::menuID().'&entry='.$Entry->EntryID);
		} else {
			// Wenn nicht, neue Version erstellen
			$nWkeID = $this->createNewVersion($Entry,$sText);
			// Erfolg melden und weiterleiten (Neue Entry ID!)
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/writer/edit.php?id='.page::menuID().'&entry='.$nWkeID);
		}
	}
	
	// Mit Paging die Wiki User laden
	public function loadWikiUserList(&$Data) {
		$sSQL = "SELECT tbimpersonation.imp_ID,imp_Active,imp_Email,imp_Alias,usr_ID FROM tbimpersonation 
		INNER JOIN tbmenu_impersonation ON tbmenu_impersonation.imp_ID = tbimpersonation.imp_ID
		WHERE man_ID = ".page::mandant()." AND tbmenu_impersonation.mnu_ID = ".page::menuID()."
		ORDER BY imp_Alias ASC, imp_Email ASC";
		$paging = new paging($this->Conn,'useradmin.php?id='.page::menuID());
		$paging->start($sSQL,10);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {			
			// Einfügen in Daten array
			array_push($Data,$row);
		}
		return($paging->getHtml());
	}
	
	// Editfenster für Wiki User Bearbeitung
	public function getWikiUserEdit($row,windowControl &$window,$count) {
		$out = '';
		// Button erstellen
		$alt = $this->Res->html(953,page::language());
		$out .= '
		<img src="/images/icons/bullet_wrench.png" alt='.$alt.'"" title="'.$alt.'" border="0" id="editmode_'.$count.'">
		';
		// Window erstellen
		$sHtml = $this->getWikiUserEditHtml($row,$count);
		$window->add('editmode_'.$count,$sHtml,$alt,420,270);
		$out .= $window->get('editmode_'.$count);
		return($out);
	}
	
	// Einen User aktivieren
	public function activateUser($user,$activation,$nMenuID) {
		// User suchen anhand ID, Aktivierung und zugehörendem Menu
		$sSQL = "SELECT COUNT(tbimpersonation.imp_ID) FROM tbimpersonation 
		INNER JOIN tbmenu_impersonation ON tbmenu_impersonation.imp_ID = tbimpersonation.imp_ID
		WHERE imp_Activation = '$activation' AND tbimpersonation.imp_ID = $user
		AND mnu_ID = $nMenuID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		// Nur aktivieren, wenn es genau einen User gibt der in Frage kommt
		if ($nResult == 1) {
			$sSQL = "UPDATE tbimpersonation SET
			imp_Activation = '', imp_Active = 1
			WHERE imp_ID = $user";
			$this->Conn->command($sSQL);
			return(true);
		}
		return(false);
	}
	
	// Fügt einen neuen Wiki User hinzu (Deaktiviert, ohne Pwd)
	public function addWikiUser() {
		// Benutzerrechte (Offen: keine, sonst Cug)
		$nImpUser = 0;
		if ($this->Wiki->Open == 0) $nImpUser = $this->Wiki->Cuguser;
		// Neuen Benutzer erstellen
		$sSQL = "INSERT INTO tbimpersonation (usr_ID,man_ID,imp_Access,imp_Active,imp_Alias,
		imp_Security,imp_Email,imp_Activation) VALUES ($nImpUser,".page::mandant().",0,0,
		'< ".$this->Res->html(6,page::language())." >','','','')";
		$nImpID = $this->Conn->insert($sSQL);
		// Verbindung mit aktuellem Menu erstellen
		$sSQL = "INSERT INTO tbmenu_impersonation (imp_ID,mnu_ID)
		VALUES ($nImpID,".page::menuID().")";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/wiki/admin/useradmin.php?id='.page::menuID());
	}
	
	// Speichert den geänderten Wiki User
	public function saveWikiUser() {
		$sError = '';
		// Daten aus dem richtigen Formular holen
		$nForm = getInt($_GET['form']);
		$nImpID = getInt($_POST['impID_'.$nForm]);
		$sAlias = stringOps::getPostEscaped('impAlias_'.$nForm,$this->Conn);
		$sEmail = stringOps::getPostEscaped('impEmail_'.$nForm,$this->Conn);
		$sPass1 = $_POST['pass1_'.$nForm];
		$sPass2 = $_POST['pass2_'.$nForm];
		$nIsAdmin = stringOps::getBoolInt($_POST['impType_'.$nForm]);
		$nIsActive = stringOps::getBoolInt($_POST['impActive_'.$nForm]);
		// Prüfen ob ein Alias vorhanden ist
		if (strlen($sAlias) == 0) {
			$sError = $this->Res->html(961,page::language());
		}
		// Aktuellen Security String holen und nur Alias ersetzen
		$sSQL = "SELECT imp_Security FROM tbimpersonation WHERE imp_ID = $nImpID";
		$sOrigSecurity = $this->Conn->getFirstResult($sSQL);
		// Prüfen ob das Passwort geändert werden muss
		$sPassAdd = '';
		if (strlen($sPass1) > 0 && strlen($sPass2) > 0 && strlen($sError) == 0) {
			// Prüfen ob die Passwörter gleich sind
			if ($sPass1 !== $sPass2) {
				$sError = $this->Res->html(49,page::language());
			}
			// Prüfen ob beide die minimale Länge haben
			if (strlen($sPass1) < 4 && strlen($sPass2) < 4) {
				$sError = $this->Res->html(50,page::language());
			}
			// Wenn kein Fehler, Passwort SQL erstellen
			if (strlen($sError) == 0) {
				$sSecurity = secureString::getSecurityString($sPass1,$sAlias);
			}
		} else {
			$sSecurity = secureString::insertNewAlias($sOrigSecurity,$sAlias);
		}
		// Security hinzufügen wenn geändert
		if ($sOrigSecurity != $sSecurity) {
			// Prüfen, ob der User schon existiert (Wenn keine Fehler)
			if (strlen($sError) == 0) {
				if (impersonation::exists($sSecurity,$this->Conn)) {
					$sError = $this->Res->html(964,page::language());
				} else {
					$sPassAdd = " imp_Security = '$sSecurity',";
				}
			}
		}
		// Wenn keine Fehler, Benutzer speichern
		if (strlen($sError) == 0) {
			// Ist es ein Admin?
			if ($nIsAdmin == 1) {
				$sUserAdd = ' usr_ID = '.$this->Wiki->Adminuser.', ';
			} else {
				$sUserAdd = ' usr_ID = '.$this->Wiki->Cuguser.', ';
			}
			// Query bauen
			$sSQL = "UPDATE tbimpersonation SET $sPassAdd $sUserAdd
			imp_Alias = '$sAlias', imp_Email = '$sEmail',
			imp_Active = $nIsActive WHERE imp_ID = $nImpID";
			$this->Conn->command($sSQL);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/admin/useradmin.php?id='.page::menuID());
		} else {
			// Fehler melden und weiterleiten
			logging::debug('error saving wiki impersonation');
			$this->setErrorSession($sError);
			session_write_close();
			redirect('location: /modules/wiki/admin/useradmin.php?id='.page::menuID());
		}	
	}
	
	// Speichern der eigenen Einstellungen
	public function saveWikiUserSimple() {
		$sError = '';
		// Daten aus dem richtigen Formular holen
		$sOrigSecurity = sessionConfig::get('ImpersonationSecurity','');
		// Fehler, wenn keine Security vorhanden, sonst ID laden
		if (strlen($sOrigSecurity) == 0) {
			$sError = $this->Res->html(963,page::language());
		} else {
			$nImpID = $this->getLoggedInUserId($sOrigSecurity);
		}
		// Restliche Daten holen
		$sAlias = stringOps::getPostEscaped('impAlias',$this->Conn);
		$sEmail = stringOps::getPostEscaped('impEmail',$this->Conn);
		$sPass1 = $_POST['impPass1'];
		$sPass2 = $_POST['impPass2'];
		// Prüfen ob ein Alias vorhanden ist
		if (strlen($sAlias) == 0) {
			$sError = $this->Res->html(961,page::language());
		}
		// Prüfen ob das Passwort geändert werden muss
		$sPassAdd = '';
		if (strlen($sPass1) > 0 && strlen($sPass2) > 0 && strlen($sError) == 0) {
			// Prüfen ob die Passwörter gleich sind
			if ($sPass1 !== $sPass2) {
				$sError = $this->Res->html(49,page::language());
			}
			// Prüfen ob beide die minimale Länge haben
			if (strlen($sPass1) < 4 && strlen($sPass2) < 4) {
				$sError = $this->Res->html(50,page::language());
			}
			// Wenn kein Fehler, Passwort SQL erstellen
			if (strlen($sError) == 0) {
				$sSecurity = secureString::getSecurityString($sPass1,$sAlias);
			}
		} else {
			// Nur Alias ersetzen
			$sSecurity = secureString::insertNewAlias($sOrigSecurity,$sAlias);
		}
		// Security hinzufügen wenn geändert
		if ($sOrigSecurity != $sSecurity) {
			// Prüfen, ob der User schon existiert (Wenn keine Fehler)
			if (strlen($sError) == 0) {
				if (impersonation::exists($sSecurity,$this->Conn)) {
					$sError = $this->Res->html(964,page::language());
				} else {
					$sPassAdd = " imp_Security = '$sSecurity',";
					sessionConfig::set('ImpersonationSecurity',$sSecurity);
				}
			}
		}
		// Wenn keine Fehler, Benutzer speichern
		if (strlen($sError) == 0) {
			// Query bauen
			$sSQL = "UPDATE tbimpersonation SET $sPassAdd
			imp_Alias = '$sAlias', imp_Email = '$sEmail' WHERE imp_ID = $nImpID";
			$this->Conn->command($sSQL);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/cug/config.php?id='.page::menuID());
		} else {
			// Fehler melden und weiterleiten
			logging::debug('error saving own wiki impersonation');
			$this->setErrorSession($sError);
			session_write_close();
			redirect('location: /modules/wiki/cug/config.php?id='.page::menuID());
		}	
	}
	
	// Löscht einen User des Wikis
	public function deleteWikiUser() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(tbimpersonation.imp_ID) FROM tbimpersonation 
		INNER JOIN tbmenu_impersonation ON tbmenu_impersonation.imp_ID = tbimpersonation.imp_ID
		WHERE tbimpersonation.imp_ID = $nDeleteID AND mnu_ID = ".page::menuID()." 
		AND man_ID = ".page::mandant();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbimpersonation WHERE imp_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			$sSQL = "DELETE FROM tbmenu_impersonation WHERE imp_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted wiki impersonation');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /modules/wiki/admin/useradmin.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting wiki impersonation');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /modules/wiki/admin/useradmin.php?id='.page::menuID()); 
		}
	}
	
	// Einen neuen Wiki Eintrag erstellen
	public function addEntry() {
		$sEntry = stringOps::getPostEscaped('conTitle',$this->Conn);
		// Prüfen ob es den Eintrag im Wiki schon gibt
		if (!$this->isExisting($sEntry)) {
			// Eintag neu erstellen
			$this->addEntryByName($sEntry);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/cug/user.php?id='.page::menuID());
		} else {
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(965,page::language()));
			session_write_close();
			redirect('location: /modules/wiki/cug/user.php?id='.page::menuID());
		}
	}
	
	// (Einträge) Daten des eingeloggten Users laden
	public function loadUserEntries(&$Data,access &$Access) {
		$nAccess = $Access->getControllerAccessType();
		// Paging Engine erstellen
		$PagingEngine = new paging($this->Conn,'user.php?id='.page::menuID());
		// SQL Erstellen (Initiierungsversionen des Users
		$nImpID = getInt($this->User->ImpersonationID);
		$nWkiID = $this->Wiki->WikiID;
		if ($nImpID > 0) {
			$sSQL = "SELECT wke_ID FROM tbwikientry WHERE
			imp_ID = $nImpID AND wki_ID = $nWkiID AND wke_Version = 1
			ORDER BY wke_ID DESC";
		} else if ($nAccess == access::ACCESS_ADMIN) {
			$sSQL = "SELECT wke_ID FROM tbwikientry WHERE
			wki_ID = $nWkiID AND wke_Version = 1
			ORDER BY wke_ID DESC";
		}
		$PagingEngine->start($sSQL,10,false);
		// Erste Versionen suchen
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Aktuelle Version suchen und laden
			$Entry = new wikiEntry($row['wke_ID'],$this->Conn);
			$this->getNewestVersion($row['wke_ID'],$Entry);
			// Einfügen in das Datenarray
			array_push($Data,$Entry);
		}
		// HTML Code für Seitennavi zurückgeben
		return($PagingEngine->getHtml());
	}
	
	// Lädt alle Versionen eines Contents (nur Abwärts)
	public function loadContentVersions(&$Data,$nWkeID) {
		// Wikidaten, Content und User laden
		$sSQL = "SELECT tbwikientry.wke_Version,tbwikientry.wke_Parent,
		tbcontent.con_Date, tbwikientry.wke_ID,tbcontent.con_Title,
		IFNULL(tbimpersonation.imp_Alias,'') AS imp_Alias FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID 
		LEFT JOIN tbimpersonation ON tbimpersonation.imp_ID = tbwikientry.imp_ID 
		WHERE tbwikientry.wke_ID = $nWkeID";
		// Erstes Resultat in das Datenarray
		$nRes = $this->Conn->execute($sSQL);
		if ($row = $this->Conn->next($nRes)) {
			// Wenn kein User, etwas einfüllen
			if (strlen($row['imp_Alias']) == 0) {
				$row['imp_Alias'] = 'anonymous';
			}
			// Daten befüllen
			array_push($Data,$row);
			// Parent suchen, wenn einer vorhanden ist
			$nParent = getInt($row['wke_Parent']);
			if ($nParent > 0) $this->loadContentVersions($Data,$nParent);
		}
	}
	
	// Vollen Wiki Eintrag laden, anhand ID
	public function loadWikiEntry($nWkeID,&$Entry) {
		// Alle Wiki Daten laden
		$sSQL = "SELECT tbwikientry.con_ID,tbwikientry.imp_ID,tbwikientry.wke_Version,
		tbwikientry.wke_Parent,tbwikientry.wke_Session,tbcontent.con_Date,
		tbcontent.con_Title,tbcontent.con_Content FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID 
		WHERE tbwikientry.wke_ID = $nWkeID";
		// Erstes Resultat in das Datenarray
		$nRes = $this->Conn->execute($sSQL);
		if ($row = $this->Conn->next($nRes)) {
			// Datum verwandeln
			$row['con_Date'] = $this->getHumanReadableDatetime($row['con_Date']);
			// Daten zum zurückgeben speichern
			$Entry = $row;
		}
	}

	// Resultate einer Suche holen
	public function loadSearchResults($sSearch,&$Results) {
		// Alle Wiki Daten laden
		$sSQL = "SELECT tbwikientry.wke_ID FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID
		WHERE tbcontent.con_Title LIKE '%".$sSearch."%'
		OR tbcontent.con_Content LIKE '%".$sSearch."%'";
		// Erstes Resultat in das Datenarray
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Datum verwandeln
			$this->getNewestVersion($row['wke_ID'], $row);
			// Daten zum zurückgeben speichern
			if (!$this->isInEntryArray($Results,$row)) {
				array_push($Results,$row);
			}
		}
	}
	
	// Gibt Datum/Zeit im Format "dd.mm.yyyy um hh:mm:ss" zurück
	public function getHumanReadableDatetime($date) {
		$sDate = '';
		$sDate.= dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$date);
		$sDate.= ' '.$this->Res->html(327,page::language()).' ';
		$sDate.= dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_TIME,$date);
		return($sDate);
	}
	
	// Liste der gesamten Metadaten laden
	public function loadWikiList(&$Data, access &$Access) {
		// Gesamte Daten allenfalls aus Session holen
		$Data = sessionConfig::get('WikiList',false);
		// Prüfen, ob es schon in der Session ist
		if (!is_array($Data)) {
			$sSQL = "SELECT wke_ID,wke_Version,wke_Parent,tbcontent.con_ID,
			con_Date,con_Title,con_Content FROM tbwikientry
			INNER JOIN tbcontent ON tbwikientry.con_ID = tbcontent.con_ID
			WHERE wki_ID = ".$this->WikiID." AND LENGTH(con_Content) > 0
			ORDER BY con_Date DESC";
			// Daten in Liste füllen
			$Data = array();
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				array_push($Data,$row);
			}
			// Das ganze auch noch in der Session speichern
			sessionConfig::set('WikiList',$Data);
		} 
	}
	
	// Listen für die Übersicht erstellen
	public function getOverviewLists(&$Newest,&$Update,&$Data) {
		// Neuste Beiträge holen (Version 0, nach Datum)
		foreach ($Data as $Row) {
			if (getInt($Row['wke_Version']) == 1) {
				$Entry = $Row;
				$this->getNewestVersion($Row['wke_ID'],$Entry);
				array_push($Newest,$Entry);
			}
			// Bei drei Einträgen, abbrechen
			if (count($Newest) == self::NEWEST_ENTRIES) break;
		}
		// Updates holen (Neuste Versionen, nach Datum)
		foreach ($Data as $Row) {
			if ($this->isNewestVersion($Row['wke_ID'])) {
				array_push($Update,$Row);
			}
			// Bei vier Einträgen, abbrechen
			if (count($Update) == self::UPDATE_ENTRIES) break;
		}
	}
	
	// Suche ausgeben
	public function getWikiSearch() {
		$out = '
		<div class="cWikiTitle">
			<span>Suche</span>
		</div>
		<div class="cWikiBox">
			<form action="search.php?id='.page::menuID().'" method="post">
				<input type="text" class="cWikiSearch" name="wikiSearch">
				<input type="submit" value="Suche" class="cButton">
			</form>
		</div>
		';
		return($out);
	}
	
	// Boxen ausgeben im Array, mit Titel
	public function getWikiBoxes($Boxes,$Title) {
		$out = '
		<div class="cWikiTitle">
			<span>'.$Title.'</span>
		</div>
		';
		foreach ($Boxes as $Box) {
			if ($Box instanceOf wikiEntry) {
				$title = $Box->Title;
				$content = $Box->Content;
			} else {
				$title = $Box['con_Title'];
				$content = $Box['con_Content'];
			}
			// Text verkleinern
			$text = wikiEditor::unparse($content);
			$text = stringOps::chopString($text,250,true);
			$text = wikiEditor::parseWords($text);
			// Link für folgeseite
			$text.= '
			<a class="cMoreLink" href="show.php?id='.page::menuID().'&article='.$title.'">
				'.$this->Res->html(442,page::language()).'
			</a>';
			$out.= '
			<div class="cWikiBox">
				<h3 class="cWikiStartTitle">'.$title.'</h3>
				<p>'.$text.'</p>
			</div>
			';
		}
		return($out);
	}
	
	// Wiki Eintrag anhand des Namen holen
	public function getEntryByName($name) {
		// Erste gefundene ID (Most likely die neuste Version) holen
		$sSQL = "SELECT wke_ID FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID
		WHERE con_Title = '$name' ORDER BY wke_ID DESC LIMIT 0,1";
		// Schauen ob vorhanden
		$nRes = $this->Conn->execute($sSQL);
		if ($entry = $this->Conn->next($nRes)) {
			$this->getNewestVersion($entry['wke_ID'],$entry);
		}
		return($entry);
	}
	
	// Parst den Text nach möglichen neuen Links
	private function parseLinks($sText) {
		$regex = '/\[\[(.*?)]]/';
		preg_match_all($regex,$sText,$result);
		// Alle Resultate durchgehen
		for ($i = 0; $i < count($result[0]);$i++) {
			// Schauen ob der Eintrag existiert
			if (!$this->isExisting($result[1][$i])) {
				$this->addEntryByName($result[1][$i]);
			}
		}
	}
	
	// Neuen Eintrag anhand Name erstellen
	private function addEntryByName($sEntry) {
		// Daten erstellen
		$nMenuID = page::menuID();
		$nUserID = getInt($this->User->ImpersonationID);
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		$nNewID = ownerID::get($this->Conn);
		// Datensätz einfügen
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Hits,con_Views,con_Active,con_ShowName,
		con_ShowDate,con_ShowModified,con_Title,con_Content,con_Date) VALUES
		($nNewID,$nMenuID,$nUserID,0,0,1,0,1,0,'$sEntry','','$sDate')";
		$this->Conn->command($sSQL);
		// Wiki Entry in erster Version erstellen
		$nWkiID = $this->Wiki->WikiID;
		$sSQL = "INSERT INTO tbwikientry (con_ID,imp_ID,wki_ID,wke_Version,wke_Parent,wke_Session)
		VALUES ($nNewID,$nUserID,$nWkiID,1,0,'".session_id()."')";
		$this->Conn->command($sSQL);
	}
	
	// Holt die kompletten Content/Wiki Daten in neuster Version
	private function getNewestVersion($nWkeID,&$Entry) {
		// Prüfen ob der Eintrag bei einem Eintrag ein Parent ist
		$sSQL = "SELECT wke_ID FROM tbwikientry WHERE wke_Parent = $nWkeID";
		$nRes = $this->Conn->execute($sSQL);
		if ($row = $this->Conn->next($nRes)) {
			// Objekt laden und rekursiv aufrufen, das geschieht solange bis kein 
			// Parent mehr vorhanden ist, und somit die neuste Version gefunden wurde
			$Entry = new wikiEntry($row['wke_ID'],$this->Conn);
			$this->getNewestVersion($Entry->EntryID,$Entry);
		}
		// Wenn wir hier sind, könnte es sein, dass die gegebene ID die aktuellste Version ist
		if (!($Entry instanceOf wikiEntry)) {
			$Entry = new wikiEntry($nWkeID,$this->Conn);
		}
	}
	
	// Gibt an, ob die gegebene Version die aktuellste ist
	private function isNewestVersion($nWkeID) {
		$isNewest = true;
		// Prüfen ob der Eintrag bei einem Eintrag ein Parent ist
		$sSQL = "SELECT wke_ID FROM tbwikientry WHERE wke_Parent = $nWkeID";
		$nRes = $this->Conn->execute($sSQL);
		if ($row = $this->Conn->next($nRes)) $isNewest = false;
		return($isNewest);
	}
	
	// Erstellt vom gegebenen Content mit dem Text eine neue Version
	private function createNewVersion(wikiEntry &$Entry,$sText) {
		// Daten holen / erstellen
		$nConID = ownerID::get($this->Conn);
		$nMenuID = page::menuID();
		$nUserID = getInt($_SESSION['userid']);
		$nImpID = getInt($this->User->ImpersonationID);
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		// Neuen Content erstellen
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Date,con_Title,con_Content,
		con_Active) VALUES ($nConID,$nMenuID,$nUserID,'$sDate','".$Entry->Title."','".$sText."',1)";
		$this->Conn->command($sSQL);
		// Wikientry erstellen
		$sSQL = "INSERT INTO tbwikientry (con_ID,imp_ID,wki_ID,wke_Version,
		wke_Parent,wke_Session) VALUES ($nConID,$nImpID,".$this->Wiki->WikiID.",
		".($Entry->Version+1).",".$Entry->EntryID.",'".session_id()."')";
		$nWkeID = $this->Conn->insert($sSQL);
		// Alten Content inaktiv schalten
		$sSQL = "UPDATE tbcontent SET con_Active = 0 WHERE con_ID = ".$Entry->ContentID;
		$this->Conn->command($sSQL);
		// Startseite löschen (Damit der neue Eintrag erscheint)
		sessionConfig::set('WikiList',false);
		return($nWkeID);
	}
	
	// Prüft, ob ein Wiki Eintrag schon vorhanden ist
	private function isExisting($sEntry) {
		$sSQL = "SELECT COUNT(wke_ID) FROM tbwikientry
		INNER JOIN tbcontent ON tbcontent.con_ID = tbwikientry.con_ID
		WHERE tbwikientry.wki_ID = ".$this->Wiki->WikiID." 
		AND tbcontent.con_Title = '$sEntry'";
		if ($this->Conn->getCountResult($sSQL) >= 1) return(true);
		return(false);
	}
	
	// ID des eingeloggen Users holen
	private function getLoggedInUserId($sSecurity) {
		$sSQL = "SELECT imp_ID FROM tbimpersonation
		WHERE imp_Security = '$sSecurity' AND man_ID = ".page::mandant();
		$nImpID = getInt($this->Conn->getFirstResult($sSQL));
		return($nImpID);
	}
	
	// Gibt HTML Formular zum editieren eines Wiki Users aus
	private function getWikiUserEditHtml($row,$count) {
		$out = '
		<form name="editform_'.$count.'" action="useradmin.php?id='.page::menuID().'&form='.$count.'&save" method="post">
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td colspan="2">
				<h1>'.$this->Res->html(58,page::language()).' - '.stringOps::chopString($row['imp_Alias'],15,true).'</h1>
				<br>
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(9,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="impAlias_'.$count.'" value="'.$row['imp_Alias'].'"> <span class="red">*</span>
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(928,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="impEmail_'.$count.'" value="'.$row['imp_Email'].'">
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(39,page::language()).':</td>
			<td>
				<input type="password" style="width:200px;" name="pass1_'.$count.'" value="">
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(40,page::language()).':</td>
			<td>
				<input type="password" style="width:200px;" name="pass2_'.$count.'" value="">
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(960,page::language()).':</td>
			<td>
				<input type="checkbox" name="impType_'.$count.'" value="1"'.$this->checkWikiUserChecked($row).'> 
				'.$this->Res->html(959,page::language()).'
			</td>
		</tr>
		<tr>
			<td width="150">'.$this->Res->html(160,page::language()).':</td>
			<td>
				<input type="checkbox" name="impActive_'.$count.'" value="1"'.checkCheckbox(1,$row['imp_Active']).'> 
				'.$this->Res->html(958,page::language()).'
			</td>
		</tr>
		<tr>
			<td width="150">&nbsp;</td>
			<td>
				<input type="submit" class="cButton" value="'.$this->Res->html(36,page::language()).'">
				<input type="hidden" name="impID_'.$count.'" value="'.$row['imp_ID'].'">
			</td>
		</tr>
		</table>
		</form>
		';
		return($out);
	}
	
	// ' checked' zurückgeben, wenn der User Adminrechte hat
	private function checkWikiUserChecked($row) {
		$sChecked = '';
		if ($row['usr_ID'] == $this->Wiki->Adminuser) {
			$sChecked = ' checked';
		}
		return($sChecked);
	}
	
	// Lädt die Wiki ID oder erstellt einen Eintrag, wenn keiner vorhanden ist
	private function getWikiID() {
		$sSQL = "SELECT wki_ID FROM tbowner_wiki WHERE owner_ID = ".page::menuID();
		$nWkiID = getInt($this->Conn->getFirstResult($sSQL));
		// Neu erstellen, wenn nichts gefunden
		if ($nWkiID == 0) {
			// Neues Wiki erstellen
			$sSQL = "INSERT INTO tbwiki (wki_adminuser,wki_Cuguser,wki_Open,wki_Title,wki_Text)
			VALUES (0,0,1,'< ".$this->Res->html(924,page::language())." >','')";
			$nWkiID = $this->Conn->insert($sSQL);
			// Verbindung zum aktuellen Menu
			$sSQL = "INSERT INTO tbowner_wiki (wki_ID,owner_ID) VALUES ($nWkiID,".page::menuID().")";
			$this->Conn->command($sSQL);
		}
		return($nWkiID);
	}
	
	// Versendet das Aktivierungsmail (true/false wenn Erfolg)
	private function sendUserActivation($email,$activation,$user) {
		// Mail Objekt erstellen
		$Mail = new phpMailer();
		// Meta Daten einfüllen
		$Mail->Subject = $this->Res->html(933,page::language());;
		$Mail->From = 'noreply@'.str_replace('www.','',page::domain());
		$Mail->AddAddress($email,$email);
		// Mail Inhalt zusammenbauen
		$body = $this->Res->html(934,page::language()).'<br><br>';
		$body.= $this->Res->html(935,page::language()).'<br><br>';
		$body.= 'http://'.page::domain().'/modules/wiki/activation.php';
		$body.= '?id='.page::menuID().'&user='.$user.'&code='.$activation;
		$Mail->Body = $body;
		$Mail->IsHTML(true);
		// Mail versenden
		return($Mail->Send());
	}

	// Prüft ob ein Wikientry in einem Wikientry Array ist
	private function isInEntryArray(&$array,wikiEntry &$entry) {
		foreach ($array as $local) {
			if ($entry->EntryID == $local->EntryID) {
				return(true);
			}
		}
		return(false);
	}
}