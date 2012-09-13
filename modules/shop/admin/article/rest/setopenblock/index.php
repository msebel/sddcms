<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

$_SESSION['openblockid'] = getInt($_POST['block']);

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
session_write_close();