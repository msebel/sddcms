<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// Einen Order erstellen, wenn keiner vorhanden ist
$nShoID = shopOrder::getSessionOrder();
// Diese ID einfach ausgeben als Integer
echo $nShoID;

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
// Session sicher speichern
session_write_close();