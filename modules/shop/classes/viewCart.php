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
 * View für Artikelliste
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCart extends abstractShopView {

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
		// Controller, der eventuelle Änderungen durchführt
		$this->control();
		// Aktuelle Order ID holen und Bestellung laden
		$nShoID = shopOrder::getSessionOrder();
		$order = new shopOrder($nShoID);
		$cart = new defaultCart($order);
		$cart->setEntryTemplate('cart-article');
		$cart->setListTemplate('editable-cart');
		$this->Tpl->addSubtemplate('CART_CONTENT',$cart->getTemplate());
		$this->Tpl->addData('MENU_ID', page::menuID());
		// Fehlermeldungen behandeln (Registrierung)
		$sMessage = sessionConfig::get('CartMessage', '');
		if (strlen($sMessage) > 0) {
			$this->Tpl->addData('CART_MESSAGE',$sMessage);
			sessionConfig::set('CartMessage','');
		} else {
			$this->Tpl->addData('CART_MESSAGE','');
		}
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Controller, führt User Inputs aus
	 */
	private function control() {
		if (isset($_POST['refresh'])) {
			$this->refresh();
		}
		if (isset($_POST['back'])) {
			$this->back();
		}
		if (isset($_POST['confirm'])) {
			$this->confirm();
		}
	}

	/**
	 * Aktualisiert die Anzahl Artikel im Warenkorb
	 */
	private function refresh() {
		// Gesamte Liste durchgehen
		$nRows = count($_POST['articleid']);
		$nShoID = shopOrder::getSessionOrder();
		// Alle durchgehen
		for ($i = 0;$i < $nRows;$i++) {
			// Daten des Artikels holen
			$nShaID = getInt($_POST['articleid'][$i]);
			$sSize = $_POST['size'][$i];
			$this->Conn->escape($sSize);
			$nWant = getInt($_POST['times'][$i]);
			if ($nWant < 0) $nWant = 0;
			// Wie viele Artikel dieses Typs sind vorhanden
			$sSQL = "SELECT COUNT(soa_ID) FROM tbshoporderarticle
			WHERE sho_ID = $nShoID AND sha_ID = $nShaID
			AND soa_Size = '$sSize'";
			$nHas = $this->Conn->getCountResult($sSQL);
			// Schauen wie der Unterschied ist
			if ($nHas > $nWant) {
				$this->removeArticle($nShaID,$sSize,($nHas-$nWant));
			} else if ($nHas < $nWant) {
				$this->addArticle($nShaID,$sSize,($nWant-$nHas));
			}
		}
	}

	/**
	 * Geht auf die Startseite des Shops zurück
	 */
	private function back() {
		session_write_close();
		redirect('location: /modules/shop/view/index.php?id='.page::menuID());
	}

	/**
	 * Geht weiter zum nächsten Schritt (Login)
	 */
	private function confirm() {
		session_write_close();
		redirect('location: /modules/shop/view/login.php?id='.page::menuID());
	}

	/**
	 * Artikel aus Warenkorb löschen (Bestimmte Menge)
	 * @param int $nShaID ORiginale Artikel ID
	 * @param string $sSize Grösse des Artikels
	 * @param int $nTimes  Anzahl zu löschender Einheiten
	 */
	private function removeArticle($nShaID,$sSize,$nTimes) {
		$nShoID = shopOrder::getSessionOrder();
		// Einfach, einfach Delete mit Limit ;-)
		$sSQL = "DELETE FROM tbshoporderarticle WHERE
		sha_ID = $nShaID AND sho_ID = $nShoID
		AND soa_Size = '$sSize' LIMIT $nTimes";
		$this->Conn->command($sSQL);
	}

	/**
	 * Fügt einen Artikel eine bestimmte Anzahl im Warenkorb hinzu
	 * @param int $nShaID Originale Artikel ID
	 * @param string $sSize Grösse des Artikels
	 * @param int $nTimes  Anzahl zu erstellender Einheiten
	 */
	private function addArticle($nShaID,$sSize,$nTimes) {
		// Originalen Artikel instanzieren
		$article = new shopArticle($nShaID);
		// Aus String und Artikel ID die Grössen-ID holen
		$sSQL = "SELECT saz_ID FROM tbshoparticlesize
		WHERE saz_Value = '$sSize' AND sha_ID = $nShaID";
		$nSazID = $this->Conn->getFirstResult($sSQL);
		$size = new shopArticlesize($nSazID);
		// Den Artikel n-Mal hinzufügen
		for ($i = 0;$i < $nTimes;$i++) {
			// Order Artikel generieren
			$orderart = $article->getOrderInstance();
			// Grösse definieren, wenn möglich
			if ($size->getShaID() == $article->getShaID()) {
				$orderart->setSize($size->getValue());
				// Preis hinzufügen wegen anderer Grösse
				$nPrice = $orderart->getPrice();
				$orderart->setPrice($nPrice + $size->getPriceadd());
			}
			// Zuweisen des aktuellen Order
			$orderart->setShoID(shopOrder::getSessionOrder());
			// Order Artikel so speichern
			$orderart->save();
		}
	}
}