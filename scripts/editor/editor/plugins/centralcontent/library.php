<?php
class centralContent extends commonModule {
	
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
	
	// Zurückgeben der Optionen zur Auswahl eines
	// Zentrale Content Verwaltung Menüpunktes
	public function getCentralContentMenus() {
		$out = '';
		// Alle Menus vom Typ zentraler Content anzeigen
		$sSQL = "SELECT mnu_ID,mnu_Name FROM tbmenu 
		WHERE man_ID = ".page::mandant()." AND mnu_Active = 1 
		AND typ_ID = ".typeID::MENU_CENTRALCONTENT."
		ORDER BY mnu_Name ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['mnu_ID'].'">'.$row['mnu_Name'].'</option>'."\n";
		}
		
		return($out);
	}
}