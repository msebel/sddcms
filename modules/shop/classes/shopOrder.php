<?php
/**
 * Bestellungsobjekt eines Shops
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopOrder extends abstractRow {

    /**
     * sho_ID: ID der Bestellung
     * @var int
     */
    private $myShoID = 0;
    /**
     * shu_ID: Benutzer der die Bestellung machte
     * @var int
     */
    private $myShuID = 0;
    /**
     * scp_ID: Eingelöster Gutschein (0, wenn keiner)
     * @var int
     */
    private $myScpID = 0;
    /**
     * man_ID: Besitzender Mandant
     * @var int
     */
    private $myManID = 0;
    /**
     * sho_Total: Total der Rechnung
     * @var double
     */
    private $myTotal = 0.0;
    /**
     * sho_Date: Datum der Bestellung
     * @var string
     */
    private $myDate = '';
		/**
		 * sho_Message: Message des Bestellers
		 * @var string
		 */
		private $myMessage = '';
    /**
     * sho_Payment: Art der Zahlung (Vorauskasse / Rechnung)
     * @var int
     */
    private $myPayment = 0;
    /**
     * sho_State: 0 = Warenkorb / 1 = Offen (Bestellt) / 2 = Bezahlt / 3 = Abgesendet
     * @var int
     */
    private $myState = 0;
	/**
     * sho_Deliveryaddress: Lieferadresse (Referenz)
     * @var int
     */
    private $myDeliveryaddress = 0;
    /**
     * sho_Billingaddress: Rechnungsadresse für die Bestellung
     * @var int
     */
    private $myBillingaddress = 0;
	/**
	 * Steht für den Bestellstatus "Im Warenkorb"
	 * @var int
	 */
	const STATE_CART = 0;
	/**
	 * Steht für den Bestellstatus "Bestellt" (Offen)
	 * @var int
	 */
	const STATE_OPEN = 1;
	/**
	 * Steht für den Bestellstatus "Bezahlt"
	 * @var int
	 */
	const STATE_PAID = 2;
	/**
	 * Steht für den Bestellstatus "Abgesendet" (Abgeschlossen)
	 * @var int
	 */
	const STATE_SENT = 3;
	/**
	 * Art einer Zahlung: Vorauskasse
	 * @var int
	 */
	const PAYMENT_PREPAID = 1;
	/**
	 * Art einer Zahlung: Rechnung
	 * @var int
	 */
	const PAYMENT_BILL = 2;
	/**
	 * Art einer Zahlung: PayPEEEL
	 * @var int
	 */
	const PAYMENT_PAYPAL = 3;

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
        $sSQL = 'SELECT sho_ID,shu_ID,scp_ID,man_ID,sho_Total,sho_Date,sho_Payment,
        sho_State,sho_Deliveryaddress,sho_Billingaddress,sho_Message FROM tbshoporder
		WHERE sho_ID = '.$nID;
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
      $this->myShoID = getInt($row['sho_ID']);
      $this->setShuID($row['shu_ID']);
      $this->setScpID($row['scp_ID']);
      $this->setManID($row['man_ID']);
      $this->setTotal($row['sho_Total']);
      $this->setDate($row['sho_Date']);
      $this->setPayment($row['sho_Payment']);
      $this->setState($row['sho_State']);
			$this->setDeliveryaddress($row['sho_Deliveryaddress']);
      $this->setBillingaddress($row['sho_Billingaddress']);
			$this->setMessage($row['sho_Message']);
      // Objekt als initialisiert taxieren
      $this->isInitialized = true;
    }

    /**
     * Speichert die lokalen Daten
     * @return int Primärschlüssel
     */
    public function update() {
        $sSQL = "UPDATE tbshoporder SET
        shu_ID = $this->myShuID,scp_ID = $this->myScpID,
        man_ID = $this->myManID,sho_Total = $this->myTotal,
        sho_Date = '$this->myDate',sho_Payment = $this->myPayment,
        sho_State = $this->myState,sho_Deliveryaddress = $this->myDeliveryaddress,
        sho_Billingaddress = $this->myBillingaddress,sho_Message = '$this->myMessage'
        WHERE sho_ID = $this->myShoID";
        $this->Conn->command($sSQL);
        return($this->getShoID());
    }

    /**
     * Erstellt die lokalen Daten
     * @return int Primärschlüssel
     */
    public function insert() {
        $sSQL = "INSERT INTO tbshoporder (shu_ID,scp_ID,man_ID,sho_Total,
        sho_Date,sho_Payment,sho_State,sho_Deliveryaddress,sho_Billingaddress,sho_Message)
        VALUES ($this->myShuID,$this->myScpID,$this->myManID,
        $this->myTotal,'$this->myDate',$this->myPayment,
        $this->myState,$this->myDeliveryaddress,$this->myBillingaddress,
        '$this->myMessage')";
        $this->myShoID = $this->Conn->insert($sSQL);
        return($this->getShoID());
    }

    /**
     * Simple Löschfunktion
     */
    public function delete() {
        $sSQL = "DELETE FROM tbshoporder
        WHERE sho_ID = ".$this->getShoID();
        $this->Conn->command($sSQL);
    }

	/**
	 * Holt eine Order ID (Neuer oder bestehender Session Order)
	 * und gibt diese zurück. Sollte nur statisch verwendet werden
	 */
	public static function getSessionOrder() {
		if (!isset($_SESSION['ShopOrderID'])) {
			// Neuen leeren Order erstellen
			$order = new shopOrder();
			$order->setManID(page::mandant());
			$order->setState(self::STATE_CART);
			$order->setDate(dateOps::getTime(
				dateOps::SQL_DATETIME,time())
			);
			$_SESSION['ShopOrderID'] = $order->save();
		}
		// Den bestehenden oder neuen Order zurückgeben
		return($_SESSION['ShopOrderID']);
	}

	/**
	 * Löscht den aktuellen Order aus der Session
	 */
	public static function deleteSessionOrder() {
		unset($_SESSION['ShopOrderID']);
	}

	/**
	 * Gibt eine Liste aller Artikel zurück (Objektinstanzen)
	 * @return array Liste aller Artikel (shopOrderarticle)
	 */
	public function getArticles() {
		$articles = array();
		$nRes = $this->getArticlesRes();
        while ($row = $this->Conn->next($nRes)) {
            $article = new shopOrderarticle();
			$article->loadRow($row);
			array_push($articles,$article);
        }
		return($articles);
	}

	/**
	 * Gibt eine Resource zum loopen eines Recordsets zurück um
	 * alle Artikel des aktuellen Orders zu holen
	 */
	public function getArticlesRes() {
		$sSQL = 'SELECT soa_ID,sho_ID,sha_ID,man_ID,soa_Title,soa_Size,soa_Price,
    soa_Mwst,soa_Guarantee,soa_Articlenumber,soa_DeliveryEntity
    FROM tbshoporderarticle WHERE sho_ID = '.$this->getShoID().'
		ORDER BY soa_ID ASC';
    return($this->Conn->execute($sSQL));
	}

	/**
	 * Gibt ein Array mit den Bestell-Status zurück
	 * @return array
	 */
	public function getStates() {
		return(array(
			array(self::STATE_OPEN,$this->Res->html(1140,page::language()),"basket.png"),
			array(self::STATE_PAID,$this->Res->html(1141,page::language()),"coins.png"),
			array(self::STATE_SENT,$this->Res->html(1142,page::language()),"package_go.png")
		));
	}

	/**
	 * Gibt für den Bestellstatus das enstprechende Icon zurück
	 * @return string
	 */
	public function getStateIcon() {
		foreach ($this->getStates() as $state) {
			if ($this->getState() == $state[0]) return $state[2];
		}
	}

	/**
	 * Zählt die Lagerdaten pro gekauftem Artikel runter
	 */
	public function removeStock() {
		// Alle Lagerdaten nach Sortierung holen
		$Stock = array();
		$Areas = array();
		$sSQL = 'SELECT sas_ID,sha_ID,ssa_ID,sas_Stock
		FROM tbshoparticle_stockarea ORDER BY ssa_ID ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Lager beim ersten finden instanzieren
			if (!isset($Stock[$row['ssa_ID']])) {
				$Stock[$row['ssa_ID']] = array();
				$Areas[$row['ssa_ID']] = new shopStockarea($row['ssa_ID']);
			}
			// Artikel in Lager prüfen (Geht nur einmal, gibt auch nur eins)
			if (!isset($Stock[$row['ssa_ID']][$row['sha_ID']])) {
				$Stock[$row['ssa_ID']][$row['sha_ID']] = array(
					'sas_ID' => $row['sas_ID'],
					'sas_Stock' => $row['sas_Stock'],
					'changed' => false
				);
			}
		}
		
		// Artikel des Warenkorbs durchgehen
		foreach ($this->getArticles() as $article) {
			// Tracken ob überhaupt gefunden (Für künftige Option)
			$bFound = false;
			// Alle Lager durchgehen
			foreach ($Stock as $key => $area) {
				// Wenn was gefunden, nichts mehr tun
				if ($bFound) continue;
				// Wenn nicht, schauen ob es den Artikel in der Area gibt
				if (isset($area[$article->getShaID()])) {
					// Daten validieren
					$nStock = (int) $Stock[$key][$article->getShaID()]['sas_Stock'];
					$nSasID = getInt($Stock[$key][$article->getShaID()]['sas_ID']);
					// Schauen ob er Artikel hier verfügbar ist
					if (!empty($nStock)) {
						// Einen abziehen
						$Stock[$key][$article->getShaID()]['sas_Stock'] = --$nStock;
						$Stock[$key][$article->getShaID()]['changed'] = true;
						if ($nStock > 0) {
							$bFound = true;
						} else if ($nStock == 0) {
							// Wenn jetzt genau 0, Mail senden
							$Areas[$key]->sendOutOfStockMail($article);
							$bFound = true;
						}
					}
				}
			}
		}

		// Evtl. korrigierte Lagerdaten speichern
		foreach ($Stock as $area) {
			// Alle ARtikel durchgehen
			foreach ($area as $stockdata) {
				if ($stockdata['changed']) {
					// Datensatz updaten
					$sSQL = "UPDATE tbshoparticle_stockarea
					SET sas_Stock = '$stockdata[sas_Stock]'
					WHERE sas_ID = $stockdata[sas_ID]";
					$this->Conn->command($sSQL);
				}
			}
		}
	}

    /**
     * Getter für sho_ID: ID der Bestellung
     * @return int Wert von 'sho_ID'
     */
    public function getShoID() {
        return($this->myShoID);
    }

    /**
     * Getter für shu_ID: Benutzer der die Bestellung machte
     * @return int Wert von 'shu_ID'
     */
    public function getShuID() {
        return($this->myShuID);
    }

    /**
     * Setter für shu_ID: Benutzer der die Bestellung machte
     * @param int Neuer Wert für 'shu_ID'
     */
    public function setShuID($value) {
        $value = getInt($value);
        $this->myShuID = $value;
    }

    /**
     * Getter für scp_ID: Eingelöster Gutschein (0, wenn keiner)
     * @return int Wert von 'scp_ID'
     */
    public function getScpID() {
        return($this->myScpID);
    }

    /**
     * Setter für scp_ID: Eingelöster Gutschein (0, wenn keiner)
     * @param int Neuer Wert für 'scp_ID'
     */
    public function setScpID($value) {
        $value = getInt($value);
        $this->myScpID = $value;
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
     * Getter für sho_Total: Total der Rechnung
     * @return double Wert von 'sho_Total'
     */
    public function getTotal() {
        return($this->myTotal);
    }

    /**
     * Setter für sho_Total: Total der Rechnung
     * @param double Neuer Wert für 'sho_Total'
     */
    public function setTotal($value) {
        $value = numericOps::getDecimal($value,2);
        $this->myTotal = $value;
    }

    /**
     * Getter für sho_Date: Datum der Bestellung
     * @return string Wert von 'sho_Date'
     */
    public function getDate() {
        $value = stripslashes($this->myDate);
        return($value);
    }

    /**
     * Setter für sho_Date: Datum der Bestellung
     * @param string Neuer Wert für 'sho_Date'
     */
    public function setDate($value) {
        $this->Conn->escape($value);
        $this->myDate = $value;
    }

    /**
     * Getter für sho_Payment: Art der Zahlung (Vorauskasse / Rechnung)
     * @return int Wert von 'sho_Payment'
     */
    public function getPayment() {
        return($this->myPayment);
    }

    /**
     * Setter für sho_Payment: Art der Zahlung (Vorauskasse / Rechnung)
     * @param int Neuer Wert für 'sho_Payment'
     */
    public function setPayment($value) {
        $value = getInt($value);
        $this->myPayment = $value;
    }

    /**
     * Getter für sho_State: 0 = Warenkorb / 1 = Offen (Bestellt) / 2 = Bezahlt / 3 = Abgesendet
     * @return int Wert von 'sho_State'
     */
    public function getState() {
        return($this->myState);
    }

    /**
     * Setter für sho_State: 0 = Warenkorb / 1 = Offen (Bestellt) / 2 = Bezahlt / 3 = Abgesendet
     * @param int Neuer Wert für 'sho_State'
     */
    public function setState($value) {
        $value = getInt($value);
        $this->myState = $value;
    }

    /**
     * Getter für sho_Deliveryaddress: Lieferadresse (Referenz)
     * @return int Wert von 'sho_Deliveryaddress'
     */
    public function getDeliveryaddress() {
        return($this->myDeliveryaddress);
    }

    /**
     * Setter für sho_Deliveryaddress: Lieferadresse (Referenz)
     * @param int Neuer Wert für 'sho_Deliveryaddress'
     */
    public function setDeliveryaddress($value) {
        $value = getInt($value);
        $this->myDeliveryaddress = $value;
    }

    /**
     * Getter für sho_Billingaddress: Rechnungsadresse für die Bestellung
     * @return int Wert von 'sho_Billingaddress'
     */
    public function getBillingaddress() {
        return($this->myBillingaddress);
    }

    /**
     * Setter für sho_Billingaddress: Rechnungsadresse für die Bestellung
     * @param int Neuer Wert für 'sho_Billingaddress'
     */
    public function setBillingaddress($value) {
        $value = getInt($value);
        $this->myBillingaddress = $value;
    }

  /**
   * Getter für sho_Message: Wert des Feldes
   * @return string Wert von 'sho_Message'
   */
  public function getMessage() {
      $value = stripslashes($this->myMessage);
      return($value);
  }

  /**
   * Setter für sho_Message: Wert des Feldes
   * @param string Neuer Wert für 'sho_Message'
   */
  public function setMessage($value) {
      $this->Conn->escape($value);
      $this->myMessage = $value;
  }
}