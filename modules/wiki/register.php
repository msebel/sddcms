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

// Registrierung durchführen
if (isset($_GET['save'])) $Module->registerUser();

// Fehlermeldung abfangen
$sError = sessionConfig::get('wikiMessage','');
sessionConfig::set('wikiMessage','');

// Top Menu des Wiki holen
$Module->loadTopmenu($Access,$out);

// Registrierungsformular anzeigen
$out .= '
<div id="divWikiContent">
<form action="register.php?id='.page::menuID().'&save" method="post">
	<h1>'.$Res->html(926,page::language()).'</h1>
	<p>'.$Res->html(927,page::language()).'.</p>
	'.$sError.'<br>
	<table width="100%" cellpadding="3" cellspacing="0">
		<tr>
			<td width="130" valign="top">
				'.$Res->html(2,page::language()).':
			</td>
			<td valign="top">
				<input type="text" name="username" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(928,page::language()).':
			</td>
			<td valign="top">
				<input type="text" name="email" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(3,page::language()).':
			</td>
			<td valign="top">
				<input type="password" name="password1" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(925,page::language()).':
			</td>
			<td valign="top">
				<input type="password" name="password2" value="" style="width:200px;">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				&nbsp;
			</td>
			<td valign="top">
				<input class="cButton" type="submit" value="'.$Res->html(926,page::language()).'">
			</td>
		</tr>
	</table>
</form>
</div>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');