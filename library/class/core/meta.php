<?php
// Seitentitel zusammenbauen
$sTitleName = '';
if (strlen($Menu->CurrentMenu->Name) > 0) {
	$sTitleName = $Menu->CurrentMenu->Name.' - ';
}
// Check Caching for CSS Files
cacheLib::checkDesignFiles(page::design());
// System CSS und Javascripts einbinden
$version = getInt(option::get('designCssVersion_'.page::design(),1));
$Meta->addCSS(singleton::cdn().'/resource/css/design.php?p[d]='.page::design().'&v'.$version);
// IE Kompatible Stylesheets laden
if (option::available('ie6Css')) {
	$Meta->addIe6CSS('/design/'.page::design().'/design-ie6.css');
}
// Javascripts laden
$Meta->addJavascript('/page/'.page::ID().'/include/page.js',true);
$Meta->addJavascript('/mandant/'.page::mandant().'/include/mandant.js',true);

// Favicon einbinden
$Meta->addFavicon('/page/'.page::ID().'/include/favicon.ico');

// Meta Tags und schliesslich Title
$Meta->addMeta('keywords',page::metakeys().' '.$Menu->CurrentMenu->Metakeys);
$Meta->addMeta('description',page::metadesc().' '.$Menu->CurrentMenu->Metadesc);
if (strlen($Menu->CurrentMenu->Title) > 0) {
	$Meta->addMeta('title',$Menu->CurrentMenu->Title);
	$Meta->setTitle($Menu->CurrentMenu->Title);
} else {
	$Meta->addMeta('title',$sTitleName.page::name());
	$Meta->setTitle($sTitleName.page::name());
}
$Meta->addMeta('robots','index, follow');
$Meta->addMeta('revisit-after','15 days');
$Meta->addMeta('author',page::author());
$Meta->addMeta('generator','sddCMS');
$Meta->addVerify();
$Meta->addEquiv('content-language',$Meta->getContentLanguage());
$Meta->addEquiv('content-type','text/html; charset=iso-8859-1');