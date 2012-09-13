<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
require_once(BP.'/modules/faq/library.php');
$Module = new moduleFaq();
$Module->loadObjects($Conn,$Res);

$Meta->addJavascript('/scripts/system/formAdmin.js',true);

// FAQ ID holen
$nFaqID = getInt($_GET['entry']);
$Module->checkAccessRedirect($nFaqID);

// Datensatz holen und Anzeigen
$sSQL = "SELECT 
tbfaqentry.faq_Question AS QuestionContent,tbcontent.con_Content AS AnswerContent
FROM tbfaqentry INNER JOIN tbcontent ON tbcontent.con_ID = tbfaqentry.faq_Answer
WHERE tbfaqentry.faq_ID = $nFaqID";

$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	$Module->displayRow($out,$row,0);
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');