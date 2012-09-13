<?php
/**
 * Erstellt einen Wiki Editor.
 * Gibt den kompletten Editor aus und handelt die Formatierungen
 * für ein Wiki (Output / Input) mit allem drum und dran
 * @author Michael Sebel <michael@sebel.ch>
 */
class wikiEditor {
	
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	private $Conn = null;
	/**
	 * Resourcen Objekt
	 * @var resources
	 */
	private $Res = null;
	/**
	 * Metatag Objekt für Javascript/Css
	 * @var meta
	 */
	private $Meta = null;
	
	/**
	 * Erstellt das Wiki Editor Objekt
	 * @param dbConn Conn Datenbankverbindung
	 * @param resources Res Resourcen Objekt
	 * @param meta Meta Metatag Objekt für Javascript/Css
	 */
	public function __construct(dbConn &$Conn, resources &$Res, meta &$Meta) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->Meta = $Meta;
	}
	
	/**
	 * Gibt den Editor zurück
	 * @param string name Name des Editors (Input name)
	 * @param string width Breite des Editors in Pixel
	 * @param string height Höhe des Editors in Pixel
	 * @param string value Vordefinierter Wert des Editors
	 * @return string HTML um den Editor einzubetten
	 */
	public function getEditor($name,$width,$height,$value) {
		$out = '';
		$this->Meta->addJavascript('/library/class/wikiEditor/editor.js');
		$this->getEditorJavascript($name,$out);
		$this->getEditorButtons($out);
		$this->getEditorArea($name,$width,$height,$value,$out);
		// HTML Daten zurückgeben
		return($out);
	}
	
	/**
	 * Javascript für den Editor generieren
	 * @param string name Name des Editors
	 * @param string out Ausgabepuffer
	 */
	private function getEditorJavascript($name,&$out) {
		// 1. Javascript Editor Objekt erstellen
		// 2. Button Events registrieren
		$out .= '
		<script type="text/javascript">
			var Editor = new WikiEditor();
			addEvent(window, "load", function () { Editor.initializeEditor("'.$name.'"); }, false);
			addEvent(window, "load", function () { Editor.registerButtons(); }, false);
		</script>
		';
	}
	
	/**
	 * Gibt die Buttons des Editor zurück
	 * @param string out Buffer Variable für Inhalte
	 */
	private function getEditorButtons(&$out) {
		$out .= '
		<div class="cEditorButton">
			<img src="/images/icons/text_heading_1.png" id="h1" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/text_heading_2.png" id="h2" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/text_heading_3.png" id="h3" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/text_bold.png" id="bold" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/text_italic.png" id="italic" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/textfield.png" id="line" border="0">
		</div>
		<div class="cEditorButton">
			<img src="/images/icons/world_link.png" id="link" border="0">
		</div>
		';
	}
	
	/**
	 * Gibt den Edit Bereich des Editors zurück
	 * @param string name Name des Editors (Input name)
	 * @param string width Breite des Editors in Pixel
	 * @param string height Höhe des Editors in Pixel
	 * @param string value Vordefinierter Wert des Editors
	 * @param string out Buffer Variable für Inhalte
	 */
	private function getEditorArea($name,$width,$height,$value,&$out) {
		// Editor selbst ausgeben
		$out .= '
		<div class="cWikiEditorArea">
			<textarea name="'.$name.'" id="'.$name.'" 
				style="width:'.$width.';height:'.$height.';">'.$value.'</textarea>
		</div>
		';
	}
	
	/**
	 * Parsed den Wikitext und ersetzt HTML Code
	 * @param string text Text aus der Datenbank
	 * @return string HTML Code mit Wikifeatures
	 */
	public static function parse($text) {
		$returnText = '';
		$lastLine = '';
		$lines = explode("\r\n",$text);
		// Nur sinnvolle Zeilen anzeigen
		foreach ($lines as $line) {
			$type = wikiEditor::getLineType($line);
			// Abschliessen, wenn neuer Typ
			if ($lastLine != $type) {
				switch ($lastLine) {
					case 'list': $returnText .= '</li>'; break;
					case 'code': $returnText .= '</pre>'; break;
				}
			}
			// Je nach Typ etwas machen
			switch ($type) {
				case 'paragraph':
					// Nur diese Linien sind brauchbar
					$returnText .= '<p>'.$line.'</p>'; break;
				case 'line':
					$returnText .= '<hr>';
					break;
				case 'list':
					if ($lastLine != $type) {
						$returnText .= '<ul class="wikiList">';
					}
					$returnText .= '<li>'.substr($line,2).'</li>';
					break;
				case 'code':
					// Wenn vorher kein code, neu beginnen
					if ($lastLine != $type) {
						$returnText .= '<pre class="wikiCode">';
					}
					$returnText .= $line.'<br>';
					break;
				case 'h1':
				case 'h2':
				case 'h3':
				case 'h4':
				case 'h5':
				case 'h6':
				case 'h7':
					$htype = getInt(substr($type,1));
					$sSearch = '';
					for ($i = 0;$i < $htype;$i++) $sSearch .= '=';
					$line = str_replace($sSearch,'',$line);
					$returnText .= '<'.$type.'>'.$line.'</'.$type.'>';
					break;
			}
			// Letzten Typ speichern
			$lastLine = $type;
		}
		return($returnText);
	}
	
	/**
	 * Textversion, welche keine Wiki Tags enthält ausgeben
	 * @param string text Text aus der Datenbank
	 * @return string HTML Code ohne Wikifeatures
	 */
	public static function unparse($text) {
		$returnText = '';
		$lines = explode("\r\n",$text);
		// Nur sinnvolle Zeilen anzeigen
		foreach ($lines as $line) {
			$type = wikiEditor::getLineType($line);
			switch ($type) {
				case 'paragraph':
					// Nur diese Linien sind brauchbar
					$returnText .= $line; break;
			}
		}
		return($returnText);
	}
	
	/**
	 * Textversion, welche Wiki Tags enthält ausgeben
	 * @param string text Text aus der Datenbank
	 * @return string HTML Code mit Links, Fett und Kursiv
	 */
	public static function parseWords($text) {
		// Link Matches holen und durchgehen
		$matches = self::getWordMatches('\[\[',']]',$text);
		// Alle Resultate durchgehen
		for ($i = 0; $i < count($matches[0]);$i++) {
			$search = $matches[0][$i]; // Originalfund der ersetzt wird
			$linkname = $matches[1][$i]; // Nur das Wort zw. Tags
			// Link erstellen
			$link = '<a href="show.php?id='.page::menuID().'&article='.$linkname.'">'.$linkname.'</a>';
			// Link ersetzen
			$text = str_replace($search,$link,$text);
		}
		
		// Fett Matches (''') holen und durchgehen
		$matches = self::getWordMatches('\'\'\'','\'\'\'',$text);
		for ($i = 0; $i < count($matches[0]);$i++) {
			$search = $matches[0][$i]; // Originalfund der ersetzt wird
			$origtext = $matches[1][$i]; // Nur das Wort zw. Tags
			// Link erstellen
			$newtext = '<strong>'.$origtext.'</strong>';
			// Link ersetzen
			$text = str_replace($search,$newtext,$text);
		}
		
		// Kursiv Matches ('') holen und durchgehen
		$matches = self::getWordMatches('\'\'','\'\'',$text);
		for ($i = 0; $i < count($matches[0]);$i++) {
			$search = $matches[0][$i]; // Originalfund der ersetzt wird
			$origtext = $matches[1][$i]; // Nur das Wort zw. Tags
			// Link erstellen
			$newtext = '<em>'.$origtext.'</em>';
			// Link ersetzen
			$text = str_replace($search,$newtext,$text);
		}
		
		// Evtl. Start Tags ausnehmen
		$text = str_replace('[[','',$text);
		$text = str_replace('\'\'\'','',$text);
		$text = str_replace('\'\'','',$text);
		
		return($text);
	}
	
	/**
	 * Generiert eine Matchliste aller Texte innerhalb des
	 * gegebenen Start und End Tags
	 * @param string start Anfangstag
	 * @param string end Endtag
	 * @param string text zu durchsuchender Text
	 * @return array Liste aller funde
	 */
	private static function getWordMatches($start,$end,$text) {
		$regex = '/'.$start.'(.*?)'.$end.'/';
		$result = array();
		preg_match_all($regex,$text,$result);
		return($result);
	}
	
	/**
	 * Gibt einen Human Readable Typ der Zeile zurück
	 * @param string line Textzeile
	 * @return string Typ der Textzeile
	 */
	private static function getLineType($line) {
		$type = 'paragraph';
		// Prüfen auf Überschriften 1-7
		for ($i = 1;$i <= 7;$i++) {
			if (wikiEditor::checkHeading($line,$i)) {
				$type = 'h'.$i;
			}
		}
		// Prüfen auf Linie
		if (wikiEditor::checkLine($line)) $type = 'line';
		// Prüfen auf Codeblock
		if (wikiEditor::checkCode($line)) $type = 'code';
		// Prüfen auf Auflistung
		if (wikiEditor::checkList($line)) $type = 'list';
		return($type);
	}
	
	/**
	 * Prüft auf eine Überschrift (durch $i bestummen)
	 * @param string line Textzeile zu durchsuchen
	 * @param string type Gewünschte Überschrift: 1,2,3,4..
	 * @return bool true/false ob Überschrift oder nicht
	 */
	private static function checkHeading($line,$type) {
		$sCheck = '';
		for ($i = 0;$i < $type;$i++) $sCheck .= '=';
		// Start und Ende prüfen
		$bCheck = false;
		if (stringOps::startsWith($line,$sCheck) && stringOps::endsWith($line,$sCheck)) {
			$bCheck = true;
		}
		return($bCheck);
	}
	
	/**
	 * Prüft auf eine Linie
	 * @param string line Textzeile zu durchsuchen
	 * @return bool true/false ob Linie oder nicht
	 */
	private static function checkLine($line) {
		// Prüfen ob Inhalt stimmt
		$bCheck = false;
		if ($line == '----') $bCheck = true;
		return($bCheck);
	}
	
	/**
	 * Prüft auf eine Liste
	 * @param string line Textzeile zu durchsuchen
	 * @return bool true/false ob Liste oder nicht
	 */
	private static function checkList($line) {
		// Start prüfen
		$bCheck = false;
		if (stringOps::startsWith($line,'* ')) $bCheck = true;
		return($bCheck);
	}
	
	/**
	 * Prüft auf einen Codeblock
	 * @param string line Textzeile zu durchsuchen
	 * @return bool true/false ob Code oder nicht
	 */
	private static function checkCode($line) {
		// Start prüfen
		$bCheck = false;
		if (stringOps::startsWith($line,' ')) $bCheck = true;
		return($bCheck);
	}
}