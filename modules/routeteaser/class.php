<?php
class routeTeaser implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	
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
		return(true);
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		$out .= '
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
			<tr>
				<td>
					'.$this->Res->html(875,page::language()).':<br>
				</td>
			</tr>
			<tr>
				<td>
					<form action="/modules/routeteaser/results.php" method="post">
						<input type="text" name="startAddress" class="cSmallInput" value="'.$this->Res->html(877,page::language()).'" 
						onclick="this.value=\'\'" onblur="if(this.value.length == 0) this.value=\''.$this->Res->html(877,page::language()).'\'"> <br>
						<input type="text" name="goalAddress" class="cSmallInput" value="'.$this->Res->html(876,page::language()).'" 
						onclick="this.value=\'\'" onblur="if(this.value.length == 0) this.value=\''.$this->Res->html(876,page::language()).'\'">
						<input type="submit" name="routeSubmit" value="Go" class="cButton">
					</form>
				</td>
			</tr>
		</table>
		';
	}
}