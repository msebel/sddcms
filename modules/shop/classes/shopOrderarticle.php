<?php
/**
 * Artikel mit Bestellverknüpfung (vereinfachte Kopie des Artikels)
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopOrderarticle extends abstractRow {

    /**
     * soa_ID: Eindeutige ID
     * @var int
     */
    private $mySoaID = 0;
    /**
     * sho_ID: Gehört zu dieser Bestellung
     * @var int
     */
    private $myShoID = 0;
    /**
     * sha_ID: Referenz zum Originalartikel
     * @var int
     */
    private $myShaID = 0;
    /**
     * man_ID: Besitzender Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * soa_Title: Titel des Artikels
     * @var string
     */
    private $myTitle = '';
    /**
     * soa_Size: Grössenangabe (Wenn vorhanden)
     * @var string
     */
    private $mySize = '';
    /**
     * soa_Price: Preis des Artikels
     * @var double
     */
    private $myPrice = 0.0;
    /**
     * soa_Mwst: Mehrwertsteuersatz
     * @var double
     */
    private $myMwst = 0.0;
    /**
     * soa_Guarantee: Gibt Garantiedefinitionen an
     * @var string
     */
    private $myGuarantee = '';
    /**
     * soa_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @var string
     */
    private $myArticlenumber = '';
    /**
     * soa_DeliveryEntity: Lieferentität (Kopie)
     * @var int
     */
    private $myDeliveryEntity = 0;

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
        $sSQL = 'SELECT soa_ID,sho_ID,sha_ID,man_ID,soa_Title,soa_Size,soa_Price,
        soa_Mwst,soa_Guarantee,soa_Articlenumber,soa_DeliveryEntity
        FROM tbshoporderarticle
        WHERE soa_ID = '.$nID;
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
        $this->mySoaID = getInt($row['soa_ID']);
        $this->setShoID($row['sho_ID']);
        $this->setShaID($row['sha_ID']);
        $this->setManID($row['man_ID']);
        $this->setTitle($row['soa_Title']);
        $this->setSize($row['soa_Size']);
        $this->setPrice($row['soa_Price']);
        $this->setMwst($row['soa_Mwst']);
        $this->setGuarantee($row['soa_Guarantee']);
        $this->setArticlenumber($row['soa_Articlenumber']);
        $this->setDeliveryEntity($row['soa_DeliveryEntity']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshoporderarticle SET
        sho_ID = $this->myShoID,sha_ID = $this->myShaID,
        man_ID = $this->myManID,soa_Title = '$this->myTitle',
        soa_Size = '$this->mySize',soa_Price = $this->myPrice,
        soa_Mwst = $this->myMwst,soa_Guarantee = '$this->myGuarantee',
        soa_Articlenumber = '$this->myArticlenumber',soa_DeliveryEntity = $this->myDeliveryEntity

        WHERE soa_ID = $this->mySoaID";
        $this->Conn->command($sSQL);
        return($this->getSoaID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshoporderarticle (sho_ID,sha_ID,man_ID,soa_Title,
        soa_Size,soa_Price,soa_Mwst,soa_Guarantee,soa_Articlenumber,soa_DeliveryEntity) VALUES (
        $this->myShoID,$this->myShaID,$this->myManID,
        '$this->myTitle','$this->mySize',$this->myPrice,
        $this->myMwst,'$this->myGuarantee','$this->myArticlenumber',
        $this->myDeliveryEntity)";
        $this->mySoaID = $this->Conn->insert($sSQL);
        return($this->getSoaID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshoporderarticle
        WHERE soa_ID = ".$this->getSoaID();
        $this->Conn->command($sSQL);
    }

	/**
	 * Verarbeiten zu Template Variablen
	 * @param string $key Wenn nötig nur diese Variable zurückgeben
	 */
	public function toTemplate($key = '') {
		// Assoziatives Array für Template zurückgeben
		$data = array(
			'SOA_ID' => $this->getSoaID(),
			'ARTICLE_ID' => $this->getShaID(),
			'SOA_PRICE' => $this->getPrice(),
			'SOA_MWST' => $this->getMwst(),
			'SOA_ARTICLENUMBER' => $this->getArticlenumber(),
			'SOA_DELIVERYENTITY' => $this->getDeliveryEntity(),
			'SOA_GUARANTEE' => $this->getGuarantee(),
			'SOA_SIZE' => $this->getSize(),
			'SOA_TITLE' => $this->getTitle()
		);
		//Evtl. nur eine Variable zurückgeben
		if (strlen($key) > 0) return($data[$key]);
		// Ansonsten alles zurückgeben
		return($data);
	}

	/**
	 * Pseudodaten für Artikel der einer Anzeige von
	 * "Keine Artikel" entspricht
	 * @return array Recordset mit leeren Daten
	 */
	public static function getPseudo() {
		return(array(
			'soa_ID' => 0,
			'sha_ID' => 0,
			'soa_Times' => 0,
			'soa_Title' => singleton::resources()->normal(1097,page::language()),
			'soa_Size' => '',
			'soa_Price' => 0.00
		));
	}

    /**
     * Getter für soa_ID: Eindeutige ID
     * @return int Wert von 'soa_ID'
     */
    public function getSoaID() {
        return($this->mySoaID);
    }

    /**
     * Getter für sho_ID: Gehört zu dieser Bestellung
     * @return int Wert von 'sho_ID'
     */
    public function getShoID() {
        return($this->myShoID);
    }

    /**
     * Setter für sho_ID: Gehört zu dieser Bestellung
     * @param int Neuer Wert für 'sho_ID'
     */
    public function setShoID($value) {
        $value = getInt($value);
        $this->myShoID = $value;
    }

    /**
     * Getter für sha_ID: Referenz zum Originalartikel
     * @return int Wert von 'sha_ID'
     */
    public function getShaID() {
        return($this->myShaID);
    }

    /**
     * Setter für sha_ID: Referenz zum Originalartikel
     * @param int Neuer Wert für 'sha_ID'
     */
    public function setShaID($value) {
        $value = getInt($value);
        $this->myShaID = $value;
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
     * Getter für soa_Title: Titel des Artikels
     * @return string Wert von 'soa_Title'
     */
    public function getTitle() {
        $value = stripslashes($this->myTitle);
        return($value);
    }

    /**
     * Setter für soa_Title: Titel des Artikels
     * @param string Neuer Wert für 'soa_Title'
     */
    public function setTitle($value) {
        $this->Conn->escape($value);
        $this->myTitle = $value;
    }

    /**
     * Getter für soa_Size: Grössenangabe (Wenn vorhanden)
     * @return string Wert von 'soa_Size'
     */
    public function getSize() {
        $value = stripslashes($this->mySize);
        return($value);
    }

    /**
     * Setter für soa_Size: Grössenangabe (Wenn vorhanden)
     * @param string Neuer Wert für 'soa_Size'
     */
    public function setSize($value) {
        $this->Conn->escape($value);
        $this->mySize = $value;
    }

    /**
     * Getter für soa_Price: Preis des Artikels
     * @return double Wert von 'soa_Price'
     */
    public function getPrice() {
        return($this->myPrice);
    }

    /**
     * Setter für soa_Price: Preis des Artikels
     * @param double Neuer Wert für 'soa_Price'
     */
    public function setPrice($value) {
        $value = numericOps::getDecimal($value,2);
        $this->myPrice = $value;
    }

    /**
     * Getter für soa_Mwst: Mehrwertsteuersatz
     * @return double Wert von 'soa_Mwst'
     */
    public function getMwst() {
        return($this->myMwst);
    }

    /**
     * Setter für soa_Mwst: Mehrwertsteuersatz
     * @param double Neuer Wert für 'soa_Mwst'
     */
    public function setMwst($value) {
        $value = numericOps::getDecimal($value,2);
        $this->myMwst = $value;
    }

    /**
     * Getter für soa_Guarantee: Gibt Garantiedefinitionen an
     * @return string Wert von 'soa_Guarantee'
     */
    public function getGuarantee() {
        $value = stripslashes($this->myGuarantee);
        return($value);
    }

    /**
     * Setter für soa_Guarantee: Gibt Garantiedefinitionen an
     * @param string Neuer Wert für 'soa_Guarantee'
     */
    public function setGuarantee($value) {
        $this->Conn->escape($value);
        $this->myGuarantee = $value;
    }

    /**
     * Getter für soa_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @return string Wert von 'soa_Articlenumber'
     */
    public function getArticlenumber() {
		if (strlen($this->myArticlenumber) > 0) {
			$value = stripslashes($this->myArticlenumber);
			return($value);
		} else {
			// Ansonsten die interne ID zurückgeben
			return($this->getShaID());
		}
    }

    /**
     * Setter für soa_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @param string Neuer Wert für 'soa_Articlenumber'
     */
    public function setArticlenumber($value) {
        $this->Conn->escape($value);
        $this->myArticlenumber = $value;
    }

    /**
     * Getter für soa_DeliveryEntity: Lieferentität (Kopie)
     * @return int Wert von 'soa_DeliveryEntity'
     */
    public function getDeliveryEntity() {
        return($this->myDeliveryEntity);
    }

    /**
     * Setter für soa_DeliveryEntity: Lieferentität (Kopie)
     * @param int Neuer Wert für 'soa_DeliveryEntity'
     */
    public function setDeliveryEntity($value) {
        $value = getInt($value);
        $this->myDeliveryEntity = $value;
    }

}