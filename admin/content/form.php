<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::load('form');
$Module = new moduleForm();
$Module->loadObjects($Conn,$Res);

$Meta->addJavascript('/scripts/system/formAdmin.js',true);
require_once(BP.'/library/class/mediaManager/formCode.php');

// Zugriff testen
$Module->checkContentAccess();
// Speichern
if(isset($_GET['new']))  $Module->addFormField();
if(isset($_GET['save'])) $Module->saveFormFields();

// Toolbar erstellen
$out = '
<form name="formEdit" method="post" action="form.php?id='.page::menuID().'&content='.$_GET['content'].'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="200"><a href="index.php?id='.page::menuID().'">'.$Res->html(153,page::language()).'</a></td>
		<td class="cNavSelected" width="200">'.$Res->html(154,page::language()).'</td>
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
				<a href="#" onClick="document.formEdit.submit()">
				<img src="/images/icons/disk.png" alt="'.$Res->html(36,page::language()).'" title="'.$Res->html(36,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
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
				<a href="#" id="addNewFormLink" onClick="addNewForm(event)">
				<img src="/images/icons/table_add.png" alt="'.$Res->html(211,page::language()).'" title="'.$Res->html(211,page::language()).'" border="0">
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
				&nbsp;'.$Module->checkErrorSession($Res).'
			</div>
		</td>
	</tr>
</table>
<br>
';

// Div Popup für Element editierung
$out .= '
<div id="editDialog" class="popUpDiv" style="display:none;position:relative;margin-bottom:30px;top:10px;">
	<h1>'.$Res->html(227,page::language()).'</h1>
	<input type="hidden" name="editingIndex" value="">
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td width="150">'.$Res->html(228,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;float:left;" name="fieldDesc" value="">
				<div id="editDialogSelectbox" style="display:none;float:left;">
					&nbsp;<a href="#" onclick="OpenSelectedDropdown(\'/admin/content/select.php?id='.page::menuID().'&content='.getInt($_GET['content']).'&field=\');">
					<img src="/images/icons/page_edit.png" border="0" alt="'.$Res->html(763,page::language()).'" title="'.$Res->html(763,page::language()).'"></a>
				</div>
			</td>
		</tr>
		<tr>
			<td>'.$Res->html(221,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="fieldValue" value="">
			</td>
		</tr>
		<tr>
			<td>'.$Res->html(229,page::language()).':</td>
			<td>
				<input type="radio" name="fieldRequired" value="0"> '.$Res->html(230,page::language()).' &nbsp;&nbsp;&nbsp;
				<input type="radio" name="fieldRequired" value="1" checked> '.$Res->html(231,page::language()).'
			</td>
		</tr>
		<tr>
			<td>'.$Res->html(222,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="fieldClass" value="">
			</td>
		</tr>
		<tr>
			<td>'.$Res->html(445,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="fieldName" value="">
			</td>
		</tr>
		<tr>
			<td>'.$Res->html(232,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" name="fieldWidth" value="">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="newFormType" value="0">
				<input class="cButton" type="button" name="cmdEditForm" value="'.$Res->html(233,page::language()).'" onClick="editDialogSave()"> 
				<input class="cButton" type="button" name="cmdCancel" value="'.$Res->html(234,page::language()).'" onClick="editDialogHide()">
			</td>
		</tr>
	</table>
</div>
';

// Formular Inhalte anzeigen
$out .= '
<div id="formArea">
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="4"><h1>'.$Res->html(235,page::language()).'</h1><br></td>
	</tr>
	<tr>
		<td colspan="2">'.$Res->html(219,page::language()).':</td>
		<td colspan="2">
			<input type="text" style="width:250px;" maxlength="255" name="email" value="'.$Module->getEmail().'">
		</td>
	</tr>
	<tr>
		<td colspan="4"><hr><br></td>
	</tr>
</table>
	<div id="formTable">
