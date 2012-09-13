<?php
class TeaserLogin implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	
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
		$Res = $this->Res;
		// Login Formular ausgeben:
		$out .= '
		<form name="loginForm" method="post" action="/login.php">
			<table width="100%" cellpadding="3" cellspacing="0">
				<tr>
					<td width="120" valign="top">
						'.$Res->html(2,page::language()).':<br>
						<input type="text" name="username" value="" style="width:120px;margin-top:3px;">
					</td>
				</tr>
				<tr>
					<td width="120" valign="top">
						'.$Res->html(3,page::language()).':<br>
						<input type="password" name="password" value="" style="width:120px;margin-top:3px;">
					</td>
				</tr>
				<tr>
					<td width="120" valign="top">
						<input class="cButton" type="submit" name="cmdLogin" value="'.$Res->html(4,page::language()).'">
					</td>
				</tr>
			</table>
		</form>
		';
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		// ID wird nicht gebraucht ...
	}
}