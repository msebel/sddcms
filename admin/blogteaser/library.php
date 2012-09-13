<?php
// Library für Blogteaser Elemente
class moduleBlogTeaser extends commonModule {
	
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
	
	// Gewährt Zutritt für das Teasercontent Element
	public function checkAccess() {
		$nTapID = getInt($_GET['element']);
		// Zählen ob es die Verbindung gibt, wenn nicht
		// Fehler und Redirect auf Error noAccess
		$sSQL = "SELECT COUNT(tsa_ID) FROM tbteasersection_teaser
		WHERE tas_ID = ".$_SESSION['teaserBackID']." AND tap_ID = $nTapID";
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult != 1) {
			logging::debug('blog teaser element access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Konfiguration speichern
	public function saveConfigCategoryTagcloud($nTapID,&$Config) {
		$Config['categoryID']['Value'] = getInt($_POST['categoryID']);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog teaser category tagcloud');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blogteaser/categoryTagcloud.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Konfiguration initialisieren
	public function initConfigCategoryTagcloud($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,1)) {
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'categoryID',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
	
	// Optionen aller Kalender anzeigen
	public function getCategoryDropdown($nCurrent) {
		$out = '';
		$sSQL = "SELECT blc_ID,blc_Title FROM tbblogcategory 
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbblogcategory.mnu_ID
		WHERE tbmenu.man_ID = ".page::mandant()." AND tbmenu.mnu_Active = 1";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['blc_ID'].'"'.checkDropDown($nCurrent,$row['blc_ID']).'>'.$row['blc_Title'].'</option>'."\n";
		}
		return($out);
	}
	
	// Konfiguration speichern
	public function saveConfigBlogTagcloud($nTapID,&$Config) {
		$Config['blogID']['Value'] = $this->validateMenu($_POST['blogID']);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog teaser tagcloud');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blogteaser/blogTagcloud.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Menupunkt Kalender validieren oder 0 zurückgeben
	private function validateMenu($nMenuID) {
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE man_ID = ".page::mandant()." 
		AND mnu_Active = 1 AND typ_ID = ".typeID::MENU_BLOGADMIN." AND mnu_ID = $nMenuID";
		$nCountResult = $this->Conn->getCountResult($sSQL);
		if ($nCountResult != 1) $nMenuID = 0;
		return($nMenuID);
	}
	
	// Konfiguration initialisieren
	public function initConfigBlogTagcloud($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,1)) {
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
	
	// Optionen aller Kalender anzeigen
	public function getBlogDropdown($nCurrent) {
		$out = '';
		$sSQL = "
		SELECT mnu_ID,mnu_Name FROM tbmenu WHERE man_ID = ".page::mandant()." 
		AND mnu_Active = 1 AND typ_ID = ".typeID::MENU_BLOGADMIN;
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['mnu_ID'].'"'.checkDropDown($nCurrent,$row['mnu_ID']).'>'.$row['mnu_Name'].'</option>'."\n";
		}
		return($out);
	}
	
	// Konfiguration speichern
	public function saveConfigCategoryList($nTapID,&$Config) {
		$Config['blogID']['Value'] = $this->validateMenu($_POST['blogID']);
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog teaser category list');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blogteaser/categoryList.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Konfiguration initialisieren
	public function initConfigCategoryList($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,2)) {
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfigBlogStats($nTapID,&$Config) {
		$Config['blogID']['Value'] = $this->validateMenu($_POST['blogID']);
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog teaser stats');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blogteaser/blogStats.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Konfiguration initialisieren
	public function initConfigBlogStats($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,2)) {
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
}