<?php
class BlogTagcloud implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $BlogID;	// Anzuzeigender Blog
	
	// Konstruieren
	public function __construct() {
		
	}
	
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
		return(true);
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		$sSQL = $this->getSql();
		if (strlen($sSQL) > 0) {
			$this->appendTagcloud($out,$sSQL);
		} else {
			$out .= $this->Res->html(598,page::language());
		}
	}
	
	// SQL für eine Blog-Tagcloud zurückgeben
	private function getSql() {
		$Config = array();
		teaserConfig::get($this->TapID,$this->Conn,$Config);
		if ($Config !== false) {
			$this->BlogID = $Config['blogID']['Value'];
			$sSQL = "SELECT tbkeyword.key_Keyword FROM tbcontent 
			INNER JOIN tbkeyword ON tbkeyword.owner_ID = tbcontent.con_ID
			WHERE tbcontent.mnu_ID = ".$this->BlogID;
		} else {
			$sSQL = '';
		}
		return($sSQL);
	}
	
	// Tagcloud in den Output generieren
	private function appendTagcloud(&$out,&$sSQL) {
		// Tagcloud Objekt erstellen
		$Tagcloud = htmlControl::tagcloud();
		// Tagcloud konfigurieren
		$Tagcloud->add('blogCloud','/modules/blog/keyword.php?id='.page::menuID().'&blog='.$this->BlogID.'&keyword=');
		$Tagcloud->changePriorities('intelliCloud',2,3,5,7,9);
		// Keywords an Tagcloud übergeben
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Tagcloud->addKeyword('blogCloud',$row['key_Keyword']);
		}
		// Tagcloud in den output ausgeben
		$out .= $Tagcloud->get('blogCloud');
	}
}