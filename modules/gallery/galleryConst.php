<?php
class galleryConst {
	// Galerietypen
	const TYPE_LIGHTBOX = 1;
	const TYPE_SIMPLEVIEWER = 2;
	const TYPE_TILTVIEWER = 3;
	
	// Uploadelement für Mediamanager erstellen / holen
	public function getMenuElement(dbConn &$Conn) {
		$nMenuElement = 0;
		// Zählen wie viele Elemente die MenuID hat
		$sSQL = "SELECT COUNT(ele_ID) FROM tbelement WHERE
		owner_ID = ".page::menuID();
		$nResult = $Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			// Menu Element auslesen
			$sSQL = "SELECT ele_ID FROM tbelement WHERE
			owner_ID = ".page::menuID();
			$nMenuElement = $Conn->getFirstResult($sSQL);
		} else {
			// Menu Element erstellen
			$sSQL = "INSERT INTO tbelement (owner_ID) VALUES (".page::menuID().")";
			$nMenuElement = $Conn->insert($sSQL);
		}
		return($nMenuElement);
	}
}