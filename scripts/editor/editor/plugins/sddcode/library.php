<?php
class bbcodeLib {
	
	public static $Res;
	
	// Gibt die möglichen Codes zurück
	public static function getCodes() {
		$out = '';
		foreach (self::codeArray() as $key => $value) {
			$out .= '
			<option value="'.$key.'">'.$value.'</option>';
		}
		return($out);
	}
	
	// Gibt ein Array der Codetypen aus
	private static function codeArray() {
		return(array(
			'MORE' => self::$Res->html(859,page::language()),
			'TOPLINK' => self::$Res->html(860,page::language()),
			'BOX' => self::$Res->html(861,page::language())
		));		
	}
}