<?php
class LightboxGallery extends Gallery {
	
	public function __construct(dbConn &$Conn,$thumbWidth,$thumbHeight) {
		$this->Conn = $Conn;
		$this->thumbWidth = getInt($thumbWidth);
		$this->thumbHeight = getInt($thumbHeight);
		$this->Files = array();
		$this->buildFilelist();
	}
	
	// Methode die den HTML Output der Galerie in
	// die übergebene Buffervariable out spitzt
	public function appendHtml(&$out) {
		foreach ($this->Files as $GalleryFile) {
			$File = $GalleryFile->View;
			if ($GalleryFile->hasThumb) {
				// Thumb Prefix vor den Filenamen setzen
				$File = $GalleryFile->Thumb;
			}
			stringOps::htmlViewEnt($GalleryFile->Description);
			$widthheight = '';
			if ($this->thumbWidth > 0)
				$widthheight .= 'width="'.$this->thumbWidth.'" ';
			if ($this->thumbHeight > 0)
				$widthheight .= 'height="'.$this->thumbHeight.'" ';
			$out .= '
			<div class="lbGalleryImage">
				<a href="'.$GalleryFile->View.'" rel="lightbox[gallery]" title="'.$GalleryFile->Description.'">
				<img src="'.$File.'" '.$widthheight.' border="0"></a>
			</div>
			';
		}
	}
	
	// Plain Output ohne Menu und Design
	public function outputPlain() {
		$out = '';
		foreach ($this->Files as $GalleryFile) {
			$File = $GalleryFile->View;
			if ($GalleryFile->hasThumb) {
				// Thumb Prefix vor den Filenamen setzen
				$File = $GalleryFile->Thumb;
			}
			$out .= '
			<div class="lbGalleryImage">
				<a href="'.$GalleryFile->View.'" rel="lightbox[gallery]">
				<img src="'.$File.'" width="'.$this->thumbWidth.'" border="0"></a>
			</div>
			';
		}
		// Output erstellen
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/transitional.dtd">
		<html>
		<head>
		<script type="text/javascript" src="/scripts/prototype/prototype.js"></script>
		<script type="text/javascript" src="/scripts/scriptaculous/scriptaculous.js?load=effects,builder"></script>
		<link rel="stylesheet" type="text/css" href="/scripts/lightbox/lightbox.css">
		<script type="text/javascript" src="/scripts/lightbox/lightbox.js"></script>
		<style type="text/css">
			.lbGalleryImage {
				float:left;
				margin:2px;
				border:1px solid #ccc;
			}
		</style>
		</head>
		<body>
		'.$out.'
		</body>
		</html>
		';
	}
}