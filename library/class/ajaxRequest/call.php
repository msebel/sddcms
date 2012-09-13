<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/library/abstract/baseRequest/baseRequest.php');

// Parameter holen und validieren
$param = $_SERVER['QUERY_STRING'];
stringOps::alphaNumLow($param);

// Entsprechende Datei laden (Nicht abstürzen!)
@require_once(BP.'/library/class/ajaxRequest/objects/'.$param.'.php');
// Objekt erstellen und ausführen wenn Klasse existiert
if (class_exists($param,false)) {
	$ajax = new $param;
	$ajax->initialize($Conn,$Res);
	$ajax->output();
}