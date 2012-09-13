<?php
/**
 * View für die Passwort vergessen Seite
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewLostPassword extends abstractShopView {

	/**
	 * Ruft lediglich den Basiskonstruktor auf
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		parent::__construct($name);
	}

	/**
	 * Führt die View aus
	 */
	public function getContent() {
		$sMessage = '&nbsp;';
		// Wenn Passwort angefordert
		if (isset($_GET['pwd'])) {
			$sMessage = $this->sendPassword();
		}
		// Daten ausgeben
		$this->Tpl->addData('PASSWORD_MESSAGE', $sMessage);
		$this->Tpl->addData('MENU_ID',page::menuID());
		// Session löschen, falls sie gesetzt wurde
		unset($_SESSION['SessionConfig'][shopConfig::LoginMenuID().'_ImpersonationSecurity']);
		// System abschliessen
		return($this->Tpl->output());
	}

	/**
	 * Versendet ein neues Passwort, wenn die gegebene E-Mail
	 * Adresse im Shop vorhanden ist, sonst Fehlermeldung
	 * @return string Message Erfolg oder Fehler
	 */
	private function sendPassword() {
		// Davon ausgehen, dass die Email nicht gefunden wurde
		$sMsg = $this->Res->html(1145,page::language());
		$sEmail = stringOps::getPostEscaped('emailAddress', $this->Conn);
		// Suchen der E-Mail Adresse (In Impersonationen)
		$sSQL = "SELECT imp_ID FROM tbimpersonation
		WHERE imp_Active = 1 AND imp_Alias = '$sEmail'
		AND man_ID = ".page::mandant();
		$nImpID = getInt($this->Conn->getFirstResult($sSQL));
		// Wenn vorhanden, dessen Passwort neu setzen
		if ($nImpID > 0 && strlen($sEmail) > 0) {
			// Neues Passwort setzen
			$User = shopUser::getInstanceByImpersonationID($nImpID);
			$sPassword = stringOps::getRandom(8);
			$User->setPassword($sPassword);
			// Mail versenden
			$User->sendLoginInfo($sEmail, $sPassword);
			// Entsprechende Nachricht definieren
			$sMsg = $this->Res->html(1146,page::language());
		}
		return($sMsg);
	}
}