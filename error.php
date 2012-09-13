<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Content Teil
$sErrorType = $_GET['type'];
switch ($sErrorType) {
	case 'noAccess':
		$out = $Res->html(183,page::language());
		break;
	case 'controller':
		$out = $Res->html(183,page::language());
		break;
	case 'invalidContent':
		$out = $Res->html(184,page::language());
		break;
	case 'noConfig':
		$out = $Res->html(383,page::language());
		break;
	case '403':
		$out = $Res->html(183,page::language());
		break;
	case 'FileNotFound':
		$out = $Res->html(450,page::language());
		breka;
	case '404':
		// Schaut ob der Pfad irgendwohin führt
		// und leitet dorthin weiter oder tut nichts
		// damit ordnugnsgemäss die Fehlermeldung kommt
		pathFinder::find($Conn);
		$out = $Res->html(450,page::language());
		break;
	case '500':
		$out = $Res->html(451,page::language());
		break;
	default:
		$out = $Res->html(185,page::language());
		break;
}

// Fehlermeldung einfügen
$tpl->aC($out);
// System abschliessen
require_once(BP.'/cleaner.php');