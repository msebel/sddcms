<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/gallery') !== false) {
	$link = '/controller.php?id='.$_GET['id'];
	// Plain oder Popup anhängen
	if (isset($_GET['popup'])) $link.= '&popup';
	if (isset($_GET['plain'])) $link.= '&plain';
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.$link);
}

// Modulbezogene Funktionsklasse
library::req('/modules/gallery/galleryConst');
library::req('/modules/gallery/Gallery');
library::req('/modules/gallery/GalleryFile');

/**
 * Viewmodul für das Content Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCmsGallery extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		// Konfiguration initialisieren
		$out = '';
		$Config = array();
		pageConfig::get(page::menuID(),$this->Conn,$Config);

		// HTML Code einfügen
		if (strlen($Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>';
		}

		// Je nach Modus eine Galerie starten
		switch ($Config['mode']['Value']) {
			case galleryConst::TYPE_TILTVIEWER:
				library::req('/modules/gallery/TiltViewerGallery/TiltViewerGallery');
				$Gallery = new TiltViewerGallery($this->Conn,$Config['thumbWidth']['Value'],singleton::meta());
				break;
			case galleryConst::TYPE_SIMPLEVIEWER:
				library::req('/modules/gallery/SimpleViewerGallery/SimpleViewerGallery');
				$Gallery = new SimpleViewerGallery($this->Conn,$Config['thumbWidth']['Value'],singleton::meta(),$this->Res);
				break;
			case galleryConst::TYPE_LIGHTBOX:
			default:
				// Wenn ungültiger Modus, Lightbox galerie zeigen
				library::req('/modules/gallery/LightboxGallery/LightboxGallery');
				$Gallery = new LightboxGallery($this->Conn,$Config['thumbWidth']['Value'],$Config['thumbHeight']['Value']);
				break;
		}

		// Galerie HTML zurückbekommen
		if (isset($_GET['plain'])) {
			$Gallery->outputPlain();
			exit;
		} else {
			$Gallery->appendHtml($out);
			// Wenn gewünscht als popup anzeigen
			if (isset($_GET['popup']) || $_GET['mode'] == 'popup') {
				$this->Tpl->setPopup();
			}
			// HTML kodieren
			stringOps::htmlViewEnt($out);
			// Ans Template weitergeben
			return($out);
		}
	}
}