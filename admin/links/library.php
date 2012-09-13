<?php
class moduleLink extends commonModule {
	
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
		$sSQL = "SELECT lnk_ID,lnc_ID,lnk_Active,lnk_Sortorder,lnk_Name
		FROM tblink WHERE mnu_ID = ".page::menuID()." ORDER BY lnk_Sortorder ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($Data,$row);
		}
	}
	
	// Kategorien laden
	public function loadCategories(&$Data) {
		$sSQL = "SELECT lnc_ID,lnc_Title,lnc_Order FROM tblinkcategory
		WHERE mnu_ID = ".page::menuID()." ORDER BY lnc_Order ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data[$row['lnc_ID']] = $row;
		}
	}
	
	// Daten eines einzelnen Link laden
	public function loadLink($nLnkID,&$Data) {
		$sSQL = "SELECT lnc_ID,lnk_Clicks,lnk_Active,lnk_Name,lnk_Target,
		lnk_URL,lnk_Desc FROM tblink WHERE  lnk_ID = $nLnkID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
			// Daten verarbeiten
			$Data['lnk_Target'] = $this->targetToNumber($Data['lnk_Target']);
			if (strlen($Data['lnk_URL']) == 0) $Data['lnk_URL'] = 'http://';
		}
	}
	
	// Kategorie eines Links zurückgeben
	public function getLinkCategory($link,$categories) {
		$sName = $categories[$link['lnc_ID']]['lnc_Title'];
		// Wenn nichts gefunden, "Keine Kategorie" angeben
		if (strlen($sName) == 0) {
			$sName = $this->Res->html(1157, page::language());
		}
		// Länge prüfen und kürzen
		$sName = stringOps::chopString($sName, 25, true);
		return($sName);
	}
	
	
	public function getCategoryDropdown($link,$categories) {
		$html = '';
		foreach ($categories as $id => $category) {
			$selected = checkDropDown($link['lnc_ID'], $category['lnc_ID']);
			$html .= '
			<option value="'.$id.'"'.$selected.'>'.$category['lnc_Title'].'</option>	
			';
		}
		return($html);
	}
	
	// Zugriff auf Link checken
	public function checkAccess($nLinkID) {
		$sSQL = "SELECT COUNT(lnk_ID) FROM tblink
		WHERE lnk_ID = $nLinkID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		$bReturn = false;
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Link neu erstellen
	public function addLink() {
		$nSort = $this->getNewSort();
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		$sName = '< '.$this->Res->normal(507,page::language()).' >';
		$sSQL = "INSERT INTO tblink (mnu_ID,lnk_Clicks,lnk_Sortorder,lnk_Active,
		lnk_Date,lnk_Name,lnk_Target,lnk_URL,lnk_Desc) VALUES 
		(".page::menuID().",0,$nSort,0,'$sDate','$sName','','','')";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language())); // 'Eingaben gespeichert'
		session_write_close();
		redirect('location: /admin/links/index.php?id='.page::menuID());
	}
	
	// Link speichern
	public function saveLink() {
		$nLink = getInt($_GET['link']);
		$sName = $_POST['lnkName'];
		$sDesc = $_POST['lnkDesc'];
		// Adresse validieren
		$sURL = '';
		if (stringOps::checkURL($_POST['lnkURL'])) {
			$sURL = $_POST['lnkURL'];
		}
		// Daten HTML entfernen und escapen
		stringOps::noHtml($sName);
		stringOps::noHtml($sDesc);
		stringOps::noHtml($sURL);
		// Zielseite definieren
		$sTarget = $this->numberToTarget($_POST['lnkTarget']);
		$nLncID = getInt($_POST['lncID']);
		// Aktivität prüfen
		$nActive = getInt($_POST['lnkActive']);
		if ($nActive != 1) $nActive = 0;
		// Daten speichern
		$sSQL = 'UPDATE tblink SET
		lnk_Name = :name, lnk_URL = :url, lnk_Target = :target,
		lnk_Desc = :desc, lnk_Active = :active, lnc_ID = :lncid
		WHERE lnk_ID = :lnkid AND mnu_ID = :menuid';
		$Stmt = $this->Conn->prepare($sSQL);
		$Stmt->bind('name', $sName, PDO::PARAM_STR);
		$Stmt->bind('url', $sURL, PDO::PARAM_STR);
		$Stmt->bind('target', $sTarget, PDO::PARAM_STR);
		$Stmt->bind('desc', $sDesc, PDO::PARAM_STR);
		$Stmt->bind('active', $nActive, PDO::PARAM_INT);
		$Stmt->bind('lncid', $nLncID, PDO::PARAM_INT);
		$Stmt->bind('lnkid', $nLink, PDO::PARAM_INT);
		$Stmt->bind('menuid', page::menuID(), PDO::PARAM_INT);
		$Stmt->command();
		// Erfolg melden und weiterleiten
		logging::debug('saved link');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/links/edit.php?id='.page::menuID().'&link='.$nLink);
	}
	
	// Links speichern (übersicht)
	public function saveLinks() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nLnkID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			$nActive = getInt($_POST['active_'.$i]);
			if ($nActive != 1) $nActive = 0;
			$sName = $_POST['name'][$i];
			// Escapen des Namen
			$this->Conn->escape($sName);
			stringOps::noHtml($sName);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tblink SET lnk_Sortorder = $nSort,
			lnk_Name = '$sName', lnk_Active = $nActive WHERE lnk_ID = $nLnkID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved all links');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/links/index.php?id='.page::menuID());
	}
	
	// Alle Kategorien speichern
	public function saveCategories() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nLncID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			$sName = $_POST['lncTitle'][$i];
			stringOps::noHtml($sName);
			// SQL erstellen und abfeuern
			$sSQL = 'UPDATE tblinkcategory SET
			lnc_Title = :title, lnc_Order = :sort
			WHERE lnc_ID = :lncid';
			$Stmt = $this->Conn->prepare($sSQL);
			$Stmt->bind('title', $sName, PDO::PARAM_STR);
			$Stmt->bind('sort',$nSort,PDO::PARAM_INT);
			$Stmt->bind('lncid',$nLncID,PDO::PARAM_INT);
			$Stmt->command();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved all link categories');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/links/categories.php?id='.page::menuID());
	}
	
	// Link löschen
	public function deleteLink() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(lnk_ID) FROM tblink
		WHERE lnk_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tblink WHERE lnk_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted link');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/links/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting link');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/links/index.php?id='.page::menuID()); 
		}
	}
	
	// Kategorie löschen
	public function deleteCategory() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(lnc_ID) FROM tblinkcategory
		WHERE lnc_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = 'DELETE FROM tblinkcategory WHERE lnc_ID = '.$nDeleteID;
			$this->Conn->command($sSQL);
			// Alle Links mit der Kategorie auf ohne Kategorie stellen
			$sSQL = 'UPDATE tblink SET lnc_ID = 0 WHERE lnc_ID = '.$nDeleteID;
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted link category');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/links/categories.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting link category');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/links/index.php?id='.page::menuID()); 
		}
	}
	
	// Konfiguration initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,2)) {
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'viewType',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$nViewType = getInt($_POST['viewType']);
		if ($nViewType < 1 || $nViewType > 3) $nViewType = 1;
		$Config['viewType']['Value'] = $nViewType;
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved link config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/links/config.php?id='.page::menuID()); 
	}
	
	// Neue Kategorie hinzufügen
	public function addCategory() {
		$nSort = $this->getNewCategorySort();
		$sName = '< '.$this->Res->normal(632,page::language()).' >';
		$sSQL = "INSERT INTO tblinkcategory (mnu_ID,lnc_Order,lnc_Title) 
		VALUES (".page::menuID().",$nSort,'$sName')";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language())); // 'Eingaben gespeichert'
		session_write_close();
		redirect('location: /admin/links/categories.php?id='.page::menuID());
	}
	
	// Nächsten sortorder holen
	private function getNewSort() {
		$sSQL = "SELECT IFNULL(MAX(lnk_Sortorder),0) 
		FROM tblink WHERE mnu_ID = ".page::menuID();
		$nResult = $this->Conn->getFirstResult($sSQL);
		return(++$nResult);
	}
	
	// Nächsten Sortorder für Kategorie holen
	private function getNewCategorySort() {
		$sSQL = "SELECT IFNULL(MAX(lnc_Order),0) 
		FROM tblinkcategory WHERE mnu_ID = ".page::menuID();
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
}