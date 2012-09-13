<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Modulbezogene Funktionsklasse
library::load('menu');
$Module = new moduleMenu();
$Module->loadObjects($Conn,$Res);
// Javascripts einbinden
$Meta->addJavascript('/scripts/jsLib/adminMenu.js',true);
$Meta->addJavascript('/admin/menu/menupath_blur.js',true);

// Zugriff testen und Fehler melden
$Access->control();

// Neuerungen Speichern
if (isset($_GET['menu'])) $Module->checkEditable();
if (isset($_GET['save'])) $Module->saveMenuProperties();
if (isset($_GET['upload'])) $Module->uploadMenuImages();
if (isset($_GET['delete'])) $Module->deleteMenuImages();

$nMenuID = getInt($_GET['menu']);
$sMenuData = $Module->loadData($nMenuID);

// Toolbar erstellen
$out = '
<form name="menuEdit" method="post" action="menu.php?id='.page::menuID().'&menu='.$_GET['menu'].'&save">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(108,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(109,page::language()).'</td>
		<td class="cNav" width="150"><a href="access.php?id='.page::menuID().'&menu='.$_GET['menu'].'">'.$Res->html(110,page::language()).'</a></td>
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
				<a href="#" onClick="document.menuEdit.submit()">
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
';

// Menuformular anzeigen
$out .= '
<br>
<table border="0" cellspacing="0" cellpadding="3" width="100%">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(109,page::language()).' - '.stringOps::chopString($sMenuData['mnu_Name'],30,true).'</h1>
			<br>
			'.$Module->showErrorSession().'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(124,page::language()).':
		</td>
		<td>
			<input type="text" name="name" id="mnuname" maxlength="255" style="width:250px;" value="'.$sMenuData['mnu_Name'].'">
			<span class="red">*</span>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(125,page::language()).':
		</td>
		<td>
			<select name="type" style="width:300px;" onChange="checkLinkOption('.menuTypes::LINK_INTERNAL.','.menuTypes::LINK_EXTERNAL.');">
				'.$Module->getGlobalTypes($sMenuData['typ_ID']).'
				'.$Module->getCustomTypes($sMenuData['typ_ID']).'
				<optgroup label="----------------">
					<option value="'.menuTypes::LINK_INTERNAL.'"'.checkDropDown(menuTypes::LINK_INTERNAL,$sMenuData['typ_ID']).'>Interner Link</option>
					<option value="'.menuTypes::LINK_EXTERNAL.'"'.checkDropDown(menuTypes::LINK_EXTERNAL,$sMenuData['typ_ID']).'>Externer Link</option>
				</optgroup>
			</select>
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(404,page::language()).':</td>
		<td>
			<select name="teaser" class="cTextfield" style="width:300px;">
				'.$Module->getTeaserOptions($sMenuData['tas_ID']).'
			</select> 
		</td>
	</tr>
	<tr>
		<td width="150">'.$Res->html(773,page::language()).':</td>
		<td>
			<select name="parent" class="cTextfield" style="width:300px;">
				'.$Module->getParentDropdown($Menu,$sMenuData['mnu_Parent']).'
			</select> 
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(126,page::language()).':
		</td>
		<td>
			<input type="checkbox" name="active" value="1"'.checkCheckbox(1,$sMenuData['mnu_Active']).'> '.$Res->html(139,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="invisible" value="1"'.checkCheckbox(1,$sMenuData['mnu_Invisible']).'> '.$Res->html(140,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="secured" value="1"'.checkCheckbox(1,$sMenuData['mnu_Secured']).'> '.$Res->html(141,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			&nbsp;
		</td>
		<td>
			<input type="checkbox" name="blank" value="1"'.checkCheckbox(1,$sMenuData['mnu_Blank']).'> '.$Res->html(1167,page::language()).'
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(127,page::language()).':
		</td>
		<td>
			<input type="text" name="index" maxlength="10" style="width:50px;" value="'.$sMenuData['mnu_Index'].'">
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(1161,page::language()).':
		</td>
		<td>
			<input type="text" name="path" id="mnulink" maxlength="255" style="width:250px;" value="'.$sMenuData['mnu_Path'].'">
			<script type="text/javascript">
				// Konfigurationsvariablen fuer den Blur Handler
				var BlurLinkConfig = {
					isBlurActive : '.$Module->isBlurActive($sMenuData['mnu_Path']).',
					waitMessage : "'.$Res->normal(1163,page::language()).'"
				};
			</script>
		</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(994,page::language()).':
		</td>
		<td>
			<input type="text" name="title" maxlength="255" style="width:250px;" value="'.$sMenuData['mnu_Title'].'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(128,page::language()).':
		</td>
		<td>
			<textarea style="width:300px;height:80px;" name="metakeys">'.$sMenuData['mnu_Metakeys'].'</textarea>
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(129,page::language()).':
		</td>
		<td valign="top">
			<textarea style="width:300px;height:80px;" name="metadesc">'.$sMenuData['mnu_Metadesc'].'</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td width="150">
			'.$Res->html(501,page::language()).':
		</td>
		<td>
			<input type="text" name="shorttag" maxlength="255" style="width:250px;" value="'.$sMenuData['mnu_Shorttag'].'"> 
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(130,page::language()).':
		</td>
		<td valign="top">
			<input disabled type="text" name="external" maxlength="255" style="width:250px;" value="'.$sMenuData['mnu_External'].'">
		</td>
	</tr>
	<tr>
		<td width="150" valign="top">
			'.$Res->html(131,page::language()).':
		</td>
		<td valign="top">
			<select disabled name="redirect" style="width:300px;">
				'.$Menu->getSelectOptions($sMenuData['mnu_Redirect']).'
			</select>
		</td>
	</tr>
</table>
</form>
<script type="text/javascript">
	checkLinkOption('.menuTypes::LINK_INTERNAL.','.menuTypes::LINK_EXTERNAL.');
</script>
';

// Formular für das Hochladen von Bildern
$out .= '
<form name="imageUpload" action="menu.php?id='.page::menuID().'&menu='.$_GET['menu'].'&upload" method="post" enctype="multipart/form-data">
	<br>
	<table width="100%" cellpadding="3" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="2">
				<h1>'.$Res->html(490,page::language()).' - '.stringOps::chopString($sMenuData['mnu_Name'],30,true).'</h1>
				<br>
			</td>
		</tr>
		<tr>
			<td width="150" valign="top">
				'.$Res->html(491,page::language()).':
			</td>
			<td>
				<input type="file" name="menuPicture"> 
				<input type="submit" value="'.$Res->html(493,page::language()).'">
			</td>
		</tr>
		<tr>
			<td width="150" valign="top">
				'.$Res->html(492,page::language()).':
			</td>
			<td>
				<input type="file" name="mousePicture"> 
				<input type="submit" value="'.$Res->html(493,page::language()).'">
			</td>
		</tr>
		<tr>
			<td width="150" valign="top">
				'.$Res->html(494,page::language()).':
			</td>
			<td>
				'.$Module->getPictureState($Res).'
			</td>
		</tr>
	</table>
</form>
';

// Tabellenzeilenwechsler
$TabRow = new tabRowExtender();

$out .= '
<br>
<br>
<div id="helpDialog" style="display:none">
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr class="tabRowHead">
		<td width="150">'.$Res->html(43,page::language()).'</td>
		<td>'.$Res->html(44,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(124,page::language()).'</em></td>
		<td valign="top">'.$Res->html(132,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(125,page::language()).'</em></td>
		<td valign="top">'.$Res->html(133,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(126,page::language()).'</em></td>
		<td valign="top">'.$Res->html(134,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(127,page::language()).'</em></td>
		<td valign="top">'.$Res->html(135,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(128,page::language()).'<br>'.$Res->html(129,page::language()).'</em></td>
		<td valign="top">'.$Res->html(136,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(501,page::language()).'</em></td>
		<td valign="top">'.$Res->html(502,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(994,page::language()).'</em></td>
		<td valign="top">'.$Res->html(995,page::language()).'</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(130,page::language()).'</em></td>
		<td valign="top">'.$Res->html(137,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(131,page::language()).'</em></td>
		<td valign="top">'.$Res->html(138,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(491,page::language()).' & '.$Res->html(492,page::language()).'</em></td>
		<td valign="top">'.$Res->html(495,page::language()).'.</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="150" valign="top"><em>'.$Res->html(773,page::language()).'</em></td>
		<td valign="top">'.$Res->html(774,page::language()).'.</td>
	</tr>
</table>
</div>
';

// Ans Template weitergeben
$tpl->aC($out);

// System abschliessen
require_once(BP.'/cleaner.php');