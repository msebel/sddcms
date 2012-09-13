<?php
// Dynamischen Klassenloader laden
$CartClasses = new dynamicClassLoader();
// Basispfade nach Priorität hinzufügen
$CartClasses->setBasepaths(array(ISP,SP));
// Array aus Pfaderweiterungen angeben
$CartClasses->setSearchFolders(array('/classes/cart/'));
$CartClasses->load('defaultCart');
$CartClasses->load('deliveryCost');

/**
 * View für einen Auftrag
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewOrder extends abstractShopView {

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
		// Zurück zum Warenkorb, wenn keine Artikel
		if ($this->getCartArticles() == 0) {
			sessionConfig::set('CartMessage','<p>'.$this->Res->html(1102, page::language()).'</p>');
			session_write_close();
			redirect('location: /modules/shop/view/cart.php?id='.page::menuID());
		}
		// Eingeloggten User und dessen Bestellung
		$this->User = shopStatic::getLoginUser();
		$this->Order = new shopOrder(getInt($_GET['order']));
		$this->showData();
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Zeigt die Seite mitsamt dem ganzen Warenkorb nochmal an
	 */
	private function showData() {
		// Standard Warenkorb darstellen
		$cart = new defaultCart($this->Order);
		$cart->setDeliveryTemplate('default-delivery-small');
		// Diesen im Template so darstellen
		$this->Tpl->addSubtemplate(
			'CART_DEFAULT_LIST',
			$cart->getTemplate()
		);
		$this->Order->setTotal($cart->getTotal());
		// Sonstige variablen
		$this->addPaymentInfo($this->Tpl);
		$this->Tpl->addData('MENU_ID', page::menuID());
		$this->Tpl->addData('ORDER_ID', $this->Order->getShoID());
		$this->Tpl->addData('REFERER_FILE',$this->getReferer());
	}

	/**
	 * Payment Info in das Template ausgeben
	 * @param templateImproved $tpl Template in welches eingefügt werden soll
	 * @param string $name Name der Variable, Default: PAYMENT_INFO
	 */
	private function addPaymentInfo(templateImproved &$tpl, $name = 'PAYMENT_INFO') {
		// Je nach Typ was anderes einfüllen
		switch ($this->Order->getPayment()) {
			case shopOrder::PAYMENT_PREPAID:
				$sHtml = $this->getPrepaidHtml();
				break;
			case shopOrder::PAYMENT_BILL:
				$sHtml = $this->Res->html(1109,page::language());
				$sHtml = str_replace('{0}', 30, $sHtml);
				break;
			case shopOrder::PAYMENT_PAYPAL:
				$sHtml = $this->Res->html(1110,page::language());
				break;
		}
		// Einfüllen ins Template
		$tpl->addData($name, $sHtml);
	}

	/**
	 * HTML für Prepaid (Vorauskasse) Information
	 * @return string HTML Code mit Infos
	 */
	private function getPrepaidHtml() {
		// Template laden
		$tPath = shopStatic::getTemplate('payment-info');
		$tpl = new templateImproved($tPath);
		// Variablen mit Infos ersetzen
		$tpl->addData('IBAN', shopConfig::IBAN());
		$tpl->addData('Post', shopConfig::Post());
		$tpl->addData('Payment', shopConfig::Payment());
		// Template Output zurückliefern
		return($tpl->output());
	}

	/**
	 * Gibt die Anzahl Artikel im Warenkorb zurück
	 * @return int Anzahl Artikel im Session Order
	 */
	private function getCartArticles() {
		$nShoID = getInt($_GET['order']);
		$order = new shopOrder($nShoID);
		return(count($order->getArticles()));
	}

	/**
	 * Holt den Filenamen (ohne php vom Referer)
	 * @return string Name des Referers
	 */
	private function getReferer() {
		$URL = parse_url();
		$sFile = fileOps::getFileOnly($_SERVER['HTTP_REFERER']);
		$parts = explode('?', $sFile);
		$sFile = substr($sFile,0,  strripos($parts[0], '.'));
		return($sFile);
	}
}