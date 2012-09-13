<?php
class TiltViewerGallery extends Gallery {
	
	public function __construct(dbConn &$Conn,$thumbWidth,$Meta) {
		$this->Conn = $Conn;
		$this->thumbWidth = $thumbWidth;
		$this->Files = array();
		$this->buildFilelist();
		// Javascript einfügen
		$Meta->addJavascript('/modules/gallery/TiltViewerGallery/swfobject.js');
	}
	
	// Methode die den HTML Output der Galerie in
	// die übergebene Buffervariable out spitzt
	public function appendHtml(&$out) {
		// Filelliste in Session für file.php / xml.php
		$_SESSION['galleryFiles_'.page::menuID()] = $this->Files;
		session_write_close();
		// HTML Code für Simpleviewer Flash ausgeben
		$out .= '
		<div id="flashcontent">&nbsp;</div>	
		<script type="text/javascript">
			var fo = new SWFObject(
				"/modules/gallery/TiltViewerGallery/TiltViewer.swf",
				"viewer", "100%", "600", "8", "#000000"
			);
			fo.addVariable("xmlURL", "/modules/gallery/TiltViewerGallery/xml.php?id='.page::menuID().'"); 
			fo.addVariable("useFlickr", "false");
			fo.addVariable("maxJPGSize","800");		
			fo.addVariable("useReloadButton", "false");
			fo.addVariable("columns", "5");
			fo.addVariable("rows", "4");
			fo.write("flashcontent");	
		</script>	
		';
	}
	
	// Plain Output ohne Menu und Design
	public function outputPlain() {
		// Filelliste in Session für file.php / xml.php
		$_SESSION['galleryFiles_'.page::menuID()] = $this->Files;
		session_write_close();
		// Output erstellen
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
		"http://www.w3.org/TR/html4/transitional.dtd">
		<html>
		<head>
		<script type="text/javascript" src="/modules/gallery/TiltViewerGallery/swfobject.js"></script>
		<style type="text/css">	
			/* hide from ie on mac \*/
			html {
				height: 100%;
				overflow: hidden;
			}
			
			#flashcontent {
				height: 100%;
			}
			/* end hide */
		
			body {
				height: 100%;
				margin: 0;
				padding: 0;
				background-color: #000000;
				color:#ffffff;
				font-family:sans-serif;
				font-size:40;
			}
		
			a {	
				color:#cccccc;
			}
		</style>
		</head>
		<body style="margin:0px;">
		<div id="flashcontent">&nbsp;</div>	
		<script type="text/javascript">
			var fo = new SWFObject(
				"/modules/gallery/TiltViewerGallery/TiltViewer.swf",
				"viewer", "100%", "100%", "8", "#202020"
			);
			fo.addVariable("xmlURL", "/modules/gallery/TiltViewerGallery/xml.php?id='.page::menuID().'"); 
			fo.addVariable("useFlickr", "false");
			fo.addVariable("maxJPGSize","800");		
			fo.addVariable("useReloadButton", "false");
			fo.addVariable("columns", "5");
			fo.addVariable("rows", "4");
			fo.write("flashcontent");	
		</script>	
		</body>
		</html>
		';
	}
}