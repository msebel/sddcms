<?php
class moduleFaq extends commonModule {
	
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
	
	// Array mit Rows befüllen
	public function loadFaqElements(&$Data) {
		$sSQL = "SELECT faq_ID,faq_Question,faq_Active,faq_Answer FROM tbfaqentry 
		WHERE mnu_ID = ".page::menuID()." ORDER BY faq_Sortorder ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($Data,$row);
		}
	}
	
	// HTML ausgeben, für Antwortstatus
	public function getAnswerHtml($nAnswerID) {
		$out = '&nbsp;&nbsp;';
		switch ($nAnswerID) {
			case 0:
				$out .= '<img src="/images/icons/action_notgo.gif" alt="'.$this->Res->html(452,page::language()).'" title="'.$this->Res->html(452,page::language()).'">';
				break;
			default:
				$out .= '<img src="/images/icons/action_go.gif" alt="'.$this->Res->html(453,page::language()).'" title="'.$this->Res->html(453,page::language()).'">';
				break;
		}
		return($out);
	}
	
	// Neuen FAQ Eintrag erstellen
	public function addFaqEntry() {
		// Fragestellung (default Wert)
		$sFaqDesc = '< '.$this->Res->html(454,page::language()).' >';
		$nNextOrder = $this->getNextOrder();
		// Query absetzen
		$sSQL = "INSERT INTO tbfaqentry (mnu_ID,faq_Answer,faq_Sortorder,faq_Active,faq_Question)
		VALUES (".page::menuID().",0,$nNextOrder,0,'$sFaqDesc')";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/faq/index.php?id='.page::menuID());
	}
	
	// FAQ Eintrag löschen
	public function deleteFaqEntry() {
		// Nur wenn Menu / Eintrag zusammen gehören
		$nFaqID = getInt($_GET['delete']);
		if ($this->checkAccess($nFaqID)) {
			// Eintrag löschen
			$sSQL = "DELETE FROM tbfaqentry WHERE faq_ID = $nFaqID";
			$this->Conn->command($sSQL);
			$sError = $this->Res->html(146,page::language());
			logging::debug('deleted faq entry');
		} else {
			$sError = $this->Res->html(55,page::language());
			logging::error('error deleting faq entry');
		}
		// Meldung erstellen und Weiterleiten
		$this->setErrorSession($sError);
		session_write_close();
		redirect('location: /admin/faq/index.php?id='.page::menuID());
	}
	
	// FAQ Einträge (in der übersicht) speichern
	public function saveFaqs() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nFaqID = getInt($_POST['id'][$i]);
			$nSort = getInt($_POST['sort'][$i]);
			$nActive = getInt($_POST['active_'.$nFaqID]);
			if ($nActive != 1) $nActive = 0;
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbfaqentry SET faq_Sortorder = $nSort,
			faq_Active = $nActive WHERE faq_ID = $nFaqID";
			$this->Conn->command($sSQL);
			// Aktivflag auch im Answer Content angeben
			$sSQL = "SELECT faq_Answer FROM tbfaqentry WHERE faq_ID = $nFaqID";
			$nFaqAnswer = $this->Conn->getFirstResult($sSQL);
			if (getInt($nFaqAnswer) > 0) {
				$sSQL = "UPDATE tbcontent 
				SET con_Active = $nActive
				WHERE con_ID = $nFaqAnswer";
				$this->Conn->command($sSQL);
			}
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved faq entries');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/faq/index.php?id='.page::menuID());
	}
	
	// FAQ Eintrag speichern
	public function saveFaq(&$Data) {
		// Erstmal alle Daten holen (Aktivflag)
		$nActive = getInt($_POST['faqActive']);
		if ($nActive != 1) $nActive = 0;
		// Fragestellung
		$sQuestion = stringOps::getPostEscaped('faqQuestion',$this->Conn);
		stringOps::noHtml($sQuestion);
		if (strlen($sQuestion) == 0) {
			$sQuestion = '< '.$this->Res->html(454,page::language()).' >';
		}
		// Antwort
		$sAnswer = stringOps::getPostEscaped('conContent',$this->Conn);
		stringOps::htmlEntRev($sAnswer);
		// Query Abfeuern für FAQ eintrag
		$sSQL = "UPDATE tbfaqentry SET
		faq_Question = '$sQuestion', faq_Active = $nActive
		WHERE faq_ID = ".$Data['faq_ID'];
		$this->Conn->command($sSQL);
		// Query für den Antwort Content abfeuern
		$sSQL = "UPDATE tbcontent SET 
		con_Content = '$sAnswer', con_active = $nActive
		WHERE con_ID = ".$Data['faq_Answer'];
		$this->Conn->command($sSQL);
		// Erfolg melden und weiterleiten
		logging::debug('saved faq entry');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/faq/edit.php?id='.page::menuID().'&entry='.$Data['faq_ID']); 
	}
	
	// FAQ Daten holen
	public function loadData(&$Data) {
		// ID des FAQ holen
		$nFaqID = getInt($_GET['entry']);
		// Herkömmliche FAQ Daten holen
		$sSQL = "SELECT faq_Answer,faq_Active,faq_Question
		FROM tbfaqentry WHERE faq_ID = $nFaqID";
		// Daten in das Array packen
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
		}
		// FAQ ID Anfügen
		$Data['faq_ID'] = $nFaqID;
		// Zusätzliche Daten für Antwort holen
		$this->loadAnswerContent($Data);
	}
	
	// Konfiguration initialisieren / laden
	public function loadConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,3)) {
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'displayNumeration',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'showUnexpanded',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig () {
		$nMenuID = page::menuID();
		// Konfiguration laden
		$Config = array();
		pageConfig::get($nMenuID,$this->Conn,$Config);
		// Parameter "Expanded" holen
		$nShow = getInt($_POST['showUnexpanded']);
		if ($nShow != 1) $nShow = 0;
		$Config['showUnexpanded']['Value'] = $nShow;
		// Parameter "Mode" holen
		$Mode = getInt($_POST['displayNumeration']);
		if ($Mode != 1) $Mode = 0;
		$Config['displayNumeration']['Value'] = $Mode;
		// Parameter "htmlCode" holen
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved faq config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/faq/config.php?id='.page::menuID()); 
	}
	
	// Prüft auf Access und leitet auf Fehlerseite weiter
	public function checkAccessRedirect() {
		$nFaqID = getInt($_GET['entry']);
		if (!$this->checkAccess($nFaqID)) {
			logging::error('faq access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Antwort Content laden
	private function loadAnswerContent(&$Data) {
		// Content laden wenn schon vorhanden, sonst erstellen
		if (getInt($Data['faq_Answer']) == 0) {
			// Neuen Content und Element mit Content als Owner
			$nNewContentID = ownerID::get($this->Conn);
			$sSQL = "INSERT INTO tbcontent (con_ID,mnu_ID,usr_ID,con_Content,con_Active)
			VALUES ($nNewContentID,".page::menuID().",".$_SESSION['userid'].",'',".$Data['faq_Active'].")";
			$this->Conn->command($sSQL);
			$sSQL = "INSERT INTO tbelement (owner_ID) VALUES ($nNewContentID)";
			$nElementID = $this->Conn->insert($sSQL);
			// faqEntry Datensatz anpassen
			$sSQL = "UPDATE tbfaqentry SET faq_Answer = $nNewContentID
			WHERE faq_ID = ".$Data['faq_ID'];
			$this->Conn->command($sSQL);
			// Array Daten anpassen
			$Data['faq_Answer'] = $nNewContentID;
			$Data['ele_ID'] = $nElementID;
			$Data['con_Content'] = '';
		} else {
			// Bestehenden Content (faq_Answer = con_ID) und Element ID laden
			$sSQL = "SELECT con_Content FROM tbcontent WHERE con_ID = ".$Data['faq_Answer'];
			$Data['con_Content'] = $this->Conn->getFirstResult($sSQL);
			$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".$Data['faq_Answer'];
			$Data['ele_ID'] = $this->Conn->getFirstResult($sSQL);
		}
	}
		
	// Prüfen ob mnu_ID und faq_ID passen
	private function checkAccess($nFaqID) {
		$bState = false;
		$sSQL = "SELECT COUNT(faq_ID) FROM tbfaqentry
		WHERE mnu_ID = ".page::menuID()." AND faq_ID = $nFaqID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn ein Resultat, Zugriff gewährt
		if ($nResult == 1) $bState = true;
		return($bState);
	}
	
	// Nächsten sortorder für FAQ / Menu Kombi holen
	private function getNextOrder() {
		$sSQL = "SELECT MAX(faq_Sortorder) FROM tbfaqentry
		WHERE mnu_ID = ".page::menuID();
		$nNextOrder = $this->Conn->getFirstResult($sSQL);
		return(getInt($nNextOrder));
	}
}