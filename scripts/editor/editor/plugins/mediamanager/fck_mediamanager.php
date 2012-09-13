<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// ContentLib f�r Mediamanager laden
require_once(BP.'/scripts/editor/editor/plugins/mediamanager/library.php');
// Nichts anzeigen
$tpl->setEmpty();
// Element ID innerhalb des Contents erhalten
$nElement = contentLib::getElementInContent($Conn);
// HTML Code aus resultaten generieren
$out = '';
if (isset($_POST['cmdSave'])) {
	$out = contentLib::generateHtml($Conn,$nElement);
}
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
<body scroll="no" style="overflow: hidden"<?php
if (!isset($_POST['cmdSave']) && isset($_SESSION['ActualMenuID'])) {
	echo ' onload="BrowseServer('.$_SESSION['ActualMenuID'].','.$nElement.')"';
}
?>>
	<form name="mmAction" action="fck_mediamanager.php" method="post">
	<div id="divInfo">
		<table cellspacing="1" cellpadding="1" border="0" width="100%" height="100%">
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" width="100%" border="0">
						<tr>
							<td valign="top">
								<span>Medienwahl</span>
							</td>
						</tr>
						<tr>
							<td valign="top">
								<input id="txtUrl" style="width: 60%" type="text" /> 
								<input id="btnBrowse" onclick="BrowseServer(<?php echo $_SESSION['ActualMenuID'];?>,<?php echo $nElement;?>);" type="button" value="Medienmanager" />
							</td>
						</tr>
						<tr>
							<td valign="top" colspan="2">
								<br>
								<span>
								Der Medienmanager sollte automatisch in einem Popup starten. Falls dies nicht geschieht,
								Klicken Sie auf den "Mediamanager" Button oder aktivieren Sie Popups f&uuml;r dieses Fenster.
								</span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<input type="hidden" name="sFile" value="">
	<input type="hidden" name="sFileXL" value="">
	<input type="hidden" name="nType" value="">
	<input type="hidden" name="nWidth" value="">
	<input type="hidden" name="nHeight" value="">
	<input type="hidden" name="sDesc" value="">
	<input type="hidden" name="nThumb" value="">
	<input type="hidden" name="sSkin" value="">
	<input type="hidden" name="sAlign" value="">
	<input type="hidden" name="cmdSave" value="">
	</form>
	<?php
	// Wenn out definiert ist, absenden und schliessen
	if (strlen($out) > 0) {
		echo '
		<input type="hidden" id="processedHtml" name="processedHtml" value=\''.$out.'\'>
		<script type="text/javascript">
			Ok(); // Absenden
		</script>
		';
	}
	?>
</body>
</html>
<?php
require_once(BP.'/cleaner.php');
?>