<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Seitenkonfiguration laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Weiterleiten auf gewünschte Kategorienseite
session_write_close();
redirect('location: /modules/blog/category.php?id='.page::menuID().'&blog='.$Config['blogID']['Value'].'&category='.$Config['categoryID']['Value']);

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');