<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// Dynamischen Klassenloader laden
$CartClasses = new dynamicClassLoader();
// Basispfade nach Priorität hinzufügen
$CartClasses->setBasepaths(array(ISP,SP));
// Array aus Pfaderweiterungen angeben
$CartClasses->setSearchFolders(array('/classes/cart/'));
$CartClasses->load('deliveryCost');

// Einen Order erstellen, wenn keiner vorhanden ist
$nShoID = shopOrder::getSessionOrder();

// Alle Artikel holen und in ein array laden
$data['articles'] = array();
$sSQL = 'SELECT soa_ID,sha_ID,soa_Title,soa_Size,soa_Price FROM tbshoporderarticle
WHERE sho_ID = '.$nShoID.' AND man_ID = '.page::mandant().'
ORDER BY soa_ID ASC';
$nRes = $Conn->execute($sSQL);
while ($row = $Conn->next($nRes)) {
	// Gleiche Artikel mergeln und Preis summieren
	if (!shopStatic::mergeArticles($data['articles'],$row)) {
		$row['soa_Times'] = 1;
		// TODO: Nicht mehr encoden, sobald alles auf utf8 ist
		$row['soa_Title'] = utf8_encode($row['soa_Title']);
		array_push($data['articles'],$row);
	}
}

// Wenn keine Artikel, Pseudo erstellen für Meldung
if (count($data['articles']) == 0) {
	$pseudo = shopOrderarticle::getPseudo();
	// Diesen ins Set hinzufügen
	array_push($data['articles'],$pseudo);
} else {
	// Lieferkosten erstellen
	$delivery = new deliveryCost(new shopOrder($nShoID),'');
	// Daten holen
	$deliverycost = $delivery->getData(true);
	// Artikel erstellen
	$row = array(
		'soa_ID' => '0',
		'sha_ID' => '0',
		'soa_Title' => $deliverycost['DELIVERY_COST_NAME'],
		'soa_Size' => '',
		'soa_Price' => $deliverycost['DELIVERY_PRICE'],
		'soa_Times' => 1,
	);
	// Diesen ins Array hinzufügen
	array_push($data['articles'],$row);
}

// Metadaten hinzufügen
$data['meta']['total'] = $Res->normal(1096,page::language());
$data['meta']['currency'] = 'CHF';

// Totalen Preis berechnen
$nTotal = 0;
foreach ($data['articles'] as $article) {
	$nTotal += $article['soa_Price'];
}
$data['meta']['totalPrice'] = numericOps::getDecimal($nTotal,2);

// Array ausgeben
$json = json_encode($data);
// Sanitisieren TODO: Wegnehmen, sobald cleanes UTF-8 da ist
$json = str_replace('\u0096','-',$json);
// Und jetzt ausgeben
echo $json;

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
// Session sicher speichern
session_write_close();