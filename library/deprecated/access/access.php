<?php
// Handelt einige Funktionen im
// Zusammenhang mit Menuzugriffen
class moduleAccess extends commonModule {
	
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
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	// Gruppen und zugehörigen Access holen
	public function loadGroups($nMenuID) {
		// Leeres Datenarray
		$sGroupData = array();
		$sAccesses = array();
		// Alle Gruppen holen
		$sSQL = "SELECT ugr_ID,ugr_Desc FROM tbusergroup 
		WHERE man_ID = ".page::mandant()." ORDER by ugr_Desc";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Kein Zugriff geben
			$row['ugr_Access'] = false;
			array_push($sGroupData,$row);
		}
		// Alle Zugriffe auf das Menu holen
		$sSQL = "SELECT mnu_ID,ugr_ID FROM tbaccess WHERE mnu_ID = $nMenuID";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($sAccesses,$row);
		}
		// Jeden Zugriff durchgehen
		foreach ($sAccesses as $sAccess) {
			// Jede Gruppe durchgehen
			for ($i = 0;$i < count($sGroupData);$i++) {
				// Wenn IDs identisch, Zugriff vorhanden
				if ($sGroupData[$i]['ugr_ID'] == $sAccess['ugr_ID']) {
					$sGroupData[$i]['ugr_Access'] = true;
				}
			}
		}
		// Array zurückgeben
		return($sGroupData);
	}
	
	// Zugriffe Speichern
	public function saveMenuAccess() {
		// Erstmal alle bisherigen Zugriffe löschen
		$nMenuID = getInt($_GET['menu']);
		$sSQL = "DELETE FROM tbaccess WHERE mnu_ID = $nMenuID";
		$this->Conn->command($sSQL);
		// Neue Zugriffe erstellen wenn vorhanden
		if (count($_POST['checkedUsergroups']) > 0) {
			foreach($_POST['checkedUsergroups'] as $nGroupID) {
				$sSQL = "INSERT INTO tbaccess (ugr_ID,mnu_ID)
				VALUES ($nGroupID, $nMenuID)";
				$this->Conn->command($sSQL);
			}
		}
		// Menu Session Objekte löschen
		unset($_SESSION['menuObjects']);
		// Erfolg ausgeben und zur Adresseite zurück
		logging::debug('saved menu access');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/menu/access.php?id='.page::menuID().'&menu='.$nMenuID);
	}
}