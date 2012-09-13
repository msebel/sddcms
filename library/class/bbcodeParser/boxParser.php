<?php
/**
 * Parserklasse die nach BOX Tags sucht
 * @author Michael
 */
class boxParser {
	
	/**
	 * Parst den Inhalt nach BOX Tags ab.
	 * @param string content Seiteninhalt
	 */
	public function __construct(&$content) {
		// Resourcen Objekt erstellen
		$Res = getResources::getInstance(null);
		// Suchen nach [BOX] Tags per Regex
		$regex = '/\[BOX:(.*?)\]/';
		preg_match_all($regex,$content,$result);
		// Alle Resultate durchgehen
		for ($i = 0; $i < count($result[0]);$i++) {
			$id = 'box_'.$result[1][$i];
			$replace = $result[0][$i];
			// HTML Code basteln
			$out = '
			<div class="cMoreBoxLink">
				<a class="cMoreLink" href="javascript:toggleDiv(\''.$id.'\');">'.$Res->html(442,page::language()).'</a>
			</div>
			<div class="cMoreBox" id="'.$id.'" style="display:none;">
			';
			// Ersetzen im Content
			$content = str_replace($replace,$out,$content);
		}
		// Ende einfach als Div erstellen
		$content = str_replace('[/BOX]','</div>',$content);
	}
}