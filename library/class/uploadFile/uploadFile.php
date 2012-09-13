<?php
// Media Konstanten für File Extensions laden
require_once(BP.'/library/class/mediaManager/mediaConst.php');

/**
 * Bietet Funktionen zum Upload von Dateien.
 * Wird primär für den Upload von Elementen benutzt,
 * derzeit entsprechend nur im Mediamanager.
 * @author Michael Sebel <michael@sebel.ch>
 */
class uploadFile {
	
	/**
	 * Name des Formularfeldes mit FILE Attribut
	 * @var string
	 */
	private $FormFile;
	/**
	 * Name des Files zum Speichern
	 * @var string
	 */
	private $FileName;
	/**
	 * Element ID des Files
	 * @var integer
	 */
	private $ElementID;
	/**
	 * Wenn false, findet kein Upload statt
	 * @var boolean
	 */
	private $Allowed = false;
	/**
	 * Grösse der Datei in Bytes
	 * @var integer
	 */
	private $FileSize = 0;
	/**
	 * Ordner des upzuloadenden Files
	 * @var string
	 */
	private $Folder;
	/**
	 * Fehlermeldung
	 * @var string
	 */
	private $ErrorMessage;
	/**
	 * Ressourcen Objekt (REferenz)
	 * @var resources
	 */
	private $Res = null;
	
	/**
	 * Konstruktor, Formularfeld und Filename.
	 * Es wird davon ausgegangen, dass nElementID secured wurde.
	 * Eine entsprechende Funktion existiert in der mediaLib.
	 * @param string sFile, Name des Formularfelder mit File
	 * @param integer nElementID, Zu speichernde Element ID
	 * @param resource Res, Referenz zum Sprachobjekt
	 */
	public function __construct($sFile,$nElementID, resources &$Res) {
		$this->ElementID = $nElementID;
		$this->FormFile = $sFile;
		$this->Res = $Res;
		// Vorbereiten und Upload testen
		$this->setOriginalFilename();
		$this->setFolderName();
		$this->checkAllowed();
		$this->checkSize();
		// Nur weiter, wenn die Datei in Ordnung ist
		if ($this->Allowed == true) {
			$this->FileSize = $_FILES[$this->FormFile]['size'];
			$this->createFolder();
		}
	}
	
	/**
	 * Neuen Filenamen setzen.
	 * @param string sName, Neuer Name für das File
	 */
	public function setFilename($sName) {
		$this->sanitizeFilename($sName);
		$this->FileName = $sName;
	}
	
	/**
	 * File effektiv Speichern im Elementordner.
	 * Gibt einen Error zurück, wenn das File nicht gespeichert wurde
	 * @return boolean True, wenn das Bild gespeichert wurde
	 */
	public function save() {
		$sSource = $_FILES[$this->FormFile]['tmp_name'];
		// Destination, immer klein geschrieben
		$sDestination = $this->Folder . $this->FileName;
		if ($this->Allowed == true) {
			if (!move_uploaded_file($sSource,$sDestination)) {
				$this->Allowed = false;
			}
		}
		// Status der Erlaubnis zurückgeben
		return($this->Allowed);
	}
	
	/**
	 * Gibt die grösse des Files in Bytes zurück
	 * @return integer Dateigrösse in Bytes
	 */
	public function getSize() {
		return($this->FileSize);
	}
	
	/**
	 * Gibt den Namen des Ordners zurück
	 * @return string Name des Ordners in dem das File gespeichert wurde
	 */
	public function getFolder() {
		return($this->Folder);
	}
	
	/**
	 * Filenamen zurückgeben
	 * @return string Name des hochgeladenen Files
	 */
	public function getFilename() {
		return($this->FileName);
	}
	
	/**
	 * Error zurückgeben
	 * @return string Error-Nachricht
	 */
	public function getError() {
		return($this->ErrorMessage);
	}
	
	/**
	 * Grösse des hochgeladenen Files prüfen.
	 * Interne Variable Allowed wird gesetzt.
	 */
	public function checkSize() {
		// Filegrösse herausfinden
		$nSize = filesize($_FILES[$this->FormFile]['tmp_name']);
		$nSize = (int) $nSize / 1024;
		// Extension herausfinden
		$sExtension = strtolower(substr($this->FileName, strripos($this->FileName, '.')));
		// Wenn Bilder, weniger möglich
		switch ($sExtension) {
			case '.jpg':
			case '.gif':
			case '.png':
				if ($nSize > mediaConst::MAXSIZE_GRAPHICS) {
					$this->Allowed = false;
					// Fehlermeldung
					$this->ErrorMessage = $this->Res->html(312,page::language());
					$this->ErrorMessage .= ' '.mediaConst::MAXSIZE_GRAPHICS.' KB';
				}
				break;
			default:
				if ($nSize > mediaConst::MAXSIZE_FILES) {
					$this->Allowed = false;
					$this->ErrorMessage = $this->Res->html(313,page::language());
					$this->ErrorMessage .= ' '.mediaConst::MAXSIZE_FILES.' KB';
				}
				break;
		}
	}
	
