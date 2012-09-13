<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('group');
$Module = new moduleGroup();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Einen Benutzer löschen
if (isset($_GET['delete'])) $Module->deleteGroup();
if (isset($_GET['search'])) $Module->setSearch();

// Meldung generieren wenn vorhanden
$sMessage = '';

if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="groupIndex" method="post" action="index.php?id='.page::menuID().'&search">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(87,page::language()).'</td>
		<td class="cNav">&nbsp;</td>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbar">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<a href="add.php?id='.page::menuID().'">
				<img src="/images/icons/group_add.png" alt="'.$Res->html(88,page::language()).'" title="'.$Res->html(88,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:showHelp()">
				<img src="/images/icons/help.png" alt="'.$Res->html(8,page::language()).'" title="'.$Res->html(8,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
';

// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

// Gruppen generieren mit Paging

$sSQL = sessionConfig::get('SearchSQL','');
if (strlen($sSQL) == 0) {
	$sSQL = "SELECT tbusergroup.ugr_ID,tbusergroup.ugr_Desc,tbmenu.mnu_Name
	FROM tbusergroup LEFT JOIN tbmenu ON tbusergroup.ugr_Start = tbmenu.mnu_ID
	WHERE tbusergroup.man_ID = ".page::mandant()." ORDER BY tbusergroup.ugr_ID ASC";
}
// Paging nutzen, SQL übergeben, seite und limits
$PagingEngine = new paging($Conn,'index.php?id='.page::menuID());
$PagingEngine->start($sSQL,10,false);

// Tabellenkopf
$out.= '
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="4">
			<div style="float:right;width:200px;">
				<input type="text" style="width:100px;" maxlength="50" name="search"> 
				<input type="submit" value="'.$Res->html(497,page::language()).'" class="cButton">
			</div>
			<h1>'.$Res->html(87,page::language()).'</h1>
		</td>
	</tr>
</table>
'.$PagingEngine->getHtml().'
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="50">&nbsp;</td>
		<td width="120">'.$Res->html(85,page::language()).'</td>
		<td>'.$Res->html(41,page::language()).'</td>
	</tr>
';

$sSQL = $PagingEngine->getSQL();
// Paging SQL ausführen
$dbRes = $Conn->execute($sSQL);
$nCount = 0;
while ($row = $Conn->next($dbRes)) {
	$nCount++;
	// Startseite holen und choppen und gleich Punkte anhängen
	$sStart = '< '.$Res->html(89,page::language()).' >';
	if ($row['mnu_Name'] != NULL) {
		$sStart = stringOps::chopString($row['mnu_Name'],30,true);
	}
	// Zeile anhängen
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td>
			<a href="edit.php?id='.page::menuID().'&group='.$row['ugr_ID'].'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(91,page::language()).'" title="'.$Res->html(91,page::language()).'"></a>
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['ugr_ID'].'\',\''.addslashes($row['ugr_Desc']).'\','.page::language().')">
			<img src="/images/icons/group_delete.png" border="0" alt="'.$Res->html(90,page::language()).'" title="'.$Res->html(90,page::language()).'"></a>&nbsp;
		</td>
		<td>'.stringOps::htmlEnt($row['ugr_Desc']).'</td>
		<td>'.stringOps::htmlEnt($sStart).'</td>
	</tr>
	';
}
// Fehler ausgeben, wenn keine Benutzer
if ($nCount == 0) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td>&nbsp;</td>
		<td colspan="3">'.$Res->html(86,page::language()).' ...</td>
	</tr>
	';
} 
// Tabelleabschliessen
$out .= '</table>
</form>
<br>
<br>';

// Hilfe anzeigen

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="50">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).':</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" align="center"><img src="/images/icons/wrench.png" alt="'.$Res->html(91,page::language()).'" title="'.$Res->html(91,page::language()).'"></td>
			<td valign="top">'.$Res->html(93,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td valign="top" align="center"><img src="/images/icons/group_delete.png" alt="'.$Res->html(90,page::language()).'" title="'.$Res->html(90,page::language()).'"></td>
			<td valign="top">'.$Res->html(92,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');