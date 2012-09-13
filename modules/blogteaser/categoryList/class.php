<?php
class CategoryList implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $BlogID;	// Anzuzeigender Blog
	private $HTML;		// HTML Code über der Liste
	
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
		$Config = array();
		teaserConfig::get($this->TapID,$this->Conn,$Config);
		if ($Config !== false) {
			$this->BlogID = $Config['blogID']['Value'];
			stringOps::htmlViewEnt($Config['htmlCode']['Value']);
			$this->HTML = $Config['htmlCode']['Value'];
			// Anzeigen der Liste
			$this->printList($out);
		} else {
			$out .= $this->Res->html(598,page::language());
		}
	}
	
	// Liste aller Kategorien des Blogs erstellen
	private function printList(&$out) {
		// HTML Ausgeben
		$out .= $this->HTML;
		// Kategorien holen
		$url = '/modules/blog/category.php?id='.page::menuID().'&blog='.$this->BlogID.'&category=';
		$sSQL = "SELECT blc_ID,blc_Title FROM tbblogcategory
		WHERE mnu_ID = ".$this->BlogID." ORDER BY blc_Title ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<a class="cMoreLink" href="'.$url.$row['blc_ID'].'">'.stringOps::htmlEnt($row['blc_Title']).'</a><br>';
		}
	}
}