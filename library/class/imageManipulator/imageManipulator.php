<?php
/**
 * Klasse zum verändern von Bildern.
 * Kann ein gegebenes Bild anhand der Breite oder der Höhe
 * verkleinern oder auch vergrössern. Qualität: 80%.
 * @author Michael Sebel <michael@sebel.ch>
 */
class imageManipulator {
	
	/**
	 * Erlaubte Bild-Dateiendungen inklusive Punkt am Ende
	 * @var array
	 */
	protected $AllowedExtensions = array(".jpg",".gif",".png");
	/**
	 * Qualität der resultierenden Bilddateien (80)
	 * @var integer
	 */
	protected $JpegQuality = 80;
	/**
	 * URL zum veränderten Image
	 * @var string
	 */
	protected $ImageUrl;
	/**
	 * URL für Output, alternativ zum original
	 * @var string
	 */
	protected $ImageUrlOutput;
	/**
	 * Bildobjekt (originalbild)
	 * @var object
	 */
	protected $ImageObj;
	/**
	 * Bildtyp (jpg, gif, png)
	 * @var string
	 */
	protected $ImageType;
	/**
	 * Objekt des veränderten Bildes
	 * @var object
	 */
	protected $ResizedImage;
	/**
	 * Switching Konstante für verkleinerung nach Höhe
	 * @var integer
	 */
	const HEIGHT = 0;
	/**
	 * Switching Konstante für verkleinerung nach Breite
	 * @var integer
	 */
	const WIDTH = 1;
	
