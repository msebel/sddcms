<?php
class moduleTeaser extends commonModule {
	
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
	private $TeaserTypes;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	// Neue Teasersektion erstellen
	public function addTeaserSection() {
		$sSQL = "INSERT INTO tbteasersection (tas_Desc,man_ID)
		VALUES ('< ".$this->Res->html(407,page::language())." >',".page::mandant().")";
		$this->Conn->command($sSQL);
		$this->setErrorSession($this->Res->html(408,page::language()));
		session_write_close();
		redirect('location: /admin/teaser/index.php?id='.page::menuID());
	}
	
	// Neues Teaserelement erstellen
	public function addTeaserElement($nTeaserID) {
		// Nächsten Sortorder holen
		$sSQL = "SELECT COUNT(tap_ID)+1 
		FROM tbteasersection_teaser WHERE tas_ID = $nTeaserID";
		$nNextOrder = getInt($this->Conn->getFirstResult($sSQL));
		// Teaser Eintrag erstellen
		$nTapID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbteaser 
		(tap_ID,man_ID,tty_ID,tap_Title)
		VALUES ($nTapID,".page::mandant().",100,
		'< ".$this->Res->normal(425,page::language())." >')";
		$this->Conn->command($sSQL);
		// Verbindungseintrag für Teasersection
		$sSQL = "INSERT INTO tbteasersection_teaser 
		(tas_ID,tap_ID,tsa_Sortorder,tsa_Active) 
		VALUES ($nTeaserID,$nTapID,$nNextOrder,0)";
		$this->Conn->command($sSQL);
		// Sessions der Elemente löschen
		unset($_SESSION['standardteaser']);
		unset($_SESSION['standardteaser_time']);
		// Erfolg melden & weiterleiten
		$this->resetPaging();
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID); 
	}
	
	// Teasersektionen speichern
	public function saveTeaserSections() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nTasID = getInt($_POST['id'][$i]);
			$sDesc = $_POST['name'][$i];
			// Escapen des Namen
			$this->Conn->escape($sDesc);
			stringOps::noHtml($sDesc);
			if (strlen($sDesc) == 0) {
				$sDesc = '< '.$this->Res->html(407,page::language()).' >';
			}
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbteasersection 
			SET tas_Desc = '$sDesc' WHERE tas_ID = $nTasID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved teaser sections');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/teaser/index.php?id='.page::menuID());
	}
	
	// Teaserelemente speichern
	public function saveTeaserElements($nTeaserID) {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nTapID = getInt($_POST['id'][$i]);
			$sDesc = $_POST['title'][$i];
			$nOrder = getInt($_POST['sort'][$i]);
			$nActive = getInt($_POST['active_'.$nTapID]);
			// Escapen des Namen
			$this->Conn->escape($sDesc);
			stringOps::noHtml($sDesc);
			if (strlen($sDesc) == 0) {
				$sDesc = '< '.$this->Res->html(407,page::language()).' >';
			}
			// Typen ID validieren
			$nTypeID = $this->validateTypeID($_POST['type'][$i]);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbteaser SET 
			tap_Title = '$sDesc', tty_ID = $nTypeID
			WHERE tap_ID = $nTapID";
			$this->Conn->command($sSQL);
			// Order und Aktiv anpassen
			$sSQL = "UPDATE tbteasersection_teaser SET
			tsa_Sortorder = $nOrder,tsa_Active = $nActive
			WHERE tap_ID = $nTapID AND tas_ID = $nTeaserID";
			$this->Conn->command($sSQL);
			// Sessions der Elemente löschen
			unset($_SESSION['standardteaser']);
			unset($_SESSION['standardteaser_time']);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved teaser elements');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
	}
	
	// Teasersection löschen
	public function deleteTeaserSection() {
		$nTasID = getInt($_GET['delete']);
		// Gehört der Teaser zum Mandanten?
		$sSQL = "SELECT COUNT(tas_ID) FROM tbteasersection
		WHERE tas_ID = $nTasID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		// Löschbarkeit prüfen
		$nUseage = $this->getUseCount($nTasID);
		// Wenn vorhanden und löschbar (Keine Verwendungen)
		if ($nResult == 1 && $nUseage == 0) {
			$sSQL = "DELETE FROM tbteasersection WHERE tas_ID = $nTasID";
			$this->Conn->command($sSQL);
			// Alle Verbundenen Teaserelemente/Verbindungen löschen
			// sofern diese nicht importiert sind (Nur originale löschen)
			$sSQL = "SELECT tsa_ID,tap_ID FROM 
			tbteasersection_teaser WHERE tas_ID = $nTasID
			AND tsa_Imported = 0";
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				// Applikation (Element) löschen
				$sSQL = "DELETE FROM tbteaser 
				WHERE tap_ID = ".$row['tap_ID'];
				$this->Conn->command($sSQL);
				$sSQL = "DELETE FROM tbteasersection_teaser 
				WHERE tap_ID = ".$row['tsa_ID'];
				$this->Conn->command($sSQL);
			}
			logging::debug('deleted teaser section');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/index.php?id='.page::menuID());
		} else {
			logging::error('error deleting teaser section');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/index.php?id='.page::menuID());
		}
	}
	
	// Teaserelement löschen
	public function deleteTeaserElement($nTeaserID) {
		$nDeleteID = getInt($_GET['delete']);
		// Prüfen ob die ID zum aktuellen Teaser gehört
		$sSQL = "SELECT COUNT(tap_ID) FROM tbteasersection_teaser
		WHERE tas_ID = $nTeaserID AND tap_ID = $nDeleteID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn Resultat eins ist, dann löschbar
		if ($nResult == 1) {
			// Verbindung löschen
			$sSQL = "DELETE FROM tbteasersection_teaser
			WHERE tas_ID = $nTeaserID AND tap_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Element ansich löschen, wenn es keine Weiteren Verbindungen hat
			$sSQL = "SELECT COUNT(tap_ID) FROM tbteasersection_teaser
			WHERE tap_ID = $nDeleteID";
			$nCountDependencies = $this->Conn->getCountResult($sSQL);
			if ($nCountDependencies == 0) {
				$sSQL = "DELETE FROM tbteaser WHERE tap_ID = $nDeleteID";
				$this->Conn->command($sSQL);
			}
			// Sessions der Elemente löschen
			unset($_SESSION['standardteaser']);
			unset($_SESSION['standardteaser_time']);
			// Erfolg melden & weiterleiten
			logging::debug('deleted teaser element');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		} else {
			logging::error('error deleting teaser element');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		}
	}
	
	// Lädt alle Teaserobjekte geordnet nach Name, neuste zuerst
	// Inklusive Paging auf 10 Einträge pro Seite
	public function loadTeaserSections(&$sData) {
		// News für diese menu ID laden
		$sSQL = "SELECT tas_ID,tas_Desc FROM tbteasersection 
		WHERE man_ID = ".page::mandant()." ORDER BY tas_Desc ASC";
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		$PagingEngine->start($sSQL,10);
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Verwendung der Row zählen
			$row['tas_Count'] = $this->getUseCount($row['tas_ID']);
			array_push($sData,$row);
		}
		// Paging Engine HTML zurückgeben
		return($PagingEngine->getHtml());
	}
	
	// Teaserelemente laden
	public function loadTeaserElements(&$sData,$nTeaserID) {
		// Laden aller Teasertypen
		$TeaserTypes = array();
		$this->loadTeaserTypes($TeaserTypes);
		$this->TeaserTypes = $TeaserTypes;
		// Teaserelemente sortiert laden
		$sSQL = "SELECT tbteaser.tap_ID,tbteaser.tty_ID,tbteaser.tap_Title, 
		tbteasersection_teaser.tsa_Active,tbteasersection_teaser.tsa_Imported FROM tbteaser
		INNER JOIN tbteasersection_teaser ON tbteasersection_teaser.tap_ID = tbteaser.tap_ID
		WHERE tbteasersection_teaser.tas_ID = $nTeaserID AND man_ID = ".page::mandant()." 
		ORDER BY tbteasersection_teaser.tsa_Sortorder ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Matchen der Menutypen in die Row
			$this->matchTypes($row,$TeaserTypes);
			array_push($sData,$row);
		}
	}
	
	// Gibt HTML Zurück für um das bullet_delete auszugrauen,
	// Wenn das Objekt nicht löschbar ist
	public function getDeleteable($Data) {
		// Prüfen auf Abhängigkeit
		if ($Data['tas_Count'] == 0) {
			$out = '<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$Data['tas_ID'].'\',\''.addslashes($Data['tas_Desc']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$this->Res->html(405,page::language()).'" alt="'.$this->Res->html(405,page::language()).'" border="0"></a>';
		} else {
			$out = '<img src="/images/icons/bullet_delete_disabled.png" title="'.$this->Res->html(406,page::language()).'" alt="'.$this->Res->html(406,page::language()).'" border="0"></a>';
		}
		return($out);
	}
	
	// Gibt HTML Zurück um das bullet_wrench auszugrauen
	// Wenn das Objekt nicht bearbeitbar ist
	public function getEditable($Data,$nTeaserID,$isImported) {
		// Prüfen auf Abhängigkeit
		if (strlen($Data['tty_Adminpath']) > 0) {
			$out = '
			<a href="edit.php?id='.page::menuID().'&element='.$Data['tap_ID'].'&teaser='.$nTeaserID.'">
			<img src="/images/icons/bullet_wrench.png" title="'.$this->Res->html(423,page::language()).'" alt="'.$this->Res->html(423,page::language()).'" border="0"></a>
			';
		} else {
			$out = '
			<img src="/images/icons/bullet_wrench_disabled.png" title="'.$this->Res->html(424,page::language()).'" alt="'.$this->Res->html(424,page::language()).'" border="0"></a>
			';
		}
		// Wenn es importiert ist, anderen Text zeigen (ausgegrautes Icon)
		if ($isImported == 1) {
			$out = '
			<img src="/images/icons/bullet_wrench_disabled.png" title="'.$this->Res->html(780,page::language()).'" alt="'.$this->Res->html(780,page::language()).'" border="0"></a>
			';
		}
		return($out);
	}
	
	// Gibt die Options für Teasertypen zurück
	public function getTypeOptions($nType) {
		$out = '';
		foreach ($this->TeaserTypes as $Type) {
			$out .= '<option value="'.$Type['tty_ID'].'"'.checkDropdown($nType,$Type['tty_ID']).'>'.$Type['tty_Name'].'</option>'."\n";
		}
		return($out);
	}
	
	// Prüft ob Zugriff auf eine Teasersection vorhanden ist und leitet
	// zu der Errorseite weiter, wenn nicht
	public function checkSectionAccess($nTeaserID) {
		$sSQL = "SELECT COUNT(tas_ID) FROM tbteasersection
		WHERE tas_ID = $nTeaserID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult != 1) {
			logging::error('teaser section access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Prüft das Teaserelement existiert und dem
	// aktuellen Mandant gehört
	public function checkElementAccess($nTapID) {
		$sSQL = "SELECT COUNT(tap_ID) FROM tbteaser
		WHERE tap_ID = $nTapID AND man_ID = ".page::mandant();
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult != 1) {
			logging::error('teaser element access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Prüfen ob Element und Teaser zusammen gehören
	public function checkSectionElementMatch($nTasID,$nTapID) {
		$sSQL = "SELECT COUNT(tas_ID) FROM tbteasersection_teaser
		WHERE tap_ID = $nTapID AND tas_ID = $nTasID";
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult != 1) {
			logging::error('invalid section / element match');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Macht ein Redirect auf den Admin eines Teaser elements
	// oder zurück zur aufrufenden Teaserverwaltung mit Error
	public function doAdminRedirect($nTapID,$nTasID) {
		$TeaserTypes = array();
		$this->loadTeaserTypes($TeaserTypes);
		// Typen ID holen
		$sSQL = "SELECT tty_ID FROM tbteaser WHERE tap_ID = $nTapID";
		$nType = getInt($this->Conn->getFirstResult($sSQL));
		// Adminpfad holen
		$sAdminpath = '';
		foreach ($TeaserTypes as $Type) {
			if ($Type['tty_ID'] == $nType) {
				$sAdminpath = $Type['tty_Adminpath'];
			}
		}
		// Redirect zum Admin wenn möglich
		if (strlen($sAdminpath) > 0) {
			$_SESSION['teaserBackID'] = $nTasID;
			session_write_close();
			redirect('location: '.$sAdminpath.'?id='.page::menuID().'&element='.$nTapID);
		} else {
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTasID);
		}
	}
	
	// HTML Code für Flying Window zum Element Import zurückgeben/generieren
	public function getImportWindowHtml() {
		$nTeaser = getInt($_GET['teaser']);
		$out = '<div style="overflow:auto;width:390px;height:290px;padding:5px;">';
		// Gruppiert alle Teaser / Elemente anzeigen
		$sSQL = "SELECT tbteasersection.tas_ID, tbteasersection.tas_Desc,
		tbteaser.tap_ID, tbteaser.tap_Title FROM tbteasersection INNER JOIN tbteasersection_teaser 
		ON tbteasersection_teaser.tas_ID = tbteasersection.tas_ID INNER JOIN tbteaser 
		ON tbteaser.tap_ID = tbteasersection_teaser.tap_ID
		WHERE tbteaser.man_ID = ".page::mandant()." AND tbteasersection.tas_ID != $nTeaser
		AND tbteasersection_teaser.tsa_Imported = 0
		ORDER BY tbteasersection.tas_Desc ASC, tbteasersection_teaser.tsa_Sortorder ASC";
		// Div Beenden und zurückgeben
		$nLastID = 0;
		$TabRow = new tabRowExtender();
		$nRes = $this->Conn->execute($sSQL);
		$nID = page::menuID();
		$nTeaserID = getInt($_GET['teaser']);
		while ($row = $this->Conn->next($nRes)) {
			// Neue Gruppe beginnen wenn ID anders
			if ($nLastID != $row['tas_ID']) {
				// Wenn Letzte ID grösser als 0, letzte Tabelle schliessen
				if ($nLastID > 0) $out .= '</table><br>';
				// Neue Tabelle beginnen
				$out .= '
				<table width="95%" align="center" cellpadding="3" cellspacing="0" border="0">
				<tr class="tabRowHead">
					<td width="20">&nbsp;</td>
					<td><strong>'.$row['tas_Desc'].'</strong></td>
				</tr>
				';
				// ID Speichern
				$nLastID = $row['tas_ID'];
			}
			// Zeile für Teaserelement anhängen
			$sTapJava = str_replace("'","\'",$row['tap_Title']);
			$sMessage = $this->Res->javascript(779,page::language());
			$sMessage = str_replace('{0}',$sTapJava,$sMessage);
			$sMessage = str_replace('{nl}','\n',$sMessage);
			$out .= '
			<tr class="'.$TabRow->get().'">
				<td width="20">
					<a href="#" onclick="javascript:confirmImport(\''.$sMessage.'\','.$nID.','.$nTeaserID.','.$row['tap_ID'].');">
					<img src="/images/icons/basket_put.png" alt="'.$this->Res->html(777,page::language()).'" title="'.$this->Res->html(777,page::language()).'" border="0"></a>
				</td>
				<td>'.$row['tap_Title'].'</td>
			</tr>
			';
		}
		// Meldung, wenn nichts gefunden wurde
		if ($nLastID == 0) {
			$out .= $this->Res->html(801,page::language());
		}
		$out .= '</div>';
		return($out);
	}
	
	// Ein Teaserelement importieren
	public function importTeaserElement($nTeaserID) {
		$nImport = getInt($_GET['import']);
		// Schauen ob das Element existiert und dem User gehört
		$sSQL = "SELECT COUNT(tap_ID) FROM tbteaser
		WHERE man_ID = ".page::mandant()." AND tap_ID = $nImport";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Ausführen oder Fehler melden
		if ($nResult == 1) {
			// Row in der Verbindungstabelle erstellen
			$sSQL = "INSERT INTO tbteasersection_teaser 
			(tas_ID,tap_ID,tsa_Sortorder,tsa_Active,tsa_Imported) 
			VALUES ($nTeaserID,$nImport,1,0,1)";
			$this->Conn->command($sSQL);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		} else {
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		}
	}
	
	// Löschtext / Löschfunktion anpassen, wenn Importier/Nicht Importiert
	public function getTeaserDeleteHtml($row) {
		$nTeaserID = getInt($_GET['teaser']);
		if ($row['tsa_Imported'] == 0) {
			$out .= '
			<a href="javascript:deleteConfirm(\'elements.php?id='.page::menuID().'&teaser='.$nTeaserID.'&delete='.$row['tap_ID'].'\',\''.addslashes($row['tap_Title']).'\','.page::language().')">
			<img src="/images/icons/bullet_delete.png" title="'.$this->Res->html(429,page::language()).'" alt="'.$this->Res->html(429,page::language()).'" border="0"></a>
			';
		} else {
			$sMessage = $this->Res->javascript(781,page::language());
			$sMessage = str_replace('{0}',addslashes($row['tap_Title']),$sMessage);
			$sMessage = str_replace('{nl}','\n',$sMessage);
			$out .= '
			<a href="javascript:deleteImportedConfirm(\'elements.php?id='.page::menuID().'&teaser='.$nTeaserID.'&deleteimported='.$row['tap_ID'].'\',\''.$sMessage.'\')">
			<img src="/images/icons/bullet_delete.png" title="'.$this->Res->html(429,page::language()).'" alt="'.$this->Res->html(429,page::language()).'" border="0"></a>
			';
		}
		return($out);
	}
	
	// Importiertes Teaser Element dereferenzieren
	public function deleteImportedTeaserElement($nTeaserID) {
		$nDelete = getInt($_GET['deleteimported']);
		// Prüfen, ob es importiert ist dem Teaser
		// angehört und der Mandant der Besitzer ist
		$sSQL = "SELECT COUNT(tsa_ID) FROM tbteasersection_teaser
		WHERE tas_ID = $nTeaserID AND tap_ID = $nDelete AND tsa_Imported = 1";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Ausführen oder Fehler melden
		if ($nResult == 1) {
			// Row in der Verbindungstabelle erstellen
			$sSQL = "DELETE FROM tbteasersection_teaser
			WHERE tas_ID = $nTeaserID AND tap_ID = $nDelete AND tsa_Imported = 1";
			$this->Conn->command($sSQL);
			// Erfolg melden und Weiterleiten
			logging::debug('delete imported teaser element');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		} else {
			// Erfolg melden und Weiterleiten
			logging::error('error deleting imported teaser element');
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/teaser/elements.php?id='.page::menuID().'&teaser='.$nTeaserID);
		};
	}
	
	// Zählt die Verwendungen eines Teasers
	private function getUseCount($nTeaserID) {
		$sSQL = "SELECT COUNT(mnu_ID) AS CountResult FROM tbmenu 
		WHERE tas_ID = ".getInt($nTeaserID)." AND man_ID = ".page::mandant();
		return($this->Conn->getCountResult($sSQL));
	}
	
	// Teasertypen in Row matchen
	private function matchTypes(&$row,&$Types) {
		// Alle Typen durchgehen
		foreach ($Types as $Type) {
			// Prüfen ob Match zwischen Element / Type
			if ($row['tty_ID'] == $Type['tty_ID']) {
				$row['tty_Adminpath'] = $Type['tty_Adminpath'];
				$row['tty_Name'] = $Type['tty_Name'];
				break; // Loop verlassen
			}
		}
	}
	
	// Lädt alle Instanz Teasertypen
	private function loadTeaserTypes(&$TeaserTypes) {
		// Alle Teasertypen der Webseite laden
		$sSQL = "SELECT tty_ID,tty_Name,tty_Adminpath FROM tbteasertyp
		WHERE page_ID = ".page::ID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($TeaserTypes,$row);
		}
		// Alle globalen Teasertypenladen
		$this->Conn->setGlobalDB();
		$sSQL = "SELECT tty_ID,tty_Name,tty_Adminpath FROM tbteasertyp";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($TeaserTypes,$row);
		}
		$this->Conn->setInstanceDB();
	}
	
	// Prüfen ob Typ ID korrekt ist
	private function validateTypeID($Type) {
		$isGlobal = false;
		$isAllowed = false;
		// Prüfen ob der Typ global ist
		$this->Conn->setGlobalDB();
		$sSQL = "SELECT COUNT(tty_ID) FROM tbteasertyp
		WHERE tty_ID = $Type";
		if ($this->Conn->getCountResult($sSQL) == 1) {
			$isGlobal = true;
			$isAllowed = true;
		}
		$this->Conn->setInstanceDB();
		// Wenn nicht global, Instanz prüfen
		if (!$isGlobal) {
			$sSQL = "SELECT COUNT(tty_ID) FROM tbteasertyp
			WHERE tty_ID = $Type AND page_ID = ".page::ID();
			// Erlauben, wenn Resultat = 1
			if ($this->Conn->getCountResult($sSQL) == 1) {
				$isAllowed = true;
			}
		}
		// Wenn nicht erlaubt, 100 als Typ nehmen (Content)
		if (!$isAllowed) $Type = typeID::TEASER_CONTENT;
		return($Type);
	}
}