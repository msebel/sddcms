<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Modulbezogene Funktionsklasse
library::load('menu');
$Module = new moduleMenu();
$Module->loadObjects($Conn,$Res);

// Name validieren und ausgeben
$name = $Module->sanitizePath($_POST['mnuname']);
echo $name;