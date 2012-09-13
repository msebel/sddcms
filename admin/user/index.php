<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('user');
$Module = new moduleUser();
$Module->loadObjects($Conn,$Res);

// Zugriff testen und Fehler melden
$Access->control();

// Einen Benutzer löschen
if (isset($_GET['delete'])) $Module->deleteUser();
if (isset($_GET['search'])) $Module->setSearch();

// Meldung generieren wenn vorhanden
$sMessage = '';

if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}
// Toolbar erstellen
$out = '
<form name="userIndex" method="post" action="index.php?id='.page::menuID().'&search">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(5,page::language()).'</td>
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
				<img src="/images/icons/user_add.png" alt="'.$Res->html(6,page::language()).'" title="'.$Res->html(6,page::language()).'" border="0">
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

// Benutzerliste generieren mit Paging
$sSQL = sessionConfig::get('SearchSQL','');
if (strlen($sSQL) == 0) {
	$sSQL = "SELECT usr_Alias,usr_ID,usr_Name,usr_Access FROM tbuser
	WHERE man_ID = ".page::mandant()." ORDER BY usr_Alias ASC";
}
// Paging nutzen, sql übergeben, seite und limits
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
			<h1>'.$Res->html(5,page::language()).'</h1>
		</td>
	</tr>
</table>
'.$PagingEngine->getHtml().'
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="50">&nbsp;</td>
		<td width="30">'.$Res->html(10,page::language()).'</td>
		<td width="120">'.$Res->html(9,page::language()).'</td>
		<td>'.$Res->html(11,page::language()).'</td>
	</tr>
';

$sSQL = $PagingEngine->getSQL();
// Paging SQL ausführen
$dbRes = $Conn->execute($sSQL);
$nCount = 0;
while ($row = $Conn->next($dbRes)) {
	$nCount++;
	$nAccess = (int) $row['usr_Access'];
	switch ($nAccess) {
		case 0: $sImage = 'user_suit.png'; 		$sImageDesc = $Res->html(23,page::language()); break;
		case 1: $sImage = 'user_red.png'; 		$sImageDesc = $Res->html(24,page::language()); break;
		case 2: $sImage = 'user_green.png'; 	$sImageDesc = $Res->html(25,page::language()); break;
		case 3: $sImage = 'user_orange.png';	$sImageDesc = $Res->html(26,page::language()); break;
	}
	// Benutzerbezeichnung validieren
	$sUserDesc = $row['usr_Name'];
	if (strlen($sUserDesc) == 0) {
		$sUserDesc = '< '.$Res->html(12,page::language()).' >';
	}
	$sOutUser = stringOps::chopString($row['usr_Alias'],35,true);
	$sOutDesc = stringOps::chopString($sUserDesc,30,true);
	// Zeile anhängen
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td>
			<a href="edit.php?id='.page::menuID().'&user='.$row['usr_ID'].'">
			<img src="/images/icons/bullet_wrench.png" border="0" alt="'.$Res->html(14,page::language()).'" title="'.$Res->html(14,page::language()).'"></a>
			<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$row['usr_ID'].'\',\''.addslashes($row['usr_Alias']).'\','.page::language().')">
			<img src="/images/icons/vcard_delete.png" border="0" alt="'.$Res->html(13,page::language()).'" title="'.$Res->html(13,page::language()).'"></a>&nbsp; 
		</td>
		<td align="center"><img src="/images/icons/'.$sImage.'" alt="'.$sImageDesc.'" title="'.$sImageDesc.'"></td>
		<td>'.stringOps::htmlEnt($sOutUser).'</td>
		<td>'.stringOps::htmlEnt($sOutDesc).'</td>
	</tr>
	';
}
// Fehler ausgeben, wenn keine Benutzer
if ($nCount == 0) {
	$out .= '
	<tr class="'.$TabRow->get().'">
		<td>&nbsp;</td>
		<td colspan="3">'.$Res->html(15,page::language()).' ...</td>
	</tr>
	';
} 
// Tabelleabschliessen
$out .= '</table>
</form>
<br>
<br>';

// Hilfe anzeigen
$sImageDesc1 = $Res->html(16,page::language()); 	
$sImageDesc2 = $Res->html(17,page::language()); 	
$sImageDesc3 = $Res->html(18,page::language());  	
$sImageDesc4 = $Res->html(19,page::language()); 
$sIconDesc1  = $Res->html(20,page::language()); 
$sIconDesc2  = $Res->html(21,page::language()); 

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td>&nbsp;</td>
			<td colspan="3">'.$Res->html(22,page::language()).':</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_suit.png" alt="'.$sImageDesc1.'" title="'.$sImageDesc1.'"></td>
			<td colspan="3">'.$sImageDesc1.'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_red.png" alt="'.$sImageDesc2.'" title="'.$sImageDesc2.'"></td>
			<td colspan="3">'.$sImageDesc2.'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_green.png" alt="'.$sImageDesc3.'" title="'.$sImageDesc3.'"></td>
			<td colspan="3">'.$sImageDesc3.'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/user_orange.png" alt="'.$sImageDesc4.'" title="'.$sImageDesc4.'"></td>
			<td colspan="3">'.$sImageDesc4.'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/wrench.png" alt="'.$sIconDesc2.'" title="'.$sIconDesc2.'"></td>
			<td colspan="3">'.$sIconDesc2.'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/vcard_delete.png" alt="'.$sIconDesc1.'" title="'.$sIconDesc1.'"></td>
			<td colspan="3">'.$sIconDesc1.'.</td>
		</tr>
	</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');