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

// Parameter sammeln (validieren)
$sActivation = stringOps::getGetEscaped('code',$Conn);
$nMenuID = getInt(page::menuID());
$nUserID = getInt($_GET['user']);

// Aktivierung versuchen
if ($Module->activateUser($nUserID,$sActivation,$nMenuID)) {
	// Top Menu des Wiki holen
	$Module->loadTopmenu($Access,$out);
	// Aktivierung hat geklappt
	$out .= '
	<div id="divWikiContent">
		'.$Res->html(951,page::language()).'
	</div>
	';
} else {
	// Aktivierung war nicht erfolgreich
	$out .= $Res->html(952,page::language());
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');