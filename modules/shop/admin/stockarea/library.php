<?php
class moduleShopStockarea extends commonModule {


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


	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn = & func_get_arg(0); // $Conn
		$this->Res = & func_get_arg(1); // $Res
	}

	// Artikelübersicht laden
	public function saveAreas() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nSsaID = getInt($_POST['id'][$i]);
			$area = new shopStockarea($nSsaID);
			$area->setName($_POST['name'][$i]);
			$area->setOpening($_POST['opening'][$i]);
			$area->setDelivery($_POST['send_'.$nSsaID]);
			$area->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop stockareas');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/stockarea/index.php?id='.page::menuID());
	}

	// Ein neues Lager erstellen
	public function addArea() {
		$area = new shopStockarea();
		$area->setManID(page::mandant());
		$area->setName($this->Res->html(1083,page::language()));
		$area->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('created stockarea #'.$area->getSsaID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/stockarea/index.php?id='.page::menuID());
	}

	// Ein Lager löschen
	public function deleteArea() {
		$area = new shopStockarea(getInt($_GET['delete']));
		if ($area->getManID() == page::mandant()) {
			// Lager löschen
			$area->delete();
			// Erfolg ausgeben
			logging::debug('deleted stockarea #'.$area->getSsaID());
			$this->setErrorSession($this->Res->html(57,page::language()));
		} else {
			// Fehler ausgeben
			logging::debug('error deleting stockarea #'.$area->getSsaID());
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/stockarea/index.php?id='.page::menuID());
	}
}