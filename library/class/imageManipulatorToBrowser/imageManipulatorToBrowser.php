<?php
/**
 * Klasse zum verändern von Bildern.
 * Erbt die imageManipulator Klasse und
 * verändert die Resize Methoden so, dass
 * die Bilder direkt im Browser erscheinen
 * @author Michael Sebel <michael@sebel.ch>
 */
class imageManipulatorToBrowser extends imageManipulator {
	
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
			case ".gif": imagegif($this->ResizedImage);	break;
			case ".jpg": imagejpeg($this->ResizedImage,null,$this->JpegQuality); break;
			case ".png": imagepng($this->ResizedImage); break;
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
				case ".gif": imagegif($this->ResizedImage); break;
				case ".jpg": imagejpeg($this->ResizedImage,null,$this->JpegQuality); break;
				case ".png": imagepng($this->ResizedImage); break;
			}
		}
	}
}