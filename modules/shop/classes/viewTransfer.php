<?php
// Dynamischen Klassenloader laden
$PayClasses = new dynamicClassLoader();
// Basispfade nach Priorität hinzufügen
$PayClasses->setBasepaths(array(ISP,SP));
// Array aus Pfaderweiterungen angeben
$PayClasses->setSearchFolders(array('/classes/payment/'));
$PayClasses->load('paymentFactory');
$PayClasses->load('abstractPayment');
$PayClasses->load('prepaidPayment');
$PayClasses->load('billPayment');
$PayClasses->load('paypalPayment');

/**
 * View für die Zahlung selbst (PayPal etc.)
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewTransfer extends abstractShopView {

	/**
	 * Benutzer der aktuell eingeloggt ist
	 * @var shopUser
	 */
	private $User = NULL;
	/**
	 * Aktuelle Bestellung die Bezahlt werden soll
	 * @var shopOrder
	 */
	private $Order = NULL;
	/**
	 * Post Daten der letzten Seite
	 * @var array
	 */
	private $Post = NULL;

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		parent::__construct($name);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		$this->Post = sessionConfig::get('PaymentData', NULL);
		// AGB Akzeptiert, sonst zurück gehen
		if (getInt($this->Post['agbAccept']) !== 1) {
			sessionConfig::set('ErrorMessage','<p>'.$this->Res->html(1169, page::language()).'</p>');
			session_write_close();
			redirect('location: /modules/shop/view/payment.php?id='.page::menuID());
		}
		// Controller, der eventuelle Änderungen durchführt
		$this->control();
		// Zurück zum Warenkorb, wenn keine Artikel
		if ($this->getCartArticles() == 0) {
			sessionConfig::set('CartMessage','<p>'.$this->Res->html(1102, page::language()).'</p>');
			session_write_close();
			redirect('location: /modules/shop/view/cart.php?id='.page::menuID());
		}
		// Eingeloggten User holen
		$this->User = shopStatic::getLoginUser();
		$this->Order = new shopOrder(shopOrder::getSessionOrder());
		// Adressen speichern
		$this->Order->setDeliveryaddress($this->Post['deliveryAddress']);
		$this->Order->setBillingaddress($this->Post['billingAddress']);
    $this->Order->setMessage($this->Post['shoMessage']);
		$this->Order->save();
		// Payment durchführen (Je nach typ)
		$this->doPayment();
		// Einige Variablen abfüllen
		$this->Tpl->addData('MENU_ID',page::menuID());
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Zahlung je nach Typ durchführen oder direkt weitermachen
	 */
	private function doPayment() {
		$payment = paymentFactory::get(
			$this->Order,
			$this->Post['payment']
		);
		// Payment ausgeben
		$this->Tpl->addData('TRANSFER_CONTENT',$payment->showHtml());
	}

	/**
	 * Controller, führt User Inputs aus
	 */
	private function control() {
		if (isset($_POST['back'])) {
			$this->back();
		}
		if (isset($_POST['refresh'])) {
			$this->refresh();
		}
		if (isset($_POST['confirm'])) {
			$this->confirm();
		}
	}

	/**
	 * Lädt die Seite neu (Aktualisieren wegen Forms)
	 */
	private function refresh() {
		session_write_close();
		redirect('location: /modules/shop/view/transfer.php?id='.page::menuID());
	}

	/**
	 * Zurück zu den Zahlungsmöglichkeiten
	 */
	private function back() {
		session_write_close();
		redirect('location: /modules/shop/view/payment.php?id='.page::menuID());
	}

	/**
	 * Weiter zur Bestätigung
	 */
	private function confirm() {
		// Zahlungsobjekt laden
		$payment = paymentFactory::get(
			$this->Order,
			$this->Post['payment']
		);
		// Validierung versuchen
		$payment->validate();
	}

	/**
	 * Gibt die Anzahl Artikel im Warenkorb zurück
	 * @return int Anzahl Artikel im Session Order
	 */
	private function getCartArticles() {
		$nShoID = shopOrder::getSessionOrder();
		$order = new shopOrder($nShoID);
		return(count($order->getArticles()));
	}
}