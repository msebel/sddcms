<?php
// Repräsentiert ein einzelnes File in einer Bildergalerie
class GalleryFile {
	
	public $Filename = '';
	public $Thumb = '';
	public $View = '';
	public $Description = '';
	public $hasThumb = false;
	public $isValid = true;
	
	public function __construct($Filename,$Folder) {
		$this->Filename = $Filename;
		$this->hasThumb($Folder);
		$ViewFolder = str_replace(BP,'',$Folder);
		$this->Thumb = $ViewFolder.'tmb_'.$Filename;
		$this->isValid();
		$this->View = $ViewFolder.$Filename;
	}
	
	// Definieren ob Thumbnail vorhanden ist
	private function hasThumb($Folder) {
		// Kompletten Filepfad des Thumbnail erstellen
		$FilePath.= $Folder.'tmb_'.$this->Filename;
		// Prüfen ob Thumbnail existiert
		if (file_exists($FilePath)) {
			$this->hasThumb = true;
		}
	}
	
	private function isValid() {
		// Extension holen und prüfen
		$Ext = self::getExtension();
		switch ($Ext) {
			case '.jpg':
			case '.gif':
			case '.png': $this->isValid = true;  break;
			default:	 $this->isValid = false; break;
		}
		// Wenn Valid noch prüfen ob es kein Thumb ist
		if ($this->isValid) {
			// Erster vier Zeichen dürfen nicht tmb_ sein
			if (substr($this->Filename,0,4) == 'tmb_') {
				$this->isValid = false;
			}
		}
	}
	
	private function getExtension() {
		$sFile = strtolower($this->Filename);
		return(substr($sFile,strripos($sFile,'.')));	
	}
}