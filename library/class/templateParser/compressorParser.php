<?php 
/**
 * Entfernt Tabs und unnötige Zeichen aus dem Quelltext
 * @author Michael Sebel <michael@sebel.ch>
 */
class compressorParser implements templateParser {
	
	/**
	 * Implementation der Parserfunktion
	 * @param string content Zu parsender String
	 */
	public function parse(&$content) {
		$content = str_replace("\t",'',$content);
	}
}