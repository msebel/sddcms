<?php
class TeaserEntry implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	
	// Konstruieren
	public function __construct() {
		
	}
	
	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
	}
	
	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		// Content ausgeben
		$sSQL = "SELECT ten_Content,mnu_ID 
		FROM tbteaserentry WHERE tap_ID = ".$this->TapID;
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			stringOps::htmlViewEnt($row['ten_Content']);
			// Link erzeugen, der evtl gebraucht wird
			$link = '';
			if (getInt($row['mnu_ID']) > 0) {
				$menu = menuObject::getInstance($row['mnu_ID']);
				$link = '<a href="'.$menu->getLink().'"
				class="cMoreLink">'.$this->Res->html(442,page::language()).'</a>';
			}
			// Wenn im Content ein [MORE] Tag ist, mit Link ersetzen
			if (stristr($row['ten_Content'],'[MORE]') !== false) {
				// Ersetzen und in Output
				$row['ten_Content'] = str_replace('[MORE]', $link, $row['ten_Content']);
				$out .= $row['ten_Content'];
			} else {
				// In Output und link dahinter
				$out .= $row['ten_Content'];
				$out .= $link;
			}
		}
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
}