	/**
	 * Den Elementordner erstellen, wenn er nicht existiert
	 */
	private function createFolder() {
		if (!file_exists($this->Folder)) {
			mkdir($this->Folder, 0755, true);
		}
	}
	
	/**
	 * Den Elementordner benennen
	 */
	private function setFolderName() {
		$sPath = mediaConst::FILEPATH;
		// Page ID und ele ID ersetzen
		$sPath = str_replace('{PAGE_ID}',page::ID(),$sPath);
		$sPath = str_replace('{ELE_ID}',$this->ElementID,$sPath);
		// Pfad setzen inklusive Basepath
		$this->Folder = BP.$sPath;
	}
	
	/**
	 * Prüfen ob das File erlaubt ist
	 * Interne Variable Allowed wird gesetzt.
	 */
	private function checkAllowed() {
		// Fehler prüfen, wenn keiner, upload erlaubt
		if ($_FILES[$this->FormFile]['error'] == 0) {
			$this->Allowed = true;
		}
		// Aber wenn es ein unechtes Files ist, nicht erlaubt
		$sExtension = strtolower(substr($this->FileName, strripos($this->FileName, '.')));
		$bCheckForImage = false;
		switch ($sExtension) {
			case '.gif': $bCheckForImage = true; break;
			case '.jpg': $bCheckForImage = true; break;
			case '.png': $bCheckForImage = true; break;
			// Ansonsten einfach die Extension prüfen
			default: $this->Allowed = $this->checkExtension($sExtension);
		}
		// Image prüfen wenn nötig
		if ($bCheckForImage == true) {
			$this->Allowed = $this->checkImage();
		}
	}
	
	/**
	 * Extension anhand der Medienkonstanten prüfen
	 * @param string sExt, Dateiendung der hochgeladenen Datei
	 * @return boolean True, wenn das File erlaubt ist
	 */
	private function checkExtension($sExt) {
		$bAllowed = false;
		foreach (mediaConst::$AllowedExt as $chkExt) {
			if ($sExt == $chkExt) {
				$bAllowed = true;
			} 
		}
		if (!$bAllowed) $this->ErrorMessage = $this->Res->html(314,page::language());
		return($bAllowed);
	}
	
	/**
	 * Prüfen ob ein eventuelles Bild wirklich ein Bild ist
	 * @return boolean True, wenn es tatsächlich ein Bild ist
	 */
	private function checkImage() {
		$isImage = false;
		// Ordner erstellen, wenn nicht vorhanden 
		$this->createFolder();
		// Bild temporär erreichbar machen
		$sExtension = substr($this->FileName, strripos($this->FileName, '.'));
		$sFilename = md5($this->FileName).$sExtension;
		copy($_FILES[$this->FormFile]['tmp_name'],$this->Folder.$sFilename);
		$ImageInfo = getimagesize($this->Folder.$sFilename);
		// Bild gleich wieder löschen
		unlink($this->Folder.$sFilename);
		// Grösse des Bildes abklären, wenn 0 = kein Bild
		if ($ImageInfo[0] > 0 && $ImageInfo[1] > 0) {
			$isImage = true;
		} else {
			$this->ErrorMessage = $this->Res->html(314,page::language());
		}
		return($isImage);
	}
	
	/**
	 * Originalen Filename setzen
	 */
	private function setOriginalFilename() {
		$sName = $_FILES[$this->FormFile]['name'];
		$this->sanitizeFilename($sName);
		$this->FileName = basename($sName);
	}
	
	/**
	 * Filename flicken, wenn schwer verträgliche Zeichen
	 * @param string sName, Name der Datei
	 */
	private function sanitizeFilename(&$sName) {
		// Nur Alphanumerische Zeichen
		stringOps::alphaNumFiles($sName);
		// jpeg zu jpg umwandeln
		if (stringOps::endsWith($sName, '.jpeg')) {
			$sName = str_replace('.jpeg', '.jpg', $sName);
		}
	}
}