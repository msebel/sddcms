<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

library::loadRelative('library');
$Module = new moduleSearch();
$Module->loadObjects($Conn,$Res);

$out .= '<h1>'.$Res->html(496,page::language()).'</h1><br>';

// Suche Starten
$Module->search($out);

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');