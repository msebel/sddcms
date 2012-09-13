<?php
// Library für Teasercontent Elemente
class moduleTeaserCalendar extends commonModule {
	
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
	
	// Gewährt Zutritt für das Teasercontent Element
	public function checkAccess() {
		$nTapID = getInt($_GET['element']);
		// Zählen ob es die Verbindung gibt, wenn nicht
		// Fehler und Redirect auf Error noAccess
		$sSQL = "SELECT COUNT(tsa_ID) FROM tbteasersection_teaser
		WHERE tas_ID = ".$_SESSION['teaserBackID']." AND tap_ID = $nTapID";
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult != 1) {
			logging::error('calendar teaser access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Konfiguration speichern
	public function saveConfig($nTapID,&$Config) {
		$Config['menuSource']['Value'] = $this->validateMenu($_POST['menuSource']);
		$Config['viewConcerts']['Value'] = numericOps::validateNumber($_POST['viewConcerts'],0,1);
		$Config['viewOthers']['Value'] = numericOps::validateNumber($_POST['viewOthers'],0,1);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved calendar teaser config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/calendarcontrol/index.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Menupunkt Kalender validieren oder 0 zurückgeben
	private function validateMenu($nMenuID) {
		$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu WHERE man_ID = ".page::mandant()." 
		AND mnu_Active = 1 AND typ_ID = ".typeID::MENU_CALENDAR." AND mnu_ID = $nMenuID";
		$nCountResult = $this->Conn->getCountResult($sSQL);
		if ($nCountResult != 1) $nMenuID = 0;
		return($nMenuID);
	}
	
	// Konfiguration initialisieren
	public function initConfig($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,3)) {
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'menuSource',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'viewOthers',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'viewConcerts',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
	
	// Optionen aller Kalender anzeigen
	public function getCalendarOptions($nCurrentMenu) {
		$out = '';
		$sSQL = "SELECT mnu_ID,mnu_Name FROM tbmenu WHERE man_ID = ".page::mandant()." 
		AND mnu_Active = 1 AND typ_ID = ".typeID::MENU_CALENDAR;
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['mnu_ID'].'"'.checkDropDown($nCurrentMenu,$row['mnu_ID']).'>'.$row['mnu_Name'].'</option>'."\n";
		}
		return($out);
	}
}