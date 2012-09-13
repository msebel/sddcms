<?php
class moduleGuestbook extends commonModule {
	
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
	
	public function LoadData(&$GBData, &$GBConfig) {
		// SQL erstellen
		$sSQL = "SELECT com_Time,com_Name,com_Content FROM tbkommentar
		WHERE owner_ID = ".page::menuID()." AND com_Active = 1 ORDER BY com_ID DESC";
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		$PagingEngine->start($sSQL,getInt($GBConfig['postsPerPage']['Value']));
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			array_push($GBData,$row);
		}
		// Paging Engine HTML zurückgeben
		return($PagingEngine->getHtml());
	}
	
	// Ansicht für User
	public function showCommentsView(&$GBData, &$out) {
		// TabRowExtender für Gästebucheinträge
		$GBTabRow = new tabRowExtender('forumRowOdd','forumRowEven');
		$out .= '<table width="100%" cellpadding="5" cellspacing="1" class="forumTable">';
		foreach ($GBData as $Post) {
			// Zeilenklasse herausfinden
			$sClass = $GBTabRow->getSpecial();
			// Zeit und Datum herausfinden
			$nStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Post['com_Time']);
			$sDate = dateOps::getTime(dateOps::EU_DATE,$nStamp);
			$nTime = dateOps::getTime(dateOps::EU_CLOCK,$nStamp);
			// Texte formatierten
			$Post['com_Name'] = stringOps::chopString($Post['com_Name'],20,true);
			stringOps::htmlViewEnt($Post['com_Name']);
			stringOps::htmlViewEnt($Post['com_Content']);
			// Post anzeigen
			$out .= '
			<tr class="'.$sClass.'">
				<td width="20%" valign="top">
					'.$this->Res->html(326,page::language()).' 
					'.$Post['com_Name'].'<br>
					<br>
					<em>
					'.$sDate.' <br>
					'.$this->Res->html(327,page::language()).'
					'.$nTime.'
					</em>
					<br>
				</td>
				<td valign="top">
					'.$Post['com_Content'].'
				</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
	
	// Gästebucheintrag hinzufügen
	public function saveEntry(&$GBConfig,&$sComName,&$sComContent) {
		$Errors = array();
		// Captcha prüfen wenn nötig
		$this->validateCaptcha($GBConfig,$Errors);
		// Spam prüfen (dummy / zeitbedarf)
		$this->validateSpam($GBConfig,$Errors);
		// Zeitstempel prüfen anhand Zeitsperre
		$this->validateTimelock($GBConfig,$Errors);
		// Eintragsdaten holen und validieren
		$sComName = $_POST['comName'];
		$sComContent = $_POST['comContent'];
		$this->validateInput($sComName,$sComContent,$Errors);
		// Gästebucheintrag erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			// Zusatzdaten (Zeit, md5(IP)) speichern
			$sTime = dateOps::getTime(dateOps::SQL_DATETIME);
			$IP = md5($_SERVER['REMOTE_ADDR']);
			$nActive = $this->getActiveState($GBConfig);
			// Eintrag erstellen, je nach Einstellung aktiv/inaktiv
			$sSQL = "INSERT INTO tbkommentar (owner_ID,com_Active,com_Time,
			com_Name,com_IP,com_Content) VALUES
			(".page::menuID().",$nActive,'$sTime','$sComName','$IP','$sComContent')";
			$this->Conn->command($sSQL);
			// Benachrichtigung senden wenn erwünscht
			$this->sendNotification($GBConfig);
			// Erfolg speichern und zurück zum index
			// Meldung je nachdem ob aktiv/inaktiv
			logging::info('added guestbook entry');
			$this->setErrorSession($this->getErrorMessage($nActive));
			session_write_close();
			redirect('location: index.php?id='.page::menuID());
		} else {
			// Zurück zum erstellen des Eintrages
			$this->setErrorSession($Errors);
		}
	}
	
	// Holt den Captcha HTML Code für das Gästebuch, wenn die
	// entsprechende Option eingeschaltet ist
	public function getCaptchaCode(&$GBConfig) {
		$out = '';
		if ($GBConfig['useCaptcha']['Value'] == 1) {
			$out .= '
			<tr>
				<td>'.$this->Res->html(350,page::language()).'</td>
				<td>
					<div style="float:left;height:40px;width:160px;">	
						<img src="/scripts/captcha/code.php" style="border:1px solid #bbb;" id="captchaImage">
					</div>
					<div style="float:left;height:20px;width:140px;">	
						<input type="text" style="width:100px;" name="captchaCode"> 
						<img src="/images/icons/arrow_refresh_small.png" onClick="captchaReload()">
					</div>
				</td>
			</tr>
			';
		}
		return($out);
	}
	
	// Benachrichtigungsmail senden
	private function sendNotification(&$GBConfig) {
		$sEmail = $GBConfig['emailAddress']['Value'];
		if (stringOps::checkEmail($sEmail)) {
			// Email Objekt erstellen, Adresse anhängen und Betreff definieren
			$Mail = new phpMailer();
			$Mail->AddAddress($sEmail);
			$Mail->Subject = $this->Res->normal(357,page::language()).page::domain();
			// Inhalt des Mails definieren
			$sMessage = '';
			// Anrede, Body, Schluss, zusammensetzen
			$sMessage .= $this->Res->normal(204,page::language())."\n\n";
			$sMessage .= $this->Res->normal(358,page::language())."\n\n";
			$sMessage .= $this->Res->normal(359,page::language())."\n";
			$sMessage .= 'sddCMS v'.VERSION;
			$Mail->Body = $sMessage;
			// Email abesenden
			$Mail->send();
		}
	}
	
	// Erfolgstext für Eintrag
	private function getErrorMessage($nActive) {
		$sError = $this->Res->html(354,page::language());
		if ($nActive == 0) {
			$sError = $this->Res->html(355,page::language());
		}
		return($sError);
	}
	
	// Validiert den eingegebenen Captcha Code
	private function validateCaptcha(&$GBConfig,&$Errors) {
		// Nur prüfen wenn Captcha eingeschaltet
		if ($GBConfig['useCaptcha']['Value'] == 1) {
			if ($_SESSION['captchaCode'] != $_POST['captchaCode']) {
				array_push($Errors,$this->Res->html(351,page::language()));
			}
		}
	}
	
	// Validiert den eingegebenen Captcha Code
	private function validateSpam(&$GBConfig,&$Errors) {
		$bSpam = false;
		// Prüfen auf Zeit, 10 Sekunden
		$nTimeNow = time();
		$nTimeThen = $_SESSION['gb_'.page::menuID()];
		$nDifference = $nTimeNow - $nTimeThen;
		if ($nDifference <= 4) {
			$bSpam = true;
		}
		// Prüfen ob das Dömmi Field ausgefüllt ist
		if (strlen($_POST['comAdditional']) > 0) {
			$bSpam = true;
		}
		// Wenn Spam, Meldung setzen
		if ($bSpam == true) {
			array_push($Errors,$this->Res->html(352,page::language()));
		}
	}
	
	// Schaut ob der Besucher wegen Timelock nicht posten darf
	private function validateTimelock(&$GBConfig,&$Errors) {
		// IP des Users holen
		$IP = md5($_SERVER['REMOTE_ADDR']);
		// Letzte Postzeit holen
		$sSQL = "SELECT com_Time FROM tbkommentar WHERE com_IP = '$IP'
		AND owner_ID = ".page::menuID()." ORDER BY com_ID DESC LIMIT 0,1";
		$nLasttime = $this->Conn->getFirstResult($sSQL);
		// Wenn Ergebniss != NULL ist prüfen ob Postzeit überschritten
		if ($nLasttime != NULL) {
			$nThistime = time();
			$nLasttime = dateOps::getStamp(dateOps::SQL_DATETIME,$nLasttime);
			$nDiff = $nThistime - $nLasttime;
			// Wenn die Zeitdifferenz kleiner ist als Konfiguriert
			// darf der user nicht posten
			if ($nDiff <= $GBConfig['SpamLockSecs']['Value']) {
				array_push($Errors,$this->Res->html(353,page::language()));
			}
		}
	}
	
	// Herausfinden ob der Post gleich aktiviert wird
	private function getActiveState(&$GBConfig) {
		$nActive = 1;
		if ($GBConfig['activationNeeded']['Value'] == 1) {
			$nActive = 0;
		}
		return($nActive);
	}
	
	// Schauen ob der Input korrekt ist
	private function validateInput(&$sComName,&$sComContent,&$Errors) {
		$bError = false;
		// Eingaben validieren
		stringOps::noHtml($sComName);		
		$this->Conn->escape($sComName);
		stringOps::noHtml($sComContent);	
		$sComContent = nl2br($sComContent);
		$this->Conn->escape($sComContent);
		// Prüfen ob länge ok ist
		if (strlen($sComName) == 0) $bError = true;
		if (strlen($sComContent) == 0) $bError = true;
		// Wenn Fehler, einfüllen
		if ($bError == true) {
			array_push($Errors,$this->Res->html(356,page::language()));
		}
	}
}