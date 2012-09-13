<?php
// addressLib.php
// Handelt einige Funktionen im
// Zusammenhang mit Adressen
class moduleAddress extends commonModule {
	
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
	
	// Addressdaten holen
	public function loadData() {
		$nUserID = getInt($_GET['user']);
		// Adress ID bekommen, erstellt eine neue Adresse
		// sofern noch keine vorhanden ist für den User
		$nAdrID = $this->getAddressID($nUserID);
		// Daten auslesen
		$sSQL = "SELECT adr_Gender,adr_Addition,adr_Title,adr_Firstname,adr_Lastname,
		adr_Street,adr_Email,adr_City,adr_Phone,adr_Mobile,adr_Postbox,adr_Zip
		FROM tbaddress WHERE adr_ID = $nAdrID";
		$nRes = $this->Conn->execute($sSQL);
		$AddrData = '';
		while ($row = $this->Conn->next($nRes)) {
			$AddrData = $row;
		}
		return($AddrData);
	}
	
	// Benutzer speichern
	public function saveAddress() {
		// Benutzer ID abfragen
		$nUserID = getInt($_GET['user']);
		// Adress ID bekommen, erstellt eine neue Adresse
		// sofern noch keine vorhanden ist für den User
		$nAdrID = $this->getAddressID($nUserID);
		
		// Bei den Eingaben kann man nix falsch machen, aber
		// alle Eingaben werden Escaped
		$sFirstname = stringOps::getPostEscaped('firstname',$this->Conn);
		$sLastname 	= stringOps::getPostEscaped('lastname',$this->Conn);
		$sTitle 	= stringOps::getPostEscaped('title',$this->Conn);
		$sZip 		= stringOps::getPostEscaped('zip',$this->Conn);
		$sCity 		= stringOps::getPostEscaped('city',$this->Conn);
		$sStreet 	= stringOps::getPostEscaped('street',$this->Conn);
		$sPostbox 	= stringOps::getPostEscaped('postbox',$this->Conn);
		$sEmail 	= stringOps::getPostEscaped('email',$this->Conn);
		$sPhone 	= stringOps::getPostEscaped('phone',$this->Conn);
		$sMobile 	= stringOps::getPostEscaped('mobile',$this->Conn);
		$sAddition 	= stringOps::getPostEscaped('addition',$this->Conn);
		$nGender	= $this->validateGender();
		// HTML aller Eingaben entfernen
		stringOps::noHtml($sFirstname);	stringOps::noHtml($sLastname);
		stringOps::noHtml($sTitle);		stringOps::noHtml($sZip);
		stringOps::noHtml($sStreet);	stringOps::noHtml($sCity);
		stringOps::noHtml($sPostbox);	stringOps::noHtml($sEmail);
		stringOps::noHtml($sPhone);		stringOps::noHtml($sMobile);
		stringOps::noHtml($sAddition);
		
		// Email Adresse leeren wenn sie nicht OK ist
		if (stringOps::checkEmail($sEmail) == false) $sEmail = '';
		
		// Daten der Adresse updaten
		$sSQL = "UPDATE tbaddress SET
		adr_Firstname = '$sFirstname', adr_Lastname = '$sLastname', 
		adr_Title = '$sTitle', adr_Zip = '$sZip', 
		adr_City = '$sCity', adr_Street = '$sStreet', 
		adr_Postbox = '$sPostbox', adr_Email = '$sEmail', 
		adr_Addition = '$sAddition', adr_Gender = $nGender, 
		adr_Phone = '$sPhone', adr_Mobile = '$sMobile'
		WHERE adr_ID = $nAdrID";
		$this->Conn->command($sSQL);
		// Erfolg ausgeben und zur Adresseite zurück
		logging::debug('saved address');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/user/address.php?id='.page::menuID().'&user='.$nUserID);
	}
	
	// Adress ID eines Benutzer holen, oder eine neue erstellen
	// und deren ID zurückgeben, wenn noch keine existiert
	public function getAddressID($nUserID) {
		// Aktuelle adressen ID lesen
		$sSQL = "SELECT adr_ID FROM tbuser WHERE usr_ID = $nUserID";
		$nAdrID = $this->Conn->getFirstResult($sSQL);
		// Wenn die Adresse NULL ist, neue generieren
		if ($nAdrID == NULL) {
			// Neue Adresse erstellen
			$nAdrID = $this->getNewAddress();
			// Dem Benutzer die Adresse zuweisen
			$this->setUserAddress($nUserID,$nAdrID);
		}
		// Die Adressen ID zurückgeben
		return($nAdrID);
	}
	
	// Neue Adresse einfügen und Adress ID zurückgeben
	public function getNewAddress() {
		// SQL Statement fügt eine leere Adresse ein
		$sSQL = "INSERT INTO tbaddress (adr_ID) VALUES (NULL)";
		$nAdrID = $this->Conn->insert($sSQL);
		return($nAdrID);
	}
	
	// Einem Benutzer eine Adresse zuweisen
	public function setUserAddress($nUserID,$nAdrID) {
		// SQL Statement erstellen und abfeuern
		$sSQL = "UPDATE tbuser SET adr_ID = $nAdrID WHERE usr_ID = $nUserID";
		$this->Conn->command($sSQL);
		logging::debug('matched address to user');
	}
	
	private function validateGender() {
		$nGender = getInt($_POST['gender']);
		// Nur 0 - 3 möglich, sonst 0
		if ($nGender < 0 || $nGender > 3) {
			$nGender = 0;
		}
		return($nGender);
	}
}