<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Meta->addJavascript('/modules/wiki/script/wiki.js',true);
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Parameter validieren
$nWkeID = getInt($_GET['entry']);

// Initialisieren des Wiki
$Module->initialize();
$Module->checkWriterAccess($Access);

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Objekte erstellen
$TabRow = new tabRowExtender();

// Registrierungsformular anzeigen
$out .= '
<div id="divWikiContent">
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbarWoRegister">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/images.png" id="iDiffImg" alt="'.$Res->html(973,page::language()).'" title="'.$Res->html(973,page::language()).'" border="0">
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
<br>
</div>
';

$Data = array();
$Module->loadContentVersions($Data,$nWkeID);

$out .= '
	<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
		<div style="width:25px;float:left;">&nbsp;</div>
		<div style="width:25px;float:left;">&nbsp;</div>
		<div style="width:150px;float:left;"><strong>'.$Res->html(44,page::language()).'</strong></div>
		<div style="width:70px;float:left;"><strong>'.$Res->html(972,page::language()).'</strong></div>
		<div style="width:140px;float:left;"><strong>'.$Res->html(971,page::language()).'</strong></div>
		<div style="width:170px;float:left;"><strong>'.$Res->html(970,page::language()).'</strong></div>
	</div>

	<div id="contentTable">
';

// Daten iterieren
$nCount = 0;
foreach ($Data as $row) {
	$nCount++;
	$sDate = $Module->getHumanReadableDatetime($row['con_Date']);
	$out .= '
	<div class="'.$TabRow->get().'" name="tabRow[]" style="width:100%;height:25px;padding-top:5px;">
		<div style="width:25px;float:left;">
			<input type="checkbox" value="'.$row['wke_ID'].'" onchange="javascript:addCompare(this);" name="compares">
		</div>
		<div style="width:25px;float:left;">
			<a href="edit.php?id='.page::menuID().'&entry='.$nWkeID.'&template='.$row['wke_ID'].'">
			<img src="/images/icons/arrow_join.png" border="0" alt="'.$Res->html(969,page::language()).'" title="'.$Res->html(969,page::language()).'"></a>
		</div>
		<div style="width:150px;float:left;">
			'.$row['con_Title'].'
		</div>
		<div style="width:70px;float:left;">
			v'.$row['wke_Version'].'.0
		</div>
		<div style="width:140px;float:left;">
			'.$row['imp_Alias'].'
		</div>
		<div style="width:170px;float:left;">
			'.$sDate.'
		</div>
	</div>
	';
}
	
// Wenn nichts anzuzeigen, leere Zeile
if ($nCount == 0) {
	$out .= '
	<div class="'.$TabRow->get().'">
		<div style="width:480px">'.$Res->html(158,page::language()).' ...</div>
	</div>
	';
}

// Versteckte Felder für Vergleichsauswahl
$out .= '
</div>
<input type="hidden" value="" id="comparison">
<script type="text/javascript">
	addEvent(window, "load", function() {registerDiff('.page::menuID().')}, false);
</script>
';

$out .= '
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/images.png" title="'.$Res->html(973,page::language()).'" alt="'.$Res->html(973,page::language()).'"></td>
			<td>'.$Res->html(974,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');