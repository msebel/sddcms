<?php
/**
 * Bietet Funktionen für den Medienmanager.
 * @author Michael Sebel <michael@sebel.ch>
 */
class mediaLib extends commonModule {
		
	/**
	 * Objekte laden, überschrieben von Mutterklasse
	 */
	public function loadObjects() {
		// Nichts tun, zwecks kompatitbilität
	}
	
	/**
	 * Checken ob Zugriff auf ein Element erlaubt ist
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param mediaInstance mediaInstance, Referenz zum Options Objekt
	 */
	public static function checkElementAccess(dbConn &$Conn,mediaInstance &$mediaInstance) {
		$nElement = $mediaInstance->Element;
		$bAccess = false; // Grundsätzlich kein Zugriff
		// Element holen
		if ($mediaInstance->Caller != 'editor' && $mediaInstance->Caller != 'content') {
			$sSQL = 'SELECT COUNT(tbelement.ele_ID) AS CountResult FROM tbelement
			INNER JOIN tbmenu ON tbelement.owner_ID = tbmenu.mnu_ID
			WHERE tbmenu.mnu_ID = '.page::menuID().' AND tbelement.ele_ID = '.$nElement;
		} else {
			// Ansonsten versuchen etwas anderes zu matchen
			if (isset($_SESSION['ActualContentID'])) {
				// Mit Content Matchen
				$sSQL = 'SELECT COUNT(tbelement.ele_ID) AS CountResult FROM tbelement
				WHERE tbelement.ele_ID = '.$nElement.'
				AND tbelement.owner_ID = '.getInt($_SESSION['ActualContentID']);
			} else if (isset($_SESSION['ActualOwnerID'])) {
				// Mit Session Owner Matchen
				$sSQL = 'SELECT COUNT(tbelement.ele_ID) AS CountResult FROM tbelement
				WHERE tbelement.ele_ID = '.$nElement.'
				AND tbelement.owner_ID = '.getInt($_SESSION['ActualOwnerID']);
			} else if (isset($_SESSION['Shop:ArticleGroup'])) {
				// Mit einer Artikelgruppe im Shopmodul matchen
				$sSQL = 'SELECT COUNT(tbelement.ele_ID) AS CountResult FROM tbelement
				INNER JOIN tbshoparticlegroup ON tbshoparticlegroup.sag_Image = tbelement.ele_ID
				WHERE tbelement.ele_ID = '.$nElement.' 
				AND tbshoparticlegroup.sag_ID = '.getInt($_SESSION['Shop:ArticleGroup']);
			}
		}
		// Wenn genau ein resultat, Zugriff erlaubt
		if (strlen($sSQL) > 0) {
			$nResult = $Conn->getCountResult($sSQL);
			if ($nResult == 1) $bAccess = true;
		}
		return($bAccess);
	}
	
