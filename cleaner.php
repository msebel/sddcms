<?php
// Meta Typ herausfinden, wenn Geschützer bereich, Meta
// Admin, sonst Meta normal
if ($Menu->CurrentMenu->Secured == 1) {
	$sMeta = '/library/class/core/meta_admin.php';
} else {
	$sMeta = '/library/class/core/meta.php';
}
// Metainfos einbinden
require_once(BP.$sMeta);
// Inhalte der Webseite ausgeben
$tpl->setTeaser($Teaser);
$tpl->write();
session_write_close();