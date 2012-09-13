<?php
define('BP', realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
require_once(BP.'/modules/shop/system.php');

// Daten holen
$article = new shopArticle(getInt($_GET['article']));
$group = new shopArticleGroup(getInt($_GET['bindID']));
$mode = $_GET['mode'];

// Validieren des Artikels und der Gruppe
if ($article->getManID() != page::mandant()) exit;
if ($group->getManID() != page::mandant()) exit;

// Löschen oder erstellen je nach Parameter
switch ($mode) {
	case 'select':
		$sSQL = 'INSERT INTO tbshoparticle_articlegroup (sha_ID,sag_ID)
		VALUES ('.$article->getShaID().','.$group->getSagID().')';
		$Conn->command($sSQL);
		break;
	case 'unselect':
		$sSQL = 'DELETE FROM tbshoparticle_articlegroup WHERE
		sha_ID = '.$article->getShaID().' AND sag_ID = '.$group->getSagID();
		$Conn->command($sSQL);
		break;
}

// System abschliessen
$tpl->setEmpty();
require_once(BP.'/cleaner.php');
session_write_close();