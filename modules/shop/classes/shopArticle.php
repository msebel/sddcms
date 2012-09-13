<?php
/**
 * Repräsentiert einen Shopartikel
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopArticle extends abstractRow {

    /**
     * sha_ID: ID des Artikels
     * @var int
     */
    private $myShaID = 0;
    /**
     * con_ID: ID des Content für Freitext
     * @var int
     */
    private $myConID = 0;
    /**
     * man_ID: Besitzender Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * sha_Image: Hauptbild ID
     * @var int
     */
    private $myImage = 0;
    /**
     * sha_Tip: 0 = Nichts / 1 = Artikel des Tages
     * @var int
     */
    private $myTip = 0;
    /**
     * sha_Action: 0 = Nichts / 1 = Aktionsartikel
     * @var int
     */
    private $myAction = 0;
    /**
     * sha_New: 0 = Nichts / 1 = Neuer Artikel
     * @var int
     */
    private $myNew = 0;
	/**
	 * sha_Active: 0 = Inaktiv / 1 = Aktiv
	 * @var int
	 */
	private $myActive = 0;
    /**
     * sha_Title: Titel des Artikels
     * @var string
     */
    private $myTitle = '';
    /**
     * sha_Price: Preis des Artikels
     * @var double
     */
    private $myPrice = 0.0;
    /**
     * sha_PriceAction: Preis während einer Aktion
     * @var double
     */
    private $myPriceAction = 0.0;
    /**
     * sha_Mwst: Mehrwertsteuersatz
     * @var double
     */
    private $myMwst = 0.0;
    /**
     * sha_Guarantee: Gibt Garantiedefinitionen an
     * @var string
     */
    private $myGuarantee = '';
    /**
     * sha_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @var string
     */
    private $myArticlenumber = '';
    /**
     * sha_DeliveryEntity: Anzahl Gewichtseinheiten für Versand (Überschreibt Gruppenkonfig)
     * @var int
     */
    private $myDeliveryEntity = 0;
    /**
     * sha_Purchased: Anzahl käufe dieses Artikels
     * @var int
     */
    private $myPurchased = 0;
    /**
     * sha_Removed: So oft wurde der Artikel aus dem Warenkobr entfernt
     * @var int
     */
    private $myRemoved = 0;
    /**
     * sha_Visited: Anzahl aufrufe dieses Artikels
     * @var int
     */
    private $myVisited = 0;
	/**
	 * Geladener Content aus der con_ID
	 * @var string
	 */
	private $myContent = '';

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
        $sSQL = 'SELECT sha_ID,con_ID,man_ID,sha_Image,sha_Tip,sha_Action,sha_New,
        sha_Title,sha_Price,sha_PriceAction,sha_Mwst,sha_Guarantee,sha_Articlenumber,
        sha_DeliveryEntity,sha_Purchased,sha_Removed,sha_Visited,sha_Active
        FROM tbshoparticle WHERE sha_ID = '.$nID;
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
        $this->myShaID = getInt($row['sha_ID']);
        $this->setConID($row['con_ID']);
        $this->setManID($row['man_ID']);
        $this->setImage($row['sha_Image']);
        $this->setTip($row['sha_Tip']);
        $this->setAction($row['sha_Action']);
        $this->setNew($row['sha_New']);
		$this->setActive($row['sha_Active']);
        $this->setTitle($row['sha_Title']);
        $this->setPrice($row['sha_Price']);
        $this->setPriceAction($row['sha_PriceAction']);
        $this->setMwst($row['sha_Mwst']);
        $this->setGuarantee($row['sha_Guarantee']);
        $this->setArticlenumber($row['sha_Articlenumber']);
        $this->setDeliveryEntity($row['sha_DeliveryEntity']);
        $this->setPurchased($row['sha_Purchased']);
        $this->setRemoved($row['sha_Removed']);
        $this->setVisited($row['sha_Visited']);
		// Content Inhalt laden
		$sSQL = 'SELECT con_Content FROM tbcontent WHERE con_ID = '.$this->getConID();
		$this->myContent = $this->Conn->getFirstResult($sSQL);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
      $sSQL = 'UPDATE tbshoparticle SET
      con_ID = :con_ID, man_ID = :man_ID, sha_Image = :sha_Image, sha_Tip = :sha_Tip,
      sha_Action = :sha_Action, sha_New = :sha_New, sha_Active = :sha_Active, sha_Title = :sha_Title,
      sha_Price = :sha_Price, sha_PriceAction = :sha_PriceAction, sha_Mwst = :sha_Mwst,
      sha_Guarantee = :sha_Guarantee, sha_Articlenumber = :sha_Articlenumber, sha_DeliveryEntity = :sha_DeliveryEntity,
      sha_Purchased = :sha_Purchased,sha_Removed = :sha_Removed, sha_Visited = :sha_Visited
      WHERE sha_ID = :sha_ID';
      $Stmt = $this->Conn->prepare($sSQL);
      $Stmt->bind('con_ID',$this->myConID,PDO::PARAM_INT);
      $Stmt->bind('man_ID',$this->myManID,PDO::PARAM_INT);
      $Stmt->bind('sha_Image',$this->myImage,PDO::PARAM_INT);
      $Stmt->bind('sha_Tip',$this->myTip,PDO::PARAM_INT);
      $Stmt->bind('sha_Action',$this->myAction,PDO::PARAM_INT);
      $Stmt->bind('sha_New',$this->myNew,PDO::PARAM_INT);
      $Stmt->bind('sha_Active',$this->myActive,PDO::PARAM_INT);
      $Stmt->bind('sha_Title',$this->myTitle,PDO::PARAM_STR);
      $Stmt->bind('sha_Price',$this->myPrice,PDO::PARAM_STR);
      $Stmt->bind('sha_PriceAction',$this->myPriceAction,PDO::PARAM_STR);
      $Stmt->bind('sha_Mwst',$this->myMwst,PDO::PARAM_STR);
      $Stmt->bind('sha_Guarantee',$this->myGuarantee,PDO::PARAM_STR);
      $Stmt->bind('sha_Articlenumber',$this->myArticlenumber,PDO::PARAM_STR);
      $Stmt->bind('sha_DeliveryEntity',$this->myDeliveryEntity,PDO::PARAM_INT);
      $Stmt->bind('sha_Purchased',$this->myPurchased,PDO::PARAM_INT);
      $Stmt->bind('sha_Removed',$this->myRemoved,PDO::PARAM_INT);
      $Stmt->bind('sha_Visited',$this->myVisited,PDO::PARAM_INT);
      $Stmt->bind('sha_ID',$this->myShaID,PDO::PARAM_INT);
      $Stmt->command();
      // Content speichern
      $sSQL = 'UPDATE tbcontent SET con_Content = :con_Content WHERE con_ID = :con_ID';
      $Stmt = $this->Conn->prepare($sSQL);
      $Stmt->bind('con_Content',$this->myContent,PDO::PARAM_STR);
      $Stmt->bind('con_ID',$this->myConID,PDO::PARAM_INT);
      $Stmt->command();
      return($this->getShaID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
		// Leeren Content erstellen
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		$this->myConID = ownerID::get($this->Conn);
		$sSQL = "INSERT INTO tbcontent (con_ID,usr_ID,con_Date,con_Modified,con_Title,con_Content)
		VALUES ($this->myConID,".$_SESSION['userid'].",'$sDate','$sDate','$this->myTitle','$this->myContent')";
		$this->Conn->command($sSQL);
		// Element für Bilder erstellen
		$sSQL = "INSERT INTO tbelement (owner_ID,ele_Date,ele_Creationdate) 
		VALUES ($this->myConID,'$sDate','$sDate')";
		$this->myImage = $this->Conn->insert($sSQL);
		// Artikel erstellen
        $sSQL = "INSERT INTO tbshoparticle (con_ID,man_ID,sha_Image,
        sha_Tip,sha_Action,sha_New,sha_Title,sha_Price,sha_PriceAction,
        sha_Mwst,sha_Guarantee,sha_Articlenumber,sha_DeliveryEntity,sha_Purchased,
        sha_Removed,sha_Visited,sha_Active) VALUES (
        $this->myConID,$this->myManID,$this->myImage,$this->myTip,
        $this->myAction,$this->myNew,'$this->myTitle',$this->myPrice,
        $this->myPriceAction,$this->myMwst,'$this->myGuarantee','$this->myArticlenumber',
        $this->myDeliveryEntity,$this->myPurchased,$this->myRemoved,$this->myVisited,
		$this->myActive)";
		// Artikel erstellen
        $this->myShaID = $this->Conn->insert($sSQL);
        return($this->getShaID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
		// Artikel selbst löschen
        $sSQL = "DELETE FROM tbshoparticle WHERE sha_ID = ".$this->getShaID();
        $this->Conn->command($sSQL);
		// Artikelgrössen löschen (tbshoparticlesize)
		$sSQL = "DELETE FROM tbshoparticlesize WHERE sha_ID = ".$this->getShaID();
        $this->Conn->command($sSQL);
		// Artikel Zusatzdaten löschen (tbshopdynamicdata)
		$sSQL = "DELETE FROM tbshopdynamicdata WHERE sha_ID = ".$this->getShaID();
        $this->Conn->command($sSQL);
		// Zugehörigkeit in Gruppen löschen (_articlegroup)
		$sSQL = "DELETE FROM tbshoparticle_articlegroup WHERE sha_ID = ".$this->getShaID();
        $this->Conn->command($sSQL);
		// Lagerdaten löschen (_stockarea)
		$sSQL = "DELETE FROM tbshoparticle_stockarea WHERE sha_ID = ".$this->getShaID();
        $this->Conn->command($sSQL);
		// Elementdaten löschen
		if ($this->myImage > 0) {
			$sPath = shopModuleConfig::ELEMENT_PATH;
			$sPath = str_replace('{PAGE}', page::menuID(), $sPath);
			$sPath = str_replace('{ELEMENT}', $this->myImage, $sPath);
			// Ordner rekursiv löschen
			fileOps::deleteFolder($sPath);
			// Element löschen
			$sSQL = "DELETE FROM tbelement WHERE ele_ID = $this->myImage";
			$this->Conn->command($sSQL);
		}
    }

	/**
	 * Gibt Assoziatives Array für Template aus (Kurzversion)
	 * @param string $key Wenn nötig nur diese Variable zurückgeben
	 */
	public function toTemplate($key = '') {
		// Assoziatives Array für Template zurückgeben
		$data = array(
			'ARTICLE_ID' => $this->getShaID(),
			'ARTICLE_LINK' => $this->getArticleLink(),
			'ARTICLE_IMAGE_THUMB' => $this->getImageLink('thumb'),
			'ARTICLE_IMAGE_RESIZE' => $this->getImageLink('resize'),
			'ARTICLE_IMAGE_ORIGINAL' => $this->getImageLink('original'),
			'ARTICLE_PRICE' => $this->getCurrentPrice(),
			'ARTICLE_TITLE' => $this->getTitle(),
			'ARTICLE_NUMBER' => $this->getArticlenumber(),
			'ARTICLE_CONTENT_SHORT' => $this->getContentShort(),
			'ARTICLE_CONTENT' => $this->getContent(),
			'ARTICLE_TIP' => $this->getTipHtml(),
			'ARTICLE_NEW' => $this->getNewHtml(),
			'ARTICLE_ACTION' => $this->getActionHtml()
		);
		//Evtl. nur eine Variable zurückgeben
		if (strlen($key) > 0) return($data[$key]);
		// Ansonsten alles zurückgeben
		return($data);
	}

	/**
	 * HTML Code für Tipp des Tages
	 * @return string leer oder HTML für Tip des Tages, wenn es einer ist
	 */
	public function getTipHtml() {
		// Nur, wenn es ein Tip ist
		if ($this->getTip() == 1) {
			return('
			<div class="cSALentryTip">
				'.getResources::getInstance($this->Conn)->html(998, page::language()).'
			</div>
			');
		}
		return('');
	}

	/**
	 * HTML Code für neuen Artikel
	 * @return string leer oder HTML für neuen Artikel, wenn es einer ist
	 */
	public function getNewHtml() {
		// Nur, wenn es ein Tip ist
		if ($this->getNew() == 1) {
			return('
			<div class="cSALentryNew">
				'.getResources::getInstance($this->Conn)->html(1000, page::language()).'
			</div>
			');
		}
		return('');
	}

	/**
	 * HTML Code für Aktion
	 * @return string leer oder HTML für Aktion, wenn es einer ist
	 */
	public function getActionHtml() {
		// Nur, wenn es ein Tip ist
		if ($this->getAction() == 1) {
			return('
			<div class="cSALentryAction">
				'.getResources::getInstance($this->Conn)->html(999, page::language()).'
			</div>
			');
		}
		return('');
	}

	/**
	 * Zählt die Grössen eines Artikels
	 * @return int Anzahl Artikelgrössen
	 */
	public function countSizes() {
		$sSQL = 'SELECT COUNT(saz_ID) FROM tbshoparticlesize
		WHERE sha_ID = '.$this->getShaID();
		return($this->Conn->getCountResult($sSQL));
	}

	/**
	 * Gibt an, ob -Artikelgrössen vorhandens ind
	 * @return bool true/false ob Grössen vorhanden oder nicht
	 */
	public function hasSizes() {
		if ($this->countSizes() > 0) {
			return(true);
		}
		return(false);
	}

	/**
	 * Array aller Artikelgrössen zurückgekommen
	 */
	public function getSizesArray() {
		$sizes = array();
		$sSQL = 'SELECT saz_ID,sha_ID,saz_Value,saz_Priceadd,saz_Primary FROM tbshoparticlesize
		WHERE sha_ID = '.$this->getShaID().' ORDER BY saz_Priceadd ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$size = new shopArticlesize();
			$size->loadRow($row);
			array_push($sizes,$size);
		}
		return($sizes);
	}

	/**
	 * Gibt den aktuellen Preis zurück, je nachdem ob gerade
	 * eine Aktion ist und damit der Aktionspreis gilt
	 */
	public function getCurrentPrice() {
		if ($this->getAction() == 1) {
			return($this->getPriceAction());
		} else {
			return($this->getPrice());
		}
	}

	/**
	 * Gibt den passenden Link für eine Image Source des Artikels zurück
	 * @param string $type thumb, original oder sized (3 mögliche Grössen)
	 * @return string Link für in das SRC Attribut eines img Tags
	 */
	public function getImageLink($type) {
		return('/modules/shop/getimage.php?id='.page::menuID().'&e='.$this->getImage().'&type='.$type);
	}

	/**
	 * Gibt einen Link an um die Details des Artikels anzusehen
	 * @return string Link welcher auf die Detailseite des Artikels zeigt
	 */
	public function getArticleLink() {
		return('/modules/shop/view/article.php?id='.page::menuID().'&a='.$this->getShaID());
	}

	/**
	 * Gibt openWindow Funktion für Javascript Links zurück
	 * @return string
	 */
	public function getOpenWinCode() {
		$link = '/modules/shop/view/article.php?id='.page::menuID().'&a='.$this->getShaID();
		return('openWindow(\''.$link.'\',\'Article\',900,700)');
	}

	/**
	 * Nicht verwendete Lagerstätten zurückgeben
	 * @return array Array von shopStockarea Elementen
	 */
	public function getUnusedAreas() {
		$connections = array();
		$sSQL = 'SELECT ssa_ID FROM tbshoparticle_stockarea
		WHERE sha_ID = '.$this->getShaID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($connections,$row['ssa_ID']);
		}
		$areas = array();
		$sSQL = 'SELECT ssa_ID,man_ID,ssa_Name,ssa_Opening,ssa_Delivery
		FROM tbshopstockarea ORDER BY ssa_Name ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			if (!in_array($row['ssa_ID'], $connections)) {
				$area = new shopStockarea();
				$area->loadRow($row);
				array_push($areas,$area);
			}
		}
		return($areas);
	}

	/**
	 * Erstellt einen Artikel mit Bestellverknüpfung
	 * @return shopOrderarticle Bestellartikel
	 */
	public function getOrderInstance() {
		$orderart = new shopOrderarticle();
		// Alle Daten aus dem aktuellen Objekt hinzufügen
		$orderart->setArticlenumber($this->getArticlenumber());
		$orderart->setDeliveryEntity($this->getDeliveryEntity());
		$orderart->setGuarantee($this->getGuarantee());
		$orderart->setManID(page::mandant());
		$orderart->setMwst($this->getMwst());
		$orderart->setPrice($this->getCurrentPrice());
		$orderart->setShaID($this->getShaID());
		$orderart->setTitle($this->getTitle());
		// Objekt zurückgeben
		return($orderart);
	}

    /**
     * Getter für sha_ID: ID des Artikels
     * @return int Wert von 'sha_ID'
     */
    public function getShaID() {
        return($this->myShaID);
    }

    /**
     * Getter für con_ID: ID des Content für Freitext
     * @return int Wert von 'con_ID'
     */
    public function getConID() {
        return($this->myConID);
    }

    /**
     * Setter für con_ID: ID des Content für Freitext
     * @param int Neuer Wert für 'con_ID'
     */
    public function setConID($value) {
        $value = getInt($value);
        $this->myConID = $value;
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
     * Getter für sha_Image: Hauptbild ID
     * @return int Wert von 'sha_Image'
     */
    public function getImage() {
        return($this->myImage);
    }

    /**
     * Setter für sha_Image: Hauptbild ID
     * @param int Neuer Wert für 'sha_Image'
     */
    public function setImage($value) {
        $value = getInt($value);
        $this->myImage = $value;
    }

    /**
     * Getter für sha_Tip: 0 = Nichts / 1 = Artikel des Tages
     * @return int Wert von 'sha_Tip'
     */
    public function getTip() {
        return($this->myTip);
    }

    /**
     * Setter für sha_Tip: 0 = Nichts / 1 = Artikel des Tages
     * @param int Neuer Wert für 'sha_Tip'
     */
    public function setTip($value) {
        $value = getInt($value);
        $this->myTip = $value;
    }

    /**
     * Getter für sha_Action: 0 = Nichts / 1 = Aktionsartikel
     * @return int Wert von 'sha_Action'
     */
    public function getAction() {
        return($this->myAction);
    }

    /**
     * Setter für sha_Action: 0 = Nichts / 1 = Aktionsartikel
     * @param int Neuer Wert für 'sha_Action'
     */
    public function setAction($value) {
        $value = getInt($value);
        $this->myAction = $value;
    }

    /**
     * Getter für sha_New: 0 = Nichts / 1 = Neuer Artikel
     * @return int Wert von 'sha_New'
     */
    public function getNew() {
        return($this->myNew);
    }

    /**
     * Setter für sha_New: 0 = Nichts / 1 = Neuer Artikel
     * @param int Neuer Wert für 'sha_New'
     */
    public function setNew($value) {
        $value = getInt($value);
        $this->myNew = $value;
    }

	 /**
     * Getter für sha_Active: 0 = Inaktiv / 1 = Aktiv
     * @return int Wert von 'sha_Active'
     */
    public function getActive() {
        return($this->myActive);
    }

    /**
     * Setter für sha_Active: 0 = Inaktiv / 1 = Aktiv
     * @param int Neuer Wert für 'sha_Active'
     */
    public function setActive($value) {
        $value = getInt($value);
        $this->myActive = $value;
    }

    /**
     * Getter für sha_Title: Titel des Artikels
     * @return string Wert von 'sha_Title'
     */
    public function getTitle() {
        $value = stripslashes($this->myTitle);
        return($value);
    }

    /**
     * Setter für sha_Title: Titel des Artikels
     * @param string Neuer Wert für 'sha_Title'
     */
    public function setTitle($value) {
        $this->Conn->escape($value);
        $this->myTitle = $value;
    }

	/**
     * Getter für den Content aus con_ID
     * @return string Wert von 'sha_Title'
     */
    public function getContent() {
        $value = stripslashes($this->myContent);
        return($value);
    }

	/**
     * Getter für abgekürzten Content
     * @return string Wert von 'sha_Title'
     */
    public function getContentShort() {
        $value = stripslashes($this->myContent);
		stringOps::noHtml($value);
		$value = stringOps::chopToWords($value, 60, true);
        return($value);
    }

	/**
	 * Setter für den Textuellen Content des Artikels
	 * @param string $value
	 */
	public function setContent($value) {
		$this->Conn->escape($value);
        $this->myContent = $value;
	}

    /**
     * Getter für sha_Price: Preis des Artikels
     * @return double Wert von 'sha_Price'
     */
    public function getPrice() {
		return($this->myPrice);
    }

    /**
     * Setter für sha_Price: Preis des Artikels
     * @param double Neuer Wert für 'sha_Price'
     */
    public function setPrice($value) {
		$value = numericOps::getDecimal($value, 2);
		$this->myPrice = $value;
    }

    /**
     * Getter für sha_PriceAction: Preis während einer Aktion
     * @return double Wert von 'sha_PriceAction'
     */
    public function getPriceAction() {
		return($this->myPriceAction);
    }

    /**
     * Setter für sha_PriceAction: Preis während einer Aktion
     * @param double Neuer Wert für 'sha_PriceAction'
     */
    public function setPriceAction($value) {
		$value = numericOps::getDecimal($value, 2);
		$this->myPriceAction = $value;
    }

    /**
     * Getter für sha_Mwst: Mehrwertsteuersatz
     * @return double Wert von 'sha_Mwst'
     */
    public function getMwst() {
		return($this->myMwst);
    }

    /**
     * Setter für sha_Mwst: Mehrwertsteuersatz
     * @param double Neuer Wert für 'sha_Mwst'
     */
    public function setMwst($value) {
		$value = numericOps::getDecimal($value, 1);
		$this->myMwst = $value;
    }

    /**
     * Getter für sha_Guarantee: Gibt Garantiedefinitionen an
     * @return string Wert von 'sha_Guarantee'
     */
    public function getGuarantee() {
        $value = stripslashes($this->myGuarantee);
        return($value);
    }

    /**
     * Setter für sha_Guarantee: Gibt Garantiedefinitionen an
     * @param string Neuer Wert für 'sha_Guarantee'
     */
    public function setGuarantee($value) {
        $this->Conn->escape($value);
        $this->myGuarantee = $value;
    }

    /**
     * Getter für sha_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @return string Wert von 'sha_Articlenumber'
     */
    public function getArticlenumber() {
		if (strlen($this->myArticlenumber) > 0) {
			$value = stripslashes($this->myArticlenumber);
			return($value);
		} else {
			// Ansonsten interne ID zurückgeben
			return($this->getShaID());
		}
    }

    /**
     * Setter für sha_Articlenumber: Externe Artikelnummer (Standard = sha_ID)
     * @param string Neuer Wert für 'sha_Articlenumber'
     */
    public function setArticlenumber($value) {
        $this->Conn->escape($value);
        $this->myArticlenumber = $value;
    }

    /**
     * Getter für sha_DeliveryEntity: Anzahl Gewichtseinheiten für Versand (Überschreibt Gruppenkonfig)
     * @return int Wert von 'sha_DeliveryEntity'
     */
    public function getDeliveryEntity() {
        return($this->myDeliveryEntity);
    }

    /**
     * Setter für sha_DeliveryEntity: Anzahl Gewichtseinheiten für Versand (Überschreibt Gruppenkonfig)
     * @param int Neuer Wert für 'sha_DeliveryEntity'
     */
    public function setDeliveryEntity($value) {
        $value = getInt($value);
        $this->myDeliveryEntity = $value;
    }

    /**
     * Getter für sha_Purchased: Anzahl käufe dieses Artikels
     * @return int Wert von 'sha_Purchased'
     */
    public function getPurchased() {
        return($this->myPurchased);
    }

    /**
     * Setter für sha_Purchased: Anzahl käufe dieses Artikels
     * @param int Neuer Wert für 'sha_Purchased'
     */
    public function setPurchased($value) {
        $value = getInt($value);
        $this->myPurchased = $value;
    }

    /**
     * Getter für sha_Removed: So oft wurde der Artikel aus dem Warenkobr entfernt
     * @return int Wert von 'sha_Removed'
     */
    public function getRemoved() {
        return($this->myRemoved);
    }

    /**
     * Setter für sha_Removed: So oft wurde der Artikel aus dem Warenkobr entfernt
     * @param int Neuer Wert für 'sha_Removed'
     */
    public function setRemoved($value) {
        $value = getInt($value);
        $this->myRemoved = $value;
    }

    /**
     * Getter für sha_Visited: Anzahl aufrufe dieses Artikels
     * @return int Wert von 'sha_Visited'
     */
    public function getVisited() {
        return($this->myVisited);
    }

    /**
     * Setter für sha_Visited: Anzahl aufrufe dieses Artikels
     * @param int Neuer Wert für 'sha_Visited'
     */
    public function setVisited($value) {
        $value = getInt($value);
        $this->myVisited = $value;
    }
}