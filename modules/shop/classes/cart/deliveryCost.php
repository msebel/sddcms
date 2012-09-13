<?php
/**
 * Berechnet die Lieferkosten und fügt dem Warenkorb
 * für temporäre Anzeige einen "Artikel" hinzu
 * @author Michael Sebel <michael@sebel.ch>
 */
class deliveryCost {

	/**
	 * Zu berechnende Bestellung
	 * @var shopOrder
	 */
	private $myOrder = NULL;
	/**
	 * Template zum zurückgeben
	 * @var templateImproved
	 */
	private $Tpl = NULL;
	/**
	 * Daten Zwischenspeicher
	 * @var array
	 */
	private $Data = NULL;
	/**
	 * Klasse für allfällige Tabellenzeile/Div
	 * @var string
	 */
	private $myClass = '';

	/**
	 * Erstellt die Lieferkostenberechnung
	 * @param shopOrder $Order Shopbestellung
	 * @param string $sName Name des Templates
	 */
	public function __construct(shopOrder $Order,$sName) {
		$this->myOrder = $Order;
		$tPath = shopStatic::getTemplate($sName);
		$this->Tpl = new templateImproved($tPath);
	}

	/**
	 * Definiert eine allfällige Klasse für Tabelle/Div
	 * @param string $sClass Klassenname (CSS)
	 */
	public function setClass($sClass) {
		$this->myClass = $sClass;
	}

	/**
	 * Gibt das Template zurück welches alle Daten enthält
	 * @param bool Javascript Strings zurückgeben
	 * @return templateImproved Template um ins Subtemplate einzufüllen
	 */
	public function getTemplate($forJS) {
		$data = $this->getData($forJS);
		$data['DELIVERY_PRICE'] .= ' CHF';
		// Alle Variablen durchgehen
		foreach ($data as $key => $value) {
			$this->Tpl->addData($key,$value);
		}
		return($this->Tpl);
	}

	/**
	 * Rückgabe als assoziatives Array für manuelle Verarbeitung
	 * @param bool $javascript Output für HTML oder Javascript
	 * @return array Key/Value Pair für Daten
	 */
	public function getData($javascript) {
		if ($this->Data == NULL) {
			$Res = singleton::resources();
			if ($javascript) {
				$this->Data['DELIVERY_COST_NAME'] = $Res->javascript(1111, page::language());
			} else {
				$this->Data['DELIVERY_COST_NAME'] = $Res->html(1111, page::language());
			}
			$this->Data['DELIVERY_PRICE'] = $this->getDeliveryCost();
			$this->Data['ROWCLASS'] = $this->myClass;
		}
		return($this->Data);
	}

	/**
	 * Gibt die berechneten Lieferkosten je nach Shopeinstellung zurück
	 * @return float Lieferkosten
	 */
	private function getDeliveryCost() {
		if (shopConfig::Delivery() == 0) {
			// Standardpreis nach Konfig
			return(shopConfig::DeliveryCost());
		} else {
			// TODO Staffelpreise nach Deliveryentities
			return(0.0);
		}
	}
}