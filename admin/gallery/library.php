<?php
// Konstanten aus der View einbinden
require_once(BP.'/modules/gallery/galleryConst.php');

class moduleGallery extends commonModule {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	// Gallery Konfig initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,4)) {
			pageConfig::setConfig($nMenuID,$this->Conn,140,pageConfig::TYPE_NUMERIC,'thumbWidth',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'thumbHeight',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,galleryConst::TYPE_LIGHTBOX,pageConfig::TYPE_NUMERIC,'mode',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig($Config) {
		// Menu ID zwischenspeichern
		$nMenuID = page::menuID();
		// Parameter "Mode" holen
		$Mode = getInt($_POST['mode']);
		switch ($Mode) {
			case galleryConst::TYPE_SIMPLEVIEWER:
			case galleryConst::TYPE_TILTVIEWER:
			case galleryConst::TYPE_LIGHTBOX:
				break;
			// Wenn keiner der Typen zutrifft kommt Lightbox als default
			default:
				$Mode = galleryConst::TYPE_LIGHTBOX;
				break;
		}
		$Config['mode']['Value'] = $Mode;
		// Parameter "htmlCode" holen
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Erfolg speichern und weiterleiten
		logging::debug('saved gallery config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/gallery/index.php?id='.page::menuID()); 
	}
	
	// Thumbs generieren (immer komplett neu, vorher alle löschen)
	public function makeThumbs() {
		// Thumbgrösse holen
		$thumbWidth = getInt($_POST['thumbWidth']);
		if ($thumbWidth < 0) $thumbWidth = 0;
		$thumbHeight = getInt($_POST['thumbHeight']);
		if ($thumbHeight < 0) $thumbHeight = 0;
		// Files eruieren
		$Files = $this->getFilelist();
		$nElement = galleryConst::getMenuElement($this->Conn);
		$sFolder = BP.'/page/'.page::ID().'/element/'.$nElement.'/';
		// Timeout fürs Generieren auf 5 Minuten
		set_time_limit((5 * 60));
		foreach ($Files as $File) {
			// Thumb Pfad löschen wenn existent
			$Thumb = $sFolder.'tmb_'.$File->Filename;
			$Orig  = $sFolder.$File->Filename;
			if (file_exists($Thumb)) {
				unlink($Thumb);
			}
			// Thumbnail von original kopieren
			copy($Orig,$Thumb);
			// Thumb verkleinern auf gegebene grösse
			$Img = new imageManipulator($Thumb);
			$this->resizeImage($Img,$thumbWidth,$thumbHeight);
			unset($Img);
			// thumbWidth in der Konfiguration speichern für Anzeige
			$Config = array();
			pageConfig::get(page::menuID(),$this->Conn,$Config);
			if ($thumbWidth > 0) {
				$Config['thumbWidth']['Value'] = $thumbWidth;
				$Config['thumbHeight']['Value'] = '';
			} else {
				$Config['thumbHeight']['Value'] = $thumbHeight;
				$Config['thumbWidth']['Value'] = '';
			}
			pageConfig::saveConfig(page::menuID(),$this->Conn,$Config);
		}
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(384,page::language()));
		session_write_close();
		redirect('location: /admin/gallery/index.php?id='.page::menuID()); 
	}
	
	// Alle Bilder auf bestimmte grösse Skalieren, egal wie gross Sie sind
	public function resizeImages() {
		// Thumbgrösse holen
		$picWidth = getInt($_POST['pictureWidth']);
		if ($picWidth < 0) $picWidth = 0;
		$picHeight = getInt($_POST['pictureHeight']);
		if ($picHeight < 0) $picHeight = 0;
		// Files eruieren
		$Files = $this->getFilelist();
		$nElement = galleryConst::getMenuElement($this->Conn);
		$sFolder = BP.'/page/'.page::ID().'/element/'.$nElement.'/';
		// Timeout fürs Generieren auf 5 Minuten
		set_time_limit((5 * 60));
		foreach ($Files as $File) {
			// Bild verkleinern auf gegebene grösse
			$Img = new imageManipulator($sFolder.$File->Filename);
			$this->resizeImage($Img,$picWidth,$picHeight);
			unset($Img);
		}
		// Erfolg melden und weiterleiten
		$this->setErrorSession($this->Res->html(385,page::language()));
		session_write_close();
		redirect('location: /admin/gallery/index.php?id='.page::menuID()); 
	}
	
