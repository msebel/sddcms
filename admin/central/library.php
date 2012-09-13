<?php 
class moduleCentral extends commonModule {
	
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
	
	// Lädt die Contentsection Verbindungen
	public function loadEntities(&$Data) {
		// Join mit Menupriorität, weil nicht alle
		// Verknüpfungen immer eine Section zugeordnet haben
		$sSQL = "SELECT tbmenu_contentsection.mcs_ID,tbcontentsection.cse_Name,
		tbcontentsection.cse_Active,tbcontentsection.cse_Type FROM tbmenu_contentsection 
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbmenu_contentsection.mnu_ID
		LEFT JOIN tbcontentsection ON tbcontentsection.cse_ID = tbmenu_contentsection.cse_ID
		ORDER BY tbmenu_contentsection.mcs_Sort ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($Data,$row);
		}
	}
	
	// Lädt die Contentsection Verbindungen
	public function loadEntity($nMcsID,&$Data) {
		// Join mit Menupriorität, weil nicht alle
		// Verknüpfungen immer eine Section zugeordnet haben
		$sSQL = "SELECT tbmenu_contentsection.mcs_ID,tbcontentsection.cse_ID,
		tbcontentsection.cse_Active,tbcontentsection.cse_Type FROM tbmenu_contentsection 
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbmenu_contentsection.mnu_ID
		LEFT JOIN tbcontentsection ON tbcontentsection.cse_ID = tbmenu_contentsection.cse_ID
		WHERE tbmenu_contentsection.mcs_ID = $nMcsID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
		}
	}
	
	// Neue leere Entität erstellen
	public function addEntity() {
		// Statement erstellen und abfeuern
		$sSQL = "INSERT INTO tbmenu_contentsection (cse_ID,mnu_ID,mcs_Sort)
		VALUES (0,".page::menuID().",".$this->getNextSort().")";
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/central/index.php?id='.page::menuID());
	}
	
	// Eine Entität nach validieren löschen
	public function deleteEntity() {
		$nDeleteID = getInt($_GET['delete']);
		if ($this->validateEntity($nDeleteID)) {
			$sSQL = "DELETE FROM tbmenu_contentsection WHERE mcs_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('delete central content entity');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/central/index.php?id='.page::menuID()); 
		} else {
			// Misserfolg melden und weiterleiten
			logging::error('error deleting central content entity');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/central/index.php?id='.page::menuID()); 
		}
	}
	
	// Content Sektionen speichern
	public function saveEntities() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nMcsID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			// Sortierung updaten
			$sSQL = "UPDATE tbmenu_contentsection
			SET mcs_Sort = $nSort WHERE mcs_ID = $nMcsID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved central content entities');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/central/index.php?id='.page::menuID());
	}
	
	// Eine Entität im Editmode speichern
	public function saveEntity($nMcsID) {
		// Prüfen ob die gegebene Section dem User gehört
		$nCseID = getInt($_POST['selectedSection']);
		$sSQL = "SELECT COUNT(tbmenu.mnu_ID) FROM tbcontentsection
		INNER JOIN tbmenu ON tbmenu.mnu_ID = tbcontentsection.mnu_ID
		WHERE tbcontentsection.cse_ID = $nCseID
		AND tbmenu.man_ID = ".page::mandant();
		$nResult = $this->Conn->getFirstResult($sSQL);
		// Nur speichern, wenn Resultat vorhanden
		if ($nResult == 1) {
			// Statement erstellen und abfeuern
			$sSQL = "UPDATE tbmenu_contentsection SET
			cse_ID = $nCseID WHERE mcs_ID = $nMcsID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('saved central content entity');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/central/edit.php?id='.page::menuID().'&entity='.$nMcsID);
		} else {
			// Misserfolg melden und weiterleiten
			logging::error('error saving central content entity');
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/central/edit.php?id='.page::menuID().'&entity='.$nMcsID);
		}
	}
	
	// Gibt den Pfad zu einem passenden Icon zurück
	public function getTypeIcon($nType) {
		// Grundsätzlich Cancel Icon zeigen (Kein Typ)
		$sIcon = '/images/icons/cancel.png';
		// Nun versuchen, Typ herauszufinden
		switch ($nType) {
			case contentView::TYPE_CONTENT:
				$sIcon = '/images/icons/page.png';
				break;
			case contentView::TYPE_MEDIA:
				$sIcon = '/images/icons/image.png';
				break;
			case contentView::TYPE_FORM:
				$sIcon = '/images/icons/table.png';
				break;
		}
		return($sIcon);
	}
	
	// HTML zurückgeben für Vorschau
	public function getPreviewIcon($nType,$nID) {
		$out = '';
		if ($nType == NULL) {
			// Keine Vorschau möglich
			$out .= '
			<img src="/images/icons/bullet_magnifier_disabled.png" title="'.$this->Res->html(169,page::language()).'" alt="'.$this->Res->html(169,page::language()).'">
			';
		} else {
			// Vorschau Icon anzeigen
			$out .= '
			<a href="#" onClick="openWindow(\'preview.php?id='.page::menuID().'&entity='.$nID.'&useDesign='.page::originaldesign($Conn).'&showTeaser&runParser\',\''.$this->Res->javascript(169,page::language()).'\',950,700)">
			<img src="/images/icons/bullet_magnifier.png" title="'.$this->Res->html(169,page::language()).'" alt="'.$this->Res->html(169,page::language()).'" border="0"></a>
			';
		}
		return($out);
	}
	
	// Zugriff prüfen
	public function checkAccess($nID) {
		if (!$this->validateEntity($nID)) {
			logging::error('central content entity access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Zentralen Contentmenupunkt ahhand der Entity Daten herausfinden
	public function getSourceMenu(&$Data) {
		// Contentsection ID holen
		$nCseID = getInt($Data['cse_ID']);
		$nMenuID = 0;
		// Als Standardwert, erste gefundenr zentrale Content Verwaltung
		$sSQL = "SELECT mnu_ID FROM tbmenu 
		WHERE typ_ID = ".typeID::MENU_CENTRALCONTENT."
		AND man_ID = ".page::mandant()." 
		ORDER BY mnu_Name ASC LIMIT 0,1";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$nMenuID = $row['mnu_ID'];
		}
		// Wenn Section vorhanden, Menu von dort holen
		if ($nCseID > 0) {
			$sSQL = "SELECT mnu_ID FROM tbcontentsection
			WHERE cse_ID = $nCseID LIMIT 0,1";
			$nMenuID = $this->Conn->getFirstResult($sSQL);
		}
		return($nMenuID);
	}
	
	// Optionen für Zentrale Contentwahl ausgeben
	public function getSourceMenuOptions($nSourceMenu) {
		$out = '';
		$nCount = 0;
		// Alle Menus vom Typ zentraler Content anzeigen
		$sSQL = "SELECT mnu_ID,mnu_Name FROM tbmenu 
		WHERE man_ID = ".page::mandant()." AND mnu_Active = 1 
		AND typ_ID = ".typeID::MENU_CENTRALCONTENT."
		ORDER BY mnu_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['mnu_ID'].'"'.checkDropDown($nSourceMenu,$row['mnu_ID']).'>'.$row['mnu_Name'].'</option>'."\n";
		}
		
		return($out);
	}
	
	// Quellmenu verändern (zuerst prüfen, sonst gegebenen Wert zurück)
	public function changeSource(&$nSource) {
		$nNew = getInt($_POST['sourceMenu']);
		// Prüfen ob es existiert, wenn Ja, sourcen überschreiben
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
		WHERE man_ID = ".page::mandant()." AND mnu_ID = $nNew
		AND typ_ID = ".typeID::MENU_CENTRALCONTENT;
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) $nSource = $nNew;
	}
	
	// Liste der Contents ausgeben
	public function getContentList(&$Data, $nSourceMenu) {
		$out = '';
		$nCount = 0;
		$TabRow = new tabRowExtender();
		$nSelected = getInt($Data['cse_ID']);
		// Alle Sections des gegebenen Menus holen (ist validiert)
		$sSQL = "SELECT cse_Name, cse_ID, cse_Active, cse_Type FROM tbcontentsection
		 WHERE mnu_ID = $nSourceMenu ORDER BY cse_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$nCount++;
			$sTypeIcon = $this->getTypeIcon($row['cse_Type']);
			$out .= '
			<div class="'.$TabRow->get().'" style="width:100%;height:20px;padding-top:5px;">
				<div style="width:30px;float:left;">
					<input type="radio" name="selectedSection" '.checkCheckbox($nSelected,$row['cse_ID']).' value="'.$row['cse_ID'].'">
				</div>
				<div style="width:30px;float:left;">
					<img src="'.$sTypeIcon.'" title="'.$this->Res->html(428,page::language()).'" alt="'.$this->Res->html(428,page::language()).'">
				</div>
				<div style="float:left;" class="adminBuffer">
					<input type="text" disabled="disabled" value="'.$row['cse_Name'].'" class="adminBufferInput">
				</div>
				<div style="width:50px;float:left;">
					<input type="checkbox" disabled="disabled"'.checkCheckbox(1,$row['cse_Active']).'>
				</div>
			</div>
			';
		}
		// Wenn keine Einträge, dies Melden
		if ($nCount == 0) {
			$out .= '
			<div class="'.$TabRow->get().'">
				<div style="width:100%;">'.$this->Res->html(158,page::language()).' ...</div>
			</div>
			';
		}
		return($out);
	}
	
	// Nächsten sortorder holen
	private function getNextSort() {
		$sSQL = "SELECT IFNULL(MAX(mcs_Sort)+1,1) FROM
		tbmenu_contentsection WHERE mnu_ID = ".page::menuID();
		$nNext = $this->Conn->getFirstResult($sSQL);
		return($nNext);
	}
	
	// Validiert, ob die gegebene ID zur Entität/Menu gehört
	private function validateEntity($nID) {
		$nID = getInt($nID);
		$sSQL = "SELECT COUNT(mcs_ID) FROM tbmenu_contentsection
		WHERE mcs_ID = $nID AND mnu_ID = ".page::menuID();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		// Je nach Anzahl Datensätzen true/false
		$bValid = false;
		if ($nResult == 1) $bValid = true;
		return($bValid);
	}
}