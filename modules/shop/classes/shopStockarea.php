<?php
/**
 * Repräsentiert eine Lagerstelle des Shops
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopStockarea extends abstractRow {

    /**
     * ssa_ID: ID des Lagers
     * @var int
     */
    private $mySsaID = 0;
    /**
     * man_ID: Zugehöriger Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * ssa_Name: Name des Lagers
     * @var string
     */
    private $myName = '';
    /**
     * ssa_Opening: Öffnungszeiten
     * @var string
     */
    private $myOpening = '';
    /**
     * ssa_Delivery: 0 = Normal / 1 = Versandlager
     * @var int
     */
    private $myDelivery = 0;

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
        $sSQL = 'SELECT ssa_ID,man_ID,ssa_Name,ssa_Opening,ssa_Delivery
        FROM tbshopstockarea
        WHERE ssa_ID = '.$nID;
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
        $this->mySsaID = getInt($row['ssa_ID']);
        $this->setManID($row['man_ID']);
        $this->setName($row['ssa_Name']);
        $this->setOpening($row['ssa_Opening']);
        $this->setDelivery($row['ssa_Delivery']);
        // Objekt als initialisiert taxieren
        $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshopstockarea SET
        man_ID = $this->myManID,ssa_Name = '$this->myName',
        ssa_Opening = '$this->myOpening',ssa_Delivery = $this->myDelivery

        WHERE ssa_ID = $this->mySsaID";
        $this->Conn->command($sSQL);
        return($this->getSsaID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshopstockarea (man_ID,ssa_Name,ssa_Opening,ssa_Delivery) 
        VALUES ($this->myManID,'$this->myName','$this->myOpening',
        $this->myDelivery)";
        $this->mySsaID = $this->Conn->insert($sSQL);
        return($this->getSsaID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshopstockarea
        WHERE ssa_ID = ".$this->getSsaID();
        $this->Conn->command($sSQL);
    }

	/**
	 * Mail senden, dass dieser Artikel im aktuellen Lager gerade aus gegangen ist
	 * @param shopArticle $article Artikel der nicht mehr an Lager ist
	 */
	public function sendOutOfStockMail(shopOrderarticle $article) {
		// Template Pfad und Template laden
		$tPath = shopStatic::getMailTemplate('article-outofstock');
		$tpl = new templateImproved($tPath);
		// Wichtige Variablen ersetzen
		$tpl->addData('ARTICLE_TITLE',$article->toTemplate('SOA_TITLE'));
		$tpl->addData('AREA_NAME',$this->toTemplate('AREA_NAME'));
		$tpl->addData('DEACTIVATION_INFO', $this->Res->normal(1143, page::language()));
		// Mail Erstellen und konfigurieren
		$mail = new phpMailer();
		$Mail->CharSet = 'utf-8';
		$Mail->Encoding = 'quoted-printable';
		$mail->From = shopModuleConfig::MAIL_FROM;
		$mail->FromName = shopModuleConfig::MAIL_FROMNAME;
		$mail->AddAddress(shopModuleConfig::MAIL_FROM);
		// Inhalte definieren
		$mail->IsHTML(true);
		$mail->Body = $tpl->output();
		$mail->Subject = $this->Res->normal(1144, page::language());
		// Absenden
		$mail->Send();
	}

	/**
	 * Gibt Assoziatives Array für Template aus (Kurzversion)
	 * @param string $key Wenn nötig nur diese Variable zurückgeben
	 */
	public function toTemplate($key = '') {
		// Assoziatives Array für Template zurückgeben
		$data = array(
			'AREA_ID' => $this->getSsaID(),
			'AREA_DELIVERY' => $this->getDelivery(),
			'AREA_NAME' => $this->getName(),
			'AREA_OPENING' => $this->getOpening()
		);
		//Evtl. nur eine Variable zurückgeben
		if (strlen($key) > 0) {
			return($data[$key]);
		}
		// Ansonsten alles zurückgeben
		return($data);
	}

    /**
     * Getter für ssa_ID: ID des Lagers
     * @return int Wert von 'ssa_ID'
     */
    public function getSsaID() {
        return($this->mySsaID);
    }

    /**
     * Getter für man_ID: Zugehöriger Mandant
     * @return int Wert von 'man_ID'
     */
    public function getManID() {
        return($this->myManID);
    }

    /**
     * Setter für man_ID: Zugehöriger Mandant
     * @param int Neuer Wert für 'man_ID'
     */
    public function setManID($value) {
        $value = getInt($value);
        $this->myManID = $value;
    }

    /**
     * Getter für ssa_Name: Name des Lagers
     * @return string Wert von 'ssa_Name'
     */
    public function getName() {
        $value = stripslashes($this->myName);
        return($value);
    }

    /**
     * Setter für ssa_Name: Name des Lagers
     * @param string Neuer Wert für 'ssa_Name'
     */
    public function setName($value) {
        $this->Conn->escape($value);
        $this->myName = $value;
    }

    /**
     * Getter für ssa_Opening: Öffnungszeiten
     * @return string Wert von 'ssa_Opening'
     */
    public function getOpening() {
        $value = stripslashes($this->myOpening);
        return($value);
    }

    /**
     * Setter für ssa_Opening: Öffnungszeiten
     * @param string Neuer Wert für 'ssa_Opening'
     */
    public function setOpening($value) {
        $this->Conn->escape($value);
        $this->myOpening = $value;
    }

    /**
     * Getter für ssa_Delivery: 0 = Normal / 1 = Versandlager
     * @return int Wert von 'ssa_Delivery'
     */
    public function getDelivery() {
        return($this->myDelivery);
    }

    /**
     * Setter für ssa_Delivery: 0 = Normal / 1 = Versandlager
     * @param int Neuer Wert für 'ssa_Delivery'
     */
    public function setDelivery($value) {
        $value = getInt($value);
        $this->myDelivery = $value;
    }

}