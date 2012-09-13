<?php 
require_once(BP.'/library/class/bbcodeParser/moreParser.php');
require_once(BP.'/library/class/bbcodeParser/toplinkParser.php');
require_once(BP.'/library/class/bbcodeParser/boxParser.php');
/**
 * Ersetzt BB-Code mit eigentlichem Inhalt
 * @author Michael Sebel <michael@sebel.ch>
 */
class sddcodeParser implements templateParser {
	
	/**
	 * Implementation der Parserfunktion
	 * @param string content Zu parsender String
	 */
	public function parse(&$content) {
		// Alle Parser durchlaufen lassen
		new moreParser($content);
		new toplinkParser($content);
		new boxParser($content);
	}
}