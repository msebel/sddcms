<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));
require_once(BP.'/system.php');
// Zugriff testen und Fehler melden
$Access->control();
// Schauen ob das Menu schon ein Element besitzt
$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".page::menuID();
$nEleID = getInt($Conn->getFirstResult($sSQL));

// Wenn nicht vorhanden, erstellen
if ($nEleID == 0) {
	$sSQL = "INSERT INTO tbelement (owner_ID,ele_Size,ele_Links,ele_Type,
	ele_Library,ele_Thumb,ele_Target,ele_File,ele_Desc,ele_Longdesc) VALUES
	(".page::menuID().",0,1,4,0,0,'','','','')";
	// Datensatz erstellen und neue ID zurückgeben
	$nEleID = $Conn->insert($sSQL);
	// Pfad erstellen wenn nicht existent
	$sPath = BP.'/page/'.page::ID().'/element/'.$nEleID.'/';
	if (!file_Exists($sPath)) mkdir($sPath,0755,true);
}

// Pfad basteln
$_SESSION['rootfolder_'.page::menuID()] = '/page/'.page::ID().'/element/'.$nEleID.'/';

// Weiterleiten an die Startseite der File Library
redirect('location: /admin/library/index.php?id='.page::menuID());

// System abschliessen
$tpl->aC($out);
require_once(BP.'/cleaner.php');