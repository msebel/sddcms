<?php
/**
 * Adresse eines shop Benutzers
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopAddress extends abstractRow {

    /**
     * sad_ID: Eindeutige ID
     * @var int
     */
    private $mySadID = 0;
    /**
     * man_ID: Besitzender Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * sad_Title: Anrede
     * @var string
     */
    private $myTitle = '';
    /**
     * sad_Firstname: Vorname
     * @var string
     */
    private $myFirstname = '';
    /**
     * sad_Lastname: Nachname
     * @var string
     */
    private $myLastname = '';
    /**
     * sad_Street: Strasse
     * @var string
     */
    private $myStreet = '';
    /**
     * sad_Email: Email Adresse
     * @var string
     */
    private $myEmail = '';
    /**
     * sad_City: Stadt
     * @var string
     */
    private $myCity = '';
    /**
     * sad_Phone: Telefonnummer
     * @var string
     */
    private $myPhone = '';
    /**
     * sad_Zip: Postleitzahl
     * @var string
     */
    private $myZip = '';
	/**
	 * Adresstyp (Rechnung- oder Lieferadresse)
	 * @var int
	 */
	private $myType = 0;
	/**
	 * Gibt an, ob die Adresse aktuell Primär ist
	 * @var int
	 */
	private $myPrimary = 0;
	/**
	 * Adresstyp: Rechnungsadresse
	 * @var int
	 */
	const TYPE_BILL = 1;
	/**
	 * Adresstyp: Lieferadresse
	 * @var int
	 */
	const TYPE_DELIVERY = 2;

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
        $sSQL = 'SELECT sad_ID,man_ID,sad_Title,sad_Firstname,sad_Lastname,
        sad_Street,sad_Email,sad_City,sad_Phone,sad_Zip
        FROM tbshopaddress
        WHERE sad_ID = '.$nID;
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
        $this->mySadID = getInt($row['sad_ID']);
        $this->setManID($row['man_ID']);
        $this->setTitle($row['sad_Title']);
        $this->setFirstname($row['sad_Firstname']);
        $this->setLastname($row['sad_Lastname']);
        $this->setStreet($row['sad_Street']);
        $this->setEmail($row['sad_Email']);
        $this->setCity($row['sad_City']);
        $this->setPhone($row['sad_Phone']);
        $this->setZip($row['sad_Zip']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopaddress SET
        man_ID = $this->myManID,sad_Title = '$this->myTitle',
        sad_Firstname = '$this->myFirstname',sad_Lastname = '$this->myLastname',
        sad_Street = '$this->myStreet',sad_Email = '$this->myEmail',
        sad_City = '$this->myCity',sad_Phone = '$this->myPhone',
        sad_Zip = '$this->myZip'
        WHERE sad_ID = $this->mySadID";
        $this->Conn->command($sSQL);
        return($this->getSadID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopaddress (man_ID,sad_Title,sad_Firstname,
        sad_Lastname,sad_Street,sad_Email,sad_City,sad_Phone,sad_Zip) VALUES (
        $this->myManID,'$this->myTitle','$this->myFirstname',
        '$this->myLastname','$this->myStreet','$this->myEmail',
        '$this->myCity','$this->myPhone','$this->myZip')";
        $this->mySadID = $this->Conn->insert($sSQL);
        return($this->getSadID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopaddress
        WHERE sad_ID = ".$this->getSadID();
        $this->Conn->command($sSQL);
    }

	/**
	 * Zusammenfassung der Adresse in bestimmter Länge
	 * @param int $nSize Anzahl Zeichen für Zusammenfassung
	 */
	public function getAbstract($nSize) {
		$abstract = '';
		if (strlen($this->getFirstname()) > 0 || strlen($this->getLastname()) > 0) {
			$abstract .= $this->getFirstname().' '.$this->getLastname().', ';
		}
		if (strlen($this->getTitle()) > 0) $abstract .= $this->getTitle().', ';
		if (strlen($this->getZip()) > 0 && strlen($this->getCity()) > 0) {
			$abstract .= $this->getZip().' '.$this->getCity().', ';
		}
		if (strlen($this->getStreet()) > 0) $abstract .= $this->getStreet().', ';
		if (strlen($this->getEmail()) > 0) $abstract .= $this->getEmail().', ';
		// Letztes Komma entfernen und dann Kürzen
		$abstract = substr($abstract, 0, strrpos($abstract, ','));
		if ($nSize > 0) {
			$abstract = stringOps::chopString($abstract, $nSize, true);
		}
		return($abstract);
	}

	/**
	 * Erstellt einen Tooltip der mit einer gekürzten Adresse
	 * verlinkt ist und bei mouseover erscheint
	 * @param tooltipControl $ttp Tooltip Objekt
	 * @param int $nSize Anzahl Zeichen für gekürzte Adresse
	 */
	public function getTooltip(tooltipControl $ttp,$nSize) {
		// Gekürtzte Adresse holen
		$sShort = $this->getAbstract($nSize);
		// Kurztext verlinken mit einem ID Link
		$sHtml = '<a href="javascript:void(0);" id="ttp_'.$this->getSadID().'">'.$sShort.'</a>';
		// Tooltip HTML erstellen
		$sText = $this->getAbstract(0);
		$nReplacements = 0;
		$sText = str_replace(', ', '<br>', $sText, $nReplacements);
		$ttp->add(
			'ttp_'.$this->getSadID(),
			$sText,
			$this->Res->html(1113,page::language()),
			220, (30 + (30 * $nReplacements))
		);
		$sHtml .= $ttp->get('ttp_'.$this->getSadID());
		return($sHtml);
	}

	/**
	 * HTML Variante (Mit Umbrüchen) der Adresse generieren
	 * @return string HTML Code für Adressdarstellung in View
	 */
	public function toHtml() {
		$sHtml = $this->getAbstract(0);
		$sHtml = str_replace(', ', '<br>', $sHtml);
		return($sHtml);
	}

	/**
	 * Template Repräsentation der Adresse ausgeben
	 * @return array Daten der Adresse für Templates
	 */
	public function toTemplate() {
		return(Array(
			'SAD_ID' => $this->getSadID(),
			'SAD_TITLE' => $this->getTitle(),
			'SAD_FIRSTNAME' => $this->getFirstname(),
			'SAD_LASTNAME' => $this->getLastname(),
			'SAD_STREET' => $this->getStreet(),
			'SAD_PLZ' => $this->getZip(),
			'SAD_CITY' => $this->getCity(),
			'SAD_EMAIL' => $this->getEmail(),
			'SAD_PHONE' => $this->getPhone(),
		));
	}

	/**
	 * Holt die ID des Users mit der diese Adresse verbunden wurde. Technisch
	 * Dürfte es nur einen User geben der Verbunden ist, es wir hier daher
	 * einfach der erste gefundene genommen.
	 * @param boolean Gibt an, ob es direkt als Objekt anstatt nur die ID zurück kommen soll
	 * @return mixed ID des Users
	 */
	public function getOwnerID($asObject = false) {
		// User ID holen
		$sSQL = 'SELECT shu_ID FROM tbshopuser_address
		WHERE sad_ID = '.$this->getSadID().' LIMIT 0,1';
		$nUserID = getInt($this->Conn->getFirstResult($sSQL));
		// User Laden wenn nötig
		if ($nUserID > 0 && $asObject) {
			return(new shopUser($nUserID));
		}
		// Falls nicht Objekt nur die ID zurückgeben
		return($nUserID);
	}

    /**
     * Getter für sad_ID: Eindeutige ID
     * @return int Wert von 'sad_ID'
     */
    public function getSadID() {
        return($this->mySadID);
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
     * Getter für sad_Title: Anrede
     * @return string Wert von 'sad_Title'
     */
    public function getTitle() {
        $value = stripslashes($this->myTitle);
        return($value);
    }

    /**
     * Setter für sad_Title: Anrede
     * @param string Neuer Wert für 'sad_Title'
     */
    public function setTitle($value) {
        $this->Conn->escape($value);
        $this->myTitle = $value;
    }

    /**
     * Getter für sad_Firstname: Vorname
     * @return string Wert von 'sad_Firstname'
     */
    public function getFirstname() {
        $value = stripslashes($this->myFirstname);
        return($value);
    }

    /**
     * Setter für sad_Firstname: Vorname
     * @param string Neuer Wert für 'sad_Firstname'
     */
    public function setFirstname($value) {
        $this->Conn->escape($value);
        $this->myFirstname = $value;
    }

    /**
     * Getter für sad_Lastname: Nachname
     * @return string Wert von 'sad_Lastname'
     */
    public function getLastname() {
        $value = stripslashes($this->myLastname);
        return($value);
    }

    /**
     * Setter für sad_Lastname: Nachname
     * @param string Neuer Wert für 'sad_Lastname'
     */
    public function setLastname($value) {
        $this->Conn->escape($value);
        $this->myLastname = $value;
    }

    /**
     * Getter für sad_Street: Strasse
     * @return string Wert von 'sad_Street'
     */
    public function getStreet() {
        $value = stripslashes($this->myStreet);
        return($value);
    }

    /**
     * Setter für sad_Street: Strasse
     * @param string Neuer Wert für 'sad_Street'
     */
    public function setStreet($value) {
        $this->Conn->escape($value);
        $this->myStreet = $value;
    }

    /**
     * Getter für sad_Email: Email Adresse
     * @return string Wert von 'sad_Email'
     */
    public function getEmail() {
        $value = stripslashes($this->myEmail);
        return($value);
    }

    /**
     * Setter für sad_Email: Email Adresse
     * @param string Neuer Wert für 'sad_Email'
     */
    public function setEmail($value) {
        $this->Conn->escape($value);
        $this->myEmail = $value;
    }

    /**
     * Getter für sad_City: Stadt
     * @return string Wert von 'sad_City'
     */
    public function getCity() {
        $value = stripslashes($this->myCity);
        return($value);
    }

    /**
     * Setter für sad_City: Stadt
     * @param string Neuer Wert für 'sad_City'
     */
    public function setCity($value) {
        $this->Conn->escape($value);
        $this->myCity = $value;
    }

    /**
     * Getter für sad_Phone: Telefonnummer
     * @return string Wert von 'sad_Phone'
     */
    public function getPhone() {
        $value = stripslashes($this->myPhone);
        return($value);
    }

    /**
     * Setter für sad_Phone: Telefonnummer
     * @param string Neuer Wert für 'sad_Phone'
     */
    public function setPhone($value) {
        $this->Conn->escape($value);
        $this->myPhone = $value;
    }

    /**
     * Getter für sad_Zip: Postleitzahl
     * @return string Wert von 'sad_Zip'
     */
    public function getZip() {
        $value = stripslashes($this->myZip);
        return($value);
    }

    /**
     * Setter für sad_Zip: Postleitzahl
     * @param string Neuer Wert für 'sad_Zip'
     */
    public function setZip($value) {
        $this->Conn->escape($value);
        $this->myZip = $value;
    }

	/**
     * Getter für sua_Primary: Ist die Adresse Primär
     * @return int Wert von 'sua_Primary'
     */
    public function getPrimary() {
        return($this->myPrimary);
    }

    /**
     * Setter für sua_Primary: Wird nicht gespeichert!
     * @param int Neuer Wert für 'sua_Primary'
     */
    public function setPrimary($value) {
        $value = getInt($value);
        $this->myPrimary = $value;
    }

	/**
     * Getter für sua_Type: Typ der Verbindung wenn vorhanden
     * @return int Wert von 'sua_Type'
     */
    public function getType() {
        return($this->myType);
    }

    /**
     * Setter für sua_Type: Wird nicht gespeichert!
     * @param int Neuer Wert für 'sua_Type'
     */
    public function setType($value) {
        $value = getInt($value);
        $this->myType = $value;
    }
}