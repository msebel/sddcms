<?php
class moduleShopMeta extends commonModule {


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
	public function saveMetas() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nSdfID = getInt($_POST['id'][$i]);
			$field = new shopDynamicfield($nSdfID);
			$field->setName($_POST['name'][$i]);
			$field->save();
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved shop meta field '.$nSdfID);
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/meta/index.php?id='.page::menuID());
	}

	// Speichern des Feldes
	public function saveMeta(shopDynamicfield $field) {
		// Name speichern
		$field->setName($_POST['sdfName']);
		// Default speichern
		if (isset($_POST['sdfDefault'])) {
			$field->setDefault($_POST['sdfDefault']);
		}
		// Values speichern, wenn vorhanden
		if (isset($_POST['id'])) {
			$this->saveValues($field);
		}
		// Erfolg ausgeben und weiterleiten
		$field->save();
		logging::debug('saved shop meta field '.$field->getSdfID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/meta/edit.php?id='.page::menuID().'&field='.$field->getSdfID());
	}

	// Neuen leeren Vorgabewert hinzufügen
	public function addValue(shopDynamicfield $field) {
		$sText = '< '.$this->Res->normal(1065,page::language()).' >';
		$sSQL = 'INSERT INTO tbshopdynamicvalue (sdf_ID,sdv_Value,sdv_Order)
		VALUES ('.$field->getSdfID().',"'.$sText.'",'.$this->getNextValueOrder($field).')';
		$this->Conn->command($sSQL);
		// Erfolg ausgeben und weiterleiten
		logging::debug('added new shop meta field to field #'.$field->getSdfID());
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/meta/edit.php?id='.page::menuID().'&field='.$field->getSdfID());
	}

	// Speichert die Vorgabewerte eines Feldes anhand Tabelle
	public function saveValues(shopDynamicfield $field) {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nSdvID = getInt($_POST['id'][$i]);
			$nOrder = getInt($_POST['sort'][$i]);
			$sValue = $_POST['value'][$i];
			stringOps::noHtml($sValue);
			$this->Conn->escape($sValue);
			// Per SQL direkt speichern
			$sSQL = "UPDATE tbshopdynamicvalue SET
			sdv_Order = $nOrder, sdv_Value = '$sValue'
			WHERE sdv_ID = $nSdvID AND sdf_ID = ".$field->getSdfID();
			$this->Conn->command($sSQL);
		}
		// Vorgehen loggen
		logging::debug('saved shop meta field values '.$field->getSdfID());
	}

	// Löschen des Zusatzfeldes
	public function deleteMeta() {
		$nSdfID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(sdf_ID) FROM tbshopdynamicfield
		WHERE sdf_ID = $nSdfID AND man_ID = ".page::mandant();
		// Wenn es den Record so gibt, löschen
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$field = new shopDynamicfield($nSdfID);
			$field->delete();
			// Erfolg melden und weiterleiten
			logging::debug('deleted shop article meta field '.$nSdfID);
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting shop article meta field '.$nSdfID);
			$this->setErrorSession($this->Res->html(55,page::language()));
		}
		// Auf Aktuelle Seite weiterleiten
		session_write_close();
		redirect('location: /modules/shop/admin/meta/index.php?id='.page::menuID());
	}

	// Zusatzfeld neu erstellen
	public function addMeta() {
		// Neues Feld erstellen
		$field = new shopDynamicfield();
		$field->setManID(page::mandant());
		$field->setName($_POST['sdfName']);
		$field->setType($_POST['sdfType']);
		$field->save();
		// Erfolg ausgeben und weiterleiten
		logging::debug('added shop meta field');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /modules/shop/admin/meta/index.php?id='.page::menuID());
	}

	// Felder ohne Paging
	public function loadFields(array &$fields) {
		$sSQL = 'SELECT sdf_ID,man_ID,sdf_Name,sdf_Default,sdf_Type
        FROM tbshopdynamicfield WHERE man_ID = '.page::mandant().'
		ORDER BY sdf_Name ASC, sdf_Type ASC';
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$field = new shopDynamicfield();
			$field->loadRow($row);
			array_push($fields,$field);
		}
	}

	/**
	 * Lädt das dynamische Feld
	 * @return shopDynamicfield Dynamisches Feld
	 */
	public function loadField() {
		$nSdfID = getInt($_GET['field']);
		$field = new shopDynamicfield($nSdfID);
		if ($field->getManID() != page::mandant()) {
			redirect('location: /error.php?type=noAccess');
		}
		return($field);
	}

	// Nächsten Order für Value Feld holen
	private function getNextValueOrder(shopDynamicfield $field) {
		$sSQL = 'SELECT IFNULL(MAX(sdv_Order),0)+1 FROM tbshopdynamicvalue
		WHERE sdf_ID = '.$field->getSdfID();
		return($this->Conn->getFirstResult($sSQL));
	}
}