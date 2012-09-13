<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

$nContentID = getInt($_GET['news']);
$sSQL = "SELECT COUNT(con_ID) FROM tbcontent
WHERE con_ID = $nContentID AND mnu_ID = ".page::menuID();
$nResult = $Conn->getCountResult($sSQL);

if ($nResult != 1) {
	redirect('location: /error.php?type=noAccess');
}

contentView::getContentElement($nContentID,$out,$Conn);

$out .= '
<br>
<p>
	<a class="cMoreLink" href="index.php?id='.page::menuID().'">'.$Res->html(37,page::language()).'</a>
</p>
';
// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');