	/**
	 * Einen Upload bewerkstelligen
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 * @param resources Res, Referenz zum Sprachobjekt
	 */
	public function handleUpload(dbConn &$Conn, mediaInstance &$mediaInstance, resources &$Res) {
		$nElementID = $mediaInstance->Element;
		$Upload = new uploadFile('uploadFile',$nElementID,$Res);
		$bAllowed = $Upload->save();
		// Daten des Uploads holen
		$nSize = $Upload->getSize();
		$sName = $Upload->getFilename();
		$sFolder = $Upload->getFolder();
		// Alles andere nur wenn kein Error
		if ($bAllowed == true) {
			// ZIP Archive entpacken
			$nType = self::getType($sName);
			if (getInt($_POST['unzip'] == 1)) {
				$sName = fileLib::unzipFile($sName,$sFolder);
			}
			$nThumb = self::makeThumb();
			$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
			$nLibrary = self::isLibraryElement($mediaInstance);
			// Bild Dimensionen setzen, wenn Bild
			self::setImageDimensions($mediaInstance,$nType,$sFolder.$sName);
			// Daten direkt ins Element speichern
			$sSQL = "UPDATE tbelement SET
			ele_Size = $nSize, ele_File = '$sName',
			ele_width = ".getInt($mediaInstance->getProperty('width')).", 
			ele_Height = ".getInt($mediaInstance->getProperty('height')).",
			ele_Align = '".$mediaInstance->getProperty('align')."',
			ele_Skin = '".$mediaInstance->getProperty('skin')."',
			ele_Desc = '".$mediaInstance->getProperty('desc')."',
			ele_thumb = $nThumb, ele_date = '$sDate',
			ele_Type = $nType, ele_Library = $nLibrary
			WHERE ele_ID = $nElementID AND ele_Date IS NULL";
			// mediaInstance zurücksetzen für neue Auswahl
			$mediaInstance->resetSelection();
			$Conn->execute($sSQL);
			// Bild Bearbeitungen ausführen und Thumbs erstellen
			if (getInt($_POST['resize']) == 1) {
				self::editPicture($sFolder.$sName);
			}
			// Erfolg melden und zurück
			self::setErrorSession($Res->html(189,page::language()));
			session_write_close();
			redirect('location: /admin/mediamanager/index.php?id='.page::menuID());
		} else {
			// Kein Erfolg ...
			$sError = $Res->html(190,page::language());
			if (strlen($Upload->getError()) > 0) {
				$sError = $Upload->getError();
			}
			self::setErrorSession($sError);
			session_write_close();
			redirect('location: /admin/mediamanager/index.php?id='.page::menuID());
		}
	}
	
	/**
	 * Type der Datei holen anhand Endung
	 * @param string sFile, Name des zu prüfenden Files
	 * @return integer, Konstantencode für Dateityp
	 */
	public function getType($sFile) {
		$sExtension = strtolower(self::getExtension($sFile));
		// Grundsätzlich unbekannt
		$nType = mediaConst::TYPE_UNKNOWN;
		// Switchen und Typ eruieren
		switch ($sExtension) {
			case '.jpg':
			case '.gif':
			case '.png':
				$nType = mediaConst::TYPE_PICTURE; break;
			case '.avi':
			case '.wmv':
			case '.mpg':
			case '.mpeg':
			case '.divx':
				$nType = mediaConst::TYPE_VIDEO; break;
			case '.swf':
				$nType = mediaConst::TYPE_FLASH; break;
			case '.flv':
				$nType = mediaConst::TYPE_FLASHVIDEO; break;
			case '.mp3':
				$nType = mediaConst::TYPE_MUSIC; break;
			default:
				$nType = mediaConst::TYPE_OTHER; break;
		}
		// Typ zurückgeben
		return($nType);
	}
	
	/**
	 * Art des Speicherns wählen
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 * @param string Javascript Funktion für db/js Speicherung
	 */
	public static function getSaveMethod(mediaInstance &$mediaInstance) {
		// Caller switchen
		switch ($mediaInstance->Caller) {
			case 'editor':
				$sMethod = 'jsSave();'; break;
			default:
				$sMethod = 'dbSave();'; break;
		}
		return($sMethod);
	}
	
