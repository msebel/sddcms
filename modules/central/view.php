<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/content') !== false) {
	$section = getInt($_GET['section']);
	$link = '/controller.php?id='.$_GET['id'];
	if ($section > 0)
		$link .= '&section='.$section;
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.$link);
	exit;
}