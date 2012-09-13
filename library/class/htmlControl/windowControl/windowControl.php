<?php 
/**
 * Erweiterung des Basecontrol um ein flying Window.
 * Bietet eines oder mehrere flying Windows an. Der Hintergrund
 * wird abgedunkelt, während das Window in gewünschter Grösse
 * und mit dem gegebenen Inhalt erscheint.
 * @author Michael Sebel <michael@sebel.ch>
 */
class windowControl extends abstractControl {
	
	/**
	 * Gibt an, ob das Objekt initialisiert wurde.
	 * @var boolean
	 */
	private $isInitialized = false;
	
	/**
	 * Objekte laden, nichts zu laden
	 */
	public function loadObjects() {}
	
	/**
	 * Template laden
	 * @param meta Meta, Referenz zum Metadaten Objekt
	 */
	public function loadMeta($Meta) {
		$Meta->addJavascript('/scripts/controls/window.js',true);
	}
	
	/**
	 * Neues Control hinzufügen.
	 * @param string Name, interner Name des Controls
	 * @param string HTML, Code, den das Fenster enthalten soll
	 * @param string Title, Titel für die Fensterleiste
	 * @param integer Width, Breite des Fensters (default: 640)
	 * @param integer Height, Höhe des Fensters (default: 480)
	 * @param string Follow, JS Funktion, wird nach laden aufgerufen
	 * @return integer, Index des neuen Controls
	 */
	public function add($Name,$HTML,$Title = '',$Width = 640,$Height = 480,$Follow = '') {
		$nReturnIndex = 0;
		if (!$this->exists($Name)) {
			$Control['HTML'] = $this->sanitizeString($HTML);
			$Control['Name'] = $Name;
			$Control['Title'] = $Title;
			$Control['Width'] = $Width;
			$Control['Height'] = $Height;
			$Control['Follow'] = $Follow;
			array_push($this->Controls,$Control);
			$nReturnIndex = count($this->Controls) - 1;
		} else {
			$nReturnIndex = $this->getIdByName($Name);
		}
		return($nReturnIndex);
	}
	
	/**
	 * Initialisieren des Controls
	 * @return string HTML Code zum initialisieren
	 */
	public function initialize() {
		$Output = '';
		if (!$this->isInitialized) {
			// Datenhaltung initialisieren
			$Output .= '
			<script type="text/javascript">
				var WindowData,wndContainer,wndTitle,wndContent,wndTitle;
				var wndIsOpened,wndCurrentName,wndClose,wndOverlay,wndHead;
				WindowData = new Object();
				addEvent(window, "load", function() {addWindowCloseEvent();}, false);
				addEvent(window, "load", function() {addWindowObjects();}, false);
				addEvent(window, "load", function() {initializeOverlay();}, false);
			</script>';
			// Ausgaben des Container DIVs
			$Output .= '
			<div id="windowOverlay" style="display:none;background-color:#444;z-index:99;">&nbsp;</div>
			<div id="tooltipContainer" style="z-index:100;position:fixed;display:none;">
				<div id="tooltipHead">
					<div id="windowClose">&nbsp;</div>
					<span id="tooltipTitle">&nbsp;</span>
				</div>
				<div id="tooltipContent">&nbsp;</div>
			</div>';
			$this->isInitialized = true;
		}
		return($Output);
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @return string HTML Code für das Control
	 */
	public function get($Name) {
		$Output = '';
		if (!$this->isInitialized) {
			$Output .= $this->initialize();
		}
		
		// Event hinzufügen, wenn nicht schon geschehen
		$nID = $this->getIdByName($Name);
		// Javascript herausparsen und in Follow schmeissen
		$this->extractJavascript($this->Controls[$nID]);
		$Output .= '
		<script type="text/javascript">
			// Events hinzufügen zum Objekt
			addEvent(window, "load", function() {addWindowEvent("'.$this->Controls[$nID]['Name'].'");}, false);
			// Daten für diesen Event definieren
			WindowData["'.$this->Controls[$nID]['Name'].'_Width"] = '.$this->Controls[$nID]['Width'].';
			WindowData["'.$this->Controls[$nID]['Name'].'_Height"] = '.$this->Controls[$nID]['Height'].';
			WindowData["'.$this->Controls[$nID]['Name'].'_Title"] = "'.$this->Controls[$nID]['Title'].'";
			WindowData["'.$this->Controls[$nID]['Name'].'_HTML"] = "'.$this->Controls[$nID]['HTML'].'";
			WindowData["'.$this->Controls[$nID]['Name'].'_Follow"] = "'.$this->Controls[$nID]['Follow'].'";
		</script>
		';
		return($Output);
	}
	
	/**
	 * String für Javascript vorbereiten.
	 * @param string sString, zu behandelnde Zeichenkette
	 * @return string Geflickte Zeichenkette (\rnt, HTML entfernt, escaped)
	 */
	private function sanitizeString($sString) {
		$sString = addslashes($sString);
		stringOps::htmlEntRev($sString);
		$sString = str_replace("\r",'',$sString);
		$sString = str_replace("\n",'',$sString);
		$sString = str_replace("\t",' ',$sString);
		return($sString);
	}

	/**
	 * Parst den Javascript Code heraus und fügt Ihn den
	 * Follow Funktionen an (Sofern welche vorhanden sind)
	 * @param array $Control Vorbereitetes Contro
	 */
	private function extractJavascript(&$Control) {
		$sHtml = $Control['HTML'];
		$matches = array();
		preg_match_all('/<script\b[^>]*>(.*?)<\/script>/i', $sHtml, $matches);
		for ($i = 0;$i < count($matches[0]);$i++) {
			$sReplace = $matches[0][$i];
			$sCode = $matches[1][$i];
			// HTML Code weg ersetzen
			$sHtml = str_replace($sReplace, '', $sHtml);
			$sScript .= stripslashes($sCode);
		}
		$Control['HTML'] = $sHtml;
		$Control['Follow'] = $sScript.';'.$Control['Follow'];
		$Control['Follow'] = str_replace('"',"'",$Control['Follow']);
	}
}