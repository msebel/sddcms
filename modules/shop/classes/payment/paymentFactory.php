<?php
/**
 * Gibt je nach Typ die korrekte Zahlungsart zurück
 */
class paymentFactory {

	/**
	 * Gibt eine Zahlungsinstanz zurück
	 * @param shopOrder $order Zu zahlende Bestellung
	 * @param int $nType Typ der Zahlung der gewünscht ist
	 * @return abstractPayment Zahlungsmöglichkeit
	 */
	public static function get(shopOrder $order,$nType) {
		switch(getInt($nType)) {
			case shopOrder::PAYMENT_PREPAID:
				$payment = new prepaidPayment($order);
				break;
			case shopOrder::PAYMENT_BILL:
				$payment = new billPayment($order);
				break;
			case shopOrder::PAYMENT_PAYPAL:
				$payment = new paypalPayment($order);
				break;
		}
		// Typ noch zuweisen, damit es dem Payment bekannt wird
		$payment->setType($nType);
		// Und schliesslich zurückliefern
		return($payment);
	}
}