<?php
/**
 * Einfacher Warenkorb (Nur anzeige, keine Input Felder)
 * @author Michael Sebel <michael@sebel.ch>
 */
class defaultCart {

	/**
	 * Aktuelle Bestellung
	 * @var shopOrder
	 */
	public $Order = NULL;
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	public $Conn = NULL;
	/**
	 * Name des Listeneintrages
	 * @var string
	 */
	private $ListEntry = '';
	/**
	 * Name des Haupttemplates
	 * @var string
	 */
	private $ListName = '';
	/**
	 * Name des Lieferkostentemplates
	 * @var string
	 */
	private $DeliveryName = '';
	/**
	 * Total, wird nach erstem Aufruf an getTemplate berechnet
	 * @var float
	 */
	private $Total = 0.0;

	/**
	 * Standard Cart erstellen
	 */
	public function __construct(shopOrder $Order) {
		$this->Order = $Order;
		$this->Conn = singleton::conn();
		// Templates definieren
		$this->ListEntry = 'default-cart-article';
		$this->ListName = 'default-cart';
		$this->DeliveryName = 'default-delivery';
	}

	/**
	 * Definiert einen neuen Namen für das Entry Template
	 * @param string $name Name eines Template
	 */
	public function setEntryTemplate($name) {
		$this->ListEntry = $name;
	}

	/**
	 * Definiert das Template für die Warenkorb anzeige
	 * @param string $name Name eines Template
	 */
	public function setListTemplate($name) {
		$this->ListName = $name;
	}

	/**
	 * Definiert das Template für die Lieferkosten
	 * @param string $name Name eines Template
	 */
	public function setDeliveryTemplate($name) {
		$this->DeliveryName = $name;
	}

	/**
	 * Template für Output zurückgeben
	 * @return templateImproved Gefülltes Template
	 */
	public function getTemplate() {
		$tPath = shopStatic::getTemplate($this->ListEntry);
		$entry = new templateImproved($tPath);
		$list = new templateList($entry);
		// Artikel per SQL laden
		$articles = array();
		$nRes = $this->Order->getArticlesRes();
		while ($row = $this->Conn->next($nRes)) {
			// Gleiche Artikel mergeln und Preis summieren
			if (!shopStatic::mergeArticles($articles,$row)) {
				$row['soa_Times'] = 1;
				array_push($articles,$row);
			}
		}

		$Tab = new tabRowExtender();
		$nPrice = 0;
		// Alle Artikel des Order laden und durchgehen
		foreach ($articles as $article) {
			$instance = new shopOrderarticle();
			$instance->loadRow($article);
			$data = $instance->toTemplate();
			// Zusätzliche Daten
			$data['SOA_TIMES'] = $article['soa_Times'];
			$data['MENU_ID'] = page::menuID();
			$data['ROWCLASS'] = $Tab->get();
			// Gesamtpreis berechnen
			$nPrice += numericOps::getDecimal($data['SOA_PRICE'],2);
			$data['SOA_PRICE'] .= ' CHF';
			// Daten hinzufügen
			$list->addData($data);
		}
		// Template für Rückgabe erstellen
		$tPath = shopStatic::getTemplate($this->ListName);
		$tpl = new templateImproved($tPath);
		// Liste hinzufügen
		$tpl->addList('CART_ARTICLE_LIST',$list);
		// Lieferkostenberechnung einfügen
		$delivery = new deliveryCost($this->Order,$this->DeliveryName);
		$delivery->setClass($Tab->get());
		$tpl->addSubtemplate('DELIVERY_ENTRY', $delivery->getTemplate(false));
		$data = $delivery->getData(false);
		$nPrice += numericOps::getDecimal($data['DELIVERY_PRICE'],2);
		// Totalpreis anbringen
		$this->Total = numericOps::getDecimal($nPrice,2);
		$tpl->addData('PRICE_TOTAL',$this->Total. ' CHF');
		return($tpl);
	}

	/**
	 * Total zurückgeben, "getTemplate" muss mindestens einmal aufgerufen werden
	 */
	public function getTotal() {
		return($this->Total);
	}
}