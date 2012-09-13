<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// View Klasse für Gruppen laden
$ClassLoader->load('viewSearch');
$View = new viewSearch('search');

// System abschliessen
$tpl->aC($View->getContent());
$tpl->aMeta($View->getMeta());
require_once(BP.'/cleaner.php');