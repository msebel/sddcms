<?php 
class fileUpload {
	
	public $Options;
	private $FormFile;			// Name des Formularfeldes mit FILE Attribut
	private $FileName;			// Name des Files zum Speichern
	private $ElementID;			// Element ID des Files
	private $Allowed = false;	// Wenn false, findet kein Upload statt
	private $FileSize = 0;		// Grösse der Datei in Bytes
	private $Folder;			// Ordner des upzuloadenden Files
	private $ErrorMessage;		// Fehlermeldung
	private $Res = null;		// Ressourcen Objekt
	
	// Optionen holen
	public function __construct (&$Options) {
		$this->Options = $Options;
	}
	
	// Einen Upload starten
	public function start($sDirectory, resources &$Res) {
		$this->FormFile = 'uploadedFile';
		$this->Res = $Res;
		$this->Folder = $sDirectory;
		// Vorbereiten und Upload testen
		$this->setOriginalFilename();
		$this->checkAllowed();
		$this->checkSize();
		// Nur weiter, wenn die Datei in Ordnung ist
		if ($this->Allowed == true) {
			$this->FileSize = $_FILES[$this->FormFile]['size'];
			$this->save();
		}
		return($this->ErrorMessage);
	}
	
	// Neuen Filenamen setzen
	public function setFilename($sName) {
		$this->sanitizeFilename($sName);
		$this->FileName = $sName;
	}
	
	// Kompletten Namen (inkl. Folder) holen
	public function getFilename() {
		return($this->Folder.$this->FileName);
	}
	
	// File effektiv Speichern im Elementordner
	// Gibt einen Error zurück, wenn das File nicht
	// gespeichert wurde
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
	
	// Gibt an, ob es sich um eine Zip Datei handelt
	public function isZip() {
		$IsZip = false;
		$sExtension = strtolower(substr($this->FileName, strripos($this->FileName, '.')));
		if ($sExtension == '.zip') $IsZip = true;
		return($IsZip);
	}
	
	// Grösse des Files prüfen
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
				if ($nSize > $this->Options->MaxsizeGraphics) {
					$this->Allowed = false;
					// Fehlermeldung
					$this->ErrorMessage = $this->Res->html(312,page::language());
					$this->ErrorMessage .= ' '.$this->Options->MaxsizeGraphics.' KB';
				}
				break;
			default:
				if ($nSize > $this->Options->MaxsizeFiles) {
					$this->Allowed = false;
					$this->ErrorMessage = $this->Res->html(313,page::language());
					$this->ErrorMessage .= ' '.$this->Options->MaxsizeFiles.' KB';
				}
				break;
		}
	}
	
	// Prüfen ob das File erlaubt ist
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
	
	// Extension anhand der Medienkonstanten prüfen
	private function checkExtension($sExt) {
		$bAllowed = false;
		foreach ($this->Options->AllowedExtensions as $chkExt) {
			if ($sExt == $chkExt) {
				$bAllowed = true;
			} 
		}
		if (!$bAllowed) $this->ErrorMessage = $this->Res->html(314,page::language());
		return($bAllowed);
	}
	
	// Prüfen ob ein eventuelles Bild wirklich ein Bild ist
	private function checkImage() {
		$isImage = false;
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
	
	// Originalen Filename setzen
	private function setOriginalFilename() {
		$sName = $_FILES[$this->FormFile]['name'];
		//$sExt = strtolower(substr($sName, strripos($sName, '.')));
		//$sName = substr($sName,0,strripos($sName, '.'));
		$this->sanitizeFilename($sName);
		$this->FileName = basename($sName.$sExt);
	}
	
	// Filename flicken, wenn schwer verträgliche Zeichen
	private function sanitizeFilename(&$sName) {
		stringOps::alphaNumFiles($sName);
		$sName = strtolower($sName);
	}
}