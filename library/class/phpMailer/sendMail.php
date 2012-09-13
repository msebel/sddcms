<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$tpl->setEmpty();

// Daten holen aus URL
$nCseID = getInt($_GET['mail']);

// Prüfen ob es die ContentSection gibt
$sSQL = "SELECT COUNT(cse_ID) FROM tbcontentsection WHERE cse_ID = $nCseID";
$nResult = $Conn->getCountResult($sSQL);
if ($nResult != 1) {
	redirect('location: /error.php?type=invalidContent');
}

// Prüfen auf Spam
$bSpam = false;
// Wenn keine Zeitsession
if (!isset($_SESSION['emailFormTime_'.$nCseID])) {
	$bSpam = true;
} else {
	// Wenn Zeitbedarf weniger als 5 Sekunden
	$nTimeNow = time();
	$nTimeThen = $_SESSION['emailFormTime_'.$nCseID];
	$nDifference = $nTimeNow - $nTimeThen;
	if ($nDifference <= 5) {
		$bSpam = true;
	}
}
// Wenn dummy Field ausgefüllt
if (strlen($_POST['dummyfield_'.$nCseID]) > 0) {
	$bSpam = true;
}

// Mailobjekt erstellen
$Mail = new phpMailer();
$sEmail = 'mailsend@sdd1.ch';
stringOps::htmlEntRev($_SESSION['emailFormSubject_'.$nCseID]);
$Mail->Subject = $_SESSION['emailFormSubject_'.$nCseID];

// Error Session initialisieren
$_SESSION['emailFormError_'.$nCseID] = array();
$bError = false;

// Empfänger Adressen holen aus alternative oder DB
if (isset($_SESSION['emailAlternative_'.$nCseID])) {
	$Mail->AddAddress($_SESSION['emailAlternative_'.$nCseID]);
	// Direkt löschen, damit nächstes Formular nicht damit gesendet wird
	unset($_SESSION['emailAlternative_'.$nCseID]);
} else {
	$sSQL = "SELECT DISTINCT ffi_Email FROM tbformfield WHERE cse_ID = $nCseID";
	$nRes = $Conn->execute($sSQL);
	while ($row = $Conn->next($nRes)) {
		if (stringOps::checkEmail($row['ffi_Email']) == true) {
			// Adresse hinzufügen
			$Mail->AddAddress($row['ffi_Email']);
		}
	}
}

// Begrüssung und Absicht
$sContent = '
'.$Res->normal(204,page::language()).'

'.$Res->normal(205,page::language()).':
***************************************************************************************

';

// Mail Felder einfüllen
$sSQL = "SELECT ffi_Required,ffi_Name,ffi_Desc,ffi_Type FROM tbformfield
WHERE ffi_Type != 'submit' AND cse_ID = $nCseID ORDER BY ffi_Sortorder";
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$sValue = $_POST[$row['ffi_Name'].'_'.$nCseID];
	$_SESSION['form_'.$nCseID][$row['ffi_Name']] = $sValue;
	tryMailReplace($sValue,$row['ffi_Desc'],$sEmail,$bSpam);
	if (strlen($sValue) > 0) {
		// Feld speichern
		$sContent .= $row['ffi_Desc'];
		// Leerzeichen anhängen
		if (strlen($row['ffi_Desc']) <= 30) {
			for ($i = strlen($row['ffi_Desc']);$i <= 30;$i++) {
				$sContent .= ' ';
			}
		}
		// Inhalt anfügen und umbruch erstellen
		$sContent .= ' '.$_POST[$row['ffi_Name'].'_'.$nCseID]."\n";
	} elseif (getInt($row['ffi_Required']) == 1 && $row['ffi_Type'] != 'captcha') {
		// Fehler speichern
		$row['ffi_Desc'] = str_replace(':','',$row['ffi_Desc']);
		array_push(
			$_SESSION['emailFormError_'.$nCseID],
			'- '.$Res->html(206,page::language()).': \''.$row['ffi_Desc'].'\''
		);
		$bError = true;
	}
	// Ist es ein Captcha Feld?
	if ($row['ffi_Type'] == 'captcha') {
		// PRüfen ob korrekt
		if ($_POST['captchaCode'] != $_SESSION['captchaCode']) {
			$bError = true;
			array_push(
				$_SESSION['emailFormError_'.$nCseID],
				'- '.$Res->html(863,page::language())
			);
		}
	}
}

// Gesendet am xx.xx.xxx um xx.xx uhr
$sSentAt = $Res->normal(993,page::language()).' '.dateOps::getTime(dateOps::EU_DATE,time()).' ';
$sSentAt.= $Res->normal(327,page::language()).' '.dateOps::getTime(dateOps::EU_CLOCK,time()).' ';
$sSentAt.= $Res->normal(581,page::language());

// Mail Footer erstellen
$sContent .= '
***************************************************************************************
'.$sSentAt.' - http://'.page::domain().$_SESSION['emailFormBack_'.$nCseID].'
';

// Mail versenden
if ($bError == false && $bSpam == false) {
	$Mail->Body = $sContent;
	$Mail->From = $sEmail;
	$Mail->FromName = $sEmail;
	// Mail an mich, wenn Debug Modus
	if (DEBUG) {
		$Mail->AddBCC('michael@sebel.ch','Administrator');
	}
	// Bestätigung als BCC schicken wenn eingestellt
	if ($_SESSION['emailFormConfirm_'.$nCseID] && $sEmail != 'mailsend@sdd1.ch') {
		$Mail->AddBCC($sEmail,$sEmail);
	}
	$bSent = $Mail->Send();
	if (DEBUG) {
		logging::debug(addslashes(
			'Mail debug info:
			Sent: '.stringOps::getVarDump($bSent).'
			Subject: '.$Mail->Subject.'
			From: '.$Mail->From.'
			Message:
			'.$Mail->Body
		));
	}
	// Erfolgsmeldung ausgeben wenn Mail verschickt
	if ($bSent == true) {
		array_push(
			$_SESSION['emailFormError_'.$nCseID],
			'- '.$Res->html(208,page::language())
		);
		// Preserving Session löschen
		unset($_SESSION['form_'.$nCseID]);
	} else {
		array_push(
			$_SESSION['emailFormError_'.$nCseID],
			'- '.$Res->html(209,page::language())
		);
	}
}

// Spam Fehler in Error Session schreiben
if ($bSpam == true) {
	array_push(
		$_SESSION['emailFormError_'.$nCseID],
		'- '.$Res->html(207,page::language())
	);
}

// Mail Adresse finden
function tryMailReplace($Value,$Field,&$Email,&$bSpam) {
	// Feldname minimieren
	$Field = strtolower($Field);
	stringOps::alphaNumOnly($Field);
	// Wenn 'email' im Formular
	if (stristr($Field,'email') && stringOps::checkEmail($Value)) {
		$Email = $Value;
		// Absenderdomäne: prüfen ob auflösbar, wenn nicht, spam.
		if ($bSpam == false && option::get('reverseDnsMailcheck') == 1) {
			$sDomain = substr($Value,strpos($Value,'@')+1);
			$ip = gethostbyname($sDomain);
			// Wenn keine IP sondern Host kommt, kann der Name nicht
			// aufgelöst werden, was nur bei Spam sein kann
			if ($ip == $sDomain && !DEBUG) $bSpam = true;
		}
	}
}
// Session schliessen und zur ursprünglichen Seite zurück
session_write_close();
redirect('location: '.$_SESSION['emailFormBack_'.$nCseID]);