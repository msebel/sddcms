<?php
/**
 * Vorauszahlung wird durchgereicht und ist OK
 * @author Michael Sebel <michael@sebel.ch>
 */
class prepaidPayment extends abstractPayment {

	/**
	 * Erstellt die Bezahlmethode
	 * @param shopOrder $order Bestellung die bezahlt wird
	 */
	public function __construct(shopOrder $order) {
		parent::__construct($order);
	}

	/**
	 * HTML für die Zahlungsart anzeigen
	 * @return string HTML Code fürs Templatesystem
	 */
	public function showHtml() {
		// Hier können wir direkt validieren (Keine Zahlung)
		$this->validate();
	}

	/**
	 * Validieren der Zahlung
	 */
	public function validate() {
		// Vorauskasse in der Bestellung speichern
		$this->myOrder->setPayment($this->myType);
		$this->myOrder->setState(shopOrder::STATE_OPEN);
		// Ansonsten nur speichern und weiterleiten
		$this->myOrder->save();
		$this->confirmation();
	}
}