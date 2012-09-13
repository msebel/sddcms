<?php
/**
 * Artikelgrösse
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopArticlesize extends abstractRow {

    /**
     * saz_ID: ID der Artikelgrösse
     * @var int
     */
    private $mySazID = 0;
    /**
     * sha_ID: Grösse für diesen Artikel
     * @var int
     */
    private $myShaID = 0;
    /**
     * saz_Value: Name der Grösse z.B XXL
     * @var string
     */
    private $myValue = '';
    /**
     * saz_Priceadd: Preis der Draufgeschlagen wird
     * @var double
     */
    private $myPriceadd = 0.0;
    /**
     * saz_Primary: 0 = Sekundärwahl/ 1 = Vorauswahl
     * @var int
     */
    private $myPrimary = 0;

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
        $sSQL = 'SELECT saz_ID,sha_ID,saz_Value,saz_Priceadd,saz_Primary
        FROM tbshoparticlesize
        WHERE saz_ID = '.$nID;
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
        $this->mySazID = getInt($row['saz_ID']);
        $this->setShaID($row['sha_ID']);
        $this->setValue($row['saz_Value']);
        $this->setPriceadd($row['saz_Priceadd']);
        $this->setPrimary($row['saz_Primary']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshoparticlesize SET
        sha_ID = $this->myShaID,saz_Value = '$this->myValue',
        saz_Priceadd = $this->myPriceadd,saz_Primary = $this->myPrimary

        WHERE saz_ID = $this->mySazID";
        $this->Conn->command($sSQL);
        return($this->getSazID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshoparticlesize (sha_ID,saz_Value,saz_Priceadd,saz_Primary) VALUES (
        $this->myShaID,'$this->myValue',$this->myPriceadd,
        $this->myPrimary)";
        $this->mySazID = $this->Conn->insert($sSQL);
        return($this->getSazID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshoparticlesize
        WHERE saz_ID = ".$this->getSazID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für saz_ID: ID der Artikelgrösse
     * @return int Wert von 'saz_ID'
     */
    public function getSazID() {
        return($this->mySazID);
    }

    /**
     * Getter für sha_ID: Grösse für diesen Artikel
     * @return int Wert von 'sha_ID'
     */
    public function getShaID() {
        return($this->myShaID);
    }

    /**
     * Setter für sha_ID: Grösse für diesen Artikel
     * @param int Neuer Wert für 'sha_ID'
     */
    public function setShaID($value) {
        $value = getInt($value);
        $this->myShaID = $value;
    }

    /**
     * Getter für saz_Value: Name der Grösse z.B XXL
     * @return string Wert von 'saz_Value'
     */
    public function getValue() {
        $value = stripslashes($this->myValue);
        return($value);
    }

    /**
     * Setter für saz_Value: Name der Grösse z.B XXL
     * @param string Neuer Wert für 'saz_Value'
     */
    public function setValue($value) {
        $this->Conn->escape($value);
        $this->myValue = $value;
    }

    /**
     * Getter für saz_Priceadd: Preis der Draufgeschlagen wird
     * @return double Wert von 'saz_Priceadd'
     */
    public function getPriceadd() {
        return($this->myPriceadd);
    }

    /**
     * Setter für saz_Priceadd: Preis der Draufgeschlagen wird
     * @param double Neuer Wert für 'saz_Priceadd'
     */
    public function setPriceadd($value) {
        $value = numericOps::getDecimal($value,2);
        $this->myPriceadd = $value;
    }

    /**
     * Getter für saz_Primary: 0 = Sekundärwahl/ 1 = Vorauswahl
     * @return int Wert von 'saz_Primary'
     */
    public function getPrimary() {
        return($this->myPrimary);
    }

    /**
     * Setter für saz_Primary: 0 = Sekundärwahl/ 1 = Vorauswahl
     * @param int Neuer Wert für 'saz_Primary'
     */
    public function setPrimary($value) {
        $value = getInt($value);
        $this->myPrimary = $value;
    }

}