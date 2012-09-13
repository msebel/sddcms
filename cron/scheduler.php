<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

if (isset($_GET['h73fd95hf63gdn74bf63gd84hf73g453'])) {
	$Scheduler = new jobScheduler($Conn,$Res,$out);
	$tpl->aC($Scheduler->getOutput());
}

// System abschliessen
require_once(BP.'/cleaner.php');