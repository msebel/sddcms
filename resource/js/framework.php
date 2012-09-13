<?php
define('BP',realpath($_SERVER['DOCUMENT_ROOT']));

// Cache this like... forever!
header('Content-Type: text/javascript');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + 315360000 ) . ' GMT');
header("Cache-Control: public, max-age=315360000");

// Include the file-libraries, jquery first
// f-Parameter: c=core, j=jquerycore, p=prototype, s=scriptaculous
if (isset($_GET['f']['c'])) {
	echo file_get_contents(BP.'/scripts/system/system.js')."\n";
}
if (isset($_GET['f']['j'])) {
	echo file_get_contents(BP.'/scripts/jquery/jquery-1.7.1.min.js')."\n";
	echo '$.noConflict();';
}
if (isset($_GET['f']['p'])) {
	echo file_get_contents(BP.'/scripts/prototype/prototype.js')."\n";
}

if (isset($_GET['f']['s'])) {
	echo file_get_contents(BP.'/scripts/scriptaculous/effects.js')."\n";
	echo file_get_contents(BP.'/scripts/scriptaculous/builder.js')."\n";
	echo file_get_contents(BP.'/scripts/scriptaculous/dragdrop.js')."\n";
	echo file_get_contents(BP.'/scripts/lightbox/lightbox.js')."\n";
}