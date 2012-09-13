<?php
class BlogStats implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $BlogID;	// Anzuzeigender Blog
	private $HTML;		// HTML Code über der Liste
	
	const PRESERVE_STATS = 20;
	
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
			$this->printStats($out);
		} else {
			$out .= $this->Res->html(598,page::language());
		}
	}
	
	// Liste aller Kategorien des Blogs erstellen
	private function printStats(&$out) {
		// HTML Ausgeben
		$out .= $this->HTML;
		// Von Session holen wenn vorhanden
		if (isset($_SESSION['blogStats_'.$this->TapID])) {
			if (!isset($_SESSION['blogStats_'.$this->TapID.'_count'])) {
				$_SESSION['blogStats_'.$this->TapID.'_count'] = 0;
			}
			$_SESSION['blogStats_'.$this->TapID.'_count']++;
			$out .= $_SESSION['blogStats_'.$this->TapID];
			// Wenn 10-mal aufgerufen, reset fürs nächstemal
			if ($_SESSION['blogStats_'.$this->TapID.'_count'] == BlogStats::PRESERVE_STATS) {
				unset($_SESSION['blogStats_'.$this->TapID]);
			}
		} else {
			// Generieren der Statistik und speichern in Session
			$nCategories = 0;
			$nEntries = 0;
			$nComments = 0;
			$nCommentsPerEntry = 0;
			$nEntriesPerCategory = 0;
			// Kategorien abfragen
			$sSQL = "SELECT COUNT(blc_ID) FROM tbblogcategory
			WHERE mnu_ID = ".$this->BlogID;
			$nCategories = $this->Conn->getCountResult($sSQL);
			// Einträge abfragen
			$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
			WHERE con_Active = 1 AND mnu_ID = ".$this->BlogID;
			$nEntries = $this->Conn->getCountResult($sSQL);
			// Kommentare in Einträge abfragen
			$sSQL = "SELECT COUNT(tbcontent.con_ID) FROM tbcontent
			INNER JOIN tbkommentar ON tbkommentar.owner_ID = tbcontent.con_ID
			WHERE tbcontent.mnu_ID = ".$this->BlogID;
			$nComments = $this->Conn->getCountResult($sSQL);
			// Daten in Session speichern
			$_SESSION['blogStats_'.$this->TapID] = '
			<p>
			'.$this->Res->html(620,page::language()).': '.$nCategories.'<br>
			'.$this->Res->html(666,page::language()).': '.$nEntries.'<br>
			'.$this->Res->html(628,page::language()).': '.$nComments.'<br>
			</p>
			';
			// Und den schmarrn noch ausgeben
			$out .= $_SESSION['blogStats_'.$this->TapID];
		}
	}
}