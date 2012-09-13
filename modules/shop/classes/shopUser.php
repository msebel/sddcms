<?php
/**
 * Repräsentiert einen Shopuser und beinhaltet dessen Impersonation
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopUser extends abstractRow {

    /**
     * shu_ID: Eindeutige ID
     * @var int
     */
    private $myShuID = 0;
    /**
     * man_ID: Besitzender Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * imp_ID: ID der Impersonation
     * @var int
     */
    private $myImpID = 0;
    /**
     * shu_Billable: 0 = Rechnung nicht erlaubt / 1 = Kann auf Rechnung bestellen
     * @var int
     */
    private $myBillable = 0;
    /**
     * shu_Condition: Rabatt des Users (Default = 0)
     * @var int
     */
    private $myCondition = 0;
    /**
     * shu_Active: 0 = Benutzer deaktivier / 1 = Benutzer kann Shop nutzen
     * @var int
     */
    private $myActive = 0;
	/**
	 * Primäre Rechnungsadresse des Benutzers (Ist nicht immer geladen)
	 * @var shopAddress
	 */
	private $myAddress = NULL;
	/**
	 * Alle Adressen, sortiert nach Primär/ID
	 * @var array shopAddress
	 */
	private $myAddresses = NULL;

    /**
     * Überschreibt den Standardkonstruktor, tut nichts spezielles
     * @param int $nID Zu ladender Datensatz
     */
    public function __construct($nID = 0) {
        parent::__construct($nID);
    }

    /**
     * Lädt den Datensatz ins lokale Objekt
     * @param int $nID ID des Datensatzes
     */
    public function load($nID) {
        $sSQL = 'SELECT shu_ID,man_ID,imp_ID,shu_Billable,shu_Condition,shu_Active
        FROM tbshopuser WHERE shu_ID = '.$nID;
        $nRes = $this->Conn->execute($sSQL);
        if ($row = $this->Conn->next($nRes)) {
            $this->loadRow($row);
        }
    }

    /**
     * Lädt vorhandene Datenzeile ins Objekt
     * @param array $row Datenzeile
     */
    public function loadRow($row) {
        // Alle Objekte mit settern laden
        $this->myShuID = getInt($row['shu_ID']);
        $this->setManID($row['man_ID']);
        $this->setImpID($row['imp_ID']);
        $this->setBillable($row['shu_Billable']);
        $this->setCondition($row['shu_Condition']);
        $this->setActive($row['shu_Active']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopuser SET
        man_ID = $this->myManID,imp_ID = $this->myImpID,
        shu_Billable = $this->myBillable,shu_Condition = $this->myCondition,
        shu_Active = $this->myActive
        WHERE shu_ID = $this->myShuID";
        $this->Conn->command($sSQL);
        return($this->getShuID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
		// User erstellen
        $sSQL = "INSERT INTO tbshopuser (man_ID,imp_ID,shu_Billable,shu_Condition,
        shu_Active) VALUES (
        $this->myManID,$this->myImpID,$this->myBillable,
        $this->myCondition,$this->myActive)";
        $this->myShuID = $this->Conn->insert($sSQL);
        return($this->getShuID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopuser
        WHERE shu_ID = ".$this->getShuID();
        $this->Conn->command($sSQL);
    }

	/**
	 * Erstellt eine neue Impersonation und verknüpft diese mit dem lokalen
	 * User. sendet zudem ein Mail mit dem Passwort an den User
	 * @param string $sEmail E-Mail Adresse der impersonation
	 * @param bool $bLogin true/false ob gleich eingeloggt werden soll
	 */
	public function createImpersonation($sEmail,$bLogin = true) {
		// Passwort generieren
		$sPwd = stringOps::getRandom(8);
		// Impersonation erstellen
		$nImpID = impersonation::addUser($sEmail,$sPwd,shopConfig::UserID(), $this->Conn);
		// Aktivieren!
		$sSQL = "UPDATE tbimpersonation SET
		imp_Activation = '', imp_Active = 1
		WHERE imp_ID = $nImpID";
		$this->Conn->command($sSQL);
		// Nur weitermachen, wenn der User wirklich erstellt wurde
		if ($nImpID > 0) {
			// Verknüpfen mit konfig. Menu
			impersonation::addConnection($nImpID, shopConfig::LoginMenuID(), $this->Conn);
			// Lokal die neue ID zuweisen
			$this->setImpID($nImpID);
			// Mail an den User absetzen
			$this->sendLoginInfo($sEmail,$sPwd);
			// Wenn gewünscht, einloggen
			if ($bLogin) {
				impersonation::login($sEmail, $sPwd, shopConfig::LoginMenuID(), $this->Conn, false);
			}
			// True melden, Impersonation wurde erfolgreich erstellt
			return(true);
		}
		// Wenn wir bis hier kommen war es nicht erfolgreich, false melden
		return(false);
	}

	/**
	 * Verbindet den User mit der gegebenen Adresse
	 * @param shopAddress $address Instanz einer Adresse
	 * @param int $type Adresstyp aus Klassenkonstanten
	 * @param int $primary 0/1 ob Primary oder nicht
	 */
	public function addAddress(shopAddress $address, $type, $primary = 0) {
		// Gegebene Daten validieren
		$primary = stringOps::getBoolInt($primary);
		$type = getInt($type);
		switch ($type) {
			case shopAddress::TYPE_BILL:
			case shopAddress::TYPE_DELIVERY:
				// Alles OK
				break;
			default:
				// Ansonsten Rechnung
				$type = shopAddress::TYPE_BILL;
		}
		// Wenn noch keine Verbindung, eine erstellen
		$sSQL = 'SELECT COUNT(sua_ID) FROM tbshopuser_address
		WHERE sad_ID = '.$address->getSadID().' AND sua_Type = '.$type;
		$nResult = $this->Conn->getCountResult($sSQL);
		// Nur etwas erstellen, wenn noch nichts vorhanden ist
		if ($nResult == 0) {
			$sSQL = 'INSERT INTO tbshopuser_address (shu_ID,sad_ID,sua_Type,sua_Primary)
			VALUES ('.$this->getShuID().','.$address->getSadID().','.$type.','.$primary.')';
			$this->Conn->command($sSQL);
		}
	}

	/**
	 * Holt die Primäre Rechnungsadresse. Holt die erste gefundene, falls
	 * aufgrund eines Fehlers zwei Primäre Adressen vorhanden sind
	 * @return shopAddress Instanz einer Adresse oder NULL
	 */
	public function getPrimaryAddress() {
		if ($this->myAddress == NULL) {
			$sSQL = 'SELECT tbshopaddress.sad_ID,man_ID,sad_Title,sad_Firstname,
			sad_Lastname,sad_Street,sad_Email,sad_City,sad_Phone,sad_Zip
			FROM tbshopaddress INNER JOIN tbshopuser_address ON
			tbshopuser_address.sad_ID = tbshopaddress.sad_ID
			WHERE sua_Type = '.shopAddress::TYPE_BILL.' AND sua_Primary = 1
			AND tbshopuser_address.shu_ID = '.$this->getShuID();
			$nRes = $this->Conn->execute($sSQL);
			// Wenn Ergebnisse, diese füllen
			if ($row = $this->Conn->next($nRes)) {
				$this->myAddress = new shopAddress();
				$this->myAddress->loadRow($row);
			}
		}
		// Aktuelle Adresse zurückgeben
		return($this->myAddress);
	}

	/**
	 * Adressen des Users laden und zurückgeben
	 * @return array Array aller Adressen
	 */
	public function getAddresses() {
		if ($this->myAddresses == NULL ) {
			$this->myAddresses = array();
			$sSQL = 'SELECT tbshopaddress.sad_ID,man_ID,sad_Title,sad_Firstname,
			sad_Lastname,sad_Street,sad_Email,sad_City,sad_Phone,sad_Zip,sua_Type,
			sua_Primary	FROM tbshopaddress INNER JOIN tbshopuser_address ON
			tbshopuser_address.sad_ID = tbshopaddress.sad_ID
			WHERE tbshopuser_address.shu_ID = '.$this->getShuID().'
			ORDER BY sua_Primary DESC,sua_ID ASC';
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				$address = new shopAddress();
				$address->loadRow($row);
				// Verbindungsdaten hinzufügen
				$address->setPrimary($row['sua_Primary']);
				$address->setType($row['sua_Type']);
				array_push($this->myAddresses,$address);
			}
		}
		return($this->myAddresses);
	}

	/**
	 * Gibt den Benutzernamen der Impersonation des Shopusers zurück
	 * @return string Benutzername (Meistens E-MAil Adresse)
	 */
	public function getUsername() {
		$sSQL = 'SELECT imp_Alias FROM tbimpersonation WHERE imp_ID = '.$this->getImpID();
		return($this->Conn->getFirstResult($sSQL));
	}

	/**
	 * Gibt den Benutzernamen in gekürtzter Länge zurück
	 * @param int $nSize Gewünschte maximal Grösse
	 * @return string Benutzername (Meistens E-Mail Adresse)
	 */
	public function getUsernameShortened($nSize) {
		return(stringOps::chopString($this->getUsername(), $nSize, true));
	}

	/**
	 * Passwort des Users per sofort ändern (Ohne save!!)
	 * @param string $sPassword Neues Passwort
	 */
	public function setPassword($sPassword) {
		$sSecurity = impersonation::getSecurityById($this->myImpID, $this->Conn);
		$sNewSec = secureString::getSecurityString($sPassword, $this->getUsername());
		$fields = array('imp_Security' => "'$sNewSec'");
		impersonation::changeUser($sSecurity, $fields, $this->Conn);
		$_SESSION['SessionConfig'][shopConfig::LoginMenuID().'_ImpersonationSecurity'] = $sNewSec;
	}

	/**
	 * Sendet per Template System ein UTF-8 Mail mit den aktuellen
	 * Zugangsdaten in der Sprache der gezeigten Webseite
	 * @param string $sEmail Email Adresse / Benutzername
	 * @param string $sPassword Neues Passwort (Plain, kein SecureString)
	 */
	public function sendLoginInfo($sEmail,$sPassword) {
		// Template Pfad und Template laden
		$tPath = shopStatic::getMailTemplate('logininfo');
		$tpl = new templateImproved($tPath);
		// Wichtige Variablen ersetzen
		$tpl->addData('USERNAME',$sEmail);
		$tpl->addData('PASSWORD',$sPassword);
		// Mail Erstellen und konfigurieren
		$mail = new phpMailer();
		$Mail->CharSet = 'utf-8';
		$Mail->Encoding = 'quoted-printable';
		$mail->From = shopModuleConfig::MAIL_FROM;
		$mail->FromName = shopModuleConfig::MAIL_FROMNAME;
		$mail->AddAddress($sEmail);
		// Inhalte definieren
		$mail->IsHTML(true);
		$mail->Body = $tpl->output();
		$mail->Subject = $this->Res->normal(1107, page::language());
		// Absenden
		$mail->Send();
	}

	/**
	 * User Instanz anhand der Impersonation ID laden und zurückgeben.
	 * Achtung, es wird nicht auf aktivität des Kontos geprüft
	 * @param int $nImpID Impersonation ID
	 * @return shopUser User Instanz, sofern es die Impersonation gibt
	 */
	public static function getInstanceByImpersonationID($nImpID) {
		$sSQL = 'SELECT shu_ID FROM tbshopuser WHERE imp_ID = '.$nImpID;
		$nShuID = getInt(singleton::conn()->getFirstResult($sSQL));
		// Wenn grösser als Null scheint es OK
		if ($nShuID > 0) return(new shopUser($nShuID));
		// Wenn wir hier sind, gibts nichts zurück...
		return(NULL);
	}

    /**
     * Getter für shu_ID: Eindeutige ID
     * @return int Wert von 'shu_ID'
     */
    public function getShuID() {
        return($this->myShuID);
    }

    /**
     * Getter für man_ID: Besitzender Mandant
     * @return int Wert von 'man_ID'
     */
    public function getManID() {
        return($this->myManID);
    }

    /**
     * Setter für man_ID: Besitzender Mandant
     * @param int Neuer Wert für 'man_ID'
     */
    public function setManID($value) {
        $value = getInt($value);
        $this->myManID = $value;
    }

    /**
     * Getter für imp_ID: ID der Impersonation
     * @return int Wert von 'imp_ID'
     */
    public function getImpID() {
        return($this->myImpID);
    }

    /**
     * Setter für imp_ID: ID der Impersonation
     * @param int Neuer Wert für 'imp_ID'
     */
    public function setImpID($value) {
        $value = getInt($value);
        $this->myImpID = $value;
    }

    /**
     * Getter für shu_Billable: 0 = Rechnung nicht erlaubt / 1 = Kann auf Rechnung bestellen
     * @return int Wert von 'shu_Billable'
     */
    public function getBillable() {
        return($this->myBillable);
    }

    /**
     * Setter für shu_Billable: 0 = Rechnung nicht erlaubt / 1 = Kann auf Rechnung bestellen
     * @param int Neuer Wert für 'shu_Billable'
     */
    public function setBillable($value) {
        $value = getInt($value);
        $this->myBillable = $value;
    }

    /**
     * Getter für shu_Condition: Rabatt des Users (Default = 0)
     * @return int Wert von 'shu_Condition'
     */
    public function getCondition() {
        return($this->myCondition);
    }

    /**
     * Setter für shu_Condition: Rabatt des Users (Default = 0)
     * @param int Neuer Wert für 'shu_Condition'
     */
    public function setCondition($value) {
        $value = getInt($value);
        $this->myCondition = $value;
    }

    /**
     * Getter für shu_Active: 0 = Benutzer deaktivier / 1 = Benutzer kann Shop nutzen
     * @return int Wert von 'shu_Active'
     */
    public function getActive() {
        return($this->myActive);
    }

    /**
     * Setter für shu_Active: 0 = Benutzer deaktivier / 1 = Benutzer kann Shop nutzen
     * @param int Neuer Wert für 'shu_Active'
     */
    public function setActive($value) {
        $value = getInt($value);
        $this->myActive = $value;
    }
}