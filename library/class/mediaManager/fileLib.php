<?php
/**
 * Bietet Funktionen um mit Dateien zu arbeiten.
 * @author Michael Sebel <michael@sebel.ch>
 */
class fileLib extends commonModule {
	
	/**
	 * Speichermethode (Formular oder JS)
	 * @var string
	 */
	public static $SaveMethod;
	
	/**
	 * Objekte laden, überschrieben von Mutterklasse
	 */
	public function loadObjects() {
		// Nichts tun, zwecks kompatibilität
	}
	
	/**
	 * Ein File löschen
	 * @param mediaInstance mediaInstance, Optionsobjekt des Medienmanagers
	 * @param resources Res, Referenz zum Sprachobjekt
	 */
	public static function deleteFile (mediaInstance &$mediaInstance, resources $Res) {
		// Pfad der Datei definieren
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		$sPath = str_replace("{ELE_ID}",$mediaInstance->Element,$sPath);
		// Effektiven Filenamen bauen
		$sFile = BP.$sPath.$_GET['delete'];
		// Wenn das File existiert, löschen
		if (file_exists($sFile)) {
			unlink($sFile);
			// XL Version auch löschen wenn vorhanden
			if (self::hasXLImage($sFile)) {
				unlink(self::getXLVersion($sFile));
			}
		}
		// Erfolg melden und zurück
		self::setErrorSession($Res->html(188,page::language()));
		session_write_close();
		redirect('location: /admin/mediamanager/index.php?id='.page::menuID());
	}
	
	/**
	 * Files eines Element Ordners bekommen
	 * @param integer nElementID, ID des Elementes in der Datenbank
	 * @return array Array aller Files im Elementordner
	 */
	public static function getFileList($nElementID) {
		// Ordner erstellen und öffnen
		$sFolder = BP.mediaConst::FILEPATH;
		$sFolder = str_replace('{PAGE_ID}',page::ID(),$sFolder);
		$sFolder = str_replace('{ELE_ID}',$nElementID,$sFolder);
		// Ordner erstellen, wenn nicht vorhanden
		if (!file_exists($sFolder)) {
			mkdir($sFolder, 0755, true);
		}
		$arrFiles = array();
		// Folder durchgehen
		if ($resDir = opendir($sFolder)) {
	        while (($sFile = readdir($resDir)) !== false) {
	        	if (filetype($sFolder . $sFile) == 'file') {
	        		array_push($arrFiles,$sFolder.$sFile);
	        	}
	        }
	    }
	    // Ordner schliessen und Rückgabe
	    closedir($resDir);
	    return($arrFiles);
	}
	
	/**
	 * Files anzeigen mit HTML code
	 * @param array arrFiles, Array der anzuzeigenden Files
	 * @param string out, Variable für HTML Output
	 * @param mediaInstance mediaInstance, Optionsobjekt des Medienmanagers
	 * @param resources Res, Referenz zum Sprachobjekt
	 */
	public static function showFiles(&$arrFiles,&$out, mediaInstance &$mediaInstance, resources &$Res) {
		$sSelected = self::getSelectedFile($arrFiles,$mediaInstance);
		self::$SaveMethod = mediaLib::getSaveMethod($mediaInstance);
		// Startpointer auf (3) setzen, damit eine neue Zeile
		// gleich zu Anfang simuliert wird
		$nCount = mediaConst::FILESPERPAGE;
		// Filetyp herausfinden
		foreach ($arrFiles as $sFile) {
			$nType = mediaLib::getType($sFile);
			// Switchen und anzeigen
			switch ($nType) {
				case mediaConst::TYPE_PICTURE:
					// Bild mit bearbeitungsoptionen
					$bVisible = self::showImageFile($sFile,$out,$Res,$sSelected); break;
				case mediaConst::TYPE_FLASH:
					// Mini Flash mit bearbeitungsoptionen
					$bVisible = self::showFlashFile($sFile,$out,$Res,$sSelected); break;
				case mediaConst::TYPE_FLASHVIDEO:
					// Mini Flashvideo mit bearbeitungsoptionen
					$bVisible = self::showFlashVideo($sFile,$out,$Res,$sSelected); break;
				case mediaConst::TYPE_MUSIC:
					// Musikfile mit Playeroptionen
					$bVisible = self::showMusicFile($sFile,$out,$Res,$sSelected); break;
				case mediaConst::TYPE_VIDEO:
					// Videofile, evtl. mit Playerfunktionen
					$bVisible = self::showVideoFile($sFile,$out,$Res,$sSelected); break;
				default:
					// Normales File mit Icon anzeigen
					$bVisible = self::showOtherFile($sFile,$out,$Res,$sSelected); break;
			}
			// Wenn nicht sichtbar, zurückzählen
			if ($bVisible == true) {
				$nCount++;
				// Zeilen Anfang und Ende zeichnen
				if ($nCount % mediaConst::FILESPERPAGE == 0 && $nCount != mediaConst::FILESPERPAGE) $out .= '</tr>';
				if ($nCount % mediaConst::FILESPERPAGE == 0) $out .= '<tr>';
			}
		}
		// Weitere leere Zellen bis zum nächsten TR
		if (($nCount) % mediaConst::FILESPERPAGE != 0) {
			$bEnd = false;
			$sColor = option::get('mmCellBackground');
			if ($sColor == NULL) $sColor = '#eee';
			while ($nCount % mediaConst::FILESPERPAGE != 0) {
				$out .= '
				<td style="background-color:'.$sColor.';width:33%">
					&nbsp;
				</td>';
				$nCount++;
			}
			$out .= '</tr>';
		}
	}
	
