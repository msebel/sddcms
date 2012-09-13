<?php
// Library für Teasercontent Elemente
class moduleFacebookBox extends commonModule {
	
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
			logging::error('facebook box teaser access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Konfiguration speichern
	public function saveConfig($nTapID,&$Config) {
		$Config['pageLink']['Value'] = stringOps::getPostEscaped('pageLink',$this->Conn);
		$Config['widthPixel']['Value'] = numericOps::validateNumber($_POST['widthPixel'],0,350);
		$Config['showStream']['Value'] = numericOps::validateNumber($_POST['showStream'],0,1);
		// Konfiguration speichern
		teaserConfig::saveConfig($nTapID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved facebook box teaser config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/facebookbox/index.php?id='.page::menuID().'&element='.$nTapID);
	}
	
	// Konfiguration initialisieren
	public function initConfig($nTapID,&$Config) {
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!teaserConfig::hasConfig($nTapID,$this->Conn,3)) {
			teaserConfig::setConfig($nTapID,$this->Conn,'',pageConfig::TYPE_VALUE,'pageLink',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'showStream',$Config);
			teaserConfig::setConfig($nTapID,$this->Conn,220,pageConfig::TYPE_NUMERIC,'widthPixel',$Config);
		} else {
			// Konfiguration laden
			teaserConfig::get($nTapID,$this->Conn,$Config);
		}
	}
}