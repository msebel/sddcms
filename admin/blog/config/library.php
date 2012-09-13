<?php 
class moduleBlogConfig extends commonModule {
	
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
	
	// Selektor für alle Blogs erstellen
	public function getBlogSelector($nSelected) {
		$out = '';
		$out .= '<select name="blogID" onChange="this.form.submit()" style="width:300px;">';
		$out .= '<option value="0">- - - - - </option>';
		// Suchen aller BLogverwaltungen
		$sSQL = "SELECT mnu_Name,mnu_ID FROM tbmenu 
		WHERE typ_ID = ".typeID::MENU_BLOGADMIN." AND man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '
			<option value="'.$row['mnu_ID'].'"'.checkDropdown($row['mnu_ID'],$nSelected).'>'.$row['mnu_Name'].'</option>';
		}
		$out .= '</select>';
		return($out);
	}
	
	// Selektor für Kategorien innerhalb eines Blogs erstellen
	public function getCategorySelector($nBlogID,$nSelected) {
		$out = '';
		$out .= '<select name="categoryID" style="width:300px;">';
		$out .= '<option value="0">- - - - - </option>';
		// Suchen aller BLogverwaltungen
		$sSQL = "SELECT blc_Title,blc_ID FROM tbblogcategory WHERE mnu_ID = $nBlogID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '
			<option value="'.$row['blc_ID'].'"'.checkDropdown($row['blc_ID'],$nSelected).'>'.$row['blc_Title'].'</option>';
		}
		$out .= '</select>';
		return($out);
	}
	
	// Konfiguration für Kategorien Anzeige erstellen
	public function initConfigCategory(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,2)) {
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'categoryID',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration für Kategorien Anzeige speichern
	public function saveConfigCategory(&$Config) {
		$nMenuID = page::menuID();
		$Config['blogID']['Value'] = $this->validateBlogID($_POST['blogID']);
		$Config['categoryID']['Value'] = $this->validateCategoryID(
			$_POST['categoryID'],
			$Config['blogID']['Value']
		);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog category config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/config/category.php?id='.page::menuID());
	}
	
	// Konfiguration für Kategorien Anzeige erstellen
	public function initConfigRecent(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,2)) {
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,10,pageConfig::TYPE_NUMERIC,'postCount',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration für Kategorien Anzeige speichern
	public function saveConfigRecent(&$Config) {
		$nMenuID = page::menuID();
		$Config['blogID']['Value'] = $this->validateBlogID($_POST['blogID']);
		$nPosts = getInt($_POST['postCount']);
		if ($nPosts <= 0) $nPosts = 1;
		$Config['postCount']['Value'] = $nPosts;
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog recent config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/config/recent.php?id='.page::menuID());
	}
	
	// Konfiguration für Kategorien Anzeige erstellen
	public function initConfigOverview(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,1)) {
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'blogID',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration für Kategorien Anzeige speichern
	public function saveConfigOverview(&$Config) {
		$nMenuID = page::menuID();
		$Config['blogID']['Value'] = $this->validateBlogID($_POST['blogID']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog overview config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/config/overview.php?id='.page::menuID());
	}
	
	// RSS Link zurückgeben, evtl. mit Kategorie
	public function getRssLink($nCategory) {
		// Link starten mit lokaler URL
		$sProtocol = 'http';
		if ($_SERVER['HTTPS'] == 'on') $sProtocol = 'https';
		$sLink = '';
		$sLink.= $sProtocol.'://'.page::domain().'/modules/rss/blog.php?id='.page::menuID();
		if ($nCategory > 0) $sLink.= '&ca';
		$sHtml = '<a href="'.$sLink.'" target="_blank">'.$sLink.'</a>';
		return($sHtml);
	}
	
	// BlogID validieren (gehört Sie dieser Webseite?)
	private function validateBlogID($nBlogID) {
		$nBlogID = getInt($nBlogID);
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
		WHERE mnu_ID = $nBlogID AND typ_ID = ".typeID::MENU_BLOGADMIN;
		$nReturn = $this->Conn->getCountResult($sSQL);
		// Wenn nicht gefunden, 0 zurückgeben
		if ($nReturn != 1) $nBlogID = 0;
		return($nBlogID);
	}
	
	// CategoryID validieren (gehört Sie diesem Blog?)
	private function validateCategoryID($nCatID,$nBlogID) {
		$nCatID = getInt($nCatID);
		$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
		WHERE mnu_ID = $nBlogID AND blc_ID = $nCatID";
		$nReturn = $this->Conn->getCountResult($sSQL);
		// Wenn nicht gefunden, 0 zurückgeben
		if ($nReturn != 1) $nCatID = 0;
		return($nCatID);
	}
}