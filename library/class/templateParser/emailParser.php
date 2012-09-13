<?php 
/**
 * Verwandelt Email Adressen in durch Bots 
 * unlesbare Strings
 * @author Michael Sebel <michael@sebel.ch>
 */
class emailParser implements templateParser {
	
	/**
	 * Implementation der Parserfunktion
	 * @param string content Zu parsender String
	 */
	public function parse(&$content) {
		$regex = '/<(?:[a]+)[^>]*mailto[^>]*>/';
		preg_match_all($regex,$content,$result);
		// Original mailto rausholen und hart kodieren
		$sOrig = stringOps::parseTagProperty($result[0][0],'href');
		$sNew = stringOps::stringToAsciiEntities($sOrig);
		$content = str_replace($sOrig,$sNew,$content);
		// Auch verison ohne mailto: für allfälligen Inhalt
		$sOrig = substr($sOrig,7);
		$sNew = stringOps::stringToAsciiEntities($sOrig);
		$content = str_replace($sOrig,$sNew,$content);
	}
}