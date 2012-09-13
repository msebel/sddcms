<?php
// Konstanten setzen
define('LANG_DE',0);				// Sprachcode für Deutsch
define('LANG_EN',1);				// Sprachcode für Englisch
define('FALLBACK_LANG',1);			// Rückfallsprache wenn Res nicht vorhanden
define('DEBUG',false);				// true = Debugausgaben einschalten

if (!DEBUG) error_reporting(0);

// Klassendefinitionen laden, für Sessionobjekte
require_once(BP.'/library/class/menuObject/menuObject.php');
require_once(BP.'/library/class/menuTypes/menuTypes.php');
require_once(BP.'/library/class/logging/logging.php');

session_start();					// Sitzung starten

// Globales Array für Klassenlader
$GLOBALS['ClassPath'] = array(
	'/library/class',
	'/library/abstract',
	'/library/interface',
	'/library/exception'
);

require_once(BP.'/config.php');								// Systemkonfigurationen
require_once(BP.'/library/global.php');						// Globale Bibliotheken
require_once(BP.'/library/class/core/database.php');		// Datenbankklasse
require_once(BP.'/library/class/resources/sessionres.php');	// Resourcen Session
require_once(BP.'/library/class/resources/resources.php');	// Resourcen Klasse

$Conn = database::getConnection();
// Dies der Logging Klasse zuweisen, damit diese ab hier funktioniert
logging::$Conn = $Conn;

// Resourcen Objekt erstellen
$Res = getResources::getInstance($Conn);

// Sessionres Array erstellen, wenns noch nicht gibt
if (!isset($_SESSION['sessionres'])) {
	$_SESSION['sessionres'] = array();
}
