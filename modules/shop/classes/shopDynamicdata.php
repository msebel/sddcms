<?php
/**
 * Dynamische Daten eines Artikels
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopDynamicdata extends abstractRow {

    /**
     * sdd_ID: Eindeutige ID
     * @var int
     */
    private $mySddID = 0;
    /**
     * sha_ID: Artikel zu dem dieser dynamische Wert gehört
     * @var int
     */
    private $myShaID = 0;
    /**
     * sdf_ID: Besitzer mit Metaddaten des Feldes
     * @var int
     */
    private $mySdfID = 0;
    /**
     * man_ID: Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * sdd_Value: Wert des Feldes
     * @var string
     */
    private $myValue = '';

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
        $sSQL = 'SELECT sdd_ID,sha_ID,sdf_ID,man_ID,sdd_Value
        FROM tbshopdynamicdata
        WHERE sdd_ID = '.$nID;
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
        $this->mySddID = getInt($row['sdd_ID']);
        $this->setShaID($row['sha_ID']);
        $this->setSdfID($row['sdf_ID']);
        $this->setManID($row['man_ID']);
        $this->setValue($row['sdd_Value']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopdynamicdata SET
        sha_ID = $this->myShaID,sdf_ID = $this->mySdfID,
        man_ID = $this->myManID,sdd_Value = '$this->myValue'

        WHERE sdd_ID = $this->mySddID";
        $this->Conn->command($sSQL);
        return($this->getSddID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopdynamicdata (sha_ID,sdf_ID,man_ID,sdd_Value) VALUES (
        $this->myShaID,$this->mySdfID,$this->myManID,
        '$this->myValue')";
        $this->mySddID = $this->Conn->insert($sSQL);
        return($this->getSddID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopdynamicdata
        WHERE sdd_ID = ".$this->getSddID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für sdd_ID: Eindeutige ID
     * @return int Wert von 'sdd_ID'
     */
    public function getSddID() {
        return($this->mySddID);
    }

    /**
     * Getter für sha_ID: Artikel zu dem dieser dynamische Wert gehört
     * @return int Wert von 'sha_ID'
     */
    public function getShaID() {
        return($this->myShaID);
    }

    /**
     * Setter für sha_ID: Artikel zu dem dieser dynamische Wert gehört
     * @param int Neuer Wert für 'sha_ID'
     */
    public function setShaID($value) {
        $value = getInt($value);
        $this->myShaID = $value;
    }

    /**
     * Getter für sdf_ID: Besitzer mit Metaddaten des Feldes
     * @return int Wert von 'sdf_ID'
     */
    public function getSdfID() {
        return($this->mySdfID);
    }

    /**
     * Setter für sdf_ID: Besitzer mit Metaddaten des Feldes
     * @param int Neuer Wert für 'sdf_ID'
     */
    public function setSdfID($value) {
        $value = getInt($value);
        $this->mySdfID = $value;
    }

    /**
     * Getter für man_ID: Mandant
     * @return int Wert von 'man_ID'
     */
    public function getManID() {
        return($this->myManID);
    }

    /**
     * Setter für man_ID: Mandant
     * @param int Neuer Wert für 'man_ID'
     */
    public function setManID($value) {
        $value = getInt($value);
        $this->myManID = $value;
    }

    /**
     * Getter für sdd_Value: Wert des Feldes
     * @return string Wert von 'sdd_Value'
     */
    public function getValue() {
        $value = stripslashes($this->myValue);
        return($value);
    }

    /**
     * Setter für sdd_Value: Wert des Feldes
     * @param string Neuer Wert für 'sdd_Value'
     */
    public function setValue($value) {
        $this->Conn->escape($value);
        $this->myValue = $value;
    }

}