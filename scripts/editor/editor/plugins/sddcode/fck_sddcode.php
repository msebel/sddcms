<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// ContentLib f�r Mediamanager laden
require_once(BP.'/scripts/editor/editor/plugins/sddcode/library.php');
// Nichts anzeigen
$tpl->setEmpty();
// Ressourcen referenzieren
bbcodeLib::$Res = $Res;
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
	<title>Image Properties</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<script src="fck_dialog_common.js" type="text/javascript"></script>
	<script src="fck_image.js" type="text/javascript"></script>
	<script src="/scripts/system/system.js" type="text/javascript"></script>
	<link href="fck_dialog_common.css" rel="stylesheet" type="text/css" />
</head>
<body scroll="no" style="overflow: hidden">
	<form name="mmAction" action="fck_sddcode" method="post">
	<div id="divInfo">
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
			<tr>
				<td width="80" valign="top">Code</td>
				<td>
					<select name="codeSelect" id="codeSelect">
						<?php echo bbcodeLib::getCodes(); ?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="80" valign="top">Text</td>
				<td>
					<textarea style="width:100%;height:130px;" name="codeText" id="codeText"></textarea>
				</td>
			</tr>
			<tr>
				<td width="80">&nbsp;</td>
				<td>
					<?php echo $Res->html(858,page::language())?>
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