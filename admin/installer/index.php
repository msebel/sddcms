<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/admin/installer/config.php');
require_once(BP.'/admin/installer/library.php');

$Module = new moduleIstaller();
$Module->loadObjects($Conn,$Res);

// Einstellungen speichern (Page erstellen)
$Msg = '';
if (config::INSTALLER_ACTIVE) {
	if (isset($_GET['save'])) $Msg = $Module->createPage();
}

// Sprache einstellen
switch($_GET['lang']) {
	case 'en':	$Lang = 1; break;
	case 'de':
	default:	$Lang = 0; break;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
<html>
<head>
	<title>sddCMS Installer</title>
	<link rel="stylesheet" href="/admin/installer/design/design.css">
	<script type="text/javascript" src="/admin/installer/design/javascript.js"></script>
	<script type="text/javascript">
		function setView(sType) {
			switch(sType) {
				case 'New':
					document.getElementById('usetypeNew').style.display = 'block';
					document.getElementById('usetypePresent').style.display = 'none';
					break;
				case 'Present':
					document.getElementById('usetypeNew').style.display = 'none';
					document.getElementById('usetypePresent').style.display = 'block';
					break;
			}
		}
	</script>
</head>
<body>
<div id="divContainer">
	<div id="divAllCont">
		<div id="divHeader">
			<img src="/design/14/logo-company-web.png" id="imgHeader">
			<div style="float:right;">
				<a href="?lang=de">de</a> | 
				<a href="?lang=en">en</a>
			</div>
			<span id="installerTitle">sddCMS Installer</span>
		</div>
		<div id="divContent">
		<?php 
		if (!$Module->isInstalled()) { 
			echo '
			<strong>'.$Res->html(916,$Lang).'</strong>
			<p>
				'.$Res->html(980,$Lang).' 
				<a href="/doc/manual/pdf/sddCMS-entwickler-schulung.pdf" target="_blank">
					'.$Res->html(246,$Lang).'
				</a>.
			</p>';
		} else if (config::INSTALLER_ACTIVE) { 
		?>
		<form action="index.php?lang=<?php echo $Lang; ?>&save" method="post" onSubmit="return checkForm(this);">
		<table cellpadding="3" cellspacing="0" border="0" width="90%" align="center">
			<tr>
				<td colspan="2">
					<br><strong><?php echo $Msg; ?></strong>
					<br>
					<br>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<h1><?php echo $Res->html(783,$Lang); ?></h1>
					<p style="width:500px;">
					<?php echo $Res->html(782,$Lang); ?>.
					</p>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="radio" id="usetypeNewRdo" name="usetype" onclick="setView('New');" value="1" checked="checked"> <?php echo $Res->html(784,$Lang); ?> <br>
					<input type="radio" id="usetypePresentRdo" name="usetype" onclick="setView('Present');" value="0"> <?php echo $Res->html(785,$Lang); ?><br>
				</td>
			</tr>
		</table>
		<br>
		<div id="usetypeNew" style="display:block;">
		<table cellpadding="3" cellspacing="0" border="0" width="90%" align="center">
			<tr>
				<td width="150"><?php echo $Res->html(786,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="text" id="pageDescription" name="pageDescription" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td><?php echo $Res->html(787,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="text" id="designID" name="designID" value="" style="width:80px;">
				</td>
			</tr>
			<tr>
				<td><?php echo $Res->html(788,$Lang); ?>:</td>
				<td>
					<input type="text" name="adminDesignID" value="" style="width:80px;">
				</td>
			</tr>
		</table>
		</div>
		<div id="usetypePresent" style="display:none;">
		<table cellpadding="3" cellspacing="0" border="0" width="90%" align="center">
			<tr>
				<td width="150"><?php echo $Res->html(789,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<select id="presentPageID" name="presentPageID" style="width:300px;">
						<?php echo $Module->getPageOptions(); ?>
					</select>
				</td>
			</tr>
		</table>
		</div>
		<table cellpadding="3" cellspacing="0" border="0" width="90%" align="center">
			<tr>
				<td colspan="2">
					<br><br><h1><?php echo $Res->html(790,$Lang); ?></h1>
					<p style="width:500px;">
					<?php echo $Res->html(792,$Lang); ?>.
					</p>
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(791,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="text" id="mandantDomain" name="mandantDomain" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(786,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="text" id="mandantDescription" name="mandantDescription" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(794,$Lang); ?>:</td>
				<td>
					<input type="text" name="userContact" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(795,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="text" id="userAlias" name="userAlias" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(796,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="password" id="userPassword" name="userPassword" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(793,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<input type="password" id="userConfirm" name="userConfirm" value="" style="width:300px;">
				</td>
			</tr>
			<tr>
				<td width="150"><?php echo $Res->html(798,$Lang); ?> <span class="red">*</span>:</td>
				<td>
					<select name="mandantLanguage" style="width:120px;">
						<option value="0"> Deutsch</option>
						<option value="1"> English</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="150">&nbsp;</td>
				<td>
					<br>
					<input type="submit" value="<?php echo $Res->html(797,$Lang); ?>">
				</td>
			</tr>
		</table>
		</form>
		<?php 
		} else {
		?>
			<table cellpadding="3" cellspacing="0" border="0" width="90%" align="center">
			<tr>
				<td colspan="2">
					<br><br>
					<p>
					<?php echo $Res->html(799,$Lang); ?>.
					</p>
				</td>
			</tr>
			</table>
		<?php
		}
		?>
		</div>
	</div>
</div>
</body>
</html>