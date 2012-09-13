<?php 
class libraryOptions {
	
	public $AllowedExtensions;
	public $MaxsizeGraphics;
	public $MaxsizeFiles;
	
	// Session für Optionen konstruieren
	public function __construct() {
		// Medienkonstanten nutzen
		require_once(BP.'/library/class/mediaManager/mediaConst.php');
		$this->AllowedExtensions 	= mediaConst::$AllowedExt;
		$this->MaxsizeGraphics 		= mediaConst::MAXSIZE_GRAPHICS;
		$this->MaxsizeFiles 		= mediaConst::MAXSIZE_FILES;
		// Session erstellen wenn nicht vorhanden
		if (!isset($_SESSION['filelibrary_'.page::menuID()])) {
			$_SESSION['filelibrary_'.page::menuID()] = array();
		}
	}
	
	// Eine Option setzen
	public function set($Name,$Value) {
		$_SESSION['filelibrary_'.page::menuID()][$Name] = $Value;
	}
	
	public function get($Name) {
		return($_SESSION['filelibrary_'.page::menuID()][$Name]);
	}
}