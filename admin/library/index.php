<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$Meta->addJavascript('/scripts/system/filelibrary.js',true);
// Verwaltungsklasse laden
library::loadRelative('library');
$Module = new moduleFilelibrary();
$Module->loadObjects($Conn,$Res);
// Zugriff auf Bibliothek prüfen und initialisieren
$Module->controlAccess($Access);
$Module->initialize();

// Popup, wenn modus view ist
if ($Module->Options->get('mode') == 'view') {
	$tpl->setPopup();
}

// Verschiedenste Eingaben behandeln
if (isset($_GET['newfolder'])) $Module->createFolder();
if (isset($_GET['rename'])) $Module->renameFile();
if (isset($_GET['upload'])) $Module->uploadFile();
if (isset($_GET['delete'])) $Module->deleteFile();

// Meldung generieren wenn vorhanden
$sMessage = '';
if ($Module->hasErrorSession() == true) {
	$sMessage = $Module->showErrorSession();
	$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
}

// Toolbar und Register
$out .= '
<form name="fileExplorer" method="post" action="index.php?id='.page::menuID().'">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNavSelected" width="150">'.$Res->html(687,page::language()).'</td>
		<td class="cNav">&nbsp;</td>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cToolbar">
			<div class="cToolbarItem">
				&nbsp;
			</div>
			'.$Module->getModeButtons().'
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<img id="windowUpload" src="/images/icons/folder_table.png" alt="'.$Res->html(673,page::language()).'" title="'.$Res->html(673,page::language()).'">
			</div>
			<div class="cToolbarItem">
				<img id="windowNewFolder" src="/images/icons/folder_add.png" alt="'.$Res->html(674,page::language()).'" title="'.$Res->html(674,page::language()).'">
			</div>
			<div class="cToolbarItem">
				<img id="windowRename" src="/images/icons/folder_edit.png" alt="'.$Res->html(675,page::language()).'" title="'.$Res->html(675,page::language()).'">
			</div>
			<div class="cToolbarItem">
				<img id="windowDelete" src="/images/icons/folder_delete.png" alt="'.$Res->html(676,page::language()).'" title="'.$Res->html(676,page::language()).'">
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:copySelectedFile()">
				<img id="icoCopy" src="/images/icons/page_white_copy_disabled.png" alt="'.$Res->html(677,page::language()).'" title="'.$Res->html(677,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:cutSelectedFile()">
				<img id="icoCut" src="/images/icons/cut_disabled.png" alt="'.$Res->html(678,page::language()).'" title="'.$Res->html(678,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:pasteFile()">
				<img id="icoPaste" src="/images/icons/paste_plain'.$Module->checkDisabledIcon('paste').'.png" alt="'.$Res->html(679,page::language()).'" title="'.$Res->html(679,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<img src="/images/icons/toolbar-line.gif" alt="|">
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:moveToRootFolder()">
				<img src="/images/icons/resultset_first'.$Module->checkDisabledIcon('root').'.png" alt="'.$Res->html(680,page::language()).'" title="'.$Res->html(680,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:moveUp()">
				<img src="/images/icons/resultset_previous'.$Module->checkDisabledIcon('back').'.png" alt="'.$Res->html(681,page::language()).'" title="'.$Res->html(681,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:moveNext()">
				<img src="/images/icons/resultset_next_disabled.png" alt="'.$Res->html(688,page::language()).'" title="'.$Res->html(688,page::language()).'" border="0" id="icoNext"></a>
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
';

// Darstellung der Bibliothek (Einleitung)
$out .= '
<div style="width:100%;padding-bottom:5px;overflow:auto;">
	Aktueller Ordner: /'.$Module->Options->get('currentFolder').'
	<input type="hidden" id="currentFolder" value="'.$Module->Options->get('currentFolder').'">
	<input type="hidden" id="currentRoot" value="/page/'.page::id().'/library/">
</div>
<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
	<div style="width:25px;float:left;">&nbsp;</div>
	<div style="width:260px;float:left;"><strong>'.$Res->html(682,page::language()).'</strong></div>
	<div style="width:120px;float:left;"><strong>'.$Res->html(686,page::language()).'</strong></div>
	<div style="width:100px;float:left;"><strong>'.$Res->html(683,page::language()).'</strong></div>
</div>

