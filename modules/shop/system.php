<?php
// Basispfade (lokale und individuell) bestimmen
define('SP', BP.'/modules/shop');
define('ISP', BP.'/page/'.page::id().'/shop');

// system.php wird manchmal im Klassenkontext inkludiert, Daher
// den $ClassLoader auf jeden Fall in den globalen Scope nehmen.
global $ClassLoader;

// Dynamischen Klassenloader laden
$ClassLoader = new dynamicClassLoader();
// Basispfade nach Priorität hinzufügen
$ClassLoader->setBasepaths(array(ISP,SP));
// Array aus Pfaderweiterungen angeben
$ClassLoader->setSearchFolders(array('/classes/','/'));

// Konfiguration includieren (Live oder Debug)
if (DEBUG_CONFIG) {
	$ClassLoader->load('config.debug');
} else {
	$ClassLoader->load('config.live');
}

// View Klasse
$ClassLoader->load('abstractShopView');

// ShopConfig erstellen und initialisieren
$ClassLoader->load('shopStatic');
$ClassLoader->load('shopConfig');
shopConfig::initialize($Conn);

// Basis Shopklassen laden
$ClassLoader->load('shopStockarea');
$ClassLoader->load('shopArticle');
$ClassLoader->load('shopArticleStockarea');
$ClassLoader->load('shopArticlesize');
$ClassLoader->load('shopArticlegroup');
$ClassLoader->load('shopDynamicfield');
$ClassLoader->load('shopDynamicdata');
$ClassLoader->load('shopOrderarticle');
$ClassLoader->load('shopOrder');
$ClassLoader->load('shopCoupon');
$ClassLoader->load('shopUser');
$ClassLoader->load('shopAddress');