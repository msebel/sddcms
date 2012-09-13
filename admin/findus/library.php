<?php
class moduleFindus extends commonModule {
	
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
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$nViewType = getInt($_POST['viewType']);
		if ($nViewType < 1 || $nViewType > 2) $nViewType = 1;
		$Config['goalAddress']['Value'] = stringOps::getPostEscaped('goalAddress',$this->Conn);
		$Config['htmlCode']['Value'] = stringOps::getPostEscaped('htmlCode',$this->Conn);
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		stringOps::noHtml($Config['goalAddress']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved findus config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/findus/index.php?id='.page::menuID());
	}
	
	// Konfiguration initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,2)) {
			$sText = $this->getStandardText();
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'goalAddress',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,$sText,pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Standard Text für Einleitung generieren
	private function getStandardText() {
		$sText = '<h1>'.$this->Res->html(864,page::language()).'</h1>
		<p>'.$this->Res->html(865,page::language()).'</p>';
		return($sText);
	}
}