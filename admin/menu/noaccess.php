<?php
// Toolbar erstellen
$outAlt = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="cNav" width="150"><a href="index.php?id='.page::menuID().'">'.$Res->html(108,page::language()).'</a></td>
		<td class="cNav" width="150"><a href="menu.php?id='.page::menuID().'&menu='.$_GET['menu'].'">'.$Res->html(109,page::language()).'</a></td>
		<td class="cNavSelected" width="150">'.$Res->html(110,page::language()).'</td>
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
		</td>
	</tr>
</table>
<br>
<table width="100%" cellpadding="3" cellspacing="0" border="0">
	<tr>
		<td><h1>'.$Res->html(110,page::language()).' - '.stringOps::chopString($sMenuData['mnu_Name'],30,true).'</h1>
		<br>
		'.$Res->html(122,page::language()).'.</td>
	</tr>
</table>
';