<?php 
class moduleBlog extends commonModule {
	
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
	 * Kategorienobjekt
	 * @var blogCategory
	 */
	private $Categories;
	/**
	 * Objekt aller Keywörter
	 * @var keywords
	 */
	private $Keywords;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Kategorienobjekt bekommen
	public function setCategoryObject(&$Categories) {
		$this->Categories =& $Categories;
	}
	
	// Keywords Objekt bekommen
	public function setKeywordsObject(&$Keywords) {
		$this->Keywords =& $Keywords;
	}
	
	// Neuen Eintrag hinzufügen
	public function addBlogentry() {
		// Neuen Content Eintrag erstellen
		$nUserID = getInt($_SESSION['userid']);
		$nContentID = ownerID::get($this->Conn);
		$nDate = dateOps::getTime(dateOps::SQL_DATETIME,time());
		$sTitle = '< '.$this->Res->html(607,page::language()).' >';
		$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Title,con_Date,
		con_Modified,con_Active,con_ShowName,con_ShowDate) VALUES 
		($nContentID,".page::menuID().",$nUserID,'$sTitle','$nDate','$nDate',0,1,1)";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		$this->resetPaging();
		session_write_close();
		redirect('location: /admin/blog/index.php?id='.page::menuID()); 
	}
	
	// Blog Einträge speichern
	public function saveBlogentries() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nConID = getInt($_POST['id'][$i]);
			$nActive = getInt($_POST['active_'.($i+1)]);
			if ($nActive != 1) $nActive = 0;
			$sTitle = $_POST['title'][$i];
			stringOps::noHtml($sTitle);
			$this->Conn->escape($sTitle);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbcontent SET 
			con_Active = $nActive, con_Title = '$sTitle'
			WHERE con_ID = $nConID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved blog entries');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/index.php?id='.page::menuID());
	}
	
	// Einen Blog Eintrag speichern
	public function saveBlogentry() {
		$nConID = getInt($_GET['entry']);
		// Titel und Content validieren
		$sTitle = $_POST['conTitle'];
		stringOps::noHTML($sTitle);
		$this->Conn->escape($sTitle);
		$sContent = $_POST['conContent'];
		stringOps::htmlEntRev($sContent);
		$this->Conn->escape($sContent);
		// Erstelldatum zusammenbasteln
		$sDate = $_POST['date_date'];
		$sTime = $_POST['date_time'];
		$sDateTime = $sDate.' '.$sTime.':00';
		// Wenn ungültig, aktuelle Zeit SQL konvertieren, sonst eingegebene
		if (stringOps::checkDate($sDateTime,dateOps::EU_FORMAT_DATETIME)) {
			$sDateTime = dateOps::convertDate(
				dateOps::EU_DATETIME,
				dateOps::SQL_DATETIME,
				$sDateTime
			);
		} else {
			$sDateTime = dateOps::getTime(dateOps::SQL_DATETIME,time());
		}
		// Letzte Änderung aktualisieren
		$sLastChange = dateOps::getTime(dateOps::SQL_DATETIME,time());
		// Optionen speichern
		$nShowName = getInt($_POST['conShowName']);
		if ($nShowName != 1) $nShowName = 0;
		$nShowDate = getInt($_POST['conShowDate']);
		if ($nShowDate != 1) $nShowDate = 0;
		// Statement erstellen und abfeuern
		$sSQL = "UPDATE tbcontent SET
		con_Title = '$sTitle', con_Content = '$sContent',
		con_Date = '$sDateTime', con_Modified = '$sLastChange',
		con_ShowName = $nShowName, con_ShowDate = $nShowDate
		WHERE con_ID = $nConID";
		$this->Conn->command($sSQL);
		// Keywords speichern
		$this->Keywords->save($nConID,$_POST['keywords']);
		// Erfolg melden und weiterleiten
		logging::debug('saved blog entry');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/edit.php?id='.page::menuID().'&entry='.$nConID);
	}
	
	// Blog Eintrag löschen
	public function deleteBlogentry() {
		// Content löschen
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE con_ID = $nDeleteID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbcontent WHERE con_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			$sSQL = "DELETE FROM tbblogcategory_content WHERE con_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			$sSQL = "DELETE FROM tbkommentar WHERE owner_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted blog entry');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/blog/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting blog entry');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/blog/index.php?id='.page::menuID()); 
		}
	}
	
	// Blog Einträge für Übersicht laden
	public function loadBlogentries(&$Data) {
		$sSQL = "SELECT tbcontent.con_ID,con_Title,con_Date,con_Active FROM tbcontent";
		$nSearchCategory = sessionConfig::get('SearchCategory',0);
		// Wenn nötig inneres joinen auf Verbindungstabelle
		if ($nSearchCategory > 0) {
			$sSQL .= " INNER JOIN tbblogcategory_content ON
			tbblogcategory_content.con_ID = tbcontent.con_ID";
		}
		$sSQL .= " WHERE mnu_ID = ".page::menuID();
		if ($nSearchCategory > 0) {
			$sSQL .= " AND tbblogcategory_content.blc_ID = ".$nSearchCategory;
		}
		$sSQL .= " ORDER BY con_Date DESC";
		$paging = new paging($this->Conn,'index.php?id='.page::menuID());
		$paging->start($sSQL,15);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Daten anpassen und speichern
			$row['con_Categories'] = $this->loadCategories($row['con_ID']);
			// Datum für Anzeige umwandeln
			$row['con_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$row['con_Date']
			);
			// Einfügen in Daten array
			array_push($Data,$row);
		}
		return($paging->getHtml());
	}
	
	// Einen Blog Eintrag laden
	public function loadBlogentry(&$Data) {
		$nConID = getInt($_GET['entry']);
		$sSQL = "SELECT con_Date,con_Modified,con_ShowName,con_ShowDate,
		con_Title,con_Content,usr_ID FROM tbcontent WHERE con_ID = $nConID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Daten umwandeln
			$row['date_date'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$row['con_Date']);
			$row['date_time'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_CLOCK,$row['con_Date']);
			$row['modified_date'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATE,$row['con_Modified']);
			$row['modified_time'] = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_CLOCK,$row['con_Modified']);
			// Keywords laden
			$this->Keywords->load($nConID);
			// Daten speichern
			$Data = $row;
		}
	}
	
	// Tooltip für einen Eintrag erstellen und zurückgeben
	// welches Bild angezeigt werden soll für Tooltip
	public function addTooltip(&$Tooltip,&$Entry,$HTMLID) {
		if (count($Entry['con_Categories']) > 0) {
			// Anzeige der Kategorien in Liste
			$sInfoImage = 'information.png';
			$Title = count($Entry['con_Categories']).' '.$this->Res->html(602,page::language());
			$Text = $this->Res->html(601,page::language()).':<br>';
			$Text.= '<ul>';
			foreach($Entry['con_Categories'] as $CatID) {
				$Category = $this->Categories->get($CatID);
				$Text .= '<li>'.$Category['blc_Title'].'</li>';
			}
			$Text.= '</ul>';
			// Tooltip hinzufügen
			$Tooltip->add('tooltipWin_'.$HTMLID,$Text,$Title,350,0);
		} else {
			// Fehlermeldung
			$sInfoImage = 'icon_alert.gif';
			$Title = $this->Res->html(603,page::language());
			$Text = $this->Res->html(604,page::language()).'<br>';
			$Text.= $this->Res->html(605,page::language()).'<br>';
			$Text.= $this->Res->html(606,page::language()).'<br>';
			// Tooltip hinzufügen
			$Tooltip->add('tooltipWin_'.$HTMLID,$Text,$Title,350,0);
		}
		return($sInfoImage);
	}
	
	// Suchkategorie setzen
	public function setSearchCategory() {
		$nValue = getInt($_POST['SearchCategoryID']);
		sessionConfig::set('SearchCategory',$nValue);
		session_write_close();
		redirect('location: /admin/blog/index.php?id='.page::menuID()); 
	}
	
	// Dropdown für Kategorien zeigen
	public function getCategorySearch($nCatID) {
		// Selektor erstellen
		$out .= $this->Res->html(614,page::language()).': ';
		$out .= '<select id="SearchCategorySelect" onChange="javascript:SearchCategory()">';
		// "Keine Kategorie" Dropdown hinzufügen
		$out .= '
		<optgroup label="----------------">
			<option value="0"'.checkDropdown(0,$nCatID).'>'.$this->Res->html(615,page::language()).'</option>
		</optgroup>
		<optgroup label="----------------">
		';
		// Kategorien ausgeben
		foreach ($this->Categories->Categories as $Category) {
			$out .= '<option value="'.$Category['blc_ID'].'"'.checkDropdown($Category['blc_ID'],$nCatID).'>'.$Category['blc_Title'].'</option>';
		}
		$out .= '
		</optgroup>
		</select>';
		return($out);
	}
	
	// Zugriff auf Blogeintrag testen
	public function checkBlogentryAccess() {
		$nConID = getInt($_GET['entry']);
		$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
		WHERE con_ID = $nConID AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		$bReturn = false;
		if ($nReturn != 1) {
			logging::error('blog entry access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Selektor konfigurieren und Daten einfüllen
	public function configureSelector(&$Selector) {
		$nConID = getInt($_GET['entry']);
		$Selector->add(
			'categorySelector',
			'id='.page::menuID().'&entry='.$nConID,
			'ajax/bindContentCategories.php',300,5
		);
		$sSQL = "SELECT tbblogcategory.blc_Title,tbblogcategory.blc_ID, 
		tbblogcategory_content.bcc_ID FROM tbblogcategory
		LEFT JOIN tbblogcategory_content ON tbblogcategory_content.con_ID = $nConID
		AND tbblogcategory_content.blc_ID = tbblogcategory.blc_ID
		WHERE mnu_ID = ".page::menuID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Selected = true;
			if ($row['bcc_ID'] == NULL) $Selected = false;
			$Selector->addRow(
				'categorySelector',
				$row['blc_ID'],
				$row['blc_Title'],
				$Selected
			);
		}
	}
	
	// Konfiguration initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,4)) {
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'allowComments',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'socialBookmarking',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,8,pageConfig::TYPE_NUMERIC,'postsPerPage',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$nAllowComments = getInt($_POST['allowComments']);
		if ($nAllowComments != 1) $nAllowComments = 0;
		$Config['allowComments']['Value'] = $nAllowComments;
		$nSocialBookmarks = getInt($_POST['socialBookmarking']);
		if ($nSocialBookmarks != 1) $nSocialBookmarks = 0;
		$Config['socialBookmarking']['Value'] = $nSocialBookmarks;
		$nPostsPerPage = getInt($_POST['postsPerPage']);
		if ($nPostsPerPage <= 0) $nPostsPerPage = 1;
		$Config['postsPerPage']['Value'] = $nPostsPerPage;
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved blog configuration');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/blog/config.php?id='.page::menuID());
	}
	
	// Keywords für in die Textarea laden
	public function getKeywords() {
		$out .= '';
		foreach ($this->Keywords->Data as $Keyword) {
			$out .= $Keyword['key_Keyword'].', ';
		}
		// Wenn länge vorhanden, hinterstes Komma entfernen
		if (strlen($out) > 0) {
			$out = substr($out,0,strlen($out)-2);
		}
		return($out);
	}
	
	// Benutzernamen des Schreibes holen oder "Anonymous"
	public function getUsername($nUsrID) {
		$nUsrID = getInt($nUsrID);
		$sUsername = $this->Res->html(649,page::language());
		$sSQL = "SELECT usr_Name FROM tbuser WHERE usr_ID = $nUsrID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sUsername = $row['usr_Name'];
		}
		return($sUsername);
	}
	
	// Kategoriennummern einer Content ID laden
	private function loadCategories($nConID) {
		$Data = array();
		$sSQL = "SELECT tbblogcategory.blc_ID FROM tbblogcategory INNER JOIN tbblogcategory_content
		ON (tbblogcategory.blc_ID = tbblogcategory_content.blc_ID AND tbblogcategory_content.con_ID = $nConID)";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($Data,$row['blc_ID']);
		}
		return($Data);
	}
}