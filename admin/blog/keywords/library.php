<?php 
class keywords extends commonModule {
	
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
	/**
	 * Array, welches die Daten beinhaltet
	 * @var array
	 */
	public $Data = array();
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Keywords für eine Owner ID laden
	public function load($nOwnerID) {
		$sSQL = "SELECT key_ID,key_Keyword FROM tbkeyword
		WHERE owner_ID = $nOwnerID ORDER BY key_Keyword ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			if (!$this->keywordExists($row['key_Keyword'])) {
				array_push($this->Data,$row);
			} else {
				$this->deleteKeyword($row['key_ID']);
			}
		}
	}
	
	// Alle Keywords aus einer Textbox speichern
	public function save($nOwnerID,$KeywordText) {
		// Keywordtext validieren und in ein Array wandeln
		$this->validateKeywordText($KeywordText);
		$Keywords = explode(',',$KeywordText);
		$this->validateKeywords($Keywords);
		// Keywords die es schon gibt aussortieren
		// Anbei werden nicht mehr benötigte gelöscht
		$this->sortOutExistingKeywords($Keywords);
		// Verbleibende Keywords in die Datenbank schreiben
		$this->saveToDB($nOwnerID,$Keywords);
	}
	
	// Gesamten Keywordtext validieren
	private function validateKeywordText(&$KwText) {
		stringOps::noHtml($KwText);
		$this->Conn->escape($KwText);
	}
	
	// Keyword Array neuer Keywords validieren
	private function validateKeywords(&$Keywords) {
		for ($i = 0;$i < count($Keywords);$i++) {
			$Keywords[$i] = trim($Keywords[$i]);
			$Keywords[$i] = strtolower($Keywords[$i]);
		}
	}
	
	// Doppelte Values aussortieren
	// Anbei werden nicht mehr benötigte gelöscht
	private function sortOutExistingKeywords(&$Keywords) {
		$NewKeywords = array();			// Enthält nur Keywords
		$Deleteable = array();			// Enthält nur ID's..
		// Neues Keyword array durchgehen für doppelte
		for ($i = 0;$i < count($Keywords);$i++) {
			if (!$this->keywordExists($Keywords[$i]) && strlen($Keywords[$i]) > 0) {
				array_push($NewKeywords,$Keywords[$i]);
			}
		}
		// Altes Keyword array durchgehen für zu löschende
		for ($i = 0;$i < count($this->Data);$i++) {
			if ($this->keywordDeleteable($this->Data[$i]['key_Keyword'],$Keywords)) {
				array_push($Deleteable,$this->Data[$i]['key_ID']);
			}
		}
		// Zu löschende Keywords entfernen
		$this->deleteKeywords($Deleteable);
		// Am Ende die NewKeywords als Referenz zurück geben
		$Keywords = $NewKeywords;
	}
	
	// Keyword in einem Array finden
	private function keywordExists(&$KeyFind) {
		$Exists = false;
		foreach ($this->Data as $Keyword) {
			if ($Keyword['key_Keyword'] == $KeyFind) {
				$Exists = true; break;
			}
		}
		return($Exists);
	}
	
	// Keywords zum löschen definieren
	private function keywordDeleteable(&$KeyFind,&$KeyArray) {
		$Delete = true;
		foreach ($KeyArray as $Keyword) {
			if ($Keyword == $KeyFind) {
				$Delete = false; break;
			}
		}
		return($Delete);
	}
	
	// Keywords löschen
	private function deleteKeywords(&$Deleteable) {
		foreach ($Deleteable as $ID) {
			$sSQL = "DELETE FROM tbkeyword WHERE key_ID = $ID";
			$this->Conn->Command($sSQL);
		}
	}
	
	// Keyword löschen
	private function deleteKeyword($ID) {
		$sSQL = "DELETE FROM tbkeyword WHERE key_ID = $ID";
		$this->Conn->Command($sSQL);
	}
	
	// Keyword Array mit OwnerID als Besitzer in Datenbank speichern
	private function saveToDB($nOwnerID,&$Keywords) {
		foreach ($Keywords as $Keyword) {
			$sSQL = "INSERT INTO tbkeyword (owner_ID,key_Keyword)
			VALUES ($nOwnerID,'$Keyword')";
			$this->Conn->Command($sSQL);
		}
	}
}