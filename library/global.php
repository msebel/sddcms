<?php
// Klassenlader laden
require_once(BP.'/library/loader.php');

// globalen Exception/Error Handler registrieren
set_exception_handler('globalExceptionHandler');
set_error_handler('globalErrorHandler');
spl_autoload_register('sddCmsAutoload');

// Löscht $delValue aus &$array. $array wird als
// Referenz verarbeitet und nicht zurückgegeben.
// Diese Funktion sollte nur bei kleinen Arrays
// angewendet werden, da recht intensiv
function arrayDeleteValue($delValue,&$array) {
	$newArray = array();
	// Alles durchgehen und pushen ins neue Array
	foreach ($array as $value) {
		if ($value !== $delValue) {
			array_push($newArray,$value);
		}
	}
	// Array speichern
	$array = $newArray;
}

// Nimmt einen HTTP Header, fügt ihn ein
// und beendet das Skript (Für Redirects)
function redirect($httpHeader) {
	header($httpHeader);
	exit();
}

// Validiert einen Integer und gibt ihn zurück
function getInt($value) {
	$value = (int) $value;
	if (empty($value)) $value = 0;
	return($value);
}

// Ladet die Pfade zu einem Modul, gibt den View
// und Adminpath by Reference zurück
function loadPaths(&$sViewPath,&$sAdminPath,dbConn &$Conn,$nMenuType,&$sClassName) {
	// Abfrage und Status vorbereiten
	$sSQL = 'SELECT typ_Adminpath, typ_Viewpath, typ_ClassName FROM tbmenutyp WHERE typ_ID = '.$nMenuType;
	$bFound = false;
	// Schauen obs in der Session ist
	if (isset($_SESSION['menutypeData_'.$nMenuType])) {
		$sViewPath = $_SESSION['menutypeData_'.$nMenuType]['view'];
		$sAdminPath = $_SESSION['menutypeData_'.$nMenuType]['admin'];
		$sClassName = $_SESSION['menutypeData_'.$nMenuType]['classname'];
		$bFound = true;
	}
	// Wenn nicht, global suchen
	if (!$bFound) {
		// Erstmal in der globalen Datenbank schauen
		$Conn->setGlobalDB();
		$Res = $Conn->execute($sSQL);
		while ($row = $Conn->next($Res)) {
			if (strlen($row['typ_Viewpath']) > 0) $sViewPath  = $row['typ_Viewpath'];
			if (strlen($row['typ_Adminpath']) > 0) $sAdminPath = $row['typ_Adminpath'];
			if (strlen($row['typ_ClassName']) > 0) $sClassName = $row['typ_ClassName'];
			$bFound = true;
		}
	}
	// Normale Datenbank wechseln (Falls zuvor gewechselt)
	$Conn->setInstanceDB();
	// Wenn beides leer, selbes in der lokalen DB schauen
	if (!$bFound) {
		$Res = $Conn->execute($sSQL);
		while ($row = $Conn->next($Res)) {
			if (strlen($row['typ_Viewpath']) > 0) $sViewPath  = $row['typ_Viewpath'];
			if (strlen($row['typ_Adminpath']) > 0) $sAdminPath = $row['typ_Adminpath'];
			if (strlen($row['typ_ClassName']) > 0) $sClassName = $row['typ_ClassName'];
			$bFound = true;
		}
	}
	// Wenn jetzt noch leer, mit index.php ersetzen
	if (!$bFound) {
		$sViewPath  = "/index.php";
		$sAdminPath = "/index.php";
	}
	// Speichern der Pfade in der Session
	if (!isset($_SESSION['menutypeData_'.$nMenuType])) {
		$_SESSION['menutypeData_'.$nMenuType]['view'] = $sViewPath;
		$_SESSION['menutypeData_'.$nMenuType]['admin'] = $sAdminPath;
		$_SESSION['menutypeData_'.$nMenuType]['classname'] = $sClassName;
	}
}

// gibt an ob der Menupunkt existiert
function menuExists($nMenuID,dbConn &$Conn) {
	$bReturn = false;
	// Den menupunkt zählen
	$sSQL = 'SELECT COUNT(mnu_ID) FROM tbmenu WHERE 
	mnu_ID = '.$nMenuID.' AND man_ID = '.page::mandant();
	$nResult = $Conn->getCountResult($sSQL);
	if ($nResult == 1) $bReturn = true;
	return($bReturn);
}

// Bekommt den aktuellen Wert und den Referenzwert
// eines Dropdown und gibt " selected" aus, sofern
// die beiden Werte zutreffen
function checkDropDown($sRef,&$sAct) {
	$sReturn = '';
	if ($sRef == $sAct) $sReturn = ' selected';
	return($sReturn);
}

// Checkt eine Checkbox, anhand des Referenzwertes
// und des Prüfwertes
function checkCheckBox($sRef,&$sAct) {
	$sReturn = '';
	if ($sRef == $sAct) $sReturn = ' checked';
	return($sReturn);
}

// Dömpt eine Variable im Code-Format
function debug($Var) {
	echo '<pre>';
	echo var_dump($Var);
	echo '</pre>';
}

// Globaler Exception handler
function globalExceptionHandler($exception) {
	// Error ausgeben oder loggen
	if (DEBUG) {
		if ($exception instanceof sddException) {
			echo $exception->getStackTraceFormatted();
		}
	} else {
		if ($exception instanceof sddException) {
			logging::fatal(addslashes($exception->getStackTraceFormatted()));
		} else {
			logging::error('unknown exception type occured: '.$exception->getMessage());
		}
	}
}

// Globaler Error Handler
function globalErrorHandler($errno,$errstr,$errfile,$errline,$errcontext) {
	$Message = "Error: $errstr in $errfile on line $errline";
	if ($errno > 2048) {
		if (DEBUG) {
			echo '<pre>'.$Message.'</pre>';
		} else {
			logging::fatal($Message);
		}
	}
}

// Implementiere die __autoload funktion
function sddCmsAutoload($class) {
	if (class_exists($class)) return;
	// Vom Cache nehmen oder suchen
	if ($_SESSION['class_cache'][$class] !== NULL) {
		include_once($_SESSION['class_cache'][$class]);
	} else {
		foreach ($GLOBALS['ClassPath'] as $path) {
			if (file_exists(BP.$path.'/'.$class.'/'.$class.'.php')) {
				include_once(BP.$path.'/'.$class.'/'.$class.'.php');
				$_SESSION['class_cache'][$class] = BP.$path.'/'.$class.'/'.$class.'.php';
			}
		}
	}
}

// Erstellt die htmlspecialchars_decode Funktion
// und nimmt das beste was die PHP Version zu bieten hat
// als alternative (PHP < 5.1)
if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($string) {
		return(html_entity_decode($string));
	}
}