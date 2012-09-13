<?php
class TeaserSearch implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $TapID;		// ID der Applikation
	
	// Konstruieren
	public function __construct() {
		
	}
	
	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}
	
	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		// Content ausgeben
		$out .= '
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<td>
					'.$this->Res->html(497,page::language()).':<br>
				</td>
			</tr>
			<tr>
				<td>
					<form action="/modules/search/results.php" method="post">
						<input type="text" name="searchKeywords" class="cSmallInput"> 
						<input type="submit" name="searchSubmit" value="Go" class="cButton">
					</form>
				</td>
			</tr>
		</table>
		';
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
}