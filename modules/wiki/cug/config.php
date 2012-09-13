<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Library laden
require_once(BP.'/modules/wiki/library.php');
$Module = new moduleWiki();
$Module->loadObjects($Conn,$Res);

// Initialisieren des Wiki
$Module->initialize();
$Module->checkCugAccess($Access);

// Registrierung durchführen
if (isset($_GET['save'])) $Module->saveWikiUserSimple();

// Fehlermeldung abfangen
$sError = sessionConfig::get('wikiMessage','');
sessionConfig::set('wikiMessage','');

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Registrierungsformular anzeigen
$out .= '
<div id="divWikiContent">
<form action="config.php?id='.page::menuID().'&save" method="post">
	<table width="100%" cellpadding="3" cellspacing="0">
		<tr>
			<td colspan="2">
				<h1>'.$Res->html(919,page::language()).'</h1>
				<p>'.$sError.'&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(2,page::language()).':
			</td>
			<td valign="top">
				<input type="text" name="impAlias" style="width:200px;" value="'.$Module->getUser()->Alias.'">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(928,page::language()).':
			</td>
			<td valign="top">
				<input type="text" name="impEmail" style="width:200px;" value="'.$Module->getUser()->Email.'">
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<br>
				<h1>'.$Res->html(962,page::language()).'</h1>
				<p>&nbsp;</p>
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(3,page::language()).':
			</td>
			<td valign="top">
				<input type="password" name="impPass1" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(40,page::language()).':
			</td>
			<td valign="top">
				<input type="password" name="impPass2" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				&nbsp;
			</td>
			<td valign="top">
				<input class="cButton" type="submit" value="'.$Res->html(36,page::language()).'">
			</td>
		</tr>
	</table>
</form>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');