	// Alle Bilderdaten holen
	public function getFileData(&$Data) {
		// Collection aus GalleryFiles holen
		$Data = $this->getFileList();
		// Laden der Texte und matchen
		$sSQL = "SELECT gal_Text,gal_File FROM tbgallery 
		WHERE mnu_ID = ".page::menuID();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Direktes Matching
			for ($i = 0;$i < count($Data);$i++) {
				if ($Data[$i]->Filename == $row['gal_File']) {
					$Data[$i]->Description = $row['gal_Text'];
					break;
				}
			}
		}
	}
	
	// Speichern der Texte für Bilder
	public function saveText() {
		for ($i = 0;$i < count($_POST['filename']);$i++) {
			// Daten zwischenspeichern
			$Filename = $_POST['filename'][$i];
			$Filetext = $_POST['filetext'][$i];
			// Daten validieren
			stringOps::noHtml($Filename); 
			stringOps::noHtml($Filetext);
			stringOps::htmlEntRev($Filename);
			stringOps::htmlEntRev($Filetext);
			$this->Conn->escape($Filename);
			$this->Conn->escape($Filetext);
			// Wenn der Text leer ist, anhand des Namens Record löschen
			if (strlen($Filetext) == 0) {
				$sSQL = "DELETE FROM tbgallery WHERE
				gal_File = '$Filename' AND mnu_ID = ".page::menuID();
				$this->Conn->command($sSQL);
			} else {
				// Ansonsten, schauen, ob es schon einen Record gibt
				$sSQL = "SELECT gal_ID FROM tbgallery WHERE
				gal_File = '$Filename' AND mnu_ID = ".page::menuID();
				$nGalID = getInt($this->Conn->getFirstResult($sSQL));
				if ($nGalID == 0) {
					// Neuen Record für das File erstellen
					$sSQL = "INSERT INTO tbgallery (mnu_ID,gal_Text,gal_File)
					VALUES (".page::menuID().",'$Filetext','$Filename')";
					$this->Conn->command($sSQL);
				} else {
					// Bestehenden Record aktualisieren
					$sSQL = "UPDATE tbgallery 
					SET gal_Text = '$Filetext'
					WHERE gal_ID = $nGalID";
					$this->Conn->command($sSQL);
				}
			}
		}
		// Erfolg melden und Weiterleiten
		logging::debug('saved gallery texts');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/gallery/text.php?id='.page::menuID());
	}
	
	// Thumbnail eines Bildes erstellen
	public function getThumb() {
		$sFile = stringOps::getGetEscaped('file',$this->Conn);
		$nElement = galleryConst::getMenuElement($this->Conn);
		$sFolder = BP.'/page/'.page::ID().'/element/'.$nElement.'/';
		// Wenn das File existiert, Output
		if (file_exists($sFolder.$sFile)) {
			// Extension des Files holen, Punkt entfernen
			$sExt = stringOps::getExtension($sFile);
			$sExt = substr($sExt,1);
			// Header erstellen
			header('Content-Type: image/'.$sExt);
			header('Content-Disposition: inline; filename='.$sFile);
			header('Content-Length: '.getInt(filesize(BP.$sFolder.$sFile)));
			header('Content-Transfer-Encoding: binary');
			// Bild lesen und ausgeben
			$Image = new imageManipulatorToBrowser($sFolder.$sFile);
			$Image->Resize(50,50);
		}
	}
	
	// Bild verkleinern
	private function resizeImage(imageManipulator &$Img, $nWidth, $nHeight) {
		// Nur etwas tun wenn nicht beide 0
		if (!($nWidth == 0 && $nHeight == 0)) {
			// AspectOf Breite holen
			if ($nWidth == 0) {
				$nWidth = $Img->getAspectOf($nHeight,imageManipulator::HEIGHT);
			}
			// AspectOf Höhe holen
			if ($nHeight == 0) {
				$nHeight = $Img->getAspectOf($nWidth,imageManipulator::WIDTH);
			}
			// Bild resizen
			$Img->Resize($nWidth,$nHeight);
		}
	}
	
	// Gibt Array aller Galleryfiles zurück
	private function getFilelist() {
		// Klasse inkludieren
		require_once(BP.'/modules/gallery/GalleryFile.php');
		$Files = array();
		$nElement = galleryConst::getMenuElement($this->Conn);
		$sFolder = BP.'/page/'.page::ID().'/element/'.$nElement.'/';
		if (file_exists($sFolder)) {
			// Folder durchgehen
			if ($this->ResDir = opendir($sFolder)) {
		        while (($sFile = readdir($this->ResDir)) !== false) {
		        	if (filetype($sFolder.$sFile) == 'file') {
		        		$GalleryFile = new GalleryFile($sFile,$sFolder);
		        		if ($GalleryFile->isValid) {
		        			array_push($Files,$GalleryFile);
		        		}
		        	}
		        }
		    }
		    // Ordner schliessen und rückgabe
		    closedir($this->ResDir);
		}
		return($Files);
	}
}