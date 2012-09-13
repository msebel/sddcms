<?php
/**
 * View für Adressbearbeitung
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewAddress extends abstractShopView {

	/**
	 * Adresse die bearbeite oder erstellt wird
	 * @var shopAddress
	 */
	private $Address = NULL;
	/**
	 * Typ der Verbindung für allenfalls neues Objekt
	 * @var int
	 */
	private $Type = 0;
	/**
	 * Aktuell eingeloggter Benutzer
	 * @var shopUser
	 */
	private $User = NULL;

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		parent::__construct($name);
		$this->userCheck(true);
		// Typ definieren (0 = Bearbeiten, keine Zuordnung)
		$this->Type = getInt($_GET['type']);
		// Aktuellen Benutzer laden
		$this->User = shopStatic::getLoginUser();
		// Adressobjekt instanzieren und Checken
		$this->loadAddress();
		// Diverseste Variablen abspitzen
		$this->Tpl->addData('MENU_ID', page::menuID());
		$this->Tpl->addData('ADDRESS_ID',$this->Address->getSadID());
		$this->Tpl->addData('TYPE',$this->Type);
		// Adresse abfüllen
		foreach ($this->Address->toTemplate() as $key => $value) {
			$this->Tpl->addData($key,$value);
		}
		// Modus definieren (Erstellen oder Bearbeiten)
		if ($this->Type > 0) {
			$this->Tpl->addData('MODE',$this->Res->html(1139, page::language()));
		} else {
			$this->Tpl->addData('MODE',$this->Res->html(536, page::language()));
		}
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		$this->control();
		// Meldung printen
		$sMessage = sessionConfig::get('ErrorMessage', '&nbsp;');
		$this->Tpl->addData('ERROR_MESSAGE', $sMessage);
		// Meldung auf Default zurückstellen
		sessionConfig::set('ErrorMessage', '&nbsp;');
		return($this->Tpl->output());
	}

	/**
	 * Lädt das lokale Addressobjekt anhand der GET Parameter oder
	 * erstellt eine leere neue Adresse die allenfalls gespeichert wird
	 */
	private function loadAddress() {
		// Adress ID laden
		$nAdrID = getInt($_GET['address']);
		if ($nAdrID > 0) {
			$this->Address = new shopAddress($nAdrID);
			// Wenn der Besitzer und der User nicht übereinstimmen, Error
			if ($this->Address->getOwnerID() != $this->User->getShuID()) {
				redirect('location: /error.php?type=noAccess');
			}
		} else {
			// Neue Adresse instanzieren
			$this->Address = new shopAddress();
		}
	}

	/**
	 * Controller Funktion, handelt Speicherung ab
	 */
	private function control() {
		// Speichern?
		if (isset($_GET['save'])) {
			$this->save();
		}
	}

	/**
	 * Speichert die zu bearbeitende oder neue Adresse
	 */
	private function save() {
		// Daten in Adresse speichern
		$this->Address->setCity($_POST['sadCity']);
		$this->Address->setEmail($_POST['sadEmail']);
		$this->Address->setFirstname($_POST['sadFirstname']);
		$this->Address->setLastname($_POST['sadLastname']);
		$this->Address->setManID(page::mandant());
		$this->Address->setPhone($_POST['sadPhone']);
		$this->Address->setStreet($_POST['sadStreet']);
		$this->Address->setTitle($_POST['sadTitle']);
		$this->Address->setZip($_POST['sadPlz']);
		$this->Address->save();
		// Verbinden, wenn ein Typ vorhanden (Dann ist es eine neue Adresse)
		if ($this->Type > 0) {
			$this->User->addAddress($this->Address, $this->Type);
		}
		// Meldung generieren, wenn nicht alle / inkorrekte Daten (pro forma)
		if (!$this->isAddressInvalid()) {
			sessionConfig::set('ErrorMessage', $this->Res->html(1138, page::language()));
		} else {
			sessionConfig::set('ErrorMessage', $this->Res->html(57, page::language()));
		}
		// Weiterleiten, ohne Typ, da dieser nicht mehr gebraucht wird
		session_write_close();
		redirect('location: /modules/shop/view/address.php?id='.page::menuID().'&address='.$this->Address->getSadID());
	}

	/**
	 * Gibt an, ob die Adresse Fehler enthält
	 * @param boolean true/false ob Fehler oder nicht
	 */
	private function isAddressInvalid() {
		// Initialisieren, grundsätzlich alles OK
		$isValid = true;
		// Werte prüfen auf Länge
		if (strlen($this->Address->getFirstname()) == 0) $isValid = false;
		if (strlen($this->Address->getLastname()) == 0) $isValid = false;
		if (strlen($this->Address->getStreet()) == 0) $isValid = false;
		if (strlen($this->Address->getZip()) == 0) $isValid = false;
		if (strlen($this->Address->getCity()) == 0) $isValid = false;
		// Email Adresse prüfen
		if (!stringOps::checkEmail($this->Address->getEmail())) $isValid = false;
		return($isValid);
	}
}