<div id="contentTable">';
$nCount = 0;
// Ordner, nach Alphabet
foreach ($Module->Directories->Data as $Directory) {
	$nCount++;
	$out .= '
	<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;"
		onmouseover="hoverFileIn('.$nCount.')" onmouseout="hoverFileOut('.$nCount.')"
		onclick="selectFile('.$nCount.')" ondblclick="changeDirectory(\''.$Directory['Name'].'\')" 
		id="file_'.$nCount.'">
		<div id="file_'.$nCount.'" style="width:25px;float:left;">
			<img src="/images/icons/folder.png">
			<input type="hidden" name="filename_'.$nCount.'" value="'.$Directory['Name'].'">
			<input type="hidden" name="filetype_'.$nCount.'" value="folder">
		</div>
		<div style="width:260px;float:left;white-space:nowrap;overflow:hidden;">'.$Directory['Name'].'</div>
		<div style="width:120px;float:left;white-space:nowrap;overflow:hidden;">'.$Directory['Date'].'</div>
		<div style="width:100px;float:left;white-space:nowrap;overflow:hidden;">-</div>
	</div>';
}
// Files nach Alphabet
foreach ($Module->Files->Data as $File) {
	$nCount++;
	$sOnDblClick = '';
	// Doppelklick wählt Datei aus, wenn view (editor)
	if ($Module->Options->get('mode') == 'view') {
		$sOnDblClick = 'onDblClick="javascript:selectFile('.$nCount.');saveSubmit();"';
	}
	$out .= '
	<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;"
		onmouseover="hoverFileIn('.$nCount.')" onmouseout="hoverFileOut('.$nCount.')"
		onclick="selectFile('.$nCount.')" '.$sOnDblClick.' id="file_'.$nCount.'">
		<div style="width:25px;float:left;">
			<img src="/images/icons/page_white_text.png">
			<input type="hidden" name="filename_'.$nCount.'" value="'.$File['Name'].'">
			<input type="hidden" name="filetype_'.$nCount.'" value="file">
		</div>
		<div style="width:260px;float:left;white-space:nowrap;overflow:hidden;">'.$File['Name'].'</div>
		<div style="width:120px;float:left;white-space:nowrap;overflow:hidden;">'.$File['Date'].'</div>
		<div style="width:100px;float:left;white-space:nowrap;overflow:hidden;">'.$File['Size'].'</div>
	</div>';
}
// Abschliessen
if ($nCount == 0) {
	$out .= '
	<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;">
		<div style="width:25px;float:left;">&nbsp;</div>
		<div style="width:485px;float:left;">
			'.$Res->html(684,page::language()).'
		</div>
	</div>';
}
// Abschluss und Steuerdaten
$out .= '</div>
<script type="text/javascript">
	var selectedFileID = "";
	var selectedFile = "";
	var selectedType = "";
	var countFiles = '.$nCount.';
	var url = "id='.page::menuID().'";
	var relative = "'.$Module->Options->get('relativeFolder').'";
	var icoCopy,icoCut,icoNext,icoPaste;
	// Event um Icons zu registrieren
	addEvent(window,"load",evtRegisterIcons,false);
</script>
</form>';

// Controls für flying windows erstellen
$window = htmlControl::window();

// Upload einer Date
$HTML = '
<form action="index.php?id='.page::menuID().'&upload" method="post" enctype="multipart/form-data" name="uploadFileForm">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="3">
			<h1>'.$Res->html(696,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="130">
			'.$Res->html(697,page::language()).':
		</td>
		<td>
			<input onChange="changeOptions()" type="file" name="uploadedFile">
		</td>
		<td width="20">
			<a href="#" onClick="startProgressBar()">
			<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
		</td>
	</tr>
	<tr>
		<td width="130">
			&nbsp;
		</td>
		<td colspan="2">
			<div id="fileOptionZip" style="display:none;">
				<input type="checkbox" value="1" name="unpackZip"> '.$Res->html(382,page::language()).'
			</div>
			<div id="progressMessage" style="display:none;height:15px;width:80px;float:left;">
				'.$Res->html(695,page::language()).'
			</div>
			<div id="progressBar" style="display:none;height:10px;float:left;border:1px solid #ccc;margin-top:2px;background-color:#879DFF;"></div>
			<div id="progressBarLeft" style="display:none;height:10px;float:left;border:1px solid #ccc;margin-top:2px;background-color:transparent;"></div>
		</td>
	</tr>
</table>
</form>
';
$window->add('windowUpload',$HTML,'',520,140);
$out .= $window->get('windowUpload');

// Erstellen eines neuen Ordners
$HTML = '
<form action="index.php?id='.page::menuID().'&newfolder" method="post" name="createFolderForm">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="3">
			<h1>'.$Res->html(689,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="130">
			'.$Res->html(690,page::language()).':
		</td>
		<td width="200">
			<input style="width:195px;" type="text" maxlength="50" name="newFolderName">
		</td>
		<td width="20">
			<a href="#" onClick="javascript:document.createFolderForm.submit()">
			<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
		</td>
	</tr>
