<?php
class moduleComment extends commonModule {
	
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
	
	// Kommentar hinzufügen
	public function saveEntry(&$sComName,&$sComContent) {
		$Errors = array();
		$nConID = getInt($_GET['entry']);
		$nBlogID = getInt($_GET['blog']);
		// Captcha prüfen wenn nötig
		$this->validateCaptcha($Errors);
		// Spam prüfen (dummy / zeitbedarf)
		$this->validateSpam($Errors,$nConID);
		// Zeitstempel prüfen anhand Zeitsperre
		$this->validateTimelock($Errors);
		// Eintragsdaten holen und validieren
		$sComName = $_POST['comName'];
		$sComContent = $_POST['comContent'];
		$this->validateInput($sComName,$sComContent,$Errors);
		// Kommentar erstellen wenn keine Fehler
		if (count($Errors) == 0) {
			// Zusatzdaten (Zeit, md5(IP)) speichern
			$sTime = dateOps::getTime(dateOps::SQL_DATETIME);
			$IP = md5($_SERVER['REMOTE_ADDR']);
			$nActive = $this->getActiveState();
			// Eintrag erstellen, je nach Einstellung aktiv/inaktiv
			$sSQL = "INSERT INTO tbkommentar (owner_ID,com_Active,com_Time,
			com_Name,com_IP,com_Content) VALUES
			($nConID,$nActive,'$sTime','$sComName','$IP','$sComContent')";
			$this->Conn->command($sSQL);
			// Erfolg speichern und zurück zum index
			// Meldung je nachdem ob aktiv/inaktiv
			logging::info('added blog comment');
			$this->setErrorSession($this->getErrorMessage($nActive));
			session_write_close();
			redirect('location: /modules/blog/entry.php?id='.page::menuID().'&blog='.$nBlogID.'&entry='.$nConID);
		} else {
			// Zurück zum erstellen des Eintrages
			$this->setErrorSession($Errors);
		}
	}
	
	// Holt den Captcha HTML Code für die Kommentare
	public function getCaptchaCode() {
		$out = '
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
		return($out);
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
	private function validateCaptcha(&$Errors) {
		// Nur prüfen wenn Captcha eingeschaltet
		if ($_SESSION['captchaCode'] != $_POST['captchaCode']) {
			array_push($Errors,$this->Res->html(351,page::language()));
		}
	}
	
	// Validiert den eingegebenen Captcha Code
	private function validateSpam(&$Errors,$nConID) {
		$bSpam = false;
		// Prüfen auf Zeit, 10 Sekunden
		$nTimeNow = time();
		$nTimeThen = $_SESSION['comment_'.page::menuID()]['entry_'.$nConID];
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
	private function validateTimelock(&$Errors) {
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
			if ($nDiff <= 180) {
				array_push($Errors,$this->Res->html(353,page::language()));
			}
		}
	}
	
	// Herausfinden ob der Post gleich aktiviert wird
	private function getActiveState() {
		$nActive = 1;
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