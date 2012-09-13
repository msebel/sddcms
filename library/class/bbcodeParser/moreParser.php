<?php
/**
 * Parserklasse die nach MORE Tags sucht
 * @author Michael
 */
class moreParser {
	
	/**
	 * Parst den Inhalt nach MORE Tags ab. Diese werden nur in
	 * den News verwendet und hier nur gelöscht falls noch übrig
	 * @param string content Seiteninhalt
	 */
	public function __construct(&$content) {
		$content = str_replace('[MORE]','',$content);
		$content = str_replace('[/MORE]','',$content);
	}
}