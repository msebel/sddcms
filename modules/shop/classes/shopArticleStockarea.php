<?php
/**
 * Verbindung aus Shopartikel / Lagerplatz
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopArticleStockarea extends abstractRow {

    /**
     * sas_ID: ID Der Verbindung
     * @var int
     */
    private $mySasID = 0;
    /**
     * sha_ID: Artikel der Verbindung
     * @var int
     */
    private $myShaID = 0;
    /**
     * ssa_ID: Lager der Verbindung
     * @var int
     */
    private $mySsaID = 0;
    /**
     * sas_Stock: Anzahl Artikel an Lager
     * @var string
     */
    private $myStock = '';
    /**
     * sas_Ontheway: Anzahl Artikel auf dem Weg
     * @var string
     */
    private $myOntheway = '';
    /**
     * sas_Remark: Bemerkungen zum Artikel (Lieferzeit etc.)
     * @var string
     */
    private $myRemark = '';

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
        $sSQL = 'SELECT sas_ID,sha_ID,ssa_ID,sas_Stock,sas_Ontheway,sas_Remark
        FROM tbshoparticle_stockarea
        WHERE sas_ID = '.$nID;
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
        $this->mySasID = getInt($row['sas_ID']);
        $this->setShaID($row['sha_ID']);
        $this->setSsaID($row['ssa_ID']);
        $this->setStock($row['sas_Stock']);
        $this->setOntheway($row['sas_Ontheway']);
        $this->setRemark($row['sas_Remark']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshoparticle_stockarea SET
        sha_ID = $this->myShaID,ssa_ID = $this->mySsaID,
        sas_Stock = '$this->myStock',sas_Ontheway = '$this->myOntheway',
        sas_Remark = '$this->myRemark'
        WHERE sas_ID = $this->mySasID";
        $this->Conn->command($sSQL);
        return($this->getSasID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshoparticle_stockarea (sha_ID,ssa_ID,sas_Stock,sas_Ontheway,
        sas_Remark) VALUES (
        $this->myShaID,$this->mySsaID,'$this->myStock',
        '$this->myOntheway','$this->myRemark')";
        $this->mySasID = $this->Conn->insert($sSQL);
        return($this->getSasID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshoparticle_stockarea
        WHERE sas_ID = ".$this->getSasID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für sas_ID: ID Der Verbindung
     * @return int Wert von 'sas_ID'
     */
    public function getSasID() {
        return($this->mySasID);
    }

    /**
     * Getter für sha_ID: Artikel der Verbindung
     * @return int Wert von 'sha_ID'
     */
    public function getShaID() {
        return($this->myShaID);
    }

    /**
     * Setter für sha_ID: Artikel der Verbindung
     * @param int Neuer Wert für 'sha_ID'
     */
    public function setShaID($value) {
        $value = getInt($value);
        $this->myShaID = $value;
    }

    /**
     * Getter für ssa_ID: Lager der Verbindung
     * @return int Wert von 'ssa_ID'
     */
    public function getSsaID() {
        return($this->mySsaID);
    }

    /**
     * Setter für ssa_ID: Lager der Verbindung
     * @param int Neuer Wert für 'ssa_ID'
     */
    public function setSsaID($value) {
        $value = getInt($value);
        $this->mySsaID = $value;
    }

    /**
     * Getter für sas_Stock: Anzahl Artikel an Lager
     * @return string Wert von 'sas_Stock'
     */
    public function getStock() {
        $value = stripslashes($this->myStock);
        return($value);
    }

    /**
     * Setter für sas_Stock: Anzahl Artikel an Lager
     * @param string Neuer Wert für 'sas_Stock'
     */
    public function setStock($value) {
        $this->Conn->escape($value);
        $this->myStock = $value;
    }

    /**
     * Getter für sas_Ontheway: Anzahl Artikel auf dem Weg
     * @return string Wert von 'sas_Ontheway'
     */
    public function getOntheway() {
        $value = stripslashes($this->myOntheway);
        return($value);
    }

    /**
     * Setter für sas_Ontheway: Anzahl Artikel auf dem Weg
     * @param string Neuer Wert für 'sas_Ontheway'
     */
    public function setOntheway($value) {
        $this->Conn->escape($value);
        $this->myOntheway = $value;
    }

    /**
     * Getter für sas_Remark: Bemerkungen zum Artikel (Lieferzeit etc.)
     * @return string Wert von 'sas_Remark'
     */
    public function getRemark() {
        $value = stripslashes($this->myRemark);
        return($value);
    }

    /**
     * Setter für sas_Remark: Bemerkungen zum Artikel (Lieferzeit etc.)
     * @param string Neuer Wert für 'sas_Remark'
     */
    public function setRemark($value) {
        $this->Conn->escape($value);
        $this->myRemark = $value;
    }

}