<?php 
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');

// Javascript einbinden
$Meta->addJavascript('/modules/findus/index.js',true);

// Karte initialisieren
$map = new googleMap();

// Route erstellen
$nRouteID = $map->createRoute();
$map->setProperty('RouteClass','cGoogleRouteFindUs');

// Adressen holen
$sStart = stringOps::getPostEscaped('startAddress',$Conn);
$sEnd = stringOps::getPostEscaped('goalAddress',$Conn);

// Koordinaten erstellen
$coordStart = new googleCoordinate($sStart);
$coordEnd = new googleCoordinate ($sEnd);

// Route übergeben
$map->setStart($coordStart,$nRouteID);
$map->setEnd($coordEnd,$nRouteID);

// Route ausgeben
$out .= '<h1>'.$Res->html(875,page::language()).'</h1><br>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="cFindUsNavi">
	<tr>
		<td id="iRoute" class="cNavSelected" width="150"><a href="javascript:setTdRegister(1,\''.$map->getProperty('MapID').'\');">'.$Res->html(872,page::language()).'</a></td>
		<td id="iDesc" class="cNav" width="150"><a href="javascript:setTdRegister(2,\''.$map->getProperty('MapID').'\');">'.$Res->html(873,page::language()).'</a></td>
		<td id="iBoth" class="cNav" width="150"><a href="javascript:setTdRegister(3,\''.$map->getProperty('MapID').'\');">'.$Res->html(874,page::language()).'</a></td>
		<td class="cNav">&nbsp;</td>
	</tr>
</table>';
$out .= $map->output();

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');