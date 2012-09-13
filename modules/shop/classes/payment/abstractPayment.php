<?php
/**
 * Basisklasse für eine Zahlungsart, ist nur sehr Primitiv,
 * da eine Zahlungsart keine abstrakte Form annehmen kann
 * @author Michael Sebel <michael@sebel.ch>
 */
abstract class abstractPayment {
    
	/**
	 * Die Bestellung die zu Zahlen ist
	 * @var shopOrder
	 */
	protected $myOrder = NULL;
	/**
	 * Typ der Zahlung (Vorauskasse, Rechnung etc)
	 * @var int
	 */
	protected $myType = 0;

	/**
	 * Erstellt die Bezahlmethode
	 * @param shopOrder $order Bestellung die bezahlt wird
	 */
	public function __construct(shopOrder $order) {
		$this->myOrder = $order;
	}

	/**
	 * Art der Zahlung definieren
	 * @param int $nType
	 */
	public function setType($nType) {
		// Zahl als solches validieren
		$nType = getInt($nType);
		// Effektive Nummer validieren
		switch($nType) {
			case shopOrder::PAYMENT_PREPAID:
			case shopOrder::PAYMENT_BILL:
			case shopOrder::PAYMENT_PAYPAL:
				break;
			default:
				$nType = shopOrder::PAYMENT_PREPAID;
				break;
		}
		// Typ so setzen
		$this->myType = $nType;
	}

	/**
	 * Typ der Zahlung zurückgeben (Durch abstr. unbekannt)
	 * @return int Typ der Zahlung shopORder::PAYMENT_*
	 */
	public function getType() {
		return($this->myType);
	}

	/**
	 * Weiter zur Bestätigung
	 */
	public function confirmation() {
		session_write_close();
		redirect('location: /modules/shop/view/confirmation.php?id='.page::menuID());
	}

	/**
	 * Funktion zur Darstellung der Zahlungsart. Das kann ein
	 * IFrame sein, ein STück JS Code oder reines HTML
	 * @return string HTML Code fürs Templatesystem
	 */
	abstract public function showHtml();

	/**
	 * Diese Funktion wird zur Validierung der Zahlung eingesetzt.
	 * Sie wird bei einem Postback automatisch aufgerufen
	 */
	abstract public function validate();
}