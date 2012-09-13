<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Modulbezogene Funktionsklasse
library::loadRelative('library');
$Module = new moduleLog();
$Module->loadObjects($Conn,$Res);

$out .= '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(828,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(829,page::language()).'</td>
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
				<a href="#" onClick="javascript:location.reload()">
				<img src="/images/icons/arrow_refresh.png" alt="'.$Res->html(7,page::language()).'" title="'.$Res->html(7,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarItem">
				<a href="index.php?id='.page::menuID().'">
				<img src="/images/icons/door_out.png" alt="'.$Res->html(37,page::language()).'" title="'.$Res->html(37,page::language()).'" border="0">
				</a>
			</div>
			<div class="cToolbarError">
				&nbsp;'.$sMessage.'
			</div>
		</td>
	</tr>
</table>
<br>';

// Daten des Elements laden
$Data = NULL;
$Module->loadLogEntry($Data);
$TabRow = new tabRowExtender();

$out .= '
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td colspan="2">
			<h1>'.$Res->html(830,page::language()).' (#'.$Data['log_ID'].')</h1><br>
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_ID:</strong>
		</td>
		<td>
			'.$Data['log_ID'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>man_ID:</strong>
		</td>
		<td>
			'.$Data['man_ID'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>mnu_ID:</strong>
		</td>
		<td>
			'.$Data['mnu_ID'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>usr_ID:</strong>
		</td>
		<td>
			'.$Data['usr_ID'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Type:</strong>
		</td>
		<td>
			('.$Module->getCategoryByType($Data['log_Type']).') 
			'.$Data['log_Type'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Date:</strong>
		</td>
		<td>
			'.$Data['log_Date'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Userinfo:</strong>
		</td>
		<td>
			'.$Data['log_Userinfo'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Menuinfo:</strong>
		</td>
		<td>
			'.$Data['log_Menuinfo'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Error:</strong>
		</td>
		<td>
			'.$Data['log_Error'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Referer:</strong>
		</td>
		<td>
			'.$Data['log_Referer'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Urlinfo:</strong>
		</td>
		<td>
			'.$Data['log_Urlinfo'].'&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Postdata:</strong>
		</td>
		<td>
			<pre>'.$Data['log_Postdata'].'</pre>&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Getdata:</strong>
		</td>
		<td>
			<pre>'.$Data['log_Getdata'].'</pre>&nbsp;
		</td>
	</tr>
	<tr class="'.$TabRow->get().'">
		<td width="140" valign="top">
			<strong>log_Sessiondata:</strong>
		</td>
		<td>
			<pre>'.$Data['log_Sessiondata'].'</pre>&nbsp;
		</td>
	</tr>
</table>
';

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');