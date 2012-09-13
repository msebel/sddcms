<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// User auslesen
$nUserID = getInt($_GET['user']);
$sSQL = "SELECT usr_Name,usr_Alias FROM tbuser
WHERE usr_ID = $nUserID AND man_ID = ".page::mandant();
$nRes = $Conn->Execute($sSQL);
// Rückgabe String vorbereiten
$sUser = 'Standard web user';
while ($row = $Conn->next($nRes)) {
	$sUser = 'User #'.$nUserID.', '.$row['usr_Alias'].' ('.$row['usr_Name'].')';
}
// Ergebnis choppen und ausgeben
$sUser = stringOps::chopString($sUser,18,true);
echo $sUser;