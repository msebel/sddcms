<?php
/**
 * View für den Userbereich
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewUser extends abstractShopView {

	/**
	 * Instanz des eingeloggen Users
	 * @var shopUser
	 */
	private $User = NULL;

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		$this->User = shopStatic::getLoginUser();
		parent::__construct($name);
		$this->userCheck(true);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		// Controller durchführen
		$this->control();
		// Adressen Dropdowns generieren
		$this->showAddresses();
		// Sonstige Variablen
		$this->Tpl->addData('MENU_ID', page::menuID());
		$this->Tpl->addData('TYPE_BILL',shopAddress::TYPE_BILL);
		$this->Tpl->addData('TYPE_DELIVERY',shopAddress::TYPE_DELIVERY);
		$this->Tpl->addData('USER_NAME', $this->User->getUsername());
		// Fehlermeldungen behandeln (Registrierung)
		$sMessage = sessionConfig::get('PasswordMessage', '');
		if (strlen($sMessage) > 0) {
			$this->Tpl->addData('PASSWORD_MESSAGE',$sMessage);
			sessionConfig::set('PasswordMessage','');
		} else {
			$this->Tpl->addData('PASSWORD_MESSAGE','');
		}
		return($this->Tpl->output());
	}

	/**
	 * Erstellt die Dropdowns für Rechnungs- und Lieferadresse
	 */
	private function showAddresses() {
		// Alle Adressen laden (Alle Typen)
		$addresses = $this->User->getAddresses();
		// Dropdown mit Rechungsadressen, Primäre zuerst
		$billing = '<select id="billing" class="cPaymentAddress">';
		foreach ($addresses as $addr) {
			if ($addr->getType() == shopAddress::TYPE_BILL) {
				$billing .= '
					<option value="'.$addr->getSadID().'">'.$addr->getAbstract(50).'</option>
				';
			}
		}
		$billing .= '</select>';
		// Dropdown mit Lieferadressen, Primäre zuerst
		$deli = '<select id="delivery" class="cPaymentAddress">';
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
	}

	/**
	 * Führt Kontrollroutinen aus
	 */
	public function control() {
		if (isset($_GET['save'])) {
			$this->save();
		}
	}

	/**
	 * Speichert das Passwort des aktuellen Users
	 */
	public function save() {
		// Passwörter holen und checken
		$sPwd1 = $_POST['password1'];
		$sPwd2 = $_POST['password2'];
		$Messages = stringOps::checkPasswords($sPwd1,$sPwd2,4);
		// Erfolg oder Misserfolg speichern
		if (count($Messages) == 0) {
			// Neues Passwort setzen
			$this->User->setPassword($sPwd1);
			sessionConfig::set('PasswordMessage', $this->Res->html(1153, page::language()).'<br><br>');
		} else {
			sessionConfig::set('PasswordMessage', $Messages[0].'<br><br>');
		}
		// ...und weiterleiten
		session_write_close();
		redirect('location: /modules/shop/view/user.php?id='.page::menuID());
	}
}