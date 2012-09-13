<?php
class moduleIstaller extends commonModule {
	
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
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Liste aller Pages zurückgeben
	public function getPageOptions() {
		$out = '';
		$sSQL = "SELECT page_Name, page_ID FROM tbpage
		ORDER BY page_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '
			<option value="'.$row['page_ID'].'">'.$row['page_Name'].'</option>
			';
		}
		return($out);
	}
	
	// Gibt an, ob sddCMS Datenbanken bereits installiert sind
	public function isInstalled() {
		$bInstalled = false;
		// Prüfen ob im Root ein 'installed' File steht
		if (file_exists(BP.'/installed')) {
			$bInstalled = true;
		}
		return($bInstalled);
	}
	
	// Neue Page / Mandant erstellen
	public function createPage() {
		
		// Sprache einstellen
		switch($_GET['lang']) {
			case 'en':	$Lang = 1; break;
			case 'de':
			default:	$Lang = 0; break;
		}
		
		// Variablen initialisieren
		$sDomain = '';
		$sConfirmName = '';
		$nPageID = 0;
		$nMandantID = 0;
		
		// Datenbank Daten erstellen
		$this->createData($sConfirmName,$sDomain,$nPageID,$nMandantID,$Lang);
		// Page / Mandant files erstellen (wenn nicht schon vorhanden)
		$this->createFolders($nPageID,$nMandantID);
		// Design erstellen wenn nötig
		$this->assignDesign($nPageID);
		
		// Meldung mit Erfolg
		$Text = $this->Res->html(800,$Lang);
		$Text = str_replace('{0}',$sDomain,$Text);
		$Text = str_replace('{1}',$sConfirmName,$Text);
		return($Text);
	}
	
	// Hauptfunktion für Datenbank Tasks
	private function createData(&$sConfirmName,&$sDomain,&$nPageID,&$nMandantID,$Lang) {
		// ID Variablen erstellen
		$nStartmenuID = 0;	// ID des Startmenus
		$nAdminmenuID = 0;	// ID des Adminstarts (Menuverwaltung)
		$nAddressID = 0;	// Adresse des Webseitebesitzers
		$nPageID = 0;		// ID der neuen Webseite
		$nMandantID = 0;	// ID des ersten Mandanten der Webseite
		$nAdminuserID = 0;	// ID des Adminusers des Mandanten
		$nAdmingroupID = 0;	// Gruppe inder der Adminuser ist
		
		// Mandantendaten aus Formular holen
		$sDomain = $this->validatePostdata('mandantDomain');
		$sManDesc = $this->validatePostdata('mandantDescription');
		$sUsrName = $this->validatePostdata('userContact');
		$sUsrAlias = $this->validatePostdata('userAlias');
		$sPassword1 = $this->validatePostdata('userPassword');
		$sPassword2 = $this->validatePostdata('userConfirm');
		$nLanguage = getInt($_POST['mandantLanguage']);
		
		// Abbrechen, wenn Passwörter nicht gleich sind
		if ($sPassword1 != $sPassword2) {
			return($this->Res->html(49,$Lang));
		}
		
		// Adresse erstellen
		$sSQL = "INSERT INTO tbaddress (adr_Gender,adr_Firstname)
		VALUES (0,'$sUsrName')";
		$nAddressID = $this->Conn->insert($sSQL);
		
		// Page erstellen wenn nötig
		if (getInt($_POST['usetype']) == 1) {
			// Neue Page erstellen
			$nDesignID = getInt($_POST['designID']);
			$nAdminDesign = getInt($_POST['adminDesignID']);
			$sPageName = stringOps::getPostEscaped('pageDescription',$this->Conn);
			// Beenden, wenn kein Page Name
			if (strlen($sPageName) == 0) {
				return($this->Res->html(34,$Lang));
			}
			$sConfirmName = $sPageName;
			// Ansonsten, neue Page erstellen
			$sSQL = "INSERT INTO tbpage (adr_ID,design_ID,page_Mandant,
			page_News,page_Stats,page_Admindesign,page_Individual,page_Name)
			VALUES ($nAddressID,$nDesignID,0,0,0,$nAdminDesign,'','$sPageName')";
			$nPageID = $this->Conn->insert($sSQL);
		} else {
			// Gewählte bestehende ID nehmen
			$nPageID = getInt($_POST['presentPageID']);
			$sSQL = "SELECT COUNT(page_ID) FROM tbpage WHERE page_ID = $nPageID";
			$nResult = $this->Conn->getCountResult($sSQL);
			// Wenn es die Page nicht gibt, Error
			if ($nResult != 1) {
				return($this->Res->html(34,$Lang));
			}
			$sConfirmName = '#'.$nPageID;
		}
		
		// IDs holen
		$nStartmenuID = ownerID::get($this->Conn);
		
		// Startseiten Menu erstellen mit Content
		$sSQL = "INSERT INTO tbmenu (mnu_ID,man_ID,typ_ID,mnu_Index,mnu_Active,
		mnu_Invisible,mnu_Secured,mnu_Item,mnu_Parent,mnu_Name)
		VALUES (".$nStartmenuID.",0,100,200,1,0,0,1,0,'Startseite')";
		$this->Conn->command($sSQL);
		
		// Mandanten erstellen
		$sSQL = "INSERT INTO tbmandant (page_ID,man_Start,ugr_AdminID,man_Language,man_Title)
		VALUES ($nPageID,$nStartmenuID,0,$nLanguage,'$sManDesc')";
		$nMandantID = $this->Conn->insert($sSQL);
		
		// Admin Menustruktur erstellen
		$MenuIDs = $this->createAdminmenu($nMandantID,$Lang);
		
		// Page und Menus mit Mandant ID updaten
		$sSQL = "UPDATE tbpage SET page_Mandant = $nMandantID WHERE page_ID = $nPageID";
		$this->Conn->command($sSQL);
		
		$sSQL = "UPDATE tbmenu SET man_ID = $nMandantID WHERE mnu_ID = $nStartmenuID";
		$this->Conn->command($sSQL);
		
		// Security String anhand Passwort holen
		$sSecurity = secureString::getSecurityString($sPassword1,$sUsrAlias);
		// Adminuser erstellen
		$sSQL = "INSERT INTO tbuser (adr_ID,man_ID,usr_Start,
		usr_Access,usr_Alias,usr_Name,usr_Security)
		VALUES ($nAddressID,$nMandantID,$nAdminmenuID,1,
		'$sUsrAlias','$sUsrAlias','$sSecurity')";
		$nAdminuserID = $this->Conn->insert($sSQL);
		
		// Admingruppe erstellen
		$sSQL = "INSERT INTO tbusergroup (man_ID,ugr_Desc,ugr_Start)
		VALUES ($nMandantID,'Administratoren',$nAdminmenuID)";
		$nAdmingroupID = $this->Conn->insert($sSQL);
		
		// Admin zu der Gruppe matchen
		$sSQL = "INSERT INTO tbuser_usergroup (usr_ID,ugr_ID)
		VaLUES ($nAdminuserID,$nAdmingroupID)";
		$this->Conn->command($sSQL);
		
		// Zugriff auf die Admin Menus
		foreach($MenuIDs as $nMnuID) {
			$sSQL = "INSERT INTO tbaccess (ugr_ID,mnu_ID)
			VALUES ($nAdmingroupID,$nMnuID)";
			$this->Conn->command($sSQL);
		}
		
		// Mandant mit Gruppen ID updaten
		$sSQL = "UPDATE tbmandant SET ugr_AdminID = $nAdmingroupID WHERE man_ID = $nMandantID";
		$this->Conn->command($sSQL);
		
		// Domain erstellen
		$sSQL = "INSERT INTO tbdomain (dom_Name,dom_Mandant,page_ID)
		VALUES ('$sDomain',$nMandantID,$nPageID)";
		$this->Conn->command($sSQL);
	}
	
	// Admin Menu erstellen
	private function createAdminmenu($nManID,$Lang) {
		$Menus = array();
		$Res = getResources::getInstance($this->Conn);
		// Administrations Menupunkt erstellen
		array_push($Menus,$this->createMenu($Res->normal(909,$Lang),'2','0',100,$nManID,typeID::MENU_ADMINSTART));
		// Untermenupunkte erstellen
		array_push($Menus,$this->createMenu($Res->normal(910,$Lang),'2.1','2',600,$nManID,typeID::MENU_PAGEADMIN));
		array_push($Menus,$this->createMenu($Res->normal(911,$Lang),'2.2','2',500,$nManID,typeID::MENU_MENUADMIN));
		array_push($Menus,$this->createMenu($Res->normal(912,$Lang),'2.3','2',400,$nManID,typeID::MENU_TEASERADMIN));
		array_push($Menus,$this->createMenu($Res->normal(913,$Lang),'2.4','2',300,$nManID,typeID::MENU_USERADMIN));
		array_push($Menus,$this->createMenu($Res->normal(914,$Lang),'2.5','2',200,$nManID,typeID::MENU_GROUPADMIN));
		array_push($Menus,$this->createMenu($Res->normal(915,$Lang),'2.6','2',100,$nManID,typeID::MENU_FILELIBRARY));
		return($Menus);
	}
	
	// Untermenupunkt erstellen
	private function createMenu($name,$item,$parent,$index,$man,$type) {
		$nMnuID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbmenu (mnu_ID,man_ID,typ_ID,mnu_Index,mnu_Active,
		mnu_Invisible,mnu_Secured,mnu_Item,mnu_Parent,mnu_Name)
		VALUES ($nMnuID,$man,$type,$index,1,0,1,'$item','$parent','$name')";
		$this->Conn->command($sSQL);
		return($nMnuID);
	}
	
	// Mandanten / Page Ordner erstellen
	private function createFolders($nPageID,$nMandantID) {
		// Resourcen Ordner
		$sResPath = BP.'/admin/installer/resources/';
		// Page Ordner erstellen, wenn nicht vorhanden
		$sPagePath = BP.'/page/'.$nPageID.'/';
		$sManPath = BP.'/mandant/'.$nMandantID.'/';
		if (!file_exists($sPagePath)) {
			// Page Ordner erstellen
			mkdir($sPagePath);
			// Element / Library / Include Ordner
			mkdir($sPagePath.'element');
			mkdir($sPagePath.'library');
			mkdir($sPagePath.'include');
			// Javascript Page file erstellen
			$this->createFile($sPagePath.'include/page.js');
			// Favicon kopieren
			copy($sResPath.'favicon.ico',$sPagePath.'include/favicon.ico');
		}
		// Mandanten Ordner erstellen, wenn nicht schon passiert
		if (!file_exists($sManPath)) {
			// Mandanten Ordner erstellen
			mkdir($sManPath);
			mkdir($sManPath.'include');
			// Footer / Header und Mandant js File
			$this->createFile($sManPath.'include/footer.htm');
			$this->createFile($sManPath.'include/top.htm');
			$this->createFile($sManPath.'include/mandant.js');
		}
	}
	
	// Datei erstellen
	private function createFile($filename) {
		if ($handle = fopen($filename, 'a')) {
		     if (is_writable($filename)) {
		          if (fwrite($handle, $content) === true){
		               fclose($handle);
		          }
		     } 
		} 
	}
	
	// Ein Design hinzufügen (sofern keines Ausgewählt)
	private function assignDesign($nPageID) {
		// Resourcen Ordner
		$sResPath = BP.'/admin/installer/resources/';
		// Wahl des Benutzers holen
		$nDesignID = getInt($_POST['designID']);
		$nAdminDesign = getInt($_POST['adminDesignID']);
		// Neue Design ID evaluieren
		$nNewDesignID = $this->getNextDesignID();
		// Wenn eines der Designs 0 ist, dann neues Design kopieren
		if ($nDesignID == 0 || $nAdminDesign == 0) {
			$sDesignPath = BP.'/design/'.$nNewDesignID.'/';
			if (!file_exists($sDesignPath)) mkdir($sDesignPath);
			$this->copyFolder($sResPath.'design/',$sDesignPath);
		}
		
		// Ist das Design 0? wenn ja, zuweisen in DB
		if ($nDesignID == 0) {
			$sSQL = "UPDATE tbpage SET design_ID = $nNewDesignID WHERE page_ID = $nPageID";
			$this->Conn->execute($sSQL);
		}
		// Ist das Admin Design 0? wenn ja, zuweisen in DB
		if ($nAdminDesign == 0) {
			$sSQL = "UPDATE tbpage SET page_Admindesign = $nNewDesignID WHERE page_ID = $nPageID";
			$this->Conn->execute($sSQL);
		}
	}
	
	// Einen Ordner kopieren (nicht rekursiv)
	private function copyFolder($source,$dest) {
		// Destination Folder erstellen
		if (!file_exists($dest)) {
			mkdir($dest);
		}
		// Files von Source folder lesen und kopieren
		if ($resDir = opendir($source)) {
	        while (($sFile = readdir($resDir)) !== false) {
	        	if (filetype($source . $sFile) == 'file') {
	        		copy($source.$sFile,$dest.$sFile);
	        	}
	        }
	    }
	    // Ordner schliessen und Rückgabe
	    closedir($resDir);
	}
	
	// Eruiert die nächste Design ID
	private function getNextDesignID() {
		// Alle Ordner des Design Ordners holen und nach grösse Ordnen (Zahlen)
		$sDesignPath = BP.'/design/';
		$DesignIds = array();
		// Folder durchgehen
		if ($resDir = opendir($sDesignPath)) {
	        while (($sFile = readdir($resDir)) !== false) {
	        	if (filetype($sDesignPath . $sFile) == 'dir') {
	        		array_push($DesignIds,getInt($sFile));
	        	}
	        }
	    }
	    // Ordner schliessen und Rückgabe
	    closedir($resDir);
	    // Design ID Array sortieren
	    sort($DesignIds);
	    // Letzter Eintrag ist folglich das grösste Design
	    return($DesignIds[count($DesignIds)-1]+1);
	}
	
	// Post Daten validieren
	private function validatePostdata($field) {
		$sValue = stringOps::getPostEscaped($field,$this->Conn);
		stringOps::noHtml($sValue);
		return($sValue);
	}
}