</table>
</form>
';
$window->add('windowNewFolder',$HTML,'',420,120);
$out .= $window->get('windowNewFolder');

// Einen Ordner / eine Datei umbenennen
$HTML = '
<form action="index.php?id='.page::menuID().'&rename" method="post" name="renameFileForm">
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="3">
			<h1>'.$Res->html(691,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td width="130">
			'.$Res->html(692,page::language()).':
		</td>
		<td width="200">
			<input style="width:195px;" type="text" maxlength="50" name="renamedFile" id="renamedFile" value="">
			<input type="hidden" maxlength="50" name="originalFile" id="originalFile" value="">
		</td>
		<td width="20">
			<a href="#" onClick="javascript:document.renameFileForm.submit()">
			<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0"></a>
		</td>
	</tr>
</table>
</form>
';
$window->add('windowRename',$HTML,'',420,120,'updateRenameForms()');
$out .= $window->get('windowRename');

// Einen Ordner / eine Datei löschen
$HTML = '
<form action="index.php?id='.page::menuID().'&delete" method="post" name="deleteForm">
<table width="400" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td>
			<h1>'.$Res->html(698,page::language()).'</h1><br>
		</td>
	</tr>
	<tr>
		<td>
			<div id="deleteInformation" style="height:40px;width:100%;">
				<img src="/images/media/progressbar.gif" height="10">&nbsp;
				'.$Res->html(699,page::language()).' ...
			</div>
		</td>
	</tr>
		<td align="right">
		<div id="deleteButtons" style="none">
			<input type="hidden" id="deletedFile" name="deletedFile" value="">
			<input type="button" class="cButton" onClick="javascript:document.deleteForm.submit();" value="'.$Res->html(231,page::language()).'">
			<input type="button" class="cButton" onClick="javascript:evtCloseWindow();" value="'.$Res->html(234,page::language()).'">
		</div>
		</td>
	</tr>
</table>
</form>
';
$window->add('windowDelete',$HTML,'',420,155,'updateDeleteForms()');
$out .= $window->get('windowDelete');

// Alle Buttons versteckt ausgeben, preload
$out .= '
<div style="display:none;position:absolute;top:0px;left:0px;">
	<img src="/images/icons/page_white_copy.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/cut.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/paste_plain.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/resultset_first.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/resultset_previous.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/page_white_copy_disabled.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/cut_disabled.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/paste_plain_disabled.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/resultset_first_disabled.png" style="width:0px;height:0px;display:none;">
	<img src="/images/icons/resultset_previous_disabled.png" style="width:0px;height:0px;display:none;">
</div>';

// Help Dialog
$TabRow = new tabRowExtender();
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
			<td><img src="/images/icons/folder_table.png" title="'.$Res->html(673,page::language()).'" alt="'.$Res->html(673,page::language()).'"></td>
			<td>'.$Res->html(713,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/folder_add.png" title="'.$Res->html(674,page::language()).'" alt="'.$Res->html(674,page::language()).'"></td>
			<td>'.$Res->html(714,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/folder_edit.png" title="'.$Res->html(675,page::language()).'" alt="'.$Res->html(675,page::language()).'"></td>
			<td>'.$Res->html(715,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/folder_delete.png" title="'.$Res->html(676,page::language()).'" alt="'.$Res->html(676,page::language()).'"></td>
			<td>'.$Res->html(716,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/page_white_copy.png" title="'.$Res->html(677,page::language()).'" alt="'.$Res->html(677,page::language()).'"></td>
			<td>'.$Res->html(717,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/cut.png" title="'.$Res->html(678,page::language()).'" alt="'.$Res->html(678,page::language()).'"></td>
			<td>'.$Res->html(718,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/paste_plain.png" title="'.$Res->html(679,page::language()).'" alt="'.$Res->html(679,page::language()).'"></td>
			<td>'.$Res->html(719,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/resultset_first.png" title="'.$Res->html(680,page::language()).'" alt="'.$Res->html(680,page::language()).'"></td>
			<td>'.$Res->html(720,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/resultset_previous.png" title="'.$Res->html(681,page::language()).'" alt="'.$Res->html(681,page::language()).'"></td>
			<td>'.$Res->html(721,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/resultset_next.png" title="'.$Res->html(688,page::language()).'" alt="'.$Res->html(688,page::language()).'"></td>
			<td>'.$Res->html(722,page::language()).'.</td>
		</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');