	/**
	 * Ein Element an den Window öffner per JS zurückgeben
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 * @param string out, Variable für HTML output
	 */
	public static function jsSave(mediaInstance &$mediaInstance, &$out) {
		$sName = $_POST['selectedFile'];
		$mediaInstance->Selected = $sName;
		// Dateityp
		$nType = self::getType($sName);
		$mediaInstance->Type = $nType;
		// Effektiver Pfad zum File
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		$sPath = str_replace("{ELE_ID}",$mediaInstance->Element,$sPath);
		$sPath = BP . $sPath . $sName;
		// Thumbing setzen
		$nThumbed = 0;
		if (fileLib::hasXLImage($sPath)) {
			$nThumbed = 1;
		}
		$mediaInstance->setProperty('thumbed',$nThumbed);
		// Align setzen
		if (strlen($mediaInstance->getProperty('align')) == 0) {
			$mediaInstance->setProperty('align','none');
		}
		// Bild Dimensionen setzen, wenn Bild
		self::setImageDimensions($mediaInstance,$nType,$sPath);
		// Wenn das selektierte File existiert
		if (file_exists($sPath)) {
			// Syntax und Parameter der opener Funktion
			// Alles übergeben, notfalls als leere Strings
			$out .= '
			<script type="text/javascript">
				window.opener.mediamanagerAction(
					\''.$mediaInstance->Selected.'\',
					\''.fileLib::getXLVersion($mediaInstance->Selected).'\',
					\''.$mediaInstance->Type.'\',
					\''.$mediaInstance->getProperty('width').'\',
					\''.$mediaInstance->getProperty('height').'\',
					\''.$mediaInstance->getProperty('desc').'\',
					\''.$mediaInstance->getProperty('thumbed').'\',
					\''.$mediaInstance->getProperty('skin').'\',
					\''.$mediaInstance->getProperty('align').'\'
				);
				window.close();
			</script>
			';
			echo $out;
		}
	}
	
