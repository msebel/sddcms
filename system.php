<?php
// Konstanten setzen
define('LANG_DE',0);					// Sprachcode für Deutsch
define('LANG_EN',1);					// Sprachcode für Englisch
define('VERSION','3.0.1');		// Software Version
define('FALLBACK_LANG',1);		// Rückfallsprache wenn Res nicht vorhanden
define('JS_FRAMEWORK',1);			// Versionsnummer für JS Frameworkdatei
define('DEBUG',true);					// true = Debugausgaben einschalten
define('DEBUG_CONFIG',true);	// true = Debugkonfigurationen anwenden

if (!DEBUG) error_reporting(0);

// Klassendefinitionen laden, für Sessionobjekte
require_once(BP.'/library/class/core/sessionobjects.php');

// Sitzung starten
session_start();

// Globales Array für Klassenlader
$GLOBALS['ClassPath'] = array(
	'/library/class',
	'/library/abstract',
	'/library/interface',
	'/library/exception'
);

require_once(BP.'/config.php');															// Systemkonfigurationen
require_once(BP.'/library/global.php');											// Globale Bibliotheken
require_once(BP.'/library/class/core/database.php');				// Datenbankklasse
require_once(BP.'/library/class/resources/sessionres.php');	// Resourcen Session
require_once(BP.'/library/class/resources/resources.php');	// Resourcen Klasse
require_once(BP.'/library/class/core/page.php');						// Seitenklasse
require_once(BP.'/library/class/core/option.php');					// Seitenoptionen (Sessioned)
require_once(BP.'/library/class/core/access.php'); 					// Zugriffsobjekt (Auch Login)
require_once(BP.'/library/class/core/template.php');				// Templatedesign (Menu/Teaser/Meta)
require_once(BP.'/library/class/core/system.php');					// Ausgeführter Code vor Systemstart