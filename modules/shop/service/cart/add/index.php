<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// Einen Order erstellen, wenn keiner vorhanden ist
$nShoID = shopOrder::getSessionOrder();
// POST Daten holen
$article = new shopArticle($_POST['shaID']);
$nCount = getInt($_POST['articles']);
$size = new shopArticlesize($_POST['sazID']);

// Ein Artikel minimum
if ($nCount <= 0) $nCount = 1;

// Fehler generieren, wenn kein gültiger Artikel
if ($article->getManID() != page::mandant()) {
	header('HTTP/1.1 500 Internal Server Error');
	echo "{ 'message' : '".$Res->javascript(1095,page::language())."' }";
	exit;
}

// Den Artikel n-Mal hinzufügen
for ($i = 0;$i < $nCount;$i++) {
	// Order Artikel generieren
	$orderart = $article->getOrderInstance();
	// Grösse definieren, wenn möglich
	if ($size->getShaID() == $article->getShaID()) {
		$orderart->setSize($size->getValue());
		// Preis hinzufügen wegen anderer Grösse
		$nPrice = $orderart->getPrice();
		$orderart->setPrice($nPrice + $size->getPriceadd());
	}
	// Zuweisen des aktuellen Order
	$orderart->setShoID(shopOrder::getSessionOrder());
	// Order Artikel so speichern
	$orderart->save();
}

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
// Session sicher speichern
session_write_close();