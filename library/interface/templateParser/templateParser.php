<?php 
/**
 * Interface für Contentparser.
 * Verändert den Inhalt einer Variable
 * @author Michael Sebel <michael@sebel.ch>
 */
interface templateParser {
	
	/**
	 * Parsed den Inhalt der Variabelreferenz
	 * @param string content Zu parsender Inhalt
	 */
	public function parse(&$content);
}