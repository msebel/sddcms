<?php
class moduleUpdates extends commonModule {
	
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
	
	// Linkdaten (übersicht) laden
	public function loadData(&$Data) {
		$sSQL = "SELECT lnk_ID,lnk_Active,lnk_Name,lnk_Date
		FROM tblink WHERE mnu_ID = ".page::menuID()." ORDER BY lnk_Date DESC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$row['lnk_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$row['lnk_Date']
			);
			array_push($Data,$row);
		}
	}
	
	// Daten eines einzelnen Link laden
	public function loadUpdate($nLnkID,&$Data) {
		$sSQL = "SELECT lnk_Clicks,lnk_Active,lnk_Name,lnk_Target,lnk_URL,lnk_Desc,lnk_Date 
		FROM tblink WHERe mnu_ID = ".page::menuID()." AND lnk_ID = $nLnkID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
			// Daten verarbeiten
			$Data['lnk_Target'] = $this->targetToNumber($Data['lnk_Target']);
			$Data['lnk_URL'] = getInt(str_replace('/controller.php?id=','',$Data['lnk_URL']));
			$Data['lnk_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$Data['lnk_Date']
			);
		}
	}
	
	// Zugriff auf Link checken
	public function checkAccess($nLinkID) {
		$sSQL = "SELECT COUNT(lnk_ID) FROM tblink
		WHERE lnk_ID = $nLinkID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		$bReturn = false;
		if ($nReturn != 1) {
			logging::debug('updates access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Link neu erstellen
	public function addUpdate() {
		$nSort = $this->getNewSort();
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		$sName = '< '.$this->Res->normal(541,page::language()).' >';
		// Neues Update, Standardmässig in selbem Fenster öffnen
		$sSQL = "INSERT INTO tblink (mnu_ID,lnk_Clicks,lnk_Sortorder,lnk_Active,
		lnk_Date,lnk_Name,lnk_Target,lnk_URL,lnk_Desc) VALUES 
		(".page::menuID().",0,$nSort,0,'$sDate','$sName','_self','','')";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language())); // 'Eingaben gespeichert'
		session_write_close();
		redirect('location: /admin/updates/index.php?id='.page::menuID());
	}
	
	// Link speichern
	public function saveUpdate() {
		$nLink = getInt($_GET['link']);
		$sName = $_POST['lnkName'];
		$sDesc = $_POST['lnkDesc'];
		$sDate = $this->validateDate($_POST['lnkDate']);
		// Adresse validieren
		$sURL = '/controller.php?id='.getInt($_POST['lnkURL']);
		// Daten HTML entfernen und escapen
		stringOps::noHtml($sName);	$this->Conn->escape($sName);
		stringOps::noHtml($sDesc);	$this->Conn->escape($sDesc);
		stringOps::noHtml($sURL);	$this->Conn->escape($sURL);
		// Zielseite definieren
		$sTarget = $this->numberToTarget($_POST['lnkTarget']);
		// Aktivität prüfen
		$nActive = getInt($_POST['lnkActive']);
		if ($nActive != 1) $nActive = 0;
		// Daten speichern
		$sSQL = "UPDATE tblink SET
		lnk_Name = '$sName', lnk_URL = '$sURL', lnk_Target = '$sTarget',
		lnk_Desc = '$sDesc', lnk_Active = $nActive, lnk_Date = '$sDate'
		WHERE lnk_ID = $nLink AND mnu_ID = ".page::menuID();
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		logging::debug('saved update');
		$this->setErrorSession($this->Res->html(57,page::language())); // 'Eingaben gespeichert'
		session_write_close();
		redirect('location: /admin/updates/edit.php?id='.page::menuID().'&link='.$nLink);
	}
	
	// Links speichern (übersicht)
	public function saveUpdates() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nLnkID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			$sName = $_POST['name'][$i];
			$sDate = $this->validateDate($_POST['date'][$i]);
			// Escapen des Namen
			$this->Conn->escape($sName);
			stringOps::noHtml($sName);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tblink SET lnk_Sortorder = $nSort, lnk_Date = '$sDate',
			lnk_Name = '$sName', lnk_Active = $nActive WHERE lnk_ID = $nLnkID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved all updates');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/updates/index.php?id='.page::menuID());
	}
	
	// Link löschen
	public function deleteUpdate() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(lnk_ID) FROM tblink
		WHERE lnk_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tblink WHERE lnk_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted update');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/updates/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting update');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/updates/index.php?id='.page::menuID()); 
		}
	}
	
	// Konfiguration initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,2)) {
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'futureUpdates',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		$Config['futureUpdates']['Value'] = getInt($_POST['futureUpdates']);
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved update config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/updates/config.php?id='.page::menuID()); 
	}
	
	// Nächsten sortorder holen
	private function getNewSort() {
		$sSQL = "SELECT IFNULL(MAX(lnk_Sortorder),0) 
		FROM tblink WHERE mnu_ID = ".page::menuID();
		$nResult = $this->Conn->getFirstResult($sSQL);
		return(++$nResult);
	}
	
	// Target String zu einer Zahl machen
	private function targetToNumber($sTarget) {
		switch ($sTarget) {
			case '_blank':
				$nNumber = 1;
				break;
			case '_self':
				$nNumber = 2;
				break;
			default:
				$nNumber = 0;
				break;
		}
		return($nNumber);
	}
	
	// Zahl zu einem Target String machen
	private function numberToTarget($nNumber) {
		switch ($nNumber) {
			case 1:
				$sTarget = '_blank';
				break;
			case 2:
				$sTarget = '_self';
				break;
			default:
				$sTarget = '';
				break;
		}
		return($sTarget);
	}
	
	// Eingegebenes Datum validieren
	private function validateDate($sDate) {
		$sReturn = dateOps::getTime(dateOps::SQL_DATETIME,time());
		if (stringOps::checkDate($sDate,dateOps::EU_FORMAT_DATE)) {
			$sReturn = dateOps::convertDate(
				dateOps::EU_DATE,
				dateOps::SQL_DATETIME,
				$sDate
			);
		}
		return($sReturn);
	}
}