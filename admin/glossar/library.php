<?php
// Glossar Library
class moduleGlossary extends commonModule {
	
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
	
	// Alle Elemente in der übersicht speichern
	public function saveItems() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['conID']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nConID = getInt($_POST['conID'][$i]);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			// Titel des Content holen
			$sTitle = $_POST['conTitle'][$i];
			$this->Conn->escape($sTitle);
			stringOps::noHtml($sTitle);
			// Validieren des Eingabedatums
			$sDate = $this->validateDate($_POST['conDate'][$i]);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbcontent SET
			con_Date = '$sDate', con_Title = '$sTitle',
			con_Active = $nActive WHERE con_ID = $nConID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved glossary entries');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/glossar/index.php?id='.page::menuID().'&page='.getInt($_GET['page'])); 
	}
	
	// Einzelnes Element speichern
	public function saveItem($nConID) {;
		// Titel des Content holen
		$sTitle = $_POST['conTitle'];
		$this->Conn->escape($sTitle);
		stringOps::noHtml($sTitle);
		// Content selbst holen
		$sContent = $_POST['conContent'];
		$this->Conn->escape($sContent);
		stringOps::htmlEntRev($sContent);
		// Datumsanzeige
		$nShowDate = getInt($_POST['conShowDate']);
		if ($nShowDate < 0) $nShowDate = 0;
		if ($nShowDate > 1) $nShowDate = 1;
		// Validieren des Eingabedatums
		$sDate = $this->validateDate($_POST['conDate']);
		// SQL erstellen und abfeuern
		$sSQL = "UPDATE tbcontent SET
		con_Date = '$sDate', con_Content = '$sContent',
		con_Title = '$sTitle', con_ShowDate = $nShowDate
		WHERE con_ID = $nConID";
		$this->Conn->command($sSQL);
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved glossary entry');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/glossar/edit.php?id='.page::menuID().'&item='.$nConID.'&page='.getInt($_GET['page'])); 
	}
	
	// Ein Element löschen
	public function deleteItem() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE con_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbcontent WHERE con_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted glossary entry');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/glossar/index.php?id='.page::menuID().'&page='.getInt($_GET['page'])); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting glossary entry');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/glossar/index.php?id='.page::menuID().'&page='.getInt($_GET['page'])); 
		}
	}
	
	// Neues Element hinzufügen
	public function addItem() {
		// Neuen Content Eintrag erstellen
		$nContentID = ownerID::get($this->Conn);
		$nDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,con_Title,con_Date,con_Active)
		VALUES ($nContentID,".page::menuID().",'','$nDate',0)";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		$this->resetPaging();
		session_write_close();
		redirect('location: /admin/glossar/index.php?id='.page::menuID().'&page='.getInt($_GET['page'])); 
	}
	
	// Mit Paging Glossar Einträge holen
	public function loadEntries(&$GlossarEntries) {
		$sSQL = "SELECT con_ID,con_Title,con_Date,con_Active FROM tbcontent 
		WHERE mnu_ID = ".page::menuID()." 
		ORDER BY con_Active ASC, con_Title ASC";
		$Paging = new paging($this->Conn,'/admin/glossar/index.php?id='.page::menuID());
		$Paging->start($sSQL,20);
		$sSQL = $Paging->getSQL();
		// SQL Verarbeiten
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Leeren Titel füllen
			if (strlen($row['con_Title']) == 0) {
				$row['con_Title'] = '< '.$this->Res->html(425,page::language()).' >';
			}
			// SQL Datum umwandeln
			$row['con_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$row['con_Date']
			);			
			array_push($GlossarEntries,$row);
		}
		// Paging HTML zurückgeben
		return($Paging->getHtml());
	}
	
	// Konfiguration laden/erstellen
	public function initConfig(&$Conn,&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$Conn,2)) {
			pageConfig::setConfig($nMenuID,$Conn,1,pageConfig::TYPE_NUMERIC,'viewType',$Config);
			pageConfig::setConfig($nMenuID,$Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$nViewType = getInt($_POST['viewType']);
		if ($nViewType < 1 || $nViewType > 2) $nViewType = 1;
		$Config['viewType']['Value'] = $nViewType;
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved glossary config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/glossar/config.php?id='.page::menuID().'&page='.getInt($_GET['page'])); 
	}
	
	// Glossareintragsdaten laden
	public function loadItem($nConID,&$Data) {
		$sSQL = "SELECT con_Title,con_Date,con_ShowDate,con_Content
		FROM tbcontent WHERE con_ID = $nConID";
		$nRes = $this->Conn->execute($sSQL);
		// Daten speichern
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
		}
		// Leeren Titel füllen
		if (strlen($Data['con_Title']) == 0) {
			$Data['con_Title'] = '< '.$this->Res->html(425,page::language()).' >';
		}
		// Datum verarbeiten
		$Data['con_Date'] = dateOps::convertDate(
			dateOps::SQL_DATETIME,
			dateOps::EU_DATE,
			$Data['con_Date']
		);
	}
	
	// Content ID validieren
	public function validateEntry($nConID) {
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE con_ID = $nConID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		$bReturn = false;
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Datum validieren, gibt heutiges Datum zurück, wenn
	// dieses nicht korrekt eingegeben wurde
	private function validateDate($sDate) {
		// Dem Datum eine leere Zeit anhängen
		$sDate .= ' 00:00:00';
		if (stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATETIME)) {
			$sDate = dateOps::convertDate(dateOps::EU_DATETIME,dateOps::SQL_DATETIME,$sDate);
		} else {
			$sDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		}
		return($sDate);
	}
}