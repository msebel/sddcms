<?php
/**
 * Repräsentiert einen Gutschein
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopCoupon extends abstractRow {

    /**
     * scp_ID: Eindeutige interne ID
     * @var int
     */
    private $myScpID = 0;
    /**
     * shu_ID: User dem der Gutschein gehört
     * @var int
     */
    private $myShuID = 0;
    /**
     * scp_Number: Gutscheinnummer zur Eingabe
     * @var string
     */
    private $myNumber = '';
    /**
     * scp_Value: Währungswert des Gutscheins
     * @var double
     */
    private $myValue = 0.0;
    /**
     * scp_Activated: 0 = Nicht benutzt / 1 = Genutzt, abgelaufen
     * @var int
     */
    private $myActivated = 0;
    /**
     * scp_Validuntil: Ablaufdatum des Gutscheins
     * @var string
     */
    private $myValiduntil = '';

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
        $sSQL = 'SELECT scp_ID,shu_ID,scp_Number,scp_Value,scp_Activated,scp_Validuntil
        FROM tbshopcoupon
        WHERE scp_ID = '.$nID;
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
        $this->myScpID = getInt($row['scp_ID']);
        $this->setShuID($row['shu_ID']);
        $this->setNumber($row['scp_Number']);
        $this->setValue($row['scp_Value']);
        $this->setActivated($row['scp_Activated']);
        $this->setValiduntil($row['scp_Validuntil']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int PrimärschlÃ¼ssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopcoupon SET
        shu_ID = ".$this->getShuID().",
        scp_Number = '".$this->getNumber()."',
        scp_Value = ".$this->getValue().",
        scp_Activated = ".$this->getActivated().",
        scp_Validuntil = '".$this->getValiduntil()."'
        WHERE scp_ID = ".$this->getScpID();
        $this->Conn->command($sSQL);
        return($this->getScpID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int PrimärschlÃ¼ssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopcoupon (scp_ID,shu_ID,scp_Number,scp_Value,
        scp_Activated,scp_Validuntil)
        ".$this->getShuID().",'".$this->getNumber()."',
        ".$this->getValue().",".$this->getActivated().",
        '".$this->getValiduntil()."')";
        $this->myScpID = $this->Conn->insert($sSQL);
        return($this->getScpID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopcoupon
        WHERE scp_ID = ".$this->getScpID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für scp_ID: Eindeutige interne ID
     * @return int Wert von 'scp_ID'
     */
    public function getScpID() {
        return($this->myScpID);
    }

    /**
     * Getter für shu_ID: User dem der Gutschein gehört
     * @return int Wert von 'shu_ID'
     */
    public function getShuID() {
        return($this->myShuID);
    }

    /**
     * Setter für shu_ID: User dem der Gutschein gehört
     * @param int Neuer Wert für 'shu_ID'
     */
    public function setShuID($value) {
        $value = getInt($value);
        $this->myShuID = $value;
    }

    /**
     * Getter für scp_Number: Gutscheinnummer zur Eingabe
     * @return string Wert von 'scp_Number'
     */
    public function getNumber() {
        $value = stripslashes($this->myNumber);
        return($value);
    }

    /**
     * Setter für scp_Number: Gutscheinnummer zur Eingabe
     * @param string Neuer Wert für 'scp_Number'
     */
    public function setNumber($value) {
        $this->Conn->escape($value);
        $this->myNumber = $value;
    }

    /**
     * Getter für scp_Value: Währungswert des Gutscheins
     * @return double Wert von 'scp_Value'
     */
    public function getValue() {
    }

    /**
     * Setter für scp_Value: Währungswert des Gutscheins
     * @param double Neuer Wert für 'scp_Value'
     */
    public function setValue($value) {
    }

    /**
     * Getter für scp_Activated: 0 = Nicht benutzt / 1 = Genutzt, abgelaufen
     * @return int Wert von 'scp_Activated'
     */
    public function getActivated() {
        return($this->myActivated);
    }

    /**
     * Setter für scp_Activated: 0 = Nicht benutzt / 1 = Genutzt, abgelaufen
     * @param int Neuer Wert für 'scp_Activated'
     */
    public function setActivated($value) {
        $value = getInt($value);
        $this->myActivated = $value;
    }

    /**
     * Getter für scp_Validuntil: Ablaufdatum des Gutscheins
     * @return string Wert von 'scp_Validuntil'
     */
    public function getValiduntil() {
        $value = stripslashes($this->myValiduntil);
        return($value);
    }

    /**
     * Setter für scp_Validuntil: Ablaufdatum des Gutscheins
     * @param string Neuer Wert für 'scp_Validuntil'
     */
    public function setValiduntil($value) {
        $this->Conn->escape($value);
        $this->myValiduntil = $value;
    }
}