';
// Formularfelder ausgeben
$nCseID = getInt($_GET['content']);
$nMenuID = page::menuID();
$sSQL = "SELECT ffi_ID,ffi_Width,ffi_Required,ffi_Name,
ffi_Desc,ffi_Type,ffi_Class,ffi_Value,ffi_Sortorder,ffi_Options FROM tbformfield 
WHERE cse_ID = $nCseID AND mnu_ID = $nMenuID ORDER BY ffi_Sortorder ASC";
// SQL für alle Felder abfeuern und loopen
$nRes = $Conn->execute($sSQL); $nCount = 0;
while ($row = $Conn->next($nRes)) {
	
	$out .= '
	<div name="tabRow[]" id="tabRow_'.$nCount.'" style="width:100%;float:left;padding:3px;">
		<div style="width:50px;float:left;vertical-align:top;">
			<a name="editDialogLink" href="#" onClick="showEditDialog('.$nCount.')" title="'.$Res->html(212,page::language()).'">
			<img src="/images/icons/table_edit.png" alt="'.$Res->html(212,page::language()).'" title="'.$Res->html(212,page::language()).'" border="0"></a> 
			<a name="deleteLink" href="#" onClick="deleteFormfield('.$nCount.')" title="'.$Res->html(213,page::language()).'">
			<img src="/images/icons/table_delete.png" alt="'.$Res->html(213,page::language()).'" title="'.$Res->html(213,page::language()).'" border="0"></a>
			
			<input type="hidden" name="ffi_ID[]" value="'.$row['ffi_ID'].'">
			<input type="hidden" name="ffi_Width[]" value="'.$row['ffi_Width'].'">
			<input type="hidden" name="ffi_Required[]" value="'.$row['ffi_Required'].'">
			<input type="hidden" name="ffi_Name[]" value="'.$row['ffi_Name'].'">
			<input type="hidden" name="ffi_Desc[]" value="'.$row['ffi_Desc'].'">
			<input type="hidden" name="ffi_Type[]" value="'.$row['ffi_Type'].'">
			<input type="hidden" name="ffi_Class[]" value="'.$row['ffi_Class'].'">
			<input type="hidden" name="ffi_Value[]" value="'.$row['ffi_Value'].'">
			<input type="hidden" name="ffi_Sortorder[]" value="'.$row['ffi_Sortorder'].'">
			<input type="hidden" name="ffi_Options[]" value="'.$row['ffi_Options'].'">
			<input type="hidden" name="ffi_Deleted[]" value="0">
			<input type="hidden" name="ffi_Changed[]" value="0">
		</div>
		<div style="float:left;width:140px;vertical-align:top;">
			<div id="tabCellDesc_'.$nCount.'">
				&nbsp;
			</div>
		</div>
		<div style="float:left;width:320px;vertical-align:top;">
			<div id="tabCellForm_'.$nCount.'">
				&nbsp;
			</div>
		</div>
		<div style="float:left;text-align:center;width:20px;vertical-align:top;">
			<a href="#" id="moveLink_'.$nCount.'" onMouseover="SetPointer(this.id,\'move\')" onMouseout="SetPointer(this.id,\'default\')" title="'.$Res->html(214,page::language()).'">
			<img src="/images/icons/arrow_in.png" border="0" alt="'.$Res->html(214,page::language()).'" title="'.$Res->html(214,page::language()).'"></a>
		</div>
	</div>
	';
	$nCount++;
}
// Tabellenende ausgeben
$out .= '
	</div>
