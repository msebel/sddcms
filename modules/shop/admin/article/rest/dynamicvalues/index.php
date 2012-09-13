<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

$nSdfID = getInt($_POST['fieldID']);
$Values = array();
$sSQL = 'SELECT sdv_ID,sdv_Value FROM tbshopdynamicvalue
WHERE sdf_ID = '.$nSdfID.' ORDER BY sdv_Order ASC';
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	array_push($Values,array(
		'ID' => $row['sdv_ID'],
		'Value' => $row['sdv_Value']
	));
}

echo json_encode($Values);

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
session_write_close();