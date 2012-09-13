<?php 
class moduleDirectlink extends commonModule {
	
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
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Laden der Daten
	public function loadData(&$Data) {
		$sSQL = "SELECT drl_ID,drl_Name,drl_Url FROM tbdirectlink 
		WHERE man_ID = ".page::mandant()." ORDER BY drl_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Standardtext, wenn leer
			if (strlen($row['drl_Name']) == 0) {
				$row['drl_Name'] = $this->Res->html(425,page::language());
			}
			// Einfügen in Daten array
			array_push($Data,$row);
		}
	}
	
	// Alle Links speichern
	public function saveLinks() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nDrlID = getInt($_POST['id'][$i]);
			$sName = $_POST['linkname'][$i];
			$sLink = $_POST['linkurl'][$i];
			// Validieren
			$this->Conn->escape($sName);
			$this->Conn->escape($sLink);
			stringOps::noHtml($sName);
			stringOps::alphaNumLow($sName);
			stringOps::noHtml($sLink);
			// SQL erstellen und abfeuern
			$sSQL = "UPDATE tbdirectlink SET 
			drl_Name = '$sName', drl_Url = '$sLink'
			WHERE drl_ID = $nDrlID";
			$this->Conn->command($sSQL);
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved directlinks');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/direct/index.php?id='.page::menuID());
	}
	
	// Löscht einen Directlink
	public function deleteLink() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(drl_ID) FROM tbdirectlink
		WHERE drl_ID = $nDeleteID AND man_ID = ".page::mandant();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tbdirectlink WHERE drl_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted directlink');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/direct/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting directlink');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/direct/index.php?id='.page::menuID()); 
		}
	}
	
	// Neuen Directlink hinzufügen
	public function addLink() {
		$sSQL = "INSERT INTO tbdirectlink (man_ID) VALUES (".page::mandant().")";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/direct/index.php?id='.page::menuID());
	}
}