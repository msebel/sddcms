<?php
/**
 * Repräsentiert einen Feldvorgabewert
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopDynamicvalue extends abstractRow {

    /**
     * sdv_ID: Eindeutige ID
     * @var int
     */
    private $mySdvID = 0;
    /**
     * sdf_ID: Gehört als Vorgabewert zu diesem Feld
     * @var int
     */
    private $mySdfID = 0;
    /**
     * sdv_Value: Wert für Anzeige
     * @var string
     */
    private $myValue = '';
    /**
     * sdv_Order: Sortierung der Werte
     * @var int
     */
    private $myOrder = 0;

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
        $sSQL = 'SELECT sdv_ID,sdf_ID,sdv_Value,sdv_Order
        FROM tbshopdynamicvalue
        WHERE sdv_ID = '.$nID;
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
        $this->mySdvID = getInt($row['sdv_ID']);
        $this->setSdfID($row['sdf_ID']);
        $this->setValue($row['sdv_Value']);
        $this->setOrder($row['sdv_Order']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopdynamicvalue SET
        sdf_ID = $this->mySdfID,sdv_Value = '$this->myValue',
        sdv_Order = $this->myOrder
        WHERE sdv_ID = $this->mySdvID";
        $this->Conn->command($sSQL);
        return($this->getSdvID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopdynamicvalue (sdf_ID,sdv_Value,sdv_Order) VALUES (
        $this->mySdfID,'$this->myValue',$this->myOrder)";

        $this->mySdvID = $this->Conn->insert($sSQL);
        return($this->getSdvID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopdynamicvalue
        WHERE sdv_ID = ".$this->getSdvID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für sdv_ID: Eindeutige ID
     * @return int Wert von 'sdv_ID'
     */
    public function getSdvID() {
        return($this->mySdvID);
    }

    /**
     * Getter für sdf_ID: Gehört als Vorgabewert zu diesem Feld
     * @return int Wert von 'sdf_ID'
     */
    public function getSdfID() {
        return($this->mySdfID);
    }

    /**
     * Setter für sdf_ID: Gehört als Vorgabewert zu diesem Feld
     * @param int Neuer Wert für 'sdf_ID'
     */
    public function setSdfID($value) {
        $value = getInt($value);
        $this->mySdfID = $value;
    }

    /**
     * Getter für sdv_Value: Wert für Anzeige
     * @return string Wert von 'sdv_Value'
     */
    public function getValue() {
        $value = stripslashes($this->myValue);
        return($value);
    }

    /**
     * Setter für sdv_Value: Wert für Anzeige
     * @param string Neuer Wert für 'sdv_Value'
     */
    public function setValue($value) {
        $this->Conn->escape($value);
        $this->myValue = $value;
    }

    /**
     * Getter für sdv_Order: Sortierung der Werte
     * @return int Wert von 'sdv_Order'
     */
    public function getOrder() {
        return($this->myOrder);
    }

    /**
     * Setter für sdv_Order: Sortierung der Werte
     * @param int Neuer Wert für 'sdv_Order'
     */
    public function setOrder($value) {
        $value = getInt($value);
        $this->myOrder = $value;
    }

}