	/**
	 * Aktuelles File herausfinden, damit es direkt selektiert wird.
	 * @param array arrFiles, Array aller Files
	 * @param mediaInstance mediaInstance, Optionsobjekt des Medienmanagers
	 * @return string Name des selektierten Files
	 */
	public static function getSelectedFile(&$arrFiles, mediaInstance &$mediaInstance) {
		$sSelected = '';
		// Alle Files durchgehen
		foreach ($arrFiles as $File) {
			$File = basename($File);
			if ($File == $mediaInstance->Selected) {
				$sSelected = $File;
			}
		}
		return($sSelected);
	}
	
	/**
	 * Bildpfad zurückgeben
	 * @param string File, Bildpfad
	 * @param boolean getXL, angabe ob dessen XL File geholt werden soll
	 * @return string gewünschter Dateiname ohne Basispfad
	 */
	public static function getImagePath($File,$getXL) {
		// XL File holen, wenn vorhanden, sonst nix tun
		if ($getXL == true) {
			$sXLFile = fileLib::getXLVersion($File);
			if ($sXLFile != NULL) {
				$File = $sXLFile;
			}
		}
		// Basepath entfernen für Darstellung
		$nOffset = strlen(BP);
		$File = substr($File,$nOffset);
		return($File);
	}
	
	/**
	 * XL Version eines Bild holen
	 * @param string sFilename, Bild dessen XL Version zu holen ist
	 * @return string Name des XL Bildes
	 */
	public static function getXLVersion ($sFilename) {
		// Extension eruieren und XL damit ersetzen
		$sExt = mediaLib::getExtension($sFilename);
		$sXLFile = str_replace($sExt,mediaConst::XL_SUFFIX.$sExt,$sFilename);
		return($sXLFile);
	}
	
	/**
	 * Nicht XL Version eines XL Bildes bekommen
	 * @param string sFilename, Bild dessen nicht-XL Version zu holen ist
	 * @return string Name des nicht-XL Bildes
	 */
	public static function getNonXLVersion ($sFilename) {
		// Extension eruieren und XL damit ersetzen
		$sExt = mediaLib::getExtension($sFilename);
		$sXLFile = '';
		if (stristr($sFilename,mediaConst::XL_SUFFIX.$sExt) !== false) {
			$sXLFile = str_replace(mediaConst::XL_SUFFIX.$sExt,$sExt,$sFilename);
		}
		return($sXLFile);
	}
	
