<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

$generator = new abstractRowGenerator('tbshoporder', $Conn);
$generator->setClassName('shopOrder');
$out = $generator->generate(true);

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');