	/**
	 * Ein Element in der Datenbank speichern
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param resources Res, Referenz zum Sprachobjekt
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 */
	public static function dbSave(dbConn &$Conn, resources &$Res, mediaInstance &$mediaInstance) {
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		// File namen holen
		$sName = $_POST['selectedFile'];
		$mediaInstance->Selected = $sName;
		// Effektiver Pfad zum File
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		$sPath = str_replace("{ELE_ID}",$mediaInstance->Element,$sPath);
		$sPath = BP . $sPath . $sName;
		if (file_exists($sPath)) {
			$nType = self::getType($sName);
			$nSize = filesize($sPath);
			// Herausfinden ob Thumb
			$nThumb = 0;
			if (fileLib::hasXLImage($sPath)) {
				$nThumb = 1;
			}
			// Bild Dimensionen setzen, wenn Bild
			self::setImageDimensions($mediaInstance,$nType,$sPath);
			// Daten direkt ins Element speichern
			$sSQL = "UPDATE tbelement SET
			ele_Size = $nSize, ele_File = '$sName',
			ele_width = ".getInt($mediaInstance->getProperty('width')).", 
			ele_Height = ".getInt($mediaInstance->getProperty('height')).",
			ele_Align = '".$mediaInstance->getProperty('align')."',
			ele_Skin = '".$mediaInstance->getProperty('skin')."',
			ele_Desc = '".$mediaInstance->getProperty('desc')."',
			ele_date = '$sDate', ele_Type = $nType, ele_Thumb = $nThumb
			WHERE ele_ID = ".$mediaInstance->Element;
			// mediaInstance zurücksetzen für neue Auswahl
			$mediaInstance->resetSelection();
			$Conn->execute($sSQL);
			// Erfolg melden und zurück
			self::setErrorSession($Res->html(199,page::language()));
			session_write_close();
			redirect('location: /admin/mediamanager/index.php?id='.page::menuID());
		} else {
			// Erfolg melden und zurück
			self::setErrorSession($Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/mediamanager/index.php?id='.page::menuID());
		}
	}
	
	/**
	 * Ein Bild nachträglich bearbeiten
	 * @param resources Res, Referenz zum Sprachobjekt
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 */
	public static function resizePictureSave(resources &$Res, mediaInstance &$mediaInstance) {
		$sFolder = $mediaInstance->getProperty('path');
		self::editPicture(BP.$sFolder.$mediaInstance->Progress);
	}
	
	/**
	 * Elementdaten updaten, wenn nicht editor
	 * @param dbConn Conn, Referenz zum Datenbankobjekt
	 * @param resources Res, Referenz zum Sprachobjekt
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 */
	public static function updateElementData(dbConn &$Conn, resources &$Res, mediaInstance &$mediaInstance,$sRedirect) {
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME);
		// File namen holen
		$sName = $mediaInstance->Progress;
		$mediaInstance->Selected = $mediaInstance->Progress;
		// Effektiver Pfad zum File
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		$sPath = str_replace("{ELE_ID}",$mediaInstance->Element,$sPath);
		$sPath = BP . $sPath . $sName;
		if (file_exists($sPath)) {
			$nType = self::getType($sName);
			$nSize = filesize($sPath);
			// Bild Dimensionen setzen, wenn Bild
			if (fileLib::isImage($sFile)) {
				self::setImageDimensions($mediaInstance,$nType,$sPath);
			}
			// Immer das nicht XL Bild speichern
			if (fileLib::isXLImage($sPath)) {
				$sPath = fileLib::getNonXLVersion($sPath);
				$sName = basename($sPath);
			}
			// Daten direkt ins Element speichern
			$sSQL = "UPDATE tbelement SET
			ele_Size = $nSize, ele_File = '$sName',
			ele_Thumb = ".getInt($mediaInstance->getProperty('thumbed')).",
			ele_width = ".getInt($mediaInstance->getProperty('width')).", 
			ele_Height = ".getInt($mediaInstance->getProperty('height')).",
			ele_Align = '".$mediaInstance->getProperty('align')."',
			ele_Skin = '".$mediaInstance->getProperty('skin')."',
			ele_Desc = '".$mediaInstance->getProperty('desc')."',
			ele_Longdesc = '".$mediaInstance->getProperty('longdesc')."',
			ele_date = '$sDate', ele_Type = $nType
			WHERE ele_ID = ".$mediaInstance->Element;
			// mediaInstance zurücksetzen für neue Auswahl wenn nicht Editor
			if ($mediaInstance->Caller != 'editor') {
				$Conn->execute($sSQL);
				$mediaInstance->resetSelection();
			}
			// Erfolg melden und zurück
			self::setErrorSession($Res->html(202,page::language()));
			session_write_close();
			redirect('location: /admin/mediamanager/'.$sRedirect.'?id='.page::menuID());
		} else {
			// Fehler
			self::setErrorSession($Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/mediamanager/'.$sRedirect.'?id='.page::menuID());
		}
	}
	
	/**
	 * Herausfinden, ob die Extension bekannt ist
	 * @param string sExt, Eingegebebe Extension
	 * @return boolean True, wenn Extension erlaubt ist
	 */
	public static function isKnownExtension($sExt) {
		$bAllowed = false;
		foreach (mediaConst::$AllowedExt as $chkExt) {
			if ($sExt == $chkExt) $bAllowed = true;
		}
		return($bAllowed);
	}
	
	/**
	 * Generiert die Extension aus einem File
	 * @param string sFile, Eingangsdatei
	 * @return string Extension der Eingangsdatei
	 */
	public static function getExtension($sFile) {
		return(substr($sFile,strripos($sFile,'.')));
	}
	
	/**
	 * String für Bildgrösse bekommen
	 * @param string sFile, Bilddatei
	 * @return string Bildgrösse als String (z.B. 800 x600)
	 */
	public static function getImageSize($sFile) {
		$ImageInfo = getimagesize($sFile);
		$sAdd = $ImageInfo[0].' x '.$ImageInfo[1];
		return($sAdd);
	}
	
	/**
	 * Image Dimensionen in mediainstance speichern wenn Bild
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 * @param integer nType, Typ der Datei aus Medienkonstanten
	 * @param string sPath, Bildpfad der zu prüfen ist
	 */
	private function setImageDimensions(mediaInstance &$mediaInstance,$nType,$sPath) {
		// Nur wenn Bild
		if (strlen($mediaInstance->Selected) > 0) {
			if ($nType == mediaConst::TYPE_PICTURE) {
				$ImageInfo = getimagesize(BP.$mediaInstance->getProperty('path').$mediaInstance->Selected);
				$mediaInstance->setProperty('width',$ImageInfo[0]);
				$mediaInstance->setProperty('height',$ImageInfo[1]);
			}
		}
	}
	
	/**
	 * Ein Bild bearbeiten
	 * @param string sName, Name des zu verändernden Bildes
	 */
	private function editPicture($sName) {
		// Parameter holen
		$nKeepQuality = getInt($_POST['keepQuality']);
		$nResizeType = getInt($_POST['resizeType']);
		$nResizeValue = getInt($_POST['resizeValue']);
		$nKeepOriginal = getInt($_POST['keepOriginal']);
		$nSharpPicture = getInt($_POST['sharpPicture']);
		// Grafikobjekt erstellen
		if ($nKeepOriginal == 1) {
			// Original zu einem XL machen und
			// und die kopie bearbeiten
			fileLib::copyXLImage($sName);
		} 
		// Bildobjekt erstellen
		$Img = new imageManipulator($sName);
		// Qualität setzen, wenn nötig
		if ($nKeepQuality == 1) {
			// Naja, nicht ganz 100% Qualität =P
			$Img->setQuality(95);
		}
		// Bild vergrössern / Verkleinern
		self::resizePicture($Img,$nResizeValue,$nResizeType);
		// Bild optimieren
		if ($nSharpPicture == 1) {
			//$Img->optimize();
		}
	}
	
	/**
	 * Bild zuschneiden
	 * @param imageManipulator Img, Objekt zur Bildmanipulation
	 * @param integer nResizeValue, Verkleinerungswert
	 * @param integer nResizeType, Resize Type aus Medienkonstanten
	 */
	public static function resizePicture(imageManipulator &$Img,$nResizeValue,$nResizeType) {
		if ($nResizeValue < 10) $nResizeValue = mediaConst::THUMB_WIDTH;
		switch ($nResizeType) {
			case mediaConst::BY_WIDTH:
				// Die Breite muss nResizeValue sein
				$nHeight = $Img->getAspectOf($nResizeValue,imageManipulator::WIDTH);
				$Img->Resize($nResizeValue,$nHeight);
				break;
			case mediaConst::BY_HEIGHT:
				// Die Höhe muss nResizeValue sein
				$nWidth = $Img->getAspectOf($nResizeValue,imageManipulator::HEIGHT);
				$Img->Resize($nWidth,$nResizeValue);
				break;
			case mediaConst::BY_LONGEREDGE:
				list($nWidth, $nHeight) = getimagesize($sName);
				// Resize anhand der Breite, wenn die Breite grösser ist
				if ($nWidth > $nHeight) {
					$nHeight = $Img->getAspectOf($nResizeValue,imageManipulator::WIDTH);
					$Img->Resize($nResizeValue,$nHeight);
				} else {
					// Ansonsten anhand der Höhe
					$nWidth = $Img->getAspectOf($nResizeValue,imageManipulator::HEIGHT);
					$Img->Resize($nWidth,$nResizeValue);
				}
				break;
			case mediaConst::BY_PERCENT:
				$Img->ResizePercentual($nResizeValue);
				break;
		}
	}
	
	/**
	 * Bestimmen ob ein Thumb erstellt werden muss.
	 * @return integer Antwort mit 1 oder 0 für die Datenbank
	 */
	private function makeThumb() {
		$nThumb = getInt($_POST['keepOriginal']);
		return($nThumb);
	}
	
	/**
	 * Eruieren ob ein Bibliothekselement hochgeladen wurde
	 * @param mediaInstance mediaInstance, Optionsobjekt
	 * @return integer 1 wenn es ein Library Objekt ist
	 */
	private function isLibraryElement(mediaInstance &$mediaInstance) {
		$nReturn = 0;
		if ($mediaInstance->Caller == 'library') {
			$nReturn = 1;
		}
		return($nReturn);
	}
}