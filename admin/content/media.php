<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();

// Toolbar erstellen
$out = '
<form name="elementEdit" method="post" action="content.php?id='.page::menuID().'&content='.$_GET['content'].'&save">
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
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
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



// Element ID holen
$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".getInt($_GET['content']);
$nEleID = $Conn->getFirstResult($sSQL);

$_SESSION['ActualOwnerID'] = getInt($_GET['content']);
// Formular anzeigen
$out .= '
<table width="100%" cellspacing="0" cellpadding="3" border="0">
	<tr>
		<td><h1>'.$Res->html(244,page::language()).' ...</h1><br>
		'.$Res->html(245,page::language()).' 
		<a href="/admin/mediamanager/index.php?id='.page::menuID().'&element='.$nEleID.'&caller=content" target="_blank">'.$Res->html(246,page::language()).'</a> 
		'.$Res->html(247,page::language()).'.
		<script type="text/javascript">
			openWindow(\'/admin/mediamanager/index.php?id='.page::menuID().'&element='.$nEleID.'&caller=content\',\'mediaManager\',950,700);
		</script>
		</td>
	</tr>
</table>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');