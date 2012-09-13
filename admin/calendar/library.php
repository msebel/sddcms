<?php
class moduleCalendar extends commonModule {
	
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
	
	// Alle Daten speichern
	public function saveDates(&$Config) {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nCalID = getInt($_POST['id'][$i]);
			$sDate = $this->validateDate($_POST['date'][$i],$_POST['time'][$i]);
			$sTitle = $_POST['title'][$i];
			stringOps::noHtml($sTitle);
			$this->Conn->escape($sTitle);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			// Wenn Anmeldung aktiviert, Titel voreingabe updaten
			if ($Config['registerAllowed']['Value'] == 1) {
				$sDisplayDate = dateOps::convertDate(
				dateOps::SQL_DATETIME,
					dateOps::EU_DATE,
					$sDate
				);
				$this->updateFormTitle($nCalID,$sTitle.' / '.$sDisplayDate);
			}
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbkalender SET cal_Start = '$sDate', 
			cal_Title = '$sTitle', cal_Active = $nActive
			WHERE cal_ID = $nCalID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved calendar dates');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/calendar/index.php?id='.page::menuID());
	}
	
	// Einen Event speichern
	public function saveDate(&$Config) {
		$nCalID = getInt($_GET['item']);
		// Daten validieren
		$sTitle = $this->validateString($_POST['calTitle']);
		$sCity = $this->validateString($_POST['calCity']);
		$sLocation = $this->validateString($_POST['calLocation']);
		$sStart = $this->validateStart();
		$sEnd = $this->validateEnd();
		$nType = $this->validateType($_POST['calType']);
		$nActive = getInt($_POST['calActive']);
		if ($nActive != 1) $nActive = 0;
		$sText = stringOps::getPostEscaped('calText',$this->Conn);
		stringOps::htmlEntRev($sText);
		$nEleID = $this->uploadFile();
		// Wenn Anmeldung aktiviert, Titel voreingabe updaten
		if ($Config['registerAllowed']['Value'] == 1) {
			$sFieldTitle = $_POST['calTitle'];
			$sDate = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				str_replace("'","",$sStart)
			);
			$this->updateFormTitle($nCalID,$sFieldTitle.' / '.$sDate);
		}
		// Statement erstellen und abfeuern
		$sSQL = "UPDATE tbkalender SET ele_ID = $nEleID,
		cal_Title = $sTitle, cal_City = $sCity,
		cal_Location = $sLocation, kca_ID = $nType,
		cal_Start = $sStart, cal_End = $sEnd,
		cal_Active = $nActive, cal_Text = '$sText'
		WHERE cal_ID = $nCalID";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		logging::debug('saved calendar date');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/calendar/edit.php?id='.page::menuID().'&item='.$nCalID);
	}
	
	// Alle Daten des Kalenders für Übersicht laden
	public function loadDates(&$Data) {
		$sSQL = "SELECT cal_ID,cal_Active,cal_Start,cal_Title FROM tbkalender 
		WHERE mnu_ID = ".page::menuID()." 
		ORDER BY cal_Active ASC, cal_Start DESC";
		$paging = new paging($this->Conn,'index.php?id='.page::menuID());
		$paging->start($sSQL,20);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Daten anpassen und speichern
			$row['cal_Start_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$row['cal_Start']
			);
			$row['cal_Start_Time'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_CLOCK,
				$row['cal_Start']
			);
			// Einfügen in Daten array
			array_push($Data,$row);
		}
		return($paging->getHtml());
	}
	
	// Ein bestimmen Event laden
	public function loadDate(&$Data) {
		$nCalID = getInt($_GET['item']);
		$sSQL = "SELECT ele_ID,kca_ID,cal_Active,cal_Start,cal_End,cal_Title,
		cal_Location,cal_City,cal_Text FROM tbkalender WHERE cal_ID = $nCalID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Daten konvertieren
			$row['cal_Start_Date'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$row['cal_Start']);
			$row['cal_Start_Time'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_TIME,$row['cal_Start']);
			if ($row['cal_End'] != NULL) {
				$row['cal_End_Date'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$row['cal_End']);
				$row['cal_End_Time'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_TIME,$row['cal_End']);
			}
			// Daten speichern
			$Data = $row;
		}
	}
	
	// Zugriff testen
	public function checkAccess($nCalID) {
		$sSQL = "SELECT COUNT(cal_ID) FROM tbkalender
		WHERE cal_ID = $nCalID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		$bReturn = false;
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Neuen Termin einfügen
	public function addDate(&$Config) {
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
        $sTitle = '< '.$this->Res->html(548,page::language()).' >';
        $sSQL = "INSERT INTO tbkalender (mnu_ID,ele_ID,kca_ID,cal_Active,cal_Start,cal_Title,cal_Text) 
        VALUES (".page::menuID().",0,0,0,'".$sDate."','".$sTitle."','')";
        $nCalID = $this->Conn->insert($sSQL);
		// Wenn Anmeldung aktiviert, Formular erstellen
		if ($Config['registerAllowed']['Value'] == 1) {
			$this->createEventForm(
				$nCalID,
				$Config['registerCaptcha']['Value'],
				$Config['registerMail']['Value'],
				$Config['additionalFields']['Value']
			);
		}
		// Erfolg melden und Weiterleiten
		$this->resetPaging();
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/calendar/index.php?id='.page::menuID());
	}
	
	// Einene Termin entfernen
	public function deleteDate() {
		// Content löschen
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(cal_ID) FROM tbkalender
		WHERE cal_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbkalender WHERE cal_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Wenn Anmeldung aktiviert, Titel voreingabe updaten
			$this->deleteEventForm($nDeleteID);
			// Erfolg melden und weiterleiten
			logging::debug('deleted calendar date');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/calendar/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting calendar date');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/calendar/index.php?id='.page::menuID()); 
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standard Dinge
		$nViewType = getInt($_POST['viewType']);
		if ($nViewType < 1 || $nViewType > 6) $nViewType = 1;
		$Config['viewType']['Value'] = $nViewType;
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Spezielle Dinge
		$nShowOldDates = getInt($_POST['showOldDates']);
		if ($nShowOldDates != 1) $nShowOldDates = 0;
		$Config['showOldDates']['Value'] = $nShowOldDates;
		$nCalendarStart = getInt($_POST['calendarStart']);
		if ($nCalendarStart != 1) $nCalendarStart = 0;
		$Config['calendarStart']['Value'] = $nCalendarStart;
		$nPdfPrint = getInt($_POST['pdfPrint']);
		if ($nPdfPrint != 1) $nPdfPrint = 0;
		$Config['pdfPrint']['Value'] = $nPdfPrint;
		// Anmeldungsdaten
		$nRegister = getInt($_POST['registerAllowed']);
		$nCaptcha = getInt($_POST['registerCaptcha']);
		$sRegMail = stringOps::getPostEscaped('registerMail',$this->Conn);
		if (!stringOps::checkEmail($sRegMail)) $sRegMail = '';
		// Änderung an Anmeldung prüfen
		if ($nRegister != $Config['registerAllowed']['Value']) {
			// Trigger ausführen
			$this->triggerRegisterAllowedChange($nRegister,$nCaptcha,$sRegMail,$Config);
			// Speichern der Änderung 
			$Config['registerAllowed']['Value'] = $nRegister;
			// Captcha/Mail zurücksetzen
			$Config['registerCaptcha']['Value'] = 0;
			$Config['registerMail']['Value'] = '';
		} else {
			// Captcha ein/ausgeschaltet?
			if ($nCaptcha != $Config['registerCaptcha']['Value']) {
				$this->triggerRegisterCaptcha($nCaptcha,$sRegMail);
				$Config['registerCaptcha']['Value'] = $nCaptcha;
			}
			// Mail Adresse aktualisieren (egal ob anders oder nicht)
			$Config['registerMail']['Value'] = $sRegMail;
			if ($nRegister == 1) $this->triggerRegisterMailChange($sRegMail);
		}
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved calendar config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/calendar/config.php?id='.page::menuID());
	}
	
	/**
	 * Ausgeben des Typen Dropdown mit Preselektion
	 * @param array $data Datenzeile
	 */
	public function getTypeDropdown($data) {
		$out .= '';
		$sSQL = 'SELECT kca_ID,kca_Desc FROM tbkalendercategory
		WHERE mnu_ID = '.page::menuID().' ORDER BY kca_Desc ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$check = checkDropDown($data['kca_ID'], $row['kca_ID']);
			$out .= '
				<option value="'.$row['kca_ID'].'"'.$check.'>'.$row['kca_Desc'].'</option>
			';
		}
		return($out);
	}
	
	// Konfiguration initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,9)) {
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'registerAllowed',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'registerCaptcha',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'registerMail',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'calendarStart',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'showOldDates',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'viewType',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'pdfPrint',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'additionalFields',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// HTML Formular für Anmeldungsoptionen
	public function getRegisterHtml(&$Config) {
		$out .= '
		<tr>
			<td width="150" valign="top">
				'.$this->Res->html(982,page::language()).':
			</td>
			<td valign="top">
				<input type="checkbox" value="1"'.checkCheckbox(1,$Config['registerAllowed']['Value']).' name="registerAllowed"> '.$this->Res->html(981,page::language()).'
			</td>
		</tr>';
		// Optionen für Captcha und Mail
		if ($Config['registerAllowed']['Value'] == 1) {
			$out .= '
			<tr>
				<td width="150" valign="top">
					'.$this->Res->html(983,page::language()).':
				</td>
				<td valign="top">
					<input type="text" value="'.$Config['registerMail']['Value'].'" name="registerMail" style="width:250px;">
				</td>
			</tr>
			<tr>
				<td width="150" valign="top">
					&nbsp;
				</td>
				<td valign="top">
					<input type="checkbox" value="1"'.checkCheckbox(1,$Config['registerCaptcha']['Value']).' name="registerCaptcha"> '.$this->Res->html(984,page::language()).'
				</td>
			</tr>';
		}
		return($out);
	}
	
	// Das File entfernen und kein Element referenzieren
	public function removeFile() {
		$nCalID = getInt($_GET['item']);
		$sSQL = "SELECT ele_ID FROM tbkalender WHERE cal_ID = $nCalID";
		$nEleID = getInt($this->Conn->getFirstResult($sSQL));
		// Dereferenzieren
		$sSQL = "UPDATE tbkalender SET ele_ID = 0 WHERE cal_ID = $nCalID";
		$this->Conn->command($sSQL);
		// Löschen des Elementes physikalisch
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace('{PAGE_ID}',page::ID(),$sPath);
		$sPath = str_replace('{ELE_ID}',$nEleID,$sPath);
		$sPath = BP.$sPath;
		if (file_exists($sPath)) {
			// Folder durchgehen und File darin löschen
			if ($resDir = opendir($sPath)) {
		        while (($sFile = readdir($resDir)) !== false) {
		        	if (filetype($sPath . $sFile) == 'file') {
			        	unlink($sPath . $sFile);
		        	}
		        }
		    }
		    // Ordner schliessen und löschen
		    closedir($resDir);
		    rmdir($sPath);
		    // Erfolg melden und weiterleiten
		    logging::debug('removed calendar event file');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/calendar/edit.php?id='.page::menuID().'&item='.$nCalID);
		} else {
			// Fehler melden
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/calendar/edit.php?id='.page::menuID().'&item='.$nCalID);
		}
	}
	
	// HTML Code für das File bekommen
	public function getFileHtml($nEleID) {
		$nEleID = getInt($nEleID);
		$nCalID = getInt($_GET['item']);
		// File hochladen oder löschen
		if ($nEleID > 0) {
			// Daten des Elementes holen
			$sSQL = "SELECT ele_File FROM tbelement WHERE ele_ID = $nEleID";
			$sName = $this->Conn->getFirstResult($sSQL);
			// Pfad des Files erstellen
			$sPath = mediaConst::FILEPATH;
			$sPath = str_replace('{PAGE_ID}',page::ID(),$sPath);
			$sPath = str_replace('{ELE_ID}',$nEleID,$sPath);
			$sPath.= $sName;
			// File vorhanden, Name mit Link anzeigen
			// und Link zum löschen des Files
			$out .= '
			<div style="float:left;margin-right:10px;">
			<a href="edit.php?id='.page::menuID().'&item='.$nCalID.'&remove">
			<img src="/images/icons/delete.png" alt="'.$this->Res->html(193,page::language()).'" title="'.$this->Res->html(193,page::language()).'" border=""></a>
			</div> 
			<div style="float:left;"><a href="'.$sPath.'" target="_blank">'.$sName.'</a></div>
			';
		} else {
			// Kein File, Upload Formular anzeigen
			$out .= '
			<input type="file" name="calFile" class="adminBufferInput">
			';
		}
		return($out);
	}
	
	// Das File hochladen und im aktuellen Record referenzieren
	private function uploadFile() {
		// Bisherige Element ID holen
		$nCalID = getInt($_GET['item']);
		$sSQL = "SELECT ele_ID FROM tbkalender WHERE cal_ID = $nCalID";
		$nEleID = getInt($this->Conn->getFirstResult($sSQL));
		// Schauen ob ein Upload daher kommt
		if (isset($_FILES['calFile'])) {
			// Neues Element erstellen
			$sSQL = "INSERT INTO tbelement (ele_ID) VALUES (NULL)";
			$nEleID = $this->Conn->insert($sSQL);
			// Elementen Ordner erstellen
			$sPath = mediaConst::FILEPATH;
			$sPath = str_replace('{PAGE_ID}',page::ID(),$sPath);
			$sPath = str_replace('{ELE_ID}',$nEleID,$sPath);
			if (!file_exists(BP.$sPath)) {
				mkdir(BP.$sPath,755,true);
			}
			// Datei verarbeiten
			$Upload = new uploadFile('calFile',$nEleID,$this->Res);
			$Success = $Upload->save();
			
			// ele_ID löschen, wenn kein Erfolg
			if (!$Success) {
				$nEleID = 0; 
			} else {
				// Weitere Daten im Element speichern
				$nSize = $Upload->getSize();
				$sName = $Upload->getFilename();
				$sSQL = "UPDATE tbelement SET ele_Size = $nSize,
				ele_File = '$sName' WHERE ele_ID = $nEleID";
				$this->Conn->command($sSQL);
			}
		}
		return($nEleID);
	}
	
	// Eingegebenes Datum validieren
	private function validateDate($sDate,$sTime) {
		// Fallback Werte generieren
		$sValidDate = dateOps::getTime(dateOps::EU_DATE,time());
		$sValidTime = dateOps::getTime(dateOps::EU_TIME,time());
		// Datum überprüfen
		if (stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATE)) {
			$sValidDate = $sDate;
		}
		// Zeitengabe überprüfen
		if (stringOps::checkDate($sTime.':00',dateOps::EU_FORMAT_TIME)) {
			$sValidTime = $sTime.':00';
		}
		// Konvertieren der fertigen Werte
		$sReturn = dateOps::convertDate(
			dateOps::EU_DATETIME,
			dateOps::SQL_DATETIME,
			$sValidDate.' '.$sValidTime
		);
		return($sReturn);
	}
	
	// Startdatum validieren
	private function validateStart() {
		$sDate = $_POST['calStartDate'];
		$sTime = $_POST['calStartTime'];
		$sDateTime = $this->convertInputDate($sDate,$sTime);
		return("'".$sDateTime."'");
	}
	
	// Enddatum validieren
	private function validateEnd() {
		$sDate = $_POST['calEndDate'];
		$sTime = $_POST['calEndTime'];
		// Nur validieren, wenn etwas eingegeben wurde
		if (strlen($sDate) > 0 || (strlen($sDate) > 0 && strlen($sTime) > 0)) {
			$sDateTime = $this->convertInputDate($sDate,$sTime);
			$sDateTime = "'".$sDateTime."'";
		} else {
			$sDateTime = 'NULL';
		}
		return($sDateTime);
	}
	
	// Ein eingegebenes Datum validieren
	private function convertInputDate($sDate,$sTime) {
		// Sekunden anhängen, wenn nicht angegeben
		if (strlen($sTime) == 5) $sTime .= ':00';
		// Einzeln validieren ob ok
		if (!stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATE)) {
			$sDate = dateOps::getTime(dateOps::EU_DATE);
		}
		if (!stringOps::checkDate($sTime,dateOps::EU_FORMAT_TIME)) {
			$sTime = dateOps::getTime(dateOps::EU_TIME);
		}
		// Zusammenführen und zu SQL konvertieren
		$sDateTime = $sDate.' '.$sTime;
		$sDateTime = dateOps::convertDate(
			dateOps::EU_DATETIME,
			dateOps::SQL_DATETIME,
			$sDateTime
		);
		return($sDateTime);
	}
	
	// Typ des Events validieren
	private function validateType($nType) {
		switch (getInt($nType)) {
			case 1: $nType = 1; break;
			// Case 0 und default sind gleich
			case 0: 
			default:
				$nType = 0;
				break;
		}
		// Default 0 zurückgeben
		return($nType);
	}
	
	// Eingegebenen String validieren
	private function validateString($sString) {
		stringOps::noHtml($sString);
		$this->Conn->escape($sString);
		return("'".$sString."'");
	}
	
	// Ändern der Empfänger Adresse für alle Formulare
	private function triggerRegisterMailChange($sRegMail) {
		// Ganz einfach alle Felder dieses Menu updaten
		$sSQL = "UPDATE tbformfield SET ffi_Email = '$sRegMail'
		WHERE mnu_ID = ".page::menuID();
		$this->Conn->command($sSQL);
	}
	
	// Löschen, hinzufügen der Captcha, wenn geändert
	private function triggerRegisterCaptcha($nCaptcha,$sRegMail) {
		// Wenn Captcha deaktiviert wurde, alle Felder löschen
		if ($nCaptcha == 0) {
			$sSQL = "DELETE FROM tbformfield
			WHERE (ffi_Type = 'captcha' OR ffi_Type = 'submit') 
			AND mnu_ID = ".page::menuID();
			$this->Conn->command($sSQL);
		}
		// Wenn Captcha aktiviert, Felder hinzufügen (komplexer)
		if ($nCaptcha == 1) {
			// Sections aller Events holen
			$Sections = $this->getEventSections();
			foreach ($Sections as $nCseID) {
				// Submit löschen
				$sSQL = "DELETE FROM tbformfield
				WHERE ffi_Type = 'submit' AND cse_ID = $nCseID";
				$this->Conn->command($sSQL);
				// Formularfeld hinzufügen
				$this->addEventField($nCseID,1,$this->Res->html(990,page::language()),'',$sRegMail,'captcha');
			}
		}
		// Für alle Sections, neue Submits erstellen, da diese gelöscht wurden
		if (!isset($Sections)) $Sections = $this->getEventSections();
		foreach ($Sections as $nCseID) {
			// Formularfeld hinzufügen
			$this->addEventField($nCseID,0,$this->Res->html(991,page::language()),'',$sRegMail,'submit');
		}
	}
	
	// Section IDs aller Events holen
	private function getEventSections() {
		$sSQL = "SELECT cse_ID FROM tbkalender_contentsection
		INNER JOIN tbkalender ON tbkalender.cal_ID = tbkalender_contentsection.cal_ID
		WHERE tbkalender.mnu_ID = ".page::menuID();
		$nRes = $this->Conn->execute($sSQL);
		$Sections = array();
		while ($row = $this->Conn->next($nRes)) {
			array_push($Sections,$row['cse_ID']);
		}
		return($Sections);
	}
	
	// Änderung an der Anmeldeoption ist geschehen
	private function triggerRegisterAllowedChange($nRegister,$nCaptcha,$sRegMail,&$Config) {
		// Alle Event IDs lesen
		$sSQL = "SELECT cal_ID FROM tbkalender WHERE mnu_ID = ".page::menuID();
		$nRes = $this->Conn->execute($sSQL);
		// Aktiviert
		if ($nRegister == 1) {
			// Für alle Events Formulare erfassen
			while ($row = $this->Conn->next($nRes)) {
				$this->createEventForm(
					$row['cal_ID'],
					$nCaptcha,
					$sRegMail,
					$Config['additionalFields']['Value']
				);
			}
		}
		// Deaktiviert
		if ($nRegister == 0) {
			// Alle Formulare der Events löschen
			while ($row = $this->Conn->next($nRes)) {
				$this->deleteEventForm($row['cal_ID']);
			}
		}
	}
	
	// Formular für einen Event erstellen
	private function createEventForm($nCalID,$nCaptcha,$sRegMail,$sAdd) {
		// Bestehendes allenfalls löschen
		$this->deleteEventForm($nCalID);
		// Section erstellen
		$nCseID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,
		cse_Type,cse_Name,cse_Active) VALUES 
		($nCseID,0,".page::menuID().",0,3,'tbkalender_contentsection connection',0)";
		$this->Conn->command($sSQL);
		// Verbindung zur Section herstellen
		$sSQL = "INSERT INTO tbkalender_contentsection (cal_ID,cse_ID) VALUES ($nCalID,$nCseID)";
		$this->Conn->command($sSQL);
		// Event Titel holen
		$sSQL = "SELECT cal_Title,cal_Start FROM tbkalender WHERE cal_ID = $nCalID";
		$nRes = $this->Conn->execute($sSQL);
		// Event Titel generieren
		$sTitle = '';
		if ($Event = $this->Conn->next($nRes)) {
			$sDate = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$Event['cal_Start']
			);
			$sTitle = $Event['cal_Title'].' / '.$sDate;
			$this->Conn->escape($sTitle);
		}
		// Formularfelder erstellen
		$this->addEventField($nCseID,0,$this->Res->html(985,page::language()),'',$sRegMail,'text');
		$this->addEventField($nCseID,1,$this->Res->html(986,page::language()),'',$sRegMail,'text');
		$this->addEventField($nCseID,0,$this->Res->html(987,page::language()),'',$sRegMail,'text');
		$this->addEventField($nCseID,0,$this->Res->html(66,page::language()).':','',$sRegMail,'text');
		$this->addEventField($nCseID,0,$this->Res->html(65,page::language()).':','',$sRegMail,'text');
		$this->addEventField($nCseID,1,$this->Res->html(988,page::language()),$sTitle,$sRegMail,'text');
		// individuelle Felder einfügen
		if (strlen($sAdd) > 0) {
			$fields = explode(';',$sAdd);
			foreach ($fields as $field) {
				$this->addEventField($nCseID,0,$field.':','',$sRegMail,'text');
			}
		}
		// Textfeld für Nachricht
		$this->addEventField($nCseID,0,$this->Res->html(989,page::language()),'',$sRegMail,'textarea');
		// Captcha, wenn gewünscht
		if ($nCaptcha == 1) {
			$this->addEventField($nCseID,1,$this->Res->html(990,page::language()),'',$sRegMail,'captcha');
		}
		// Und den Sendebutton
		$this->addEventField($nCseID,0,$this->Res->html(991,page::language()),'',$sRegMail,'submit');
	}
	
	// Ein Feld einem Formular zuweisen
	private function addEventField($nCseID,$nRequired,$sFormName,$sPrefilled,$sRegMail,$sFormType) {
		// Maximale Sortierung holen
		$sSQL = "SELECT MAX(ffi_Sortorder) FROM tbformfield WHERE cse_ID = $nCseID";
		$nSort = getInt($this->Conn->getFirstResult($sSQL)) + 1;
		$sSQL = "INSERT INTO tbformfield (cse_ID,mnu_ID,ffi_Width,
		ffi_Sortorder,ffi_Required,ffi_Name,ffi_Desc,ffi_Type,ffi_Value,ffi_Email) 
		VALUES ($nCseID,".page::menuID().",250,$nSort,$nRequired,'form_$nSort',
		'$sFormName','$sFormType','$sPrefilled','$sRegMail')";
		$this->Conn->execute($sSQL);
	}
	
	// Formular eines Events löschen
	private function deleteEventForm($nCalID) {
		// Verbindung (Content Section) suchen
		$sSQL = "SELECT cse_ID FROM tbkalender_contentsection WHERE cal_ID = $nCalID";
		$nCseID = getInt($this->Conn->getFirstResult($sSQL));
		if ($nCseID > 0) {
			// Wenn es sie gibt, löschen
			$sSQL = "DELETE FROM tbkalender_contentsection
			WHERE cse_ID = $nCseID AND cal_ID = $nCalID";
			$this->Conn->command($sSQL);
			// Section selbst löschen
			$sSQL = "DELETE FROM tbcontentsection
			WHERE cse_ID = $nCseID AND mnu_ID = ".page::menuID();
			$this->Conn->command($sSQL);
			// Alle Formulare darin auch löschen
			$sSQL = "DELETE FROM tbformfield
			WHERE cse_ID = $nCseID AND mnu_ID = ".page::menuID();
			$this->Conn->command($sSQL);
		}
	}
	
	// Titel Vorgabe des Formular updaten
	private function updateFormTitle($nCalID,$sTitle) {
		// Verbindung und Feld ID holen
		$sFormName = $this->Res->html(988,page::language());
		$this->Conn->escape($sFormName);
		$sSQL = "SELECT ffi_ID FROM tbformfield
		INNER JOIN tbkalender_contentsection ON tbkalender_contentsection.cse_ID = tbformfield.cse_ID
		WHERE ffi_Desc = '$sFormName' AND tbkalender_contentsection.cal_ID = $nCalID";
		$nFfiID = getInt($this->Conn->getFirstResult($sSQL));
		// Formularfeld updatenm
		$sSQL = "UPDATE tbformfield SET ffi_Value = '$sTitle' WHERE ffi_ID = $nFfiID";
		$this->Conn->command($sSQL);
	}
}