<?php
// Library für Teasercontent Elemente
class moduleTeaserentry extends commonModule {
	
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
			logging::debug('teaser entry access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Teasereintrag speichern
	public function saveEntry() {
		// Daten holen
		$Errors = array();
		$nTapID = getInt($_GET['element']);
		$sContent = stringOps::getPostEscaped('content',$this->Conn);
		stringOps::htmlEntRev($sContent);
		$nMenuID = $this->validateMenu($Errors);
		// Wenn keine Fehler, ausführen, sonst Meldung
		if (count($Errors) == 0) {
			// Speichern der Daten
			$sSQL = "UPDATE tbteaserentry SET
			ten_Content = '$sContent', mnu_ID = $nMenuID
			WHERE tap_ID = $nTapID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('saved teaser entry');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/teaserentry/index.php?id='.page::menuID().'&element='.$nTapID);
		} else {
			// Errors speichern und weiterleiten
			logging::error('error saving teaser entry');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/teaserentry/index.php?id='.page::menuID().'&element='.$nTapID);
		}
	}
	
	// Teasereintrag aus DB lesen
	public function loadEntry(&$sData) {
		$nTapID = getInt($_GET['element']);
		$sSQL = "SELECT ten_ID,con_ID,ele_ID,IFNULL(mnu_ID,0) AS mnu_ID,
		ten_Date,ten_Content FROM tbteaserentry WHERE tap_ID = $nTapID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$sData = $row;
		}
		// Wenn kein Eintrag, neues Element erstellen
		if ($sData == NULL) {
			$this->newEntry($sData);
		} else {
			// Eventuell nicht vorhandenes Element generieren für Editor
			if ($sData['ele_ID'] == NULL) $this->getElement($sData);
			// Datumsdaten verarbeiten
			$sData['date_date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_DATE,
				$sData['ten_Date']
			);
			$sData['date_time'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_TIME,
				$sData['ten_Date']
			);
		}
	}
	
	// Menu ID validieren / returnieren
	private function validateMenu(&$Errors) {
		$nMenuID = getInt($_POST['menulinkID']);
		// Zugriff prüfen
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE
		mnu_ID = $nMenuID AND man_ID = ".page::mandant();
		$nReturn = $this->Conn->getCountResult($sSQL);
		// Fehler wenn weniger oder mehr als 1 Resultat
		if ($nReturn != 1 && $nMenuID > 0) {
			array_push($Errors,$this->Res->html(435,page::language()));
		}
		return($nMenuID);
	}
	
	// Neuen Eintrag generieren
	private function newEntry(&$sData) {
		// Neuer Entry für die aktuelle TeaserApp, inkl Datum
		$nTapID = getInt($_GET['element']);
		$sSQL = "INSERT INTO tbteaserentry (tap_ID,ten_Date) 
		VALUES ($nTapID,'".dateOps::getTime(dateOps::SQL_DATETIME)."')";
		$this->Conn->command($sSQL);
		// Wieder laden ausführen
		$this->loadEntry($sData);
	}
	
	// Element holen, in DB / Array Eintragen
	private function getElement(&$sData) {
		$nTapID = getInt($_GET['element']);
		// Neues Element einfügen
		$sSQL = "INSERT INTO tbelement (owner_ID) VALUES ($nTapID)";
		$nElementID = $this->Conn->insert($sSQL);
		// Datensatz Teaserentry updaten
		$sSQL = "UPDATE tbteaserentry SET ele_ID = $nElementID
		WHERE ten_ID = ".$sData['ten_ID'];
		$this->Conn->command($sSQL);
		// Datenarray updaten
		$sData['ele_ID'] = $nElementID;
	}
}