	/**
	 * Herausfinden, ob das betreffende Bild eine XL Version hat
	 * @param string sFilename, zu prüfende Datei
	 * @return boolean True, wenn es ein XL Bild gibt
	 */
	public static function hasXLImage ($sFilename) {
		$bReturn = false;
		// Nur wenn File erreichbar
		if (file_exists(self::getXLVersion($sFilename))) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Herausfinden, ob das Bild eine XL Version eines Bildes ist
	 * @param string sFilename, zu prüfende Datei
	 * @return boolean True, wenn es ein XL Bild ist
	 */
	public static function isXLImage ($sFilename) {
		$bReturn = false;
		// Nur wenn File erreichbar
		if (file_exists(self::getNonXLVersion($sFilename))) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Eine kopie eines Files erstellen und die kopie nach dessen XL File benennen
	 * @param string sFilename, zu kopierendes Originalbild
	 */
	public static function copyXLImage ($sFilename) {
		if (self::isValidPath($sFilename) == true) {
			$sXLFile = self::getXLVersion($sFilename);
			// Wenn das Quellfile kein XL File ist
			if (!self::isXLImage($sFilename)) {
				copy($sFilename,$sXLFile);
			}
		}
	}
	
	/**
	 * Prüft ob der Pfad mit dem basepath (BP) beginnt.
	 * Um sicherzustellen, dass Fileoperationen funktionieren.
	 * @param string sFilename, zu prüfender Bilderpfad
	 * @param boolean True, wenn der Pfad in Ordnung ist
	 */
	public static function isValidPath ($sFilename) {
		$nOffset = strlen(BP);
		$bReturn = false;
		if (substr($sFilename,0,$nOffset) == BP) {
			$bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * HTML für Toolbar zurückgeben, wenn ein XL File vorhanden ist
	 * @param string File, Name des zu prüfenden Files
	 * @param resources Res, Sprachressourcenobjekt
	 * @return string HTML Code für die Toolbar
	 */
	public static function getXLFileHtml($File,resources &$Res) {
		$sHtml = '';
		if (self::hasXLImage($File)) {
			$sHtml = '<td class="cNav" width="150">
			<a href="imgXLResize.php?id='.page::menuID().'&file='.self::getXLVersion(basename($File)).'">
			'.$Res->html(201,page::language()).'
			</a></td>';
		}
		return($sHtml);
	}
	
	/**
	 * Zip Archiv entpacken und ersten Fund zurückgeben
	 * @param string sFile, zu entpackende Datei
	 * @param string sFolder, Destinations Ordner
	 * @return string Name des letzten entpackten Files
	 */
	public static function unzipFile($sFile,$sFolder) {
		$zipRes = zip_open($sFolder.$sFile);
		$sReturn = $sFile;
		if (!is_int($zipRes)) {
			while ($zipEntry = zip_read($zipRes)) {
				$sName = zip_entry_name($zipEntry);
				// Prüfen dass es kein Ordner ist
				if ($sName[strlen($sName)-1] != '/') {
					$sName = strtolower(basename($sName));
					self::sanitizeFilename($sName);
					if (mediaLib::isKnownExtension(mediaLib::getExtension($sName))) {
						// Namen zum zurückgeben speichern
						$sReturn = $sName;
						$DataAppender = '';
						while ($Data = zip_entry_read($zipEntry)) {
							$DataAppender .= $Data;
						}
						file_put_contents($sFolder.$sName,$DataAppender);
						unset($DataAppender);
					}
				}
			}
			// Archiv schliessen und lüschen
			zip_close($zipRes);
			unlink($sFolder.$sFile);
		}
		return($sReturn);
	}
	
	/**
	 * Filename flicken, wenn schwer verträgliche Zeichen
	 * @param string sName, Name der zu sanitierenden Datei
	 */
	public static function sanitizeFilename(&$sName) {
		stringOps::alphaNumFiles($sName);
	}
	
	/**
	 * Prüft ob es sich um ein Image handelt
	 * @param string sFile, Name des zu prüfenden Bildes
	 * @return boolean True, wenn es ein Bild ist
	 */
	public static function isImage($sFile) {
		$bReturn = false;
		switch (mediaLib::getExtension($sFile)) {
			case 'jpg':
			case 'gif':
			case 'png': $bReturn = true;
		}
		return($bReturn);
	}
	
	/**
	 * Ein Bild als HTML mit Optionen anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showImageFile(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->editFile = 'images/imgResize.php';
		$displayObject->viewFile = 'images/imgView.php';
		$displayObject->editable = true;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Grösse des Bildes eruieren
		$ImageInfo = getimagesize($File);
		$sAdd = $ImageInfo[0].' x '.$ImageInfo[1];
		if (fileLib::hasXLImage($File)) {
			$sAdd .= '&nbsp;&nbsp;&nbsp;
			<img src="/images/icons/zoom_in.png" alt="'.$Res->html(200,page::language()).'" title="'.$Res->html(200,page::language()).'">';
		}
		$displayObject->additionalInfo = $sAdd;
		// HTML Code für die Darstellung
		$sRandom = stringOps::getRandom(50);
		$displayObject->displayHtml = '
		<img src="'.fileLib::getImagePath($File,false).'?'.$sRandom.'" border="0" width="'.mediaConst::THUMB_WIDTH.'">
		';
		// Anzeigbar?
		$bVisible = false;
		if (!fileLib::isXLImage($File)) {
			$bVisible = true;
			$out .= $displayObject->getHtml($Res);
		}
		return($bVisible);
	}
	
	/**
	 * Ein Flashfile als HTML mit Optionen anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showFlashFile(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->editFile = 'flash/swfEdit.php';
		$displayObject->viewFile = 'flash/swfView.php';
		$displayObject->editable = true;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Zusatzinformation
		$sAdd = $Res->html(194,page::language());
		$displayObject->additionalInfo = $sAdd;
		// HTML Code für die Darstellung
		$displayObject->displayHtml = '
		<img src="/images/media/swf.gif" border="0">
		';
		$out .= $displayObject->getHtml($Res);
		return(true);
	}
	 
	/**
	 * Ein Flashvideo als HTML mit Optionen anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showFlashVideo(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->editFile = 'flash/flvEdit.php';
		$displayObject->viewFile = 'flash/flvView.php';
		$displayObject->editable = true;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Zusatzinformation
		$sAdd = $Res->html(195,page::language());
		$displayObject->additionalInfo = $sAdd;
		// HTML Code für die Darstellung
		$displayObject->displayHtml = '
		<img src="/images/media/flv.gif" border="0">
		';
		$out .= $displayObject->getHtml($Res);
		return(true);
	}
	
	/**
	 * Ein Musikfile als HTML mit Optionen anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showMusicFile(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->editFile = 'audio/audioEdit.php';
		$displayObject->viewFile = 'audio/audioView.php';
		$displayObject->editable = true;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Zusatzinformation
		$sAdd = $Res->html(196,page::language());
		$displayObject->additionalInfo = $sAdd;
		// HTML Code für die Darstellung
		$displayObject->displayHtml = '
		<img src="/images/media/mp3.gif" border="0">
		';
		$out .= $displayObject->getHtml($Res);
		return(true);
	}
	
	/**
	 * Ein Videofile als HTML mit Optionen anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showVideoFile(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->viewFile = 'other/download.php';
		$displayObject->editable = false;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Zusatzinformation
		$sAdd = $Res->html(197,page::language());
		$displayObject->additionalInfo = $sAdd;
		// HTML Code f�r die Darstellung
		$displayObject->displayHtml = '
		<img src="/images/media/video.gif" border="0">
		';
		$out .= $displayObject->getHtml($Res);
		return(true);
	}
	
	/**
	 * Ein anderes file als HTML und Icon anzeigen.
	 * @param string File, Name de darzustellenden Files
	 * @param string out, Variable in die HTML angehängt wird
	 * @param resources Res, Referenz zum Sprachobject
	 * @param string sSelected, Name des selektierten Files
	 * @return boolean True, wenn die Datei sichtbar ist
	 */
	public static function showOtherFile(&$File,&$out,resources &$Res,$sSelected) {
		$displayObject = new displayObject(self::$SaveMethod);
		// Properties setzen
		$displayObject->fileName = basename($File);
		$displayObject->fileSize = filesize($File);
		$displayObject->viewFile = 'other/download.php';
		$displayObject->editable = false;
		// Schauen ob selektiert
		if (basename($File) == $sSelected) {
			$displayObject->isSelected = true;
		}
		// Zusatzinformation
		$sAdd = $Res->html(198,page::language());
		$displayObject->additionalInfo = $sAdd;
		// Extension ohne punkt
		$sExt = substr(mediaLib::getExtension($File),1);
		if (!mediaLib::isKnownExtension('.'.$sExt)) $sExt = 'unknown';
		// HTML Code f�r die Darstellung
		$displayObject->displayHtml = '
		<img src="/images/media/'.$sExt.'.gif" border="0">
		';
		$out .= $displayObject->getHtml($Res);
		return(true);
	}
}