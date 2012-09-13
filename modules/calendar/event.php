<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Konfiguration laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Event laden
$nEventID = getInt($_GET['event']);

// Event laden
$sSQL = "SELECT cal_ID,kca_ID AS cal_Type,tbkalender.ele_ID,cal_Start,cal_End,cal_Title,
cal_Location,cal_City,tbelement.ele_File,cal_Text FROM tbkalender 
LEFT JOIN tbelement ON tbkalender.ele_ID = tbelement.ele_ID
WHERE (cal_Active = 1 AND mnu_ID = ".page::menuID()." AND cal_ID = $nEventID)";
$nRes = $Conn->execute($sSQL);

if (!$Event = $Conn->next($nRes)) {
	// Nichts gefunden, Fehler
	redirect('location: /error.php?type=FileNotFound');
}

// Daten zusammentragen
$tabs = new tabRowExtender();
// Daten formatieren
$sFrom = dateOps::convertDate(
	dateOps::SQL_DATETIME,
	dateOps::EU_CLOCK,
	$Event['cal_Start']
);
// Datum konvertieren
$sDate = dateOps::convertDate(
	dateOps::SQL_DATETIME,
	dateOps::EU_DATE,
	$Event['cal_Start']
);
$sTo = '';
if ($Event['cal_End'] !== NULL) {
	$sTo = dateOps::convertDate(
		dateOps::SQL_DATETIME,
		dateOps::EU_CLOCK,
		$Event['cal_End']
	);
}
// Flyer HTML erstellen
$sFlyerHtml = '&nbsp;';
if ($Event['ele_ID'] > 0 && strlen($Event['ele_File']) > 0) {
	$sLink = '/page/'.page::id().'/element/'.$Event['ele_ID'].'/'.$Event['ele_File'];
	// Schauen ob Lightbox möglich
	$sRel = '';
	$sExt = stringOps::getExtension($Event['ele_File']);
	switch (strtolower($sExt)) {
		case '.jpg':
		case '.png':
		case '.gif':
			$sRel = ' rel="lightbox"';
			break;
	}
	$sFlyerHtml = '<a href="'.$sLink.'"'.$sRel.' target="_blank">Flyer</a>';
}
// Daten ausgeben
if (strlen($Event['cal_Text']) == 0) {
	$sClass = ' class="'.$tabs->getLine().'"';
}
$sLocation = '';
if (strlen($Event['cal_City']) > 0) {
	$sLocation .= $Event['cal_City'];
}
if (strlen($Event['cal_Location']) > 0) {
	if (strlen($sLocation) > 0) $sLocation .= ', ';
	$sLocation .= $Event['cal_Location'];
}

stringOps::htmlViewEnt($Event['cal_Title']);

// Wenn wir hier her kommen, ist in der Row was drin
$out .= '
<h1>'.$Event['cal_Title'].' / '.$sDate.'</h1>
<p>
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr class="tabRowHead">
			<td>'.$Res->html(728,page::language()).'</td>
			<td>'.$Res->html(729,page::language()).'</td>
			<td>'.$Res->html(730,page::language()).'</td>
			<td>'.$Res->html(579,page::language()).'</td>
			<td>&nbsp;</td>
		</tr>
		<tr'.$sClass.'>
			<td width="50">'.$sFrom.'</td>
			<td width="50">'.$sTo.'</td>
			<td><a class="cMoreLink" href="event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$Event['cal_Title'].'</a></td>
			<td>'.$sLocation.'</td>
			<td>'.$sFlyerHtml.'</td>
		</tr>
		<tr class="'.$tabs->getLine().'">
			<td colspan="5">'.$Event['cal_Text'].'</td>
		</tr>
	</table>
</p>
<br>
';

// Wenn Anmeldung aktiviert, Formular anzeigen
if ($Config['registerAllowed']['Value'] == 1) {
	$out .= '<br><h2>'.$Res->html(4,page::language()).'</h2>';
	$sSQL = "SELECT cse_ID FROM tbkalender_contentsection WHERE cal_ID = $nEventID";
	$nCseID = getInt($Conn->getFirstResult($sSQL));
	contentView::getElement($nCseID,0,3,$out,$Conn);
	// Betrefftext
	$sSubject = $Res->normal(992,page::language()).' - '.page::domain();
	$sSubject = str_replace('{0}',$Event['cal_Title'].' / '.$sDate,$sSubject);
	$_SESSION['emailFormConfirm_'.$nCseID] = true;
	$_SESSION['emailFormSubject_'.$nCseID] = $sSubject;
	$_SESSION['emailFormBack_'.$nCseID] = '/modules/calendar/event.php?id='.page::menuID().'&event='.$nEventID;
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
?>