	/**
	 * Instanziert das zu vergrössernde Bild
	 * @param string Image, Pfad zum zu verändernden Bild
	 */
	public function __construct($Image,$out = '') {
		if ($this->IsImage($Image)) {
			$this->GetImageType($Image);
			$this->ImageUrl = $Image;
			$this->ImageUrlOutput = $out;
			if (strlen($out) == 0) {
				$this->ImageUrlOutput = $Image;
			}
			$this->Create();	
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Aktuelles Bild zerstören
	 */
	public function __destruct() {
		if ($this->IsImage($this->ImageUrl)) {
			imagedestroy($this->ImageObj);
			if (!empty($this->ResizedImage)) {
				imagedestroy($this->ResizedImage);
			}
		}
	}
	
	/**
	 * JPEG Ausgabe-Qualität verändern
	 * @param integer nPercent, Wert zwischen 0 - 100 (beste Qualität)
	 */
	public function setQuality($nPercent) {
		$this->JpegQuality = $nPercent;
	}
	
	/**
	 * Gibt einen entsprechende Verkleinerung zurück.
	 * @param integer nParam, gewünschter Wert auf nRadio Seite
	 * @param integer nRatio, Switching-Wert für WIDTH/HEIGHT Konstanten
	 * @return integer Entsprechende Kantenverklein- / -grösserung
	 */
	public function getAspectOf($nParam,$nRatio) {
		$ImageInfo = getimagesize($this->ImageUrl);
		$nWidth = $ImageInfo[0];
		$nHeight = $ImageInfo[1];
		$nResult = $nParam;
		switch ($nRatio) {
			case self::HEIGHT:
				// nParam ist die höhe
				// Prozentuale Vergrösserung height zu param
				$nPercentage = ($nParam * 100) / $nHeight;
				$nResult = ($nWidth / 100) * $nPercentage;
				break;
			case self::WIDTH:
				// nParam ist die Breite
				// Prozentuale Vergrösserung width zu param
				$nPercentage = ($nParam * 100) / $nWidth;
				$nResult = ($nHeight / 100) * $nPercentage;
				break;
		}
		// Resultat zurücksenden
		return(getInt($nResult));
	}
	
	/**
	 * Image Objekt erstellen
	 */
	public function Create() {
		switch($this->ImageType) {
			case ".gif": $this->ImageObj = imagecreatefromgif($this->ImageUrl);		break;
			case ".jpg": $this->ImageObj = imagecreatefromjpeg($this->ImageUrl);	break;
			case ".png": $this->ImageObj = imagecreatefrompng($this->ImageUrl);		break;
		}
	}
	
	/**
	 * Verkleinern oder Vergrössern anhand Prozentsatz
	 * @param integer Percent, gewünschter Prozentsatz zw. 1 und X
	 */
	public function ResizePercentual($Percent) {
		list($Width, $Height) = getimagesize($this->ImageUrl);
		$NewWidth = intval(($Width * $Percent) / 100);
		$NewHeight = intval(($Height * $Percent) / 100);
		
		$this->ResizedImage = imagecreatetruecolor($NewWidth, $NewHeight);
		imagecopyresampled($this->ResizedImage, $this->ImageObj, 0, 0, 0, 0, $NewWidth, $NewHeight, $Width, $Height);
		
		switch($this->ImageType) {
			case ".gif": imagegif($this->ResizedImage,$this->ImageUrlOutput);	break;
			case ".jpg": imagejpeg($this->ResizedImage,$this->ImageUrlOutput,$this->JpegQuality); break;
			case ".png": imagepng($this->ResizedImage,$this->ImageUrlOutput); break;
		}
	}
	
	/**
	 * Verkleiner oder Vergrössern anhand Längenparameter
	 * @param integer W, Gewünschte Breite des Bildes
	 * @param integer H, Gewünschte Höhe des Bildes
	 */
	public function Resize($W,$H) {
		if ($this->IsImage($this->ImageUrl)) {
			list($Width, $Height) = getimagesize($this->ImageUrl);		
			$NewWidth = $W;
			$NewHeight = $H;
			
			$this->ResizedImage = imagecreatetruecolor($NewWidth, $NewHeight);
			imagecopyresampled($this->ResizedImage, $this->ImageObj, 0, 0, 0, 0, $NewWidth, $NewHeight, $Width, $Height);
			
			switch($this->ImageType) {
				case ".gif": imagegif($this->ResizedImage,$this->ImageUrlOutput); break;
				case ".jpg": imagejpeg($this->ResizedImage,$this->ImageUrlOutput,$this->JpegQuality); break;
				case ".png": imagepng($this->ResizedImage,$this->ImageUrlOutput); break;
			}
		}
	}
	
	/**
	 * Prüft, ob es sich tatsächlich um ein Bild handelt
	 * @param string File, Kompletter Pfad zum Bild
	 * @return boolean True, wenn es ein Bild ist
	 */
	public function IsImage($File) {
	$bReturn = false;
		if ($this->ImageExists($File)) {
			$Extensions = implode(",",$this->AllowedExtensions);
			
			$Extension = strtolower(substr ($File, strrpos ($File, ".")));
			$this->Extension = $Extension;
			return $Extension;
			
			$this->AllowedExtensions = explode(",",strtolower($Extensions));
			if (!empty($this->AllowedExtensions)) {
				if(in_array($this->GetExtension(),$this->AllowedExtensions)) {
					$bReturn = true;
				} 
			}
		}
		return ($bReturn);
	}
	
	/**
	 * Gibt an, ob das gegebene Image überhaupt existiert
	 * @param string Image, Pfad zum Bild
	 * @return boolean True, wenn das Bild existiert
	 */
	public function ImageExists($Image) {
		if (file_exists($Image)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Setzt den Bildtyp anhand des gegebenen Bildes
	 * @param string Image, Pfad zum Bild
	 */
	public function GetImageType($Image) {
		if ($this->IsImage($Image)) {
			$Extension = strtolower(substr ($Image, strrpos ($Image, ".")));
			
			switch($Extension) {
				case ".gif": $this->ImageType = ".gif"; break;
				case ".jpg": $this->ImageType = ".jpg"; break;
				case ".png": $this->ImageType = ".png"; break;
				default: return false; break;
			}
		}
	}
}