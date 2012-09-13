<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Javascript einbinden
$Meta->addJavascript('/admin/start/index.js',true);

// Content Teil
$out = '
<br>
<br>
<h1>'.$Res->html(187,page::language()).'</h1>
<br>'.$Res->html(186,page::language());
// Zählen, ob weitere Mandanten vorhanden sind
$sSQL = "SELECT COUNT(man_ID) FROM tbmandant WHERE 
man_ID != ".page::mandant()." AND page_ID = ".page::id();;
$nWebs = $Conn->getCountResult($sSQL);
// Wenn Webs vorhanden, Liste und Logins anzeigen
if ($nWebs > 0) {
	$TabRow = new tabRowExtender();
	// Intro
	$out .= '<p><h2>'.$Res->html(852,page::language()).'</h2></p>
	<p>
	<table width="100%" border="0" cellspacing="0" cellpadding="3">';
	// Liste aller Webs rausholen
	$sSQL = "SELECT man_ID,man_Title FROM tbmandant 
	WHERE man_ID != ".page::mandant()." AND page_ID = ".page::id();
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		// Hauptdomain herausfinden (Erste die kein Redirect ist)
		$sSQL = "SELECT dom_Name FROM tbdomain 
		WHERE dom_redirect = 0 AND dom_Mandant = ".$row['man_ID'];
		$sDomain = $Conn->getFirstResult($sSQL);
		// Mandantendaten aufzeigen
		$out .= '
		<tr class="'.$TabRow->get().'">
			<td>'.$row['man_Title'].'</td>
			<td>'.$sDomain.'</td>
		';
		// Alle User/Securitystrings laden
		$sSQL = "SELECT usr_Alias,usr_Name,usr_Security FROM tbuser
		WHERE man_ID = ".$row['man_ID'];
		$nResUsr = $Conn->execute($sSQL);
		// Formular generieren
		$out .= '
			<td>
			<form action="http://'.$sDomain.'/login.php" method="post" target="_blank">
				<input type="hidden" name="LeavePasswordUnencrypted" value="1">
				<input type="hidden" name="username" value="">
				<input type="hidden" name="password" value="">
				<select name="userselect" onChange="selectUser(this.form);">
					<option value="0$$0">'.$Res->html(853,page::language()).'</option>
		';
		// Select erstellen für User/Alias/Security
		while ($usr = $Conn->next($nResUsr)) {
			if (strlen($usr['usr_Name']) > 0) {
				$usr['usr_Name'] = '('.$usr['usr_Name'].')';
			}
			$out .= '
					<option value="'.$usr['usr_Alias'].'$$'.$usr['usr_Security'].'">
					'.$usr['usr_Alias'].' '.$usr['usr_Name'].'</option>
			';
		}
		// Formular abschliessen
		$out .= '
				</select>
				<input type="submit" name="cmdLogin" value="'.$Res->html(4,page::language()).'">
			</form>
			</td>
		</tr>
		';
	}
	$out .= '
	</table>
	';
}
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');