<?php
/**
 * Dynamisches Feld (Beinhaltet auch Auswahldaten von tbshopdynamicvalue)
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopDynamicfield extends abstractRow {

    /**
     * sdf_ID: Eindeutige ID
     * @var int
     */
    private $mySdfID = 0;
    /**
     * man_ID: Mandant, dem das Feld gehört
     * @var int
     */
    private $myManID = 0;
    /**
     * sdf_Name: Name des Feldes
     * @var string
     */
    private $myName = '';
    /**
     * sdf_Default: Defaultwert (Wert oder ID von tbshopdynamicvalue)
     * @var string
     */
    private $myDefault = '';
    /**
     * sdf_Type: 0 = Text, 1 = Singleselect (Dropdown, Radio), 3 = Multiple (Checkboxen), 4 = Upload
     * @var int
     */
    private $myType = 0;
	/**
	 * Array der möglichen Werte (Sofern Dropdown, Checkboxen)
	 * @var array
	 */
	private $myValues = array();
	/**
	 * Type: Textfeld
	 * @var int
	 */
	const TYPE_TEXT = 0;
	/**
	 * Type: Auswahl eines Wertes aus mehreren
	 * @var int
	 */
	const TYPE_SINGLE = 1;
	/**
	 * Type: Auswahl mehrerer Werte aus mehreren
	 * @var int
	 */
	const TYPE_MULTIPLE = 2;
	/**
	 * Type: Uploadfeld (Wird vorerst nicht umgesetzt)
	 * @var int
	 */
	const TYPE_UPLOAD = 3;

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
        $sSQL = 'SELECT sdf_ID,man_ID,sdf_Name,sdf_Default,sdf_Type
        FROM tbshopdynamicfield WHERE sdf_ID = '.$nID;
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
        $this->mySdfID = getInt($row['sdf_ID']);
        $this->setManID($row['man_ID']);
        $this->setName($row['sdf_Name']);
        $this->setDefault($row['sdf_Default']);
        $this->setType($row['sdf_Type']);
		$this->loadValues();
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopdynamicfield SET
        man_ID = $this->myManID,sdf_Name = '$this->myName',
        sdf_Default = '$this->myDefault',sdf_Type = $this->myType

        WHERE sdf_ID = $this->mySdfID";
        $this->Conn->command($sSQL);
        return($this->getSdfID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopdynamicfield (man_ID,sdf_Name,sdf_Default,sdf_Type) VALUES (
        $this->myManID,'$this->myName','$this->myDefault',
        $this->myType)";
        $this->mySdfID = $this->Conn->insert($sSQL);
        return($this->getSdfID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
		// Feld selbst löschen
        $sSQL = "DELETE FROM tbshopdynamicfield WHERE sdf_ID = ".$this->getSdfID();
        $this->Conn->command($sSQL);
		// Auch Values davon löschen
		$sSQL = "DELETE FROM tbshopdynamicvalue WHERE sdf_ID = ".$this->getSdfID();
        $this->Conn->command($sSQL);
    }

    /**
     * Getter für sdf_ID: Eindeutige ID
     * @return int Wert von 'sdf_ID'
     */
    public function getSdfID() {
        return($this->mySdfID);
    }

    /**
     * Getter für man_ID: Mandant, dem das Feld gehört
     * @return int Wert von 'man_ID'
     */
    public function getManID() {
        return($this->myManID);
    }

    /**
     * Setter für man_ID: Mandant, dem das Feld gehört
     * @param int Neuer Wert für 'man_ID'
     */
    public function setManID($value) {
        $value = getInt($value);
        $this->myManID = $value;
    }

    /**
     * Getter für sdf_Name: Name des Feldes
     * @return string Wert von 'sdf_Name'
     */
    public function getName() {
        $value = stripslashes($this->myName);
        return($value);
    }

    /**
     * Setter für sdf_Name: Name des Feldes
     * @param string Neuer Wert für 'sdf_Name'
     */
    public function setName($value) {
		// Wenn leer, Standardwert nehmen
		if (strlen($value) == 0) {
			$value = $this->Res->normal(1056, page::language());
		}
		// Flicken und speichern
        $this->Conn->escape($value);
        $this->myName = $value;
    }

    /**
     * Getter für sdf_Default: Defaultwert (Wert oder ID von tbshopdynamicvalue)
     * @return string Wert von 'sdf_Default'
     */
    public function getDefault() {
        $value = stripslashes($this->myDefault);
        return($value);
    }

    /**
     * Setter für sdf_Default: Defaultwert (Wert oder ID von tbshopdynamicvalue)
     * @param string Neuer Wert für 'sdf_Default'
     */
    public function setDefault($value) {
        $this->Conn->escape($value);
        $this->myDefault = $value;
    }

    /**
     * Getter für sdf_Type: 0 = Text, 1 = Singleselect (Dropdown, Radio), 3 = Multiple (Checkboxen), 4 = Upload
     * @return int Wert von 'sdf_Type'
     */
    public function getType() {
        return($this->myType);
    }

	/**
	 * Gibt einen lesbaren String für den Feldtyp zurück
	 * (Der Typ für Upload ist vorerst nicht umgesetzt)
	 * @return string Lesbarer String für Feldtyp
	 */
	public function getFieldType() {
		switch ($this->getType()) {
			case self::TYPE_TEXT:		$nRes = 1045; break;
			case self::TYPE_SINGLE:		$nRes = 1046; break;
			case self::TYPE_MULTIPLE:	$nRes = 1047; break;
		}
		return($this->Res->html($nRes,page::language()));
	}

    /**
     * Setter für sdf_Type: 0 = Text, 1 = Singleselect (Dropdown, Radio), 3 = Multiple (Checkboxen), 4 = Upload
     * @param int Neuer Wert für 'sdf_Type'
     */
    public function setType($value) {
        $value = getInt($value);
		// Validieren durch effektive Typen
		switch ($value) {
			case self::TYPE_TEXT:
			case self::TYPE_SINGLE:
			case self::TYPE_MULTIPLE:
			case self::TYPE_UPLOAD:
				break;
			default:
				$value = self::TYPE_TEXT;
		}
        $this->myType = $value;
    }

	/**
	 * Gibt Informellen String zu getUseCount zurück (Internat.)
	 * @return string String wie "x mal verwendet", internat.
	 */
	public function getUsedReadable() {
		$nTimes = $this->getUseCount();
		if ($nTimes > 0) {
			$sString = $this->Res->html(1048, page::language());
			$sString = str_replace('{0}', $nTimes, $sString);
		} else {
			$sString = $this->Res->html(1049, page::language());
		}
		return($sString);
	}

    /**
	 * Counter, wie oft das Feld verwendet wurde
	 * @return int Ganzzahl wie oft das Feld verwendet wurde
	 */
	public function getUseCount() {
		$sSQL = "SELECT COUNT(sdf_ID) FROM tbshopdynamicdata
		WHERE sdf_ID = $this->mySdfID";
		return($this->Conn->getCountResult($sSQL));
	}

	/**
	 * Gibt die Values zurück für ein Dropdown/Checkbox Feld
	 * @return array Key/Value Pairs aus tbshopdynamicvalues
	 */
	public function getValues() {
		return($this->myValues);
	}

	/**
	 * Value Werte laden
	 */
	private function loadValues() {
		if ($this->myType == self::TYPE_SINGLE || $this->myType == self::TYPE_MULTIPLE) {
			$sSQL = "SELECT sdv_ID,sdv_Value FROM tbshopdynamicvalue
			WHERE sdf_ID = $this->mySdfID ORDER BY sdv_Order ASC";
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				$this->myValues[$row['sdv_ID']] = $row['sdv_Value'];
			}
		}
	}
}