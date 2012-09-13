<?php
/**
 * Sprachressourcenobjekt
 * Gibt je nach Angabe mehrsprachige Ressourcen (Strings)
 * in verschiedenen Formaten zurück. Diese werden, falls 
 * noch nicht geladen aus der Datenbank oder im anderen
 * Fall aus der Session geholt. Eine Instanz dieser Klasse
 * wird automatisch erzeigt (Globale Variable $Res)
 * @author Michael Sebel <michael@sebel.ch>
 */
class resources {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn = null;
	
	/**
	 * Erstellen des Sprachobjektes.
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 */
	public function __construct(dbConn &$Conn) {
		$this->Conn = $Conn;
	}
	
	/**
	 * Ressource aus Datenbank oder Session holen
	 * @param integer nResID, ID einer Sprachressource
	 * @param integer nLang, Sprachencode für Ressource
	 * @return string Geladene Ressource oder leerer String
	 */
	private function get($nResID,$nLang) {
		$sResource = '';
		// Zuerst schauen, ob die Ressource in der Session ist
		if (sessionRes::checkSessionRes($nResID,$nLang)) {
			$sResource = sessionRes::getSessionRes($nResID,$nLang);
		} else {
			// Wenn resID < 50000, global
			if ($nResID < 50000) {
				// Datenbank wechseln
				$this->Conn->setGlobalDB();
				$sResource = $this->resFromDB($nResID,$nLang);
				// Datenbank zurückwechseln
				$this->Conn->setInstanceDB();
			} else {
				// Aus der instanzdatenbank laden
				$sResource = $this->resFromDB($nResID,$nLang);
			}
		}
		// Ressource zurückgeben
		return($sResource);
	}
	
	/**
	 * Lädt eine bestimmte Ressource aus der Datenbank.
	 * Dies kann, je nach Einstellung des Datenbankobjektes
	 * die Globale- oder die Instanzdatenbank sein.
	 * @param integer nResID, ID einer Sprachressource
	 * @param integer nLang, Sprachencode für Ressource
	 * @return string Geladene Ressource oder leerer String
	 */
	private function resFromDB($nResID,$nLang) {
		$sResource = '';
		$nResults = 0;
		$sSQL = 'SELECT res_Text FROM tbresource WHERE
		res_ID = '.$nResID.' AND res_Language = '.$nLang;
		$Resource = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($Resource)) {
			$sResource = $row['res_Text'];
			$nResults++;
			// Und gleich in die Session speichern
			sessionRes::addSessionRes($nResID,$nLang,$sResource);
		}
		// Wenn keine Ergebnisse Fallback versuchen
		if ($nLang != FALLBACK_LANG && $nResults == 0) {
			$sSQL = 'SELECT res_Text FROM tbresource WHERE
			res_ID = '.$nResID.' AND res_Language = '.FALLBACK_LANG;
			$Resource = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($Resource)) {
				$sResource = $row['res_Text'];
				// Und gleich in die Session speichern
				sessionRes::addSessionRes($nResID,FALLBACK_LANG,$sResource);
			}
		}
		return($sResource);
	}
	
	/**
	 * Gebe Ressource ohne Veränderung zurück.
	 * @param integer nResID, ID einer Sprachressource
	 * @param integer nLang, Sprachencode für Ressource
	 * @return string Geladene Ressource oder leerer String
	 */
	public function normal($nResID,$nLang) {
		$sResource = $this->get($nResID,$nLang);
		return($sResource);
	}
	
	/**
	 * Gebe Ressource HTML kodiert zurück.
	 * @param integer nResID, ID einer Sprachressource
	 * @param integer nLang, Sprachencode für Ressource
	 * @return string Geladene Ressource oder leerer String
	 */
	public function html($nResID,$nLang) {
		$sResource = $this->get($nResID,$nLang);
		$sResource = htmlentities($sResource);
		return($sResource);
	}
	
	/**
	 * Gebe Ressource für Javascript kodiert zurück.
	 * @param integer nResID, ID einer Sprachressource
	 * @param integer nLang, Sprachencode für Ressource
	 * @return string Geladene Ressource oder leerer String
	 */
	public function javascript($nResID,$nLang) {
		$sResource = $this->get($nResID,$nLang);
		$sResource = addslashes($sResource);
		return($sResource);
	}
}

/**
 * Singleton Klasse für das Ressourcen Objekt
 * @author Michael Sebel <michael@sebel.ch>
 */
class getResources {
	
	/**
	 * Gibt eine Statische Instanz der Resourcen zurück.
	 * Das dbConn Objekt muss nur beim ersten mal übergeben werden
	 * @return resources Instanz der Resourcen
	 */
	public static function getInstance() {
		return(singleton::resources());
	}
}