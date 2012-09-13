<?php
// Medienkonstanten sind hier nötig (Keine doppelten Gleise hier!)
require_once(BP.'/library/class/mediaManager/mediaConst.php');

/**
 * Statische Klasse zum Arbeiten mit Datei Elementen
 * @author Michael Sebel <michael@sebel.ch>
 */
class elementOps {

	/**
	 * Erstellt ein Element und gibt dessen ID zurück.
	 * Der Ordner dazu wird auch bereits erstellt
	 */
	public static function create() {
		$Conn = singleton::conn();
		$nOwnerID = ownerID::get($Conn);
		$sSQL = "INSERT INTO tbelement (owner_ID,ele_Size,ele_Links,ele_Type,
		ele_Library,ele_Thumb,ele_Target,ele_File,ele_Desc,ele_Longdesc) VALUES
		($nOwnerID,0,0,".mediaConst::TYPE_UNKNOWN.",0,0,'','','','')";
		// Datensatz erstellen und neue ID zurückgeben
		$nElementID = $Conn->insert($sSQL);
		// Name des Ordner generieren
		$sPath = self::getFolder($nElementID, true);
		// Ordner im Filesystem erstellen
		if (!file_exists($sPath)) mkdir($sPath,0755);
		// ID nun zurückgeben
		return($nElementID);
	}

	/**
	 * Löscht ein Element in der Datenbank und im Filesystem
	 * @param int $nEleID ID eines Elements
	 */
	public static function delete($nEleID) {
		// Datenbank löschen
		$sSQL = 'DELETE FROM tbelement WHERE ele_ID = '.$nEleID;
		singleton::conn()->command($sSQL);
		// Ordner vollständig löschen
		$sPath = self::getFolder($nEleID, true);
		if (file_exists($sPath)) {
			fileOps::deleteFolder($sPath);
		}
	}
	
	/**
	 * Gibt ein Feld eines Elements zurück (Später auch individuelle)
	 * @param int $nEleID ID des Elements
	 * @param string $sField GEwünschtes Datenbankfeld
	 */
	public static function getField($nEleID,$sField) {
		$sSQL = 'SELECT '.$sField.' FROM tbelement WHERE ele_ID = '.$nEleID;
		return(singleton::conn()->getFirstResult($sSQL));
	}

	/**
	 * Gibt den Basispfad für ein Element zurück
	 * @param int $nEleID ID des Elements
	 * @param boolean $bBasepath Gibt an, ob BP Variable angehängt wird
	 */
	public static function getFolder($nEleID,$bBasepath = true) {
		$sPath = mediaConst::FILEPATH;
		if ($bBasePath) $sPath = BP.$sPath;
		$sPath = str_replace('{PAGE_ID}', page::id(), $sPath);
		$sPath = str_replace('{ELE_ID}', $nEleID, $sPath);
		return($sPath);
	}
}