</div>
<script type="text/javascript">
	Sortable.create("formTable", { tag:"div", containment:["formTable"],onUpdate:updateIndizes});
	// Alle Formulare laden
	addEvent(window,\'load\',loadUpdate,false);
</script>
<input type="hidden" value="'.$nCount.'" id="CountTabRows">
</form>
<br>
<br>
';
// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

// Div Popup für neues Element
$out .= '
<div id="newElementPopup" class="popUpDiv" style="display:none;">
<form name="formNew" method="post" action="form.php?id='.page::menuID().'&content='.$_GET['content'].'&new">	
	<h1>'.$Res->html(236,page::language()).'</h1>
	<br>
	<table width="350" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td width="80">'.$Res->html(228,page::language()).':</td>
			<td>
				<input type="text" style="width:200px;" id="newFormName" name="newFormName">
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2" style="border-bottom:1px solid #eee;">'.$Res->html(220,page::language()).':</td>
		</tr>
		<tr onClick="selectFormType(this,0)" onMouseover="formTypeOver(this,0)" onMouseout="formTypeOut(this,0)" id="newTableRow0">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<input type="text" style="width:220px;" value="'.$Res->html(237,page::language()).'">
			</td>
		</tr>
		<tr onClick="selectFormType(this,1)" onMouseover="formTypeOver(this,1)" onMouseout="formTypeOut(this,1)" id="newTableRow1">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<textarea style="width:220px;height;50px;">'.$Res->html(238,page::language()).'</textarea>
			</td>
		</tr>
		<tr onClick="selectFormType(this,6)" onMouseover="formTypeOver(this,6)" onMouseout="formTypeOut(this,6)" id="newTableRow6">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<select style="width:180px;">
					<option>'.$Res->html(762,page::language()).'</option>
				</select>
			</td>
		</tr>
		<tr onClick="selectFormType(this,2)" onMouseover="formTypeOver(this,2)" onMouseout="formTypeOut(this,2)" id="newTableRow2">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<input type="radio" value="1"> 
				'.$Res->html(239,page::language()).'
			</td>
		</tr>
		<tr onClick="selectFormType(this,3)" onMouseover="formTypeOver(this,3)" onMouseout="formTypeOut(this,3)" id="newTableRow3">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<input type="checkbox" value="1" name="exampleCheck1">
				'.$Res->html(240,page::language()).'
			</td>
		</tr>
		<tr onClick="selectFormType(this,4)" onMouseover="formTypeOver(this,4)" onMouseout="formTypeOut(this,4)" id="newTableRow4">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<input type="button" value="'.$Res->html(241,page::language()).'">
			</td>
		</tr>
		<tr onClick="selectFormType(this,5)" onMouseover="formTypeOver(this,5)" onMouseout="formTypeOut(this,5)" id="newTableRow5">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				'.$Res->html(242,page::language()).'
			</td>
		</tr>
		<tr onClick="selectFormType(this,7)" onMouseover="formTypeOver(this,7)" onMouseout="formTypeOut(this,7)" id="newTableRow7">
			<td style="border-bottom:1px solid #eee;">
				&nbsp;
			</td>
			<td style="border-bottom:1px solid #eee;">
				<div style="float:left;margin-right:10px;">
					<img src="/scripts/captcha/code.php">
				</div>
				'.$Res->html(862,page::language()).'
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="hidden" name="newFormType" value="0">
				<input class="cButton" type="submit" name="cmdNewForm" value="'.$Res->html(233,page::language()).'"> 
				<input class="cButton" type="button" name="cmdCancel" value="'.$Res->html(234,page::language()).'" onClick="addNewForm(event)">
			</td>
		</tr>
	</table>
	</form>
</div>
';

// Hilfe anzeigen
$out .= '
<br>
<div id="helpDialog" style="display:none">
	<br>
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0">
		<tr class="tabRowHead">
			<td width="25">&nbsp;</td>
			<td>'.$Res->html(22,page::language()).'</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/table_add.png" title="'.$Res->html(211,page::language()).'" alt="'.$Res->html(211,page::language()).'"></td>
			<td>'.$Res->html(216,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/table_edit.png" title="'.$Res->html(212,page::language()).'" alt="'.$Res->html(212,page::language()).'"></td>
			<td>'.$Res->html(217,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/table_delete.png" title="'.$Res->html(213,page::language()).'" alt="'.$Res->html(213,page::language()).'"></td>
			<td>'.$Res->html(218,page::language()).'.</td>
		</tr>
		<tr class="'.$TabRow->get().'">
			<td><img src="/images/icons/arrow_in.png" title="'.$Res->html(214,page::language()).'" alt="'.$Res->html(214,page::language()).'"></td>
			<td>'.$Res->html(215,page::language()).'.</td>
		</tr>
	</table>
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="120">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(219,page::language()).'</em></td>
		<td valign="top">'.$Res->html(223,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(220,page::language()).'</em></td>
		<td valign="top">'.$Res->html(224,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(221,page::language()).'</em></td>
		<td valign="top">'.$Res->html(225,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="120" valign="top"><em>'.$Res->html(222,page::language()).'</em></td>
		<td valign="top">'.$Res->html(226,page::language()).'.</td>
	</tr>
	</table>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');