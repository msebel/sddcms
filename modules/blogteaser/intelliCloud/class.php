<?php
class IntelliCloud implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $BlogID;	// Anzuzeigender Blog
	private $CatID;		// Anzuzeigende Kategorie
	
	// Konstruieren
	public function __construct() {}
	
	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
	
	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		// Blog ID laden
		$this->BlogID = getInt($_GET['blog']);
		// Wenn kein Blog vorhanden, versuchen von der Page ID zu holen
		if ($this->BlogID == 0) {
			$nMenuID = page::menuID();
			$Config = array();
			try {
				pageConfig::getWithException($nMenuID,$this->Conn,$Config);
				$this->BlogID = getInt($Config['blogID']['Value']);
			} catch (Exception $e) {
				// Einfach ruhig bleiben...
			}
		}
		// Kategorie ID laden
		$this->CatID = getInt($_GET['category']);
		// Eingaben matchen wenn möglich
		$hasOutput = $this->validateIntelligence();
		return($hasOutput);
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		$sSQL = '';
		// SQL holen anhand ausgefüllter ID, blog zuerst
		if ($this->BlogID > 0) $sSQL = $this->getBlogSql();
		// Wenn vorhanden durch Kategorien SQL überschreiben
		if ($this->CatID > 0) $sSQL = $this->getCategorySql();
		// Tagcloud generieren, wenn SQL vorhanden
		if (strlen($sSQL) > 0) $this->appendTagcloud($out,$sSQL);
	}
	
	// SQL für eine Blog-Tagcloud zurückgeben
	private function getBlogSql() {
		$sSQL = "SELECT tbkeyword.key_Keyword FROM tbcontent 
		INNER JOIN tbkeyword ON tbkeyword.owner_ID = tbcontent.con_ID
		WHERE tbcontent.mnu_ID = ".$this->BlogID;
		return($sSQL);
	}
	
	// SQL für eine Kategorien-Tagcloud zurückgeben
	private function getCategorySql() {
		$sSQL = "SELECT tbkeyword.key_Keyword FROM tbblogcategory 
		INNER JOIN tbblogcategory_content ON tbblogcategory_content.blc_ID = tbblogcategory.blc_ID
		INNER JOIN tbcontent ON tbcontent.con_ID = tbblogcategory_content.con_ID
		INNER JOIN tbkeyword ON tbkeyword.owner_ID = tbcontent.con_ID
		WHERE tbblogcategory.blc_ID = ".$this->CatID." AND tbcontent.mnu_ID = ".$this->BlogID;
		return($sSQL);
	}
	
	// Tagcloud in den Output generieren
	private function appendTagcloud(&$out,&$sSQL) {
		// Tagcloud Objekt erstellen
		$Tagcloud = htmlControl::tagcloud();
		// Tagcloud konfigurieren
		$Tagcloud->add('intelliCloud','/modules/blog/keyword.php?id='.page::menuID().'&blog='.$this->BlogID.'&keyword=');
		$Tagcloud->changePriorities('intelliCloud',2,3,5,7,9);
		// Keywords an Tagcloud übergeben
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Tagcloud->addKeyword('intelliCloud',$row['key_Keyword']);
		}
		// Tagcloud in den output ausgeben
		$out .= $Tagcloud->get('intelliCloud');
	}
	
	// Schauen, ob blogID oder catID vorhanden und ob
	// diese validiert werden können
	private function validateIntelligence() {
		// Fehler, wenn beide nicht vorhanden
		if ($this->BlogID == 0 && $this->CatID == 0) {
			return(false);
		}
		// Fehler, wenn nur Kategorie vorhanden
		if ($this->BlogID == 0 && $this->CatID != 0) {
			return(false);
		}
		// Fehler, wenn einer der beiden unter null ist
		if ($this->BlogID < 0 || $this->CatID < 0) {
			return(false);
		}
		// Wenn beide vorhanden, diese Validieren und
		// im erfolgsfall true zurückgeben
		if ($this->BlogID > 0 && $this->CatID > 0) {
			$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
			WHERE blc_ID = ".$this->CatID." AND mnu_ID = ".$this->BlogID;
			$nResult = $this->Conn->getCountResult($sSQL);
			if ($nResult == 1) return(true);
		}
		// Wenn nur Blog vorhanden, diesen anhand menu
		// validieren und im Erfolgsfall true zurückgeben
		if ($this->BlogID > 0 && $this->CatID == 0) {
			$sSQL = "SELECT COUNT(mnu_ID) FROM tbmenu
			WHERE mnu_ID = ".$this->BlogID." AND typ_ID = ".typeID::MENU_BLOGADMIN;
			$nResult = $this->Conn->getCountResult($sSQL);
			if ($nResult == 1) return(true);
		}
		// Wenn der pointer tatsächlich bis hierhin kommt
		// kann etwas nicht stimmen, false zurückgeben
		return(false);
	}
}