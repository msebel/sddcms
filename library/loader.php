<?php
class library {
	public static function loadInclude($sName) {
		$sInclude = BP.'/library/deprecated/';
		$sInclude.= $sName.'/'.$sName.'.php';
		return($sInclude);
	}
	public static function load($sName) {
		$sInclude = BP.'/library/deprecated/';
		$sInclude.= $sName.'/'.$sName.'.php';
		require_once($sInclude);
	}
	public static function loadRelative($sName) {
		$sPath = $_SERVER['SCRIPT_NAME'];
		$sPath = substr($sPath,0,strripos($sPath,'/'));
		require_once(BP.$sPath.'/'.$sName.'.php');
	}
	public static function req($sName) {
		require_once(BP.$sName.'.php');
	}
}