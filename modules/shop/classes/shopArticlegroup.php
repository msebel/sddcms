<?php
/**
 * MyClassDescriptionClicktoChange
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopArticlegroup extends abstractRow {

    /**
     * sag_ID: Eindeutige ID der Gruppe
     * @var int
     */
    private $mySagID = 0;
    /**
     * man_ID: Gehört zu diesem Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * sag_Parent: Übergeordnetes Element (0 = Hauptelement)
     * @var int
     */
    private $myParent = 0;
    /**
     * sag_Title: Titel der Gruppe
     * @var string
     */
    private $myTitle = '';
    /**
     * sag_Desc: Beschreibung der Gruppe
     * @var string
     */
    private $myDesc = '';
    /**
     * sag_Image: Elementen ID für ein Bild
     * @var int
     */
    private $myImage = 0;
    /**
     * sag_Articles: Wieviele Artikel pro Seite zeigt die Kategorie an
     * @var int
     */
    private $myArticles = 0;
    /**
     * sag_Viewtype: 0 = Alle Artikel zeigen / 1 = ABC Register
     * @var int
     */
    private $myViewtype = 0;
    /**
     * sag_DeliveryEntity: Anzahl Liefereinheiten der Artikel in der Gruppe
     * @var int
     */
    private $myDeliveryEntity = 0;
	/**
	 * Steht für die Anzeige-Option "Auflistung"
	 * @var int
	 */
	const VIEWTYPE_LIST = 0;
	/**
	 * Steht für die Anzeige-Option "ABC-Register"
	 * @var int
	 */
	const VIEWTYPE_TABS = 1;

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
        $sSQL = 'SELECT sag_ID,man_ID,sag_Parent,sag_Title,sag_Desc,sag_Image,
        sag_Articles,sag_Viewtype,sag_DeliveryEntity
        FROM tbshoparticlegroup
        WHERE sag_ID = '.$nID;
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
        $this->mySagID = getInt($row['sag_ID']);
        $this->setManID($row['man_ID']);
        $this->setParent($row['sag_Parent']);
        $this->setTitle($row['sag_Title']);
        $this->setDesc($row['sag_Desc']);
        $this->setImage($row['sag_Image']);
        $this->setArticles($row['sag_Articles']);
        $this->setViewtype($row['sag_Viewtype']);
        $this->setDeliveryEntity($row['sag_DeliveryEntity']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshoparticlegroup SET
        man_ID = $this->myManID,sag_Parent = $this->myParent,
        sag_Title = '$this->myTitle',sag_Desc = '$this->myDesc',
        sag_Image = $this->myImage,sag_Articles = $this->myArticles,
        sag_Viewtype = $this->myViewtype,
		sag_DeliveryEntity = $this->myDeliveryEntity
        WHERE sag_ID = $this->mySagID";
        $this->Conn->command($sSQL);
        return($this->getSagID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
		// Element erstellen und verknüpfen, wenn nicht vorhanden
		if (getInt($this->myImage) == 0) {
			$this->setImage(elementOps::create());
		}
		// Daten speichern
        $sSQL = "INSERT INTO tbshoparticlegroup (man_ID,sag_Parent,sag_Title,sag_Desc,
        sag_Image,sag_Articles,sag_Viewtype,sag_DeliveryEntity) VALUES (
        $this->myManID,$this->myParent,'$this->myTitle',
        '$this->myDesc',$this->myImage,$this->myArticles,
        $this->myViewtype,$this->myDeliveryEntity)";
        $this->mySagID = $this->Conn->insert($sSQL);
        return($this->getSagID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
      // Element löschen
      elementOps::delete($this->getImage());
      // Lsöchen der Untergruppen
      $sSQL = 'SELECT sag_ID FROM tbshoparticlegroup WHERE sag_Parent = :sag_Parent';
      $stmt = $this->Conn->prepare($sSQL);
      $stmt->bind('sag_Parent',$this->getSagID(),PDO::PARAM_INT);
      $stmt->select();
      while ($row = $stmt->next()) {
        $subgroup = new shopArticlegroup($row['sag_ID']);
        $subgroup->delete();
      }
      // Gruppe selbst löschen
      $sSQL = 'DELETE FROM tbshoparticlegroup
      WHERE sag_ID = :sag_ID';
      $stmt = $this->Conn->prepare($sSQL);
      $stmt->bind('sag_ID',$this->getSagID(),PDO::PARAM_INT);
      $stmt->command();
    }

	/**
	 * Gibt ein Array mit den Ansichts-Optionen zurück
	 * @return array
	 */
	public function getViewTypes() {
		return(array(
			array(
				self::VIEWTYPE_LIST,
				$this->Res->html(1114, page::language())
			),
			array(
				self::VIEWTYPE_TABS,
				$this->Res->html(1115, page::language())
			)
		));
	}

	/**
	 * Gibt ein Assoziatives Array für ins Template aus
	 * @return array Key/Value Paare für Template
	 */
	public function toTemplate() {
		return(array(
			'GROUP_ID' => $this->getSagID(),
			'GROUP_LINK' => 'group.php?id='.page::menuID().'&g='.$this->getSagID(),
			'GROUP_IMAGE' => shopStatic::getElementPath($this->getImage()),
			'GROUP_IMGTAG' => $this->getImageTag(),
			'GROUP_TITLE' => $this->getTitle(),
			'GROUP_DESC' => $this->getDesc(),
			'GROUP_ARTICLES' => $this->getArticles()
		));
	}

	/**
	 * Gibt direkt einen Imagetag für die Darstellung des Gruppenbildes zurück
	 * oder einen leeren String falls kein Bild hochgeladen/ausgewählt wurde
	 * @return string HTML Code oder leerer String
	 */
	public function getImageTag() {
		$sHtml = '';
		// Nur wenn ein Bild vorhanden ist etwas anzeigen
		$sPath = shopStatic::getElementPath($this->getImage());
		$sAlt = $this->getTitle();
		if (is_file(BP.$sPath)) {
			$sHtml = '<img src="'.$sPath.'" alt="'.$sAlt.'" title="'.$sAlt.'" class="cGroupImg">';
		}
		return($sHtml);
	}

    /**
     * Getter für sag_ID: Eindeutige ID der Gruppe
     * @return int Wert von 'sag_ID'
     */
    public function getSagID() {
        return($this->mySagID);
    }

    /**
     * Getter für man_ID: Gehört zu diesem Mandant
     * @return int Wert von 'man_ID'
     */
    public function getManID() {
        return($this->myManID);
    }

    /**
     * Setter für man_ID: Gehört zu diesem Mandant
     * @param int Neuer Wert für 'man_ID'
     */
    public function setManID($value) {
        $value = getInt($value);
        $this->myManID = $value;
    }

    /**
     * Getter für sag_Parent: Übergeordnetes Element (0 = Hauptelement)
     * @return int Wert von 'sag_Parent'
     */
    public function getParent() {
        return($this->myParent);
    }

    /**
     * Setter für sag_Parent: Übergeordnetes Element (0 = Hauptelement)
     * @param int Neuer Wert für 'sag_Parent'
     */
    public function setParent($value) {
        $value = getInt($value);
        $this->myParent = $value;
    }

    /**
     * Getter für sag_Title: Titel der Gruppe
     * @return string Wert von 'sag_Title'
     */
    public function getTitle() {
        $value = stripslashes($this->myTitle);
        return($value);
    }

    /**
     * Setter für sag_Title: Titel der Gruppe
     * @param string Neuer Wert für 'sag_Title'
     */
    public function setTitle($value) {
        $this->Conn->escape($value);
        $this->myTitle = $value;
    }

    /**
     * Getter für sag_Desc: Beschreibung der Gruppe
     * @return string Wert von 'sag_Desc'
     */
    public function getDesc() {
        $value = stripslashes($this->myDesc);
        return($value);
    }

    /**
     * Setter für sag_Desc: Beschreibung der Gruppe
     * @param string Neuer Wert für 'sag_Desc'
     */
    public function setDesc($value) {
        $this->myDesc = $value;
    }

    /**
     * Getter für sag_Image: Elementen ID für ein Bild
     * @return int Wert von 'sag_Image'
     */
    public function getImage() {
        return($this->myImage);
    }

    /**
     * Setter für sag_Image: Elementen ID für ein Bild
     * @param int Neuer Wert für 'sag_Image'
     */
    public function setImage($value) {
        $value = getInt($value);
        $this->myImage = $value;
    }

    /**
     * Getter für sag_Articles: Wieviele Artikel pro Seite zeigt die Kategorie an
     * @return int Wert von 'sag_Articles'
     */
    public function getArticles() {
        return($this->myArticles);
    }

    /**
     * Setter für sag_Articles: Wieviele Artikel pro Seite zeigt die Kategorie an
     * @param int Neuer Wert für 'sag_Articles'
     */
    public function setArticles($value) {
        $value = getInt($value);
        $this->myArticles = $value;
    }

    /**
     * Getter für sag_Viewtype: 0 = Alle Artikel zeigen / 1 = ABC Register
     * @return int Wert von 'sag_Viewtype'
     */
    public function getViewtype() {
        return($this->myViewtype);
    }

    /**
     * Setter für sag_Viewtype: 0 = Alle Artikel zeigen / 1 = ABC Register
     * @param int Neuer Wert für 'sag_Viewtype'
     */
    public function setViewtype($value) {
        $value = getInt($value);
        $this->myViewtype = $value;
    }

    /**
     * Getter für sag_DeliveryEntity: Anzahl Liefereinheiten der Artikel in der Gruppe
     * @return int Wert von 'sag_DeliveryEntity'
     */
    public function getDeliveryEntity() {
        return($this->myDeliveryEntity);
    }

    /**
     * Setter für sag_DeliveryEntity: Anzahl Liefereinheiten der Artikel in der Gruppe
     * @param int Neuer Wert für 'sag_DeliveryEntity'
     */
    public function setDeliveryEntity($value) {
        $value = getInt($value);
        $this->myDeliveryEntity = $value;
    }

}