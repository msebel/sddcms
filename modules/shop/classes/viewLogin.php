<?php
/**
 * View für Login und Registrierung
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewLogin extends abstractShopView {

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
		// Schritt überspringen wenn eingeloggt
		if (shopStatic::getLoginUser() instanceof shopUser) {
			session_write_close();
			redirect('location: /modules/shop/view/payment.php?id='.page::menuID());
		}
		// Zurück zum Warenkorb, wenn keine Artikel
		if ($this->getCartArticles() == 0) {
			sessionConfig::set('CartMessage','<p>'.$this->Res->html(1102, page::language()).'</p>');
			session_write_close();
			redirect('location: /modules/shop/view/cart.php?id='.page::menuID());
		}
		// Wir brauchen nur die Menu ID
		$this->Tpl->addData('MENU_ID',page::menuID());
		// Fehlermeldungen behandeln (Registrierung)
		$sMessage = sessionConfig::get('LoginMessage', '');
		if (strlen($sMessage) > 0) {
			$this->Tpl->addData('LOGIN_MESSAGE',$sMessage);
			sessionConfig::set('LoginMessage','');
		} else {
			$this->Tpl->addData('LOGIN_MESSAGE','');
		}
		// Fehlermeldungen behandeln (Registrierung)
		$sMessage = sessionConfig::get('RegisterMessage', '');
		if (strlen($sMessage) > 0) {
			$this->Tpl->addData('REGISTER_MESSAGE',$sMessage);
			sessionConfig::set('RegisterMessage','');
		} else {
			$this->Tpl->addData('REGISTER_MESSAGE','');
		}
		// System abschliessen
		return($this->Tpl->output());
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