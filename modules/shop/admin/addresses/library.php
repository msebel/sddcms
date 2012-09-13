<?php
class moduleShopAddresses extends commonModule {

	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	/**
	 * Rücksprungs-Url
	 * @var string
	 */
	private $myUrl;

	public function __construct($nID = 0) {
    parent::__construct($nID);
		$this->myUrl = '/modules/shop/admin/addresses/index.php?id='.page::menuID();
  }

	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
	}

	// Eingaben zu den Shop-User-Daten speichern
	public function saveAddresses() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nShuID = getInt($_POST['id'][$i]);
			$user = new shopUser($nShuID);
			$user->setActive($_POST['active_'.$nShuID]);
			$user->setBillable($_POST['bill_'.$nShuID]);
			$user->setCondition($_POST['condition'][$i]);
			$user->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop address');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location:'.$this->myUrl);
	}
}