<?php

class moduleContent extends commonModule {
	
	const TYPE_CONTENT = 1;
	const TYPE_MEDIA = 2;
	const TYPE_FORM = 3;
	
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
	
	// Content laden
	public function loadContent(&$sData) {
		// Content ID holen
		$nContentID = getInt($_GET['content']);
		// Vorhandene Daten holen
		$sSQL = "SELECT con_Title,con_Date,con_Modified,con_ShowName,
		con_ShowDate,con_ShowModified,con_DateFrom,con_DateTo,con_Content
		FROM tbcontent WHERE con_ID = $nContentID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sResult = $row;
		}
		// Contentsection holen
		$sSQL = "SELECT cse_ID FROM tbcontentsection WHERE con_ID = $nContentID";
		$nSectionID = $this->Conn->getFirstResult($sSQL);
		// Content holen
		$sContent = $sResult['con_Content'];
		// Wenn Datum = NULL, aktuelles Datum nehmen
		$nDateStamp = time();
		if ($sResult['con_Date'] != NULL) {
			$nDateStamp = dateOps::getStamp(
				dateOps::SQL_DATETIME,$sResult['con_Date']
			);
		} 
		// Gleich mit dem änderungsdatum
		$nModStamp = time();
		if ($sResult['con_Modified'] != NULL) {
			$nModStamp = dateOps::getStamp(
				dateOps::SQL_DATETIME,$sResult['con_Modified']
			);
		} 
		// Start und Enddatum erstellen
		if ($sResult['con_DateFrom'] != NULL) {
			$sResult['con_DateFrom'] = dateOps::getStamp(
				dateOps::SQL_DATETIME,$sResult['con_DateFrom']
			);
		}
		if ($sResult['con_DateTo'] != NULL) {
			$sResult['con_DateTo'] = dateOps::getStamp(
				dateOps::SQL_DATETIME,$sResult['con_DateTo']
			);
		}
		// EU konformes Datum konvertieren
		$sDate_Date = dateOps::getTime(dateOps::EU_DATE,$nDateStamp);
		$sDate_Time = dateOps::getTime(dateOps::EU_TIME,$nDateStamp);
		$sMod_Date  = dateOps::getTime(dateOps::EU_DATE,$nModStamp);
		$sMod_Time  = dateOps::getTime(dateOps::EU_TIME,$nModStamp);
		// Berechnen des Anzeigeoptionen
		$nShowdate = 0;
		if ($sResult['con_ShowDate'] == 1) $nShowDate = 1;
		if ($sResult['con_ShowModified'] == 1) $nShowDate = 2;
		// Ergebnisse in $sDate Array schreiben
		$sData['con_Title']		= $sResult['con_Title'];
		$sData['con_ShowName']	= $sResult['con_ShowName'];
		$sData['con_ShowDate']	= $nShowDate;
		$sData['date_date']		= $sDate_Date;
		$sData['date_time']		= $sDate_Time;
		$sData['date_to']		= $sResult['con_DateTo'];
		$sData['date_from']		= $sResult['con_DateFrom'];
		$sData['modified_date']	= $sMod_Date;
		$sData['modified_time']	= $sMod_Time;
		$sData['con_Content']	= $sContent;
		$sData['cse_ID']		= $nSectionID;
		// HTML entitieren
		stringOps::htmlEnt($sData['title']);
	}
	
	// Content speichern
	public function saveContent($sModule) {
		$Errors = array();
		// Welchen Content haben wir?
		$nContentID = getInt($_GET['content']);
		// Daten holen
		$sTitle = stringOps::getPostEscaped('title',$this->Conn);
		$sContent = stringOps::getPostEscaped('content',$this->Conn);
		// Datum Strings validieren
		$sDate = stringOps::getPostEscaped('date_date',$this->Conn).' '.
		         stringOps::getPostEscaped('date_time',$this->Conn);
		$this->validateDateTime($Errors,$sDate);
		// Enddatum validiaren, wenn keine Zeit, aktuelle nehmen
		$sDateTo = stringOps::getPostEscaped('date_to',$this->Conn);
		if (strlen($_POST['date_to_time']) > 0) {
			$sDateTo .= ' ' . stringOps::getPostEscaped('date_to_time',$this->Conn);
		} else {
			$sDateTo .= ' ' . date('H:i:s');
		}
		// Startdatum validiaren, wenn keine Zeit, aktuelle nehmen
		$sDateFrom = stringOps::getPostEscaped('date_from',$this->Conn);
		if (strlen($_POST['date_from_time']) > 0) {
			$sDateFrom .= ' ' . stringOps::getPostEscaped('date_from_time',$this->Conn);
		} else {
			$sDateFrom .= ' ' . date('H:i:s');
		}
		// Wenn kein Startdatum aber Enddatum, heutiges als Start nehmen
		if (stringOps::checkDate($sDateTo,dateOps::EU_FORMAT_DATETIME) 
		&& !stringOps::checkDate($sDateFrom,dateOps::EU_FORMAT_DATETIME)) {
			$sDateFrom = dateOps::getTime(dateOps::EU_DATETIME);
		}
		$this->validateDate($Errors,$sDateFrom);
		$this->validateDate($Errors,$sDateTo);
		// Flags generieren
		$nShowDate = 0; $nShowModified = 0; $nShowName = 0;
		$this->validateFlags($nShowDate,$nShowModified,$nShowName);
		// Daten verarbeiten
		stringOps::htmlEntRev($sContent);
		stringOps::noHtml($sTitle);
		// Wenn Content, Aktivflag von der Section holen
		$sActiveUpdate = '';
		if ($sModule == 'content') {
			$nActive = 0;
			$sSQL = "SELECT cse_Active FROM tbcontentsection
			WHERE con_ID = $nContentID";
			$nActive = $this->Conn->getFirstResult($sSQL);
			$sActiveUpdate = ', con_Active = '.$nActive;
		}
		// Daten speichern, wenn keine Errors
		if (count($Errors) == 0) {
			// SQL Konforme Daten konvertieren
			$sDate = dateOps::convertDate(dateOps::EU_DATETIME,dateOps::SQL_DATETIME,$sDate);
			// änderungsdatum erstellen
			$sModified = dateOps::getTime(dateOps::SQL_DATETIME);
			$sSQL = "UPDATE tbcontent SET
			con_Date = '$sDate', con_Modified = '$sModified',
			con_ShowName = $nShowName, con_ShowDate = $nShowDate,
			con_DateFrom = $sDateFrom, con_DateTo = $sDateTo,
			con_ShowModified = $nShowModified, con_Title = '$sTitle',
			con_Content = '$sContent' ".$sActiveUpdate."
			WHERE con_ID = $nContentID";
			$this->Conn->command($sSQL);
			// Loggen und weiterleiten
			logging::debug('content saved');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/'.$sModule.'/content.php?id='.page::menuID().'&content='.$nContentID);
		} else {
			$this->setErrorSession($Errors);
		}
	}
	
	// Kopieren einer Section in die Zwischenablage
	public function copySection($cut) {
		if ($cut) {
			$nCopyID = getInt($_GET['cut']);
		} else {
			$nCopyID = getInt($_GET['copy']);
		}
		// Prüfen ob die Section dem aktuellen Mandanten gehört
		$sSQL = "SELECT COUNT(tbcontentsection.cse_ID) FROM tbcontentsection
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontentsection.mnu_ID
		WHERE tbcontentsection.cse_ID = $nCopyID AND tbmenu.man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		// Fehler, wenn es nicht genau eine Kombination
		if ($nResult == 1) {
			// Speichern der Daten
			$_SESSION['CopiedContentID'] = $nCopyID;
			$_SESSION['CopiedOrigMenu'] = page::menuID();
			$_SESSION['CopyStyleCut'] = $cut;
			// Erfolg melden und weiterleiten
			logging::debug('content copied');
			$this->setErrorSession($this->Res->html(735,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		} else {
			// Fehler melden
			logging::error('content copying failed');
			$this->setErrorSession($this->Res->html(736,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		}
	}
	
	// Kopieren einer Section in die Zwischenablage
	public function copyNews($cut) {
		if ($cut) {
			$nCopyID = getInt($_GET['cut']);
		} else {
			$nCopyID = getInt($_GET['copy']);
		}
		// Prüfen ob die Section dem aktuellen Mandanten gehört
		$sSQL = "SELECT COUNT(tbcontent.con_ID) FROM tbcontent
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontent.mnu_ID
		WHERE tbcontent.con_ID = $nCopyID AND tbmenu.man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		// Fehler, wenn es nicht genau eine Kombination
		if ($nResult == 1) {
			// Speichern der Daten
			$_SESSION['CopiedNewsID'] = $nCopyID;
			$_SESSION['CopiedNewsMenu'] = page::menuID();
			$_SESSION['CopyNewsStyleCut'] = $cut;
			// Erfolg melden und weiterleiten
			logging::debug('news copied');
			$this->setErrorSession($this->Res->html(735,page::language()));
			session_write_close();
			redirect('location: /admin/news/index.php?id='.page::menuID());
		} else {
			// Fehler melden
			logging::error('news copying failed');
			$this->setErrorSession($this->Res->html(736,page::language()));
			session_write_close();
			redirect('location: /admin/news/index.php?id='.page::menuID());
		}
	}
	
	// Eine Section kopieren/einfügen
	public function pasteSection() {
		// Zu kopierende ID holen
		$nCopyID = getInt($_SESSION['CopiedContentID']);
		// Aktuelle Daten der Section holen
		$sSQL = "SELECT con_ID, mnu_ID, cse_Type, cse_Active, cse_Name
		FROM tbcontentsection WHERE cse_ID = $nCopyID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
			$Data['cse_ID'] = $nCopyID;//looks very obsolete
		}
		// Wenn Daten vorhanden, etwas tun
		$Success = false;
		switch (getInt($Data['cse_Type'])) {
			case self::TYPE_CONTENT:
				$Success = $this->pasteContent($Data);
				break;
			case self::TYPE_FORM:
				$Success = $this->pasteForm($Data);
				break;
			case self::TYPE_MEDIA:
				$Success = $this->pasteElement($Data);
				break;
		}
		// Fehlermeldung oder Erfolg
		if ($Success) {
			// Löschen der alten Section, wenn ausschneiden
			if ($_SESSION['CopyStyleCut']) {
				$this->deleteSection($nCopyID,$_SESSION['CopiedOrigMenu'],false);
				unset($_SESSION['CopiedContentID']);
				unset($_SESSION['CopiedOrigMenu']);
				unset($_SESSION['CopyStyleCut']);
			}
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		} else {
			// Fehler melden und Weiterleiten
			$this->setErrorSession($this->Res->html(736,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		}
	}
	
	// Eine Section kopieren/einfügen
	public function pasteNews() {
		// Zu kopierende ID holen
		$nCopyID = getInt($_SESSION['CopiedNewsID']);
		// Aktuelle Daten der Section holen
		$sSQL = "SELECT con_ID, mnu_ID
		FROM tbcontent WHERE con_ID = $nCopyID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
		}
		// Wenn Daten vorhanden, etwas tun
		$Success = $this->pasteContent($Data);
		// Fehlermeldung oder Erfolg
		if ($Success) {
			// Löschen der alten Section, wenn ausschneiden
			if ($_SESSION['CopyNewsStyleCut']) {
				$this->deleteContent($nCopyID,$_SESSION['CopiedNewsMenu'],false);
				unset($_SESSION['CopiedNewsID']);
				unset($_SESSION['CopiedNewsMenu']);
				unset($_SESSION['CopyNewsStyleCut']);
			}
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/news/index.php?id='.page::menuID());
		} else {
			// Fehler melden und Weiterleiten
			$this->setErrorSession($this->Res->html(736,page::language()));
			session_write_close();
			redirect('location: /admin/news/index.php?id='.page::menuID());
		}
	}
	
	// Paste Icon inklusive Flying Window Code holen
	public function getPasteIcon() {
		// In der Kopiersessions suchen
		$nCopyID = getInt($_SESSION['CopiedContentID']);
		// Jenachdem ob eine Session gesetzt ist
		if ($nCopyID > 0) {
			// Icon ausgeben
			$out = '
			<img id="windowPaste" src="/images/icons/paste_plain.png" alt="'.$this->Res->html(737,page::language()).'" title="'.$this->Res->html(737,page::language()).'">
			';
			// Flying Window vorbereiten
			$window = htmlControl::window();
			$HTML = $this->getPasteWindowHtml($nCopyID);
			$Title = $this->Res->html(740,page::language());
			$window->add('windowPaste',$HTML,$Title,400,150);
			$out .= $window->get('windowPaste');
		} else {
			$out = '
			<img src="/images/icons/paste_plain_disabled.png" alt="'.$this->Res->html(738,page::language()).'" title="'.$this->Res->html(738,page::language()).'" border="0">
			';
		}
		return($out);
	}
	
	// Paste Icon inklusive Flying Window Code holen
	public function getNewsPasteIcon() {
		// In der Kopiersessions suchen
		$nCopyID = getInt($_SESSION['CopiedNewsID']);
		// Jenachdem ob eine Session gesetzt ist
		if ($nCopyID > 0) {
			// Icon ausgeben
			$out = '
			<img id="windowPaste" src="/images/icons/paste_plain.png" alt="'.$this->Res->html(737,page::language()).'" title="'.$this->Res->html(737,page::language()).'">
			';
			// Flying Window vorbereiten
			$window = htmlControl::window();
			$HTML = $this->getPasteWindowHtml($nCopyID);
			$Title = $this->Res->html(740,page::language());
			$window->add('windowPaste',$HTML,$Title,400,150);
			$out .= $window->get('windowPaste');
		} else {
			$out = '
			<img src="/images/icons/paste_plain_disabled.png" alt="'.$this->Res->html(738,page::language()).'" title="'.$this->Res->html(738,page::language()).'" border="0">
			';
		}
		return($out);
	}
	
	// FromTo für sichtbar ab/bis laden
	public function loadFromTo(&$FromTo,&$Data) {
		// To Datum abchecken
		if ($Data['date_to'] == NULL) {
			$FromTo['date_to_time'] = '';
			$FromTo['date_to'] = '';
		} else {
			$FromTo['date_to_time'] = dateOps::getTime(dateOps::EU_TIME,$Data['date_to']);
			$FromTo['date_to'] = dateOps::getTime(dateOps::EU_DATE,$Data['date_to']);
		}
		// From Datum abchecken
	if ($Data['date_from'] == NULL) {
			$FromTo['date_from_time'] = '';
			$FromTo['date_from'] = '';
		} else {
			$FromTo['date_from_time'] = dateOps::getTime(dateOps::EU_TIME,$Data['date_from']);
			$FromTo['date_from'] = dateOps::getTime(dateOps::EU_DATE,$Data['date_from']);
		}
	}
	
	// Content Sektionen speichern
	public function saveContentSections() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nCseID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			$sName = $_POST['name'][$i];
			// Escapen des Namen
			$this->Conn->escape($sName);
			stringOps::noHtml($sName);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbcontentsection SET cse_Sortorder = $nSort,
			cse_Name = '$sName', cse_Active = $nActive WHERE cse_ID = $nCseID";
			$this->Conn->command($sSQL);
			// Wenn Contenttyp, aktivflag auf die con_ID übernehmen
			$sSQL = "SELECT con_ID,cse_Type
			FROM tbcontentsection WHERE cse_ID = $nCseID";
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				// Wenn Content
				if ($row['cse_Type'] == self::TYPE_CONTENT) {
					$sSQL = "UPDATE tbcontent
					SET con_Active = $nActive
					WHERE con_ID = ".$row['con_ID'];
					$this->Conn->command($sSQL);
				}
			}
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('contentsections saved');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/content/index.php?id='.page::menuID());
	}
	
	// Content Sektionen speichern
	public function saveNews() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nConID = getInt($_POST['id'][$i]);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			$sName = $_POST['name'][$i];
			// Escapen des Namen
			$this->Conn->escape($sName);
			stringOps::noHtml($sName);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbcontent SET con_Title = '$sName',
			con_Active = '$nActive' WHERE con_ID = $nConID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('newslist saved');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/news/index.php?id='.page::menuID());
	}
	
	// Eine neues ContentSection Element erstellen
	public function addSection () {
		// Was für Content ist gesucht?
		$sContentType = $_GET['add'];
		// Switchen, was geschehen soll
		switch ($sContentType) {
			case 'content':
				// Normalen HTML Inhalt erstellen
				$this->addContent($this->Res->html(147,page::language()));
				break;
			case 'media':
				// Element erzeugen
				$this->addMedia($this->Res->html(148,page::language()));
				break;
			case 'form':
				// Formular erstellen
				$this->addForm($this->Res->html(149,page::language()));
				break;
		}
		// Erfolg ausgeben und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/content/index.php?id='.page::menuID());
	}
	
	// Neue ContentSection mit HTML Inhalt
	public function addContent ($sDesc) {
		// In welchem Menu sind wir?
		$nMenuID = page::menuID();
		$nConID = ownerID::get($this->Conn);
		$nCseID = ownerID::get($this->Conn);
		// Content erstellen, gleich aktiv, da cse inaktiv ist
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Hits,con_Views,con_Active,con_ShowName,
		con_ShowDate,con_ShowModified,con_Title,con_Content) VALUES
		($nConID,$nMenuID,".$_SESSION['userid'].",0,0,0,0,5,0,'','')";
		// Datensatz erstellen und neue ID zurückgeben
		$this->Conn->command($sSQL);
		// Nächsten Sortorder für Contentsection holen
		$sSQL = "SELECT MAX(cse_Sortorder) FROM tbcontentsection WHERE mnu_ID = $nMenuID";
		$nNextOrder = $this->Conn->getFirstResult($sSQL);
		$nNextOrder = getInt($nNextOrder)+1;
		// ContentSection erstellen
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,cse_Type,cse_Name,cse_Active)
		VALUES ($nCseID,$nConID,$nMenuID,$nNextOrder,1,'< $sDesc >',0)";
		$this->Conn->command($sSQL);
	}
	
	// Neue ContentSection mit HTML Inhalt ohne Section
	public function addContentOnly () {
		// In welchem Menu sind wir?
		$nMenuID = getInt($_GET['id']);
		// Aktuelles Datum erstellen
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		// Content erstellen, gleich aktiv, da cse inaktiv ist
		$nNewID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Hits,con_Views,con_Active,con_ShowName,
		con_ShowDate,con_ShowModified,con_Title,con_Content,con_Date) VALUES
		($nNewID,$nMenuID,".$_SESSION['userid'].",0,0,0,0,1,0,'','','$sDate')";
		// Datensatz erstellen und neue ID zurückgeben
		$this->Conn->command($sSQL);
		// Erfolg und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		$this->resetPaging();
		session_write_close();
		redirect('location: /admin/news/index.php?id='.page::menuID());
	}
	
	// Neue ContentSection mit Element
	public function addMedia ($sDesc) {
		// In welchem Menu sind wir?
		$nMenuID = getInt($_GET['id']);
		$nCseID = ownerID::get($this->Conn);
		// Element erstellen, als unbekannte Datei
		$sSQL = "INSERT INTO tbelement (owner_ID,ele_Size,ele_Links,ele_Type,
		ele_Library,ele_Thumb,ele_Target,ele_File,ele_Desc,ele_Longdesc) VALUES
		($nCseID,0,1,6,0,0,'','','','')";
		// Datensatz erstellen und neue ID zurückgeben
		$nElementID = $this->Conn->insert($sSQL);
		// Nächsten Sortorder für Contentsection holen
		$sSQL = "SELECT MAX(cse_Sortorder) FROM tbcontentsection WHERE mnu_ID = $nMenuID";
		$nNextOrder = $this->Conn->getFirstResult($sSQL);
		$nNextOrder = getInt($nNextOrder)+1;
		// ContentSection erstellen
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,cse_Type,cse_Name,cse_Active)
		VALUES ($nCseID,0,$nMenuID,$nNextOrder,2,'< $sDesc >',0)";
		$this->Conn->command($sSQL);
	}
	
	// Neues Formular
	public function addForm ($sDesc) {
		// In welchem Menu sind wir?
		$nMenuID = getInt($_GET['id']);
		$nCseID = ownerID::get($this->Conn);
		// Nächsten Sortorder für Contentsection holen
		$sSQL = "SELECT MAX(cse_Sortorder) FROM tbcontentsection WHERE mnu_ID = $nMenuID";
		$nNextOrder = $this->Conn->getFirstResult($sSQL);
		$nNextOrder = getInt($nNextOrder)+1;
		// ContentSection erstellen, kein Owner
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,cse_Type,cse_Name,cse_Active)
		VALUES ($nCseID,0,$nMenuID,$nNextOrder,3,'< $sDesc >',0)";
		$this->Conn->command($sSQL);
	}
	
	// Eine Contentsection löschen
	public function deleteSection ($nContentID = 0,$nMenuID = 0,$redirect = true) {
		if ($nContentID == 0) {
			$nContentID = getInt($_GET['delete']);
		}
		if ($nMenuID == 0) {
			$nMenuID = page::menuID();
		}
		$bError = false; // Grundsätzlich kein Fehler
		// Contenttyp holen, NULL wenn menu manipuliert
		$sSQL = "SELECT cse_Type FROM tbcontentsection WHERE
		cse_ID = $nContentID AND mnu_ID = ".$nMenuID;
		$nType = $this->Conn->getFirstResult($sSQL);
		// Schauen was allenfalls gelöscht werden muss
		$sSQL = "SELECT con_ID FROM tbcontentsection WHERE 
		cse_ID = $nContentID";
		$nDeleteID = $this->Conn->getFirstResult($sSQL);
		// Switchen, was geschehen soll
		switch ($nType) {
			case self::TYPE_CONTENT:
				// Löschen der Inhalte
				logging::debug('content deleted');
				$sSQL = "DELETE FROM tbcontent WHERE con_ID = $nDeleteID";
				$this->Conn->command($sSQL);
				break;
			case self::TYPE_MEDIA:
				// Löschen des Elements
				logging::debug('element deleted');
				$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = $nContentID";
				$nDeleteID = $this->Conn->getFirstResult($sSQL);
				$sSQL = "DELETE FROM tbelement WHERE ele_ID = $nDeleteID";
				$this->Conn->command($sSQL);
				break;
			case self::TYPE_FORM:
				// Löschen der Formularfelder, die direkt von cse_ID abhängig sidn
				logging::debug('form deleted');
				$sSQL = "DELETE FROM tbformfield WHERE cse_ID = $nContentID";
				$this->Conn->command($sSQL);				
				break;
			default:
				// Alles andere generiert einen Fehler
				$bError = true; break;
		}
		// Erfolg ausgeben wenn kein Error
		if ($bError == false) {
			// Vorher noch die Contentsection selbst löschen
			logging::debug('contentsection deleted');
			$sSQL = "DELETE FROM tbcontentsection WHERE cse_ID = $nContentID";
			$this->Conn->command($sSQL);
			// Erfolg melden
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
		} else {
			// Fehler melden: "Fehler beim löschen!"
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
		}
		// Weiterleiten nur wenn erwünscht
		if ($redirect) {
			redirect('location: /admin/content/index.php?id='.$nMenuID);
		}
	}
	
	// Einen Contenteintrag löschen
	public function deleteContent($nContentID = 0,$nMenuID = 0,$redirect = true) {
		if ($nContentID == 0) {
			$nContentID = getInt($_GET['delete']);
		}
		if ($nMenuID == 0) {
			$nMenuID = page::menuID();
		}
		$sSQL = "DELETE FROM tbcontent 
		WHERE con_ID = $nContentID AND mnu_ID = ".$nMenuID;
		$this->Conn->command($sSQL);
		// Erfolg melden
		if ($redirect) {
			logging::debug('content deleted');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/news/index.php?id='.$nMenuID);
		}
	}
	
	// Checken, ob Zugriff gewährt wird auf Content
	public function checkContentAccess() {
		$nMenuID = getInt($_GET['id']);
		$nContentID = getInt($_GET['content']);
		// Zählen ob diese Kombination existiert
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE mnu_ID = $nMenuID AND con_ID = $nContentID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn Ergebniss nicht 1, auf Startseite gehen
		if ($nResult != 1) {
			logging::error('content access denied');
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		}
	}
	
	// Checken, ob Zugriff gewährt wird auf Content
	public function checkNewsAccess() {
		$nMenuID = getInt($_GET['id']);
		$nContentID = getInt($_GET['content']);
		// Zählen ob diese Kombination existiert
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE mnu_ID = $nMenuID AND con_ID = $nContentID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn Ergebniss nicht 1, auf Startseite gehen
		if ($nResult != 1) {
			logging::error('news access denied');
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/news/index.php?id='.page::menuID());
		}
	}
	
	// Contentsektionen laden
	public function loadContentSections() {
		// Array für die Daten erstellen
		$sData = array();
		// In welchem Menu sind wir?
		$nMenuID = getInt($_GET['id']);
		// Daten lesen
		$sSQL = "SELECT cse_ID,con_ID,cse_Sortorder,cse_Type,cse_Name,cse_Active
		FROM tbcontentsection WHERE mnu_ID = $nMenuID ORDER BY cse_Sortorder";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Typ abfragen und Link / Image setzen
			switch ($row['cse_Type']) {
				case self::TYPE_CONTENT:
					$row['link'] = 'content.php';
					$row['desc'] = $this->Res->html(150,page::language());
					$row['image'] = 'page.png';
					break;
				case self::TYPE_MEDIA:
					$row['link'] = 'media.php';
					$row['desc'] = $this->Res->html(151,page::language());
					$row['image'] = 'image.png';
					break;
				case self::TYPE_FORM:
					$row['link'] = 'form.php';
					$row['desc'] = $this->Res->html(152,page::language());
					$row['image'] = 'table.png';
					break;
			}
			// Daten speichern
			array_push($sData,$row);
		}
		// Daten zurückgeben
		return($sData);
	}
	
	// Übersicht der News laden
	public function loadNewsOverview(&$sData, &$NewsConfig) {
		// News für diese menu ID laden
		$sSQL = "SELECT con_ID,con_Date,con_Title,con_Active FROM
		tbcontent WHERE mnu_ID = ".page::menuID()." ORDER BY con_Date DESC";
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		$PagingEngine->start($sSQL,10);
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Datum in Europa kurzdatum umwandeln
			$row['con_Date'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$row['con_Date']);
			// Titel wenn leer füllen mit Text
			if (strlen($row['con_Title']) == 0) {
				$row['con_Title'] = '< '.$this->Res->html(364,page::language()).' >';
			}
			array_push($sData,$row);
		}
		// Paging Engine HTML zurückgeben
		return($PagingEngine->getHtml());
	}
	
	// News Configuration erstellen
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,6)) {
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'shortnews',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'showName',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,10,pageConfig::TYPE_NUMERIC,'postsPerPage',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'hasRss',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'socialBookmarking',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// News Konfiguration speichern
	public function saveNewsConfig(&$Config) {
		$nMenuID = page::menuID();
		// Parameter anpassen
		$Config['shortnews']['Value'] 			= stringOps::getBoolInt($_POST['shortnews']);
		$Config['showName']['Value'] 			= stringOps::getBoolInt($_POST['showName']);
		$Config['hasRss']['Value']				= stringOps::getBoolInt($_POST['rss']);
		$Config['socialBookmarking']['Value'] 	= stringOps::getBoolInt($_POST['socialBookmarking']);
		$Config['postsPerPage']['Value'] 		= stringOps::getPosInt($_POST['postsPerPage']);
		// HTML Code
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Paging zurücksetzen, da die Einstellung evtl. änderte
		$this->resetPaging();
		// Erfolg speichern und weiterleiten
		logging::debug('news config saved');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/news/config.php?id='.page::menuID());
	}
	
	// Gibt HTML Code für hasRss Konfiguration zurück
	public function getRssConfig(&$Config) {
		$hasRss = getInt($Config['hasRss']['Value']);
		// Options selbst ausgeben
		$out = '
		<tr>
			<td width="180">'.$this->Res->html(754,page::language()).':</td>
			<td>
				<div style="width:100px;float:left;">
					<input type="radio" value="1" name="rss"'.checkCheckbox(1,$hasRss).'> '.$this->Res->html(231,page::language()).'
				</div>
				<div style="width:100px;float:left;">
					<input type="radio" value="0" name="rss"'.checkCheckbox(0,$hasRss).'> '.$this->Res->html(230,page::language()).'
				</div>
			</td>
		</tr>
		';
		// Zusätzliche Zeile für RSS Link, wenn Ja gewählt
		if ($hasRss == 1) {
			$sProtocol = 'http';
			if ($_SERVER['HTTPS'] == 'on') $sProtocol = 'https';
			$Link = $sProtocol.'://'.page::domain().'/modules/rss/news.php?id='.page::menuID();
			$out.= '
			<tr>
				<td width="180">'.$this->Res->html(755,page::language()).':</td>
				<td>
					<a href="'.$Link.'" target="_blank">'.$Link.'</a>
				</td>
			</tr>
			';
		}
		return($out);
	}
	
	// Admin Titel für Contentverwaltung holen
	public function getContentAdminTitle($Type) {
		$sTitle = '';
		// Je nach Seitentyp
		switch ($Type) {
			case typeID::MENU_CENTRALCONTENT:
				$sTitle = $this->Res->html(808,page::language());
				break;
			case typeID::MENU_CONTENT:
			default:
				$sTitle = $this->Res->html(155,page::language());
				break;
		}
		return($sTitle);
	}
	
	// Einen Inhalt mit neuer Section kopieren
	private function pasteContent(&$Data) {
		// Schauen ob es den Content noch gibt
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent WHERE con_ID = ".$Data['con_ID'];
		$nResult = getInt($this->Conn->getCountResult($sSQL));
		// Wenn nicht vorhanden, Success false zurückgeben
		if ($nResult !== 1) return(false);
		// Kopieren des Content Records
		$sSQL = "SELECT usr_ID,con_Date,con_Modified,con_DateFrom,con_DateTo,
		con_ShowName, con_ShowDate, con_ShowModified, con_Title, con_Content
		FROM tbcontent WHERE con_ID = ".$Data['con_ID'];
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Con = $row;
			$Con['con_Modified'] = $this->validateNull($Con['con_Modified']);
			$Con['con_DateFrom'] = $this->validateNull($Con['con_DateFrom']);
			$Con['con_DateTo'] = $this->validateNull($Con['con_DateTo']);
		}
		// Abbrechen, wenn keine Kopierdaten vorhanden
		if (!is_array($Con)) return(false);
		// Neuer Name von Content und Section holen
		$sName = stringOps::getPostEscaped('CopiedContentName',$this->Conn);
		// HTML Entitäten Kodieren und HTML Tags entfernen
		stringOps::htmlEntRev($sName); stringOps::noHtml($sName);
		if (strlen($sName) == 0) $sName = '< '.$this->Res->html(425,page::language()).' >';
		// Daten in neuen Record einfügen
		$nConID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Hits,con_Views,
		con_Date,con_Modified,con_DateFrom,con_DateTo,con_Active,con_ShowName,
		con_ShowDate,con_ShowModified,con_Title,con_Content) VALUES
		($nConID,".page::menuID().",".$Con['usr_ID'].",0,0,'".$Con['con_Date']."',
		".$Con['con_Modified'].",".$Con['con_DateFrom'].",".$Con['con_DateTo'].",
		0,".$Con['con_ShowName'].",".$Con['con_ShowDate'].",
		".$Con['con_ShowModified'].",'".$sName."',
		'".addSlashes($Con['con_Content'])."')";
		$this->Conn->command($sSQL);
		// Gibt es im Content ein Element?
		$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".$Data['con_ID'];
		$nEleID = getInt($this->Conn->getFirstResult($sSQL));
		// Wenn vorhanden, das Element kopieren mit neuem Owner
		$this->copyElementData($nEleID,$nConID);
		// Section auch kopieren, wenn vorhanden
		if (isset($Data['cse_Type'])) {
			$nCseID = ownerID::get($this->Conn);
			$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,
			cse_Type,cse_Active,cse_Name) VALUES 
			($nCseID, $nConID, ".page::menuID().",0,".$Data['cse_Type'].",0,'$sName')";
			$this->Conn->command($sSQL);
		}
		// Rückgabe ob Erfolg oder nicht
		logging::debug('content pasted');
		return(true);
	}
	
	// Ein Formular mit neuer Section kopieren
	private function pasteForm(&$Data) {
		// Neue Section für das formular erstellen
		$nCseID = ownerID::get($this->Conn);
		// Neue Section erstellen
		$sName = stringOps::getPostEscaped('CopiedContentName',$this->Conn);
		// HTML Entitäten Kodieren und HTML Tags entfernen
		stringOps::htmlEntRev($sName); stringOps::noHtml($sName);
		if (strlen($sName) == 0) $sName = '< '.$this->Res->html(425,page::language()).' >';
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,
		cse_Type,cse_Active,cse_Name) VALUES 
		($nCseID, 0, ".page::menuID().",0,".$Data['cse_Type'].",0,'$sName')";
		$this->Conn->command($sSQL);
		// Zu Kopierende Formfelder lesen
		$sSQL = "SELECT ffi_Width,ffi_Required,ffi_Sortorder,ffi_Name,ffi_Desc,
		ffi_Type, ffi_Class, ffi_Email, ffi_Options FROM tbformfield
		WHERE cse_ID = ".$Data['cse_ID'];
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// NULL Felder validieren
			$row['ffi_Options'] = $this->validateNull($row['ffi_Options']);
			$row['ffi_Class'] = $this->validateNull($row['ffi_Class']);
			// Daten neu einfügen
			$sSQL = "INSERT INTO tbformfield (cse_ID, mnu_ID, ffi_Width,
			ffi_Required, ffi_Sortorder, ffi_Name, ffi_Desc, ffi_Type, 
			ffi_Class, ffi_Value, ffi_Email, ffi_Options) VALUES
			($nCseID,".page::menuID().",".$row['ffi_Width'].",
			".$row['ffi_Required'].",".$row['ffi_Sortorder'].",
			'".$row['ffi_Name']."','".$row['ffi_Desc']."','".$row['ffi_Type']."',
			".$row['ffi_Class'].",'".$row['ffi_Value']."','".$row['ffi_Email']."',
			".$row['ffi_Options'].")";
			$this->Conn->command($sSQL);
		}
		// Rückgabe ob Erfolg oder nicht
		logging::debug('form pasted');
		return(true);
	}
	
	// Ein Dateielement mit neuer Section kopieren
	private function pasteElement(&$Data) {
		// Herausfinden der Element ID
		$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".$Data['cse_ID'];
		$nEleID = getInt($this->Conn->getFirstResult($sSQL));
		$nCseID = ownerID::get($this->Conn);
		// Element kopieren wenn vorhanden
		$this->copyElementData($nEleID,$nCseID);
		// Neue Section erstellen
		$sName = stringOps::getPostEscaped('CopiedContentName',$this->Conn);
		// HTML Entitäten Kodieren und HTML Tags entfernen
		stringOps::htmlEntRev($sName); stringOps::noHtml($sName);
		if (strlen($sName) == 0) $sName = '< '.$this->Res->html(425,page::language()).' >';
		$sSQL = "INSERT INTO tbcontentsection (cse_ID,con_ID,mnu_ID,cse_Sortorder,
		cse_Type,cse_Active,cse_Name) VALUES 
		($nCseID, 0, ".page::menuID().",0,".$Data['cse_Type'].",0,'$sName')";
		$this->Conn->command($sSQL);
		// Rückgabe ob Erfolg oder nicht
		logging::debug('element pasted');
		return(true);
	}
	
	// Ein Element mit neuem Owner kopieren
	// Auch das Filesystem wird entsprechend kopiert
	private function copyElementData($nEleID,$nNewOwner) {
		$sSQL = "SELECT COUNT(ele_ID) FROM tbelement WHERE ele_ID = $nEleID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Kopieren wenn Element ID grösser als 0 und Element vorhanden
		if ($nEleID > 0 && $nResult == 1) {
			// Neues Element kopieren
			$sSQL = "SELECT ele_Size, ele_Links, ele_Width, ele_Height, ele_Type, 
			ele_Library, ele_Thumb, ele_Date, ele_Creationdate, ele_Align, ele_Skin,
			ele_Target, ele_File, ele_Desc, ele_Longdesc FROM tbelement
			WHERE ele_ID = $nEleID";
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				$Ele = $row;
				// NULL Daten validieren
				$Ele['ele_Date'] = $this->validateNull($Ele['ele_Date']);
				$Ele['ele_Creationdate'] = $this->validateNull($Ele['ele_Creationdate']);
				$Ele['ele_Align'] = $this->validateNull($Ele['ele_Align']);
				$Ele['ele_Skin'] = $this->validateNull($Ele['ele_Skin']);
				$Ele['ele_Longdesc'] = $this->validateNull($Ele['ele_Longdesc']);
			}
			// Neuen Datensatz erstellen, wenn Daten vorhanden
			if (!is_array($Ele)) return(false);
			$sSQL = "INSERT INTO tbelement (owner_ID,ele_Size,ele_Links, ele_Width, 
			ele_Height, ele_Type, ele_Library, ele_Thumb, ele_Date, ele_Creationdate, 
			ele_Align, ele_Skin, ele_Target, ele_File, ele_Desc, ele_Longdesc)
			VALUES ($nNewOwner,".$Ele['ele_Size'].",".$Ele['ele_Links'].",
			".$Ele['ele_Width'].",".$Ele['ele_Height'].",".$Ele['ele_Type'].",
			".$Ele['ele_Library'].",".$Ele['ele_Thumb'].",".$Ele['ele_Date'].",
			".$Ele['ele_Creationdate'].",".$Ele['ele_Align'].",".$Ele['ele_Skin'].",
			'".$Ele['ele_Target']."','".$Ele['ele_File']."','".$Ele['ele_Desc']."',
			".$Ele['ele_Longdesc'].")";
			$nNewID = $this->Conn->insert($sSQL);
			// Einfügen der Medienkonstante  wegen Pfad
			require_once(BP.'/library/class/mediaManager/mediaConst.php');
			$sOldPath = BP.mediaConst::FILEPATH;
			$sNewPath = BP.mediaConst::FILEPATH;
			// Neuer und alter Pfad erstellen
			$sOldPath = str_replace('{PAGE_ID}',page::ID(),$sOldPath);
			$sOldPath = str_replace('{ELE_ID}',$nEleID,$sOldPath);
			$sNewPath = str_replace('{PAGE_ID}',page::ID(),$sNewPath);
			$sNewPath = str_replace('{ELE_ID}',$nNewID,$sNewPath);
			// Neuen Ordner erstellen wenn nicht vorhanden
			if (!file_exists($sNewPath)) mkdir($sNewPath, 0755, true);
			// Daten kopieren, wenn Quellordner existiert
			if (file_exists($sOldPath)) {
				$aFiles = array();
				// Folder durchgehen
				if ($resDir = opendir($sOldPath)) {
			        while (($sFile = readdir($resDir)) !== false) {
			        	if (filetype($sOldPath . $sFile) == 'file') {
				        	array_push($aFiles,$sFile);
			        	}
			        }
			    }
			    // Ordner schliessen
			    closedir($resDir);
			    // Files von alt nach neu kopieren
			    foreach ($aFiles as $FileName) {
			    	copy(
						$sOldPath.$FileName,
						$sNewPath.$FileName			    	
			    	);
			    }
			}
		}
		logging::debug('element data copied');
		return(true);
	}
	
	// HTML für das Paste Window holen
	private function getPasteWindowHtml($nCopyID) {
		// Name der Section holen
		$sSQL = 'SELECT cse_Name FROM tbcontentsection WHERE cse_ID = '.$nCopyID;
		$sName = $this->Conn->getFirstResult($sSQL);
		// Wenn nichts vorhanden in Content suchen
		if (strlen($sName) == 0) {
			$sSQL = 'SELECT con_Title FROM tbcontent WHERE con_ID = '.$nCopyID;
			$sName = $this->Conn->getFirstResult($sSQL);
		}
		$sNameView = $sName;
		stringOps::htmlViewEnt($sNameView);
		// HTML Generieren
		$out .= '
		<form action="index.php?id='.page::menuID().'&paste" method="post">
			<table width="390" border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td colspan="2">
					 '.$this->Res->html(742,page::language()).' \''.$sNameView.'\' 
					 '.$this->Res->html(743,page::language()).'
					</td>
				</tr>
				<tr>
					<td width="100">
						'.$this->Res->html(741,page::language()).':
					</td>
					<td>
						<input type="text" style="width:275px;" maxlength="255" value="'.$sName.'" name="CopiedContentName">
					</td>
				</tr>
				<tr>
					<td>
						&nbsp;
					</td>
					<td>
						<input class="cButton" type="submit" name="btnSubmit" value="'.$this->Res->html(744,page::language()).'">
						<input class="cButton" type="button" name="btnCancel" value="'.$this->Res->html(234,page::language()).'" onClick="evtCloseWindow();">
					</td>
				</tr>
			</table>
		</form>
		';
		return($out);
	}
	
	// NULL Felder validieren für In-/Output
	private function validateNull($Value) {
		if ($Value == NULL) { 
			$Value = 'NULL'; 
		} else { 
			$Value = "'".$Value."'"; 
		}
		return($Value);
	}
	
	// Flags validieren
	private function validateFlags(&$nShowDate,&$nShowModified,&$nShowName) {
		$nDate = getInt($_POST['showdate']);
		$nName = getInt($_POST['showname']);
		// Titelanzeige validieren
		if ($nName == 1) $nShowName = 1;
		// Showdate validieren
		if ($nDate == 1) $nShowDate = 1;
		if ($nDate == 2) $nShowModified = 1;
	}
	
	// Datum validieren
	private function validateDateTime(&$Errors,&$sDate) {
		if (!stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATETIME)) {
			array_push($Errors,$this->Res->html(182,page::language()));
		}
	}
	
	// Datum validieren
	private function validateDate(&$Errors,&$sDate) {
		if (!stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATETIME) && strlen($sDate) == 19) {
			if (!in_array($this->Res->html(363,page::language()),$Errors)) {
				logging::error('date format error');
				array_push($Errors,$this->Res->html(363,page::language()));
			}
		}
		// Das Datum gleich Formatieren wenn es ok ist
		if (stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATETIME)) {
			$sDate = dateOps::convertDate(dateOps::EU_DATETIME,dateOps::SQL_DATETIME,$sDate);
			$sDate = "'".$sDate."'";
		} else {
			$sDate = 'NULL';
		}
	}
}