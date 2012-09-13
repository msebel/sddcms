<?php
// Dynamischen Klassenloader laden
$CartClasses = new dynamicClassLoader();
// Basispfade nach Priorität hinzufügen
$CartClasses->setBasepaths(array(ISP,SP));
// Array aus Pfaderweiterungen angeben
$CartClasses->setSearchFolders(array('/classes/cart/'));
$CartClasses->load('defaultCart');
$CartClasses->load('deliveryCost');

// Modul um Aufträge anzuzeigen
class moduleShopOrders extends commonModule {

	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	/**
	 * Rücksprungs-Url
	 * @var string
	 */
	private $MyUrl;
	/**
	 * SQL Statement für den Dataaccess
	 * @var string
	 */
	private $Sql = '';
	/**
	 * Suchstring
	 * @var string
	 */
	private $SearchTerm = '';
	/**
	 * Status Filter
	 * @var bool
	 */
	private $ShowAll = false;

	// Objekt erstellen und URL initialisieren
	public function __construct($nID = 0) {
        parent::__construct($nID);
		$this->MyUrl = '/modules/shop/admin/orders/index.php?id='.page::menuID();
    }

	// Im Destruktor die Suche speichern
	public function __destruct() {
		// Filter speichern
		sessionConfig::set('ShopOrderSql', $this->Sql);
		sessionConfig::set('ShopOrderNr', $this->SearchTerm);
		sessionConfig::set('ShopOrderShowCompleted', $this->ShowAll);
	}


	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
		// Initialisieren der Suche
		$this->initializeSearch();
	}

	// Order-Status speichern
	public function saveOrders() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$order = new shopOrder(getInt($_POST['id'][$i]));
			$order->setState($_POST['state'][$i]);
			$order->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop order (change state)');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location:'.$this->MyUrl);
	}

	/**
	 * Die Bestellung in $_GET['o'] laden und zurückgeben
	 * @return shopOrder
	 */
	public function loadOrder() {
		$nShoID = getInt($_GET['o']);
		$order = new shopOrder($nShoID);
		// Mandant prüfen, muss aktueller sein
		if ($order->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}
		// Wenn nicht, Artikel zurückgeben (Alles OK)
		return($order);
	}

	// List der Bestellungen laden
	public function loadOrders() {
		return($this->Conn->execute($this->Sql));
	}

	// Suche initialisieren
	public function initializeSearch() {
		$this->Sql = sessionConfig::get('ShopOrderSql', '');
		$this->SearchTerm = sessionConfig::get('ShopOrderNr', '');
		$this->ShowAll = sessionConfig::get('ShopOrderShowCompleted', false);
		// Wenn leer, Standard Suche nehmen
		if (strlen($this->Sql) == 0) {
			$this->resetSearch();
		}
	}

	// Standard Suche anwenden (und auf Startseite des Moduls)
	public function resetSearch() {
		$this->SearchTerm = '';
		$this->ShowAll = false;
		// SQL Statement definieren
		$this->Sql = 'SELECT sho_ID,sho_Total,sho_Date,sho_Payment,sho_State,
		sad_Firstname,sad_Lastname,sad_City FROM tbshoporder
		LEFT JOIN tbshopaddress on tbshoporder.sho_Deliveryaddress = tbshopaddress.sad_ID
		WHERE tbshoporder.man_ID = '.page::mandant().'
		AND sho_State > 0 AND sho_State < 3 ORDER BY sho_Date DESC';
		// Redirect auf die Startseite machen
		redirect('location: '.$this->MyUrl);
	}

	// Suche durchführen
	public function setSearch() {
		$this->SearchTerm = stringOps::getPostEscaped('searchTerm', $this->Conn);
		$this->ShowAll = (boolean) $_POST['completed'];
		// SQL Statement definieren
		$this->Sql = 'SELECT sho_ID,sho_Total,sho_Date,sho_Payment,sho_State,
		sad_Firstname,sad_Lastname,sad_City FROM tbshoporder
		INNER JOIN tbshopuser ON tbshoporder.shu_ID = tbshopuser.shu_ID
		LEFT JOIN tbshopuser_address ON tbshopuser.shu_ID = tbshopuser_address.shu_ID
		LEFT JOIN tbshopaddress on tbshopuser_address.sad_ID = tbshopaddress.sad_ID
		WHERE tbshoporder.man_ID = '.page::mandant().'
		AND tbshopuser_address.sua_Primary = 1
		AND sho_State > 0';
		// abgeschlossene nur bedingt berücksichtigen
		if (!$this->ShowAll) $this->Sql .= ' AND sho_State < 3';
		// Filter auf eine bestimmte Bestell-Nr
		if (strlen($this->SearchTerm) > 0) {
			$this->Sql .= ' AND (sho_ID LIKE \'%'.$this->SearchTerm.'%\' OR
			sad_Firstname LIKE \'%'.$this->SearchTerm.'%\' OR
			sad_Lastname LIKE \'%'.$this->SearchTerm.'%\')';
		}
		$this->Sql .= ' ORDER BY sho_Date DESC';

		// Redirect auf die Startseite machen
		redirect('location: '.$this->MyUrl);
	}

	// aktuell gesuchte Bestell-Nr laden
	public function getSearchTerm() {
		return($this->SearchTerm);
	}

	// Gibt an, ob der Statusfilter gegeben ist
	public function isSetStateFilter() {
		return($this->ShowAll);
	}

	// Setter für aktuelle Modul-Url
    public function setUrl($value) {
		$this->myUrl = $value;
    }

	// warenkorb von der bestellung laden
	public function getCart() {
		// Template erstellen/einlesen
		$tPath = shopStatic::getTemplate('order-admin');
		$Tpl = new templateImproved($tPath);
		$Order = new shopOrder(getInt($_GET['o']));
		// Standard Warenkorb darstellen
		$cart = new defaultCart($Order);
		$cart->setDeliveryTemplate('default-delivery-small');
		// Diesen im Template so darstellen
		$Tpl->addSubtemplate(
			'CART_DEFAULT_LIST',
			$cart->getTemplate()
		);
		$Order->setTotal($cart->getTotal());
		// Sonstige variablen
		$Tpl->addData('MENU_ID', page::menuID());
		$Tpl->addData('ORDER_ID', $Order->getShoID());
		// Templateinhalte zurückgeben
		return($Tpl->output());
	}
}