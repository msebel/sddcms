<?php
/**
 * Parserklasse die nach TOPLINK Tags sucht
 * @author Michael
 */
class toplinkParser {
	
	/**
	 * Parst den Inhalt nach TOPLINK Tags ab.
	 * @param string content Seiteninhalt
	 */
	public function __construct(&$content) {
		$content = str_replace('[TOPLINK]','<a href="#top">',$content);
		$content = str_replace('[/TOPLINK]','</a>',$content);
	}
}