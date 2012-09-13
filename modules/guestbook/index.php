<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleGuestbook();
$Module->loadObjects($Conn,$Res);

// Konfiguration initialisieren
$GBConfig = array();
pageConfig::get(page::menuID(),$Conn,$GBConfig);

$GBData = array();
$sPageHtml = $Module->LoadData($GBData,$GBConfig);

// Einleitungstext
if (strlen($GBConfig['htmlCode']['Value']) > 0) {
	stringOps::htmlViewEnt($GBConfig['htmlCode']['Value']);
	$out .= '<div class="divEntryText">'.$GBConfig['htmlCode']['Value'].'</div>';
}

// Session Flag setzen, wenn in new nicht vorhanden, Eintrag nicht möglich
$_SESSION['gb_'.page::menuID()] = true;
// Neuer Eintrag, Fehlermeldung generieren und anzeigen
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
$out .= '
<form action="new.php?id='.page::menuID().'" method="post">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			'.$sMessage.'&nbsp;
		</td>
		<td width="150" style="text-align:right;">
			<input class="cButton" type="submit" name="cmdEntry" value="'.$Res->html(349,page::language()).'">
		</td>
	</tr>
</table>
</form>
';

// Einträge inkl. Paging ausgeben
$out .= $sPageHtml;
$Module->showCommentsView($GBData,$out);
$out .= $sPageHtml;
// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');