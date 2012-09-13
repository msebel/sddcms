<?php
// Editorklasse inkludieren
require_once(BP.'/scripts/editor/fckeditor.php');

// Selbst eine Klasse bauen für die Editoren
class editor {
	
	Private $Editor;
	Private $Editors = array(
		'Default' => 'Default',
		'Config' => 'Config',
		'Basic' => 'Basic'
	);
	
	public static function get($sType,$sName,$nLanguage,$value) {
		$sLanguage = self::getLanguage($nLanguage);
		$Config = array(
			'DefaultLanguage' => $sLanguage,
			'EditorAreaCSS' => '/design/'.page::design().'/format.css'
		);
		$newEditor = new FCKEditor($sType,$sName,$value,$Config);
		$sEditorPrintBasePath = "";
		return($newEditor->CreateHTML($sEditorPrintBasePath));
	}
	
	public static function getSized($sType,$sName,$nLanguage,$value,$sWidth,$sHeight) {
		$sLanguage = self::getLanguage($nLanguage);
		$Config = array(
			'DefaultLanguage' => $sLanguage,
			'EditorAreaCSS' => '/design/'.page::design().'/format.css'
		);
		$newEditor = new FCKEditor($sType,$sName,$value,$Config,$sWidth,$sHeight);
		$sEditorPrintBasePath = "";
		return($newEditor->CreateHTML($sEditorPrintBasePath));
	}
	
	public static function getEditorList() {
		$sList = "<ul>";
		foreach ($this->Editors as $EditorName) {
			$sList .= "<li>".$editorName."</li>";
		}
		$sList .= "</ul>";
	}
	
	private function getLanguage($nLanguage) {
		switch ($nLanguage) {
			case 0:  $sResult = 'de';
			case 1:  $sResult = 'en';
			default: $sResult = 'en';
		}
		return($sResult);
	}
}