<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Content Teil

$tpl->aC('
<form name="loginForm" method="post" action="/login.php">
	<br>
	<br>
	<table width="100%" cellpadding="3" cellspacing="0">
		<tr>
			<td width="130" valign="top">
				'.$Res->html(2,page::language()).':
			</td>
			<td valign="top">
				<input type="text" name="username" value="">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				'.$Res->html(3,page::language()).':
			</td>
			<td valign="top">
				<input type="password" name="password" value="">
			</td>
		</tr>
		<tr>
			<td width="130" valign="top">
				&nbsp;
			</td>
			<td valign="top">
				<input class="cButton" type="submit" name="cmdLogin" value="'.$Res->html(4,page::language()).'">
			</td>
		</tr>
	</table>
	<script type="text/javascript">
		document.loginForm.username.focus();
	</script>
</form>
');

// System abschliessen
require_once(BP.'/cleaner.php');