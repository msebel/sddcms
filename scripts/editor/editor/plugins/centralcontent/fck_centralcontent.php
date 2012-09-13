<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Library laden
require_once(BP.'/scripts/editor/editor/plugins/centralcontent/library.php');
$Plugin = new centralContent();
$Plugin->loadObjects($Conn,$Res);
// Nichts anzeigen
$tpl->setEmpty();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Image Properties dialog window.
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Central Content</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<script src="fck_dialog_common.js" type="text/javascript"></script>
	<script src="fck_content.js" type="text/javascript"></script>
	<script src="/scripts/system/system.js" type="text/javascript"></script>
	<script src="/scripts/prototype/prototype.js" type="text/javascript"></script>
	<link href="fck_dialog_common.css" rel="stylesheet" type="text/css" />
</head>
<body scroll="no" style="overflow: hidden">
	<form name="ccAction" action="fck_centralcontent.php" method="post">
	<div id="divInfo">
		<br>
		<br>
		<table width="100%" cellpadding="2" cellspacing="0" border="0">
			<tr>
				<td width="130" valign="top"><?php echo $Res->html(815,page::language()); ?>:</td>
				<td valign="top">
					<select id="centralcontent" style="width:250px;" onchange="updateSections()">
						<option value="0"><?php echo $Res->html(824,page::language()); ?></option>
						<?php echo $Plugin->getCentralContentMenus(); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="130" valign="top"><?php echo $Res->html(817,page::language()); ?>:</td>
				<td valign="top">
					<select id="contentsection" style="width:250px;" size="3">
					</select>
				</td>
			</tr>
			<tr>
				<td width="130" valign="top"><?php echo $Res->html(820,page::language()); ?>:</td>
				<td valign="top">
					<input type="radio" name="viewtype" id="viewtypePopup" value="popup" onclick="updateViewtype('popup')" disabled="disabled"> <strike><?php echo $Res->html(821,page::language()); ?></strike><br>
					<input type="radio" name="viewtype" id="viewtypePaste" value="paste" checked="checked" onclick="updateViewtype('paste')"> <?php echo $Res->html(822,page::language()); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td width="130" valign="top"><?php echo $Res->html(174,page::language()); ?>:</td>
				<td valign="top">
					<div id="divPopupOptions" style="display:none;">
						<?php echo $Res->html(255,page::language()); ?>: 
						<input type="text" size="4" id="popupwidth" value="800">
						<?php echo $Res->html(256,page::language()); ?>: 
						<input type="text" size="4" id="popupheight" value="600">
					</div>
					<div id="divPasteOptions" style="display:block;">
						<input type="checkbox" value="1" id="newparagraph" checked="checked"> <?php echo $Res->html(823,page::language()); ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	</form>
</body>
</html>
<?php
require_once(BP.'/cleaner.php');
?>