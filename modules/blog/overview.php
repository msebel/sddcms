<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
$out = '';

// Konfiguration laden
$Config = array();
pageConfig::get(page::menuID(),$Conn,$Config);

// Suchformular anzeigen
$out .= '
<p>
<form action="keyword.php?id='.page::menuID().'&blog='.$Config['blogID']['Value'].'" method="post">
	<div style="float:right;">
		<input name="keyword" style="width:100px;" type="text"> 
		<input type="submit" name="submit" value="'.$Res->html(497,page::language()).'">
	</div>
</form>
</p>
';

// Blog Konfiguration laden
$BlogConfig = array();
pageConfig::get($Config['blogID']['Value'],$Conn,$BlogConfig);
// Header erstellen
if (strlen($BlogConfig['htmlCode']['Value']) > 0) {
	stringOps::htmlViewEnt($BlogConfig['htmlCode']['Value']);
	$out .= '<div class="divEntryText">'.$BlogConfig['htmlCode']['Value'].'</div>';
}

$sSQL = "SELECT blc_ID,blc_Title,blc_Desc
FROM tbblogcategory WHERE mnu_ID = ".$Config['blogID']['Value'];
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	stringOps::htmlViewEnt($row['blc_Title']);
	stringOps::htmlViewEnt($row['blc_Desc']);
	// Inhalte ausgeben
	$out .= '
	<div class="newsHead">
		'.$row['blc_Title'].'
	</div>
	<div class="newsContent">
		'.$row['blc_Desc'].'
		<p><a class="cMoreLink" href="category.php?id='.page::menuID().'&blog='.$Config['blogID']['Value'].'&category='.$row['blc_ID'].'">'.$Res->html(442,page::language()).'</a></p>
	</div>
	<div class="cDivider"></div>
	';
}

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');