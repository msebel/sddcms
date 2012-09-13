<?php
class facebookBoxTeaser implements teaser {
	
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
		// Konfiguration laden
		$Config = array();
		teaserConfig::get($this->TapID, $this->Conn, $Config);
		// Konfigurationsdaten verarbeiten
		stringOps::urlEncode($Config['pageLink']['Value']);
		$showStream = 'false';
		$height = 220;
		if ($Config['showStream']['Value'] == 1) {
			$showStream = 'true';
			$height = 395;
		}
		// IFrame ausgeben
		$out.= '
		<iframe '.
			'src="http://www.facebook.com/plugins/likebox.php?'.
			'href='.$Config['pageLink']['Value'].' '.
			'&amp;width='.$Config['widthPixel']['Value'].'&amp;colorscheme=light&amp;show_faces=true&amp;'.
			'stream='.$showStream.'&amp;header=false&amp;height='.$height.'" '.
			'scrolling="no" frameborder="0" '.
			'style="border:none; overflow:hidden; '.
			'width:'.$Config['widthPixel']['Value'].'px; height:'.$height.'px;" '.
			'allowTransparency="true">'.
		'</iframe>';
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
}