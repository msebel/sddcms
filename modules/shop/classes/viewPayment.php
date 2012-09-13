<?php
/**
 * View für Auswahl der Zahlung / Adressen
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewPayment extends abstractShopView {

	/**
	 * Benutzer der aktuell eingeloggt ist
	 * @var shopUser
	 */
	private $User = NULL;

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
		// Zurück zum Warenkorb, wenn keine Artikel
		if ($this->getCartArticles() == 0) {
			sessionConfig::set('CartMessage','<p>'.$this->Res->html(1102, page::language()).'</p>');
			session_write_close();
			redirect('location: /modules/shop/view/cart.php?id='.page::menuID());
		}
		// Fehlermeldungen behandeln (Registrierung)
		$sMessage = sessionConfig::get('ErrorMessage', '');
		if (strlen($sMessage) > 0) {
			$this->Tpl->addData('ERROR_MESSAGE',$sMessage);
			sessionConfig::set('ErrorMessage','');
		} else {
			$this->Tpl->addData('ERROR_MESSAGE','');
		}
		// Eingeloggten User holen
		$this->User = shopStatic::getLoginUser();
		// Zahlungsarten ausgeben je nach Konfig
		$this->showPaymentTypes();
		$this->showAddresses();
		// Einige Variablen abfüllen
		$this->Tpl->addData('MENU_ID',page::menuID());
		$this->Tpl->addData('ADDRESS_LINK','/modules/shop/view/user.php?id='.page::menuID());
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Zeigt die Zahlungsarten nach Konfiguration an
	 */
	private function showPaymentTypes() {
		$out = '';
		// Vorauskasse ist immer vorhanden
		$out .= '
			<input type="radio" name="payment" value="1" checked>
			'.$this->Res->html(1104, page::language()).'<br>
		';
		// Rechnung, wenn konfiguriert oder User darf
		if (shopConfig::BillActive() || $this->User->getBillable() == 1) {
			$out .= '
				<input type="radio" name="payment" value="2">
				'.$this->Res->html(1105, page::language()).'<br>
			';
		}
		// Paypal wenn aktiviert
		if (shopConfig::PaypalActive()) {
			$out .= '
				<input type="radio" name="payment" value="3">
				'.$this->Res->html(1106, page::language()).'<br>
			';
		}
		// Das Ganze ins Template einbringen
		$this->Tpl->addData('PAYMENT_TYPES',$out);
	}

	/**
	 * Erstellt die Dropdowns für Rechnungs- und Lieferadresse
	 */
	private function showAddresses() {
		// Alle Adressen laden (Alle Typen)
		$addresses = $this->User->getAddresses();
		// Dropdown mit Rechungsadressen, Primäre zuerst
		$billing = '<select name="billingAddress" class="cPaymentAddress">';
		foreach ($addresses as $addr) {
			if ($addr->getType() == shopAddress::TYPE_BILL) {
				$billing .= '
					<option value="'.$addr->getSadID().'">'.$addr->getAbstract(50).'</option>
				';
			}
		}
		$billing .= '</select>';
		// Dropdown mit Lieferadressen, Primäre zuerst
		$deli = '<select name="deliveryAddress" class="cPaymentAddress">';
		foreach ($addresses as $addr) {
			if ($addr->getType() == shopAddress::TYPE_DELIVERY) {
				$deli .= '
					<option value="'.$addr->getSadID().'">'.$addr->getAbstract(50).'</option>
				';
			}
		}
		$deli .= '</select>';
		// Diese im Template einfügen
		$this->Tpl->addData('BILLING_ADDRESSES',$billing);
		$this->Tpl->addData('DELIVERY_ADDRESSES',$deli);
		// AGB Checkbox aus subtemplate nehmen
		$agb_path = shopStatic::getTemplate('agb-checkbox');
		$agb = new templateImproved($agb_path);
		$this->Tpl->addSubtemplate('AGB_CHECKBOX',$agb);
	}

	/**
	 * Controller, führt User Inputs aus
	 */
	private function control() {
		if (isset($_POST['login'])) {
			$this->login();
			$this->refresh();
		}
		if (isset($_POST['register'])) {
			$this->register();
			$this->refresh();
		}
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
	 * Loggt den gewünschten User ein (POST Daten)
	 */
	private function login() {
		// Prüfen ob das Login so funktioniert (Nicht die Redirect Variante anwenden)
		$sEmail = stringOps::getPostEscaped('shopUserEmail', $this->Conn);
		$sPwd = $_POST['shopUserPassword'];
		$login = impersonation::login($sEmail, $sPwd, shopConfig::LoginMenuID(), $this->Conn, false);
		// Mit Nachricht zurück zum Login Screen wenn kein Login möglich
		if (!$login) {
			sessionConfig::set('LoginMessage',$this->Res->html(1101,page::language()));
			session_write_close();
			redirect('location: /modules/shop/view/login.php?id='.page::menuID());
		}
	}

	/**
	 * Registriert einen User und loggt Ihn gleich ein
	 */
	private function register() {
		// POST Daten holen und validieren
		$sEmail = stringOps::getPostEscaped('shopUserEmail', $this->Conn);
		// Prüfen ob eine solche Impersonation (Anhand E-Mail) schon existiert
		$sSQL = "SELECT COUNT(tbimpersonation.imp_ID) FROM tbimpersonation
		INNER JOIN tbmenu_impersonation ON
		tbmenu_impersonation.imp_ID = tbimpersonation.imp_ID
		WHERE imp_Active = 1 AND man_ID = ".page::mandant()."
		AND imp_Alias = '".$sEmail."'
		AND mnu_ID = ".shopConfig::LoginMenuID();
		// Mit Nachricht zurück zum Login Screen
		if ($this->Conn->getCountResult($sSQL) > 0 && strlen($sEmail) > 0) {
			sessionConfig::set('RegisterMessage', $this->Res->html(1099,page::language()));
			session_write_close();
			redirect('location: /modules/shop/view/login.php?id='.page::menuID());
		}
		// Sind die Pflichtfelder ausgefüllt? Wenn nein, Meldung und zurück
		if (!$this->checkRequiredFields()) {
			sessionConfig::set('RegisterMessage', $this->Res->html(1168,page::language()));
			session_write_close();
			redirect('location: /modules/shop/view/login.php?id='.page::menuID());
		}
		// Neuen User erstellen mit genannten Daten
		$user = new shopUser();
		$user->setActive(1);
		$user->setManID(page::mandant());
		// Impersonation erstellen, im Fehlerfall dem Benutzer eine Meldung zeigen
		if (!$user->createImpersonation($sEmail,true)) {
			sessionConfig::set('RegisterMessage', $this->Res->html(1100,page::language()));
			session_write_close();
			redirect('location: /modules/shop/view/login.php?id='.page::menuID());
		}
		// Adresse erstellen
		$address = new shopAddress();
		$address->setCity($_POST['shopUserCity']);
		$address->setEmail($_POST['shopUserEmail']);
		$address->setFirstname($_POST['shopUserFirstname']);
		$address->setLastname($_POST['shopUserLastname']);
		$address->setManID(page::mandant());
		$address->setStreet($_POST['shopUserStreet']);
		$address->setTitle($_POST['shopUserTitle']);
		$address->setZip($_POST['shopUserPlz']);
		// Adresse/User speichern
		$address->save();
		$user->save();
		// Adresse zuordnen als Rechnungs UND Lieferadresse
		$user->addAddress($address,shopAddress::TYPE_BILL,1);
		$user->addAddress($address,shopAddress::TYPE_DELIVERY,1);
	}

	/**
	 * Checkt, ob die nötigen Felder für eine Registrierung ausgefüllt sind
	 * @return bool true/false ob Required Fields ausgefüllt sind
	 */
	private function checkRequiredFields() {
		if (strlen($_POST['shopUserCity']) == 0 ||
				strlen($_POST['shopUserFirstname']) == 0 ||
				strlen($_POST['shopUserEmail']) == 0 ||
				strlen($_POST['shopUserLastname']) == 0 ||
				strlen($_POST['shopUserPlz']) == 0 ||
				strlen($_POST['shopUserStreet']) == 0) {
			// Da fehlt leider noch was...
			return false;
		}
		// Alles OK
		return true;
	}

	/**
	 * Lädt die Seite neu (Aktualisieren wegen Forms)
	 */
	private function refresh() {
		session_write_close();
		redirect('location: /modules/shop/view/payment.php?id='.page::menuID());
	}

	/**
	 * Zurück in den Warenkorb (Login überspringen, da es weiterleiten würde)
	 */
	private function back() {
		session_write_close();
		redirect('location: /modules/shop/view/cart.php?id='.page::menuID());
	}

	/**
	 * Weiter zum Transfer (
	 */
	private function confirm() {
		sessionConfig::set('PaymentData', $_POST);
		session_write_close();
		redirect('location: /modules/shop/view/transfer.php?id='.page::menuID());
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