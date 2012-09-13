<?php 
/**
 * Erweiterung des BaseControl um Tooltips.
 * Zeigt verschiedengrösse Tooltips auf mit einer
 * ID versehene HTML Blockelemente an.
 * @author Michael Sebel <michael@sebel.ch>
 */
class tooltipControl extends abstractControl {
	
	/**
	 * Gibt an ob die Komponente initlaisiert ist
	 * @var boolean
	 */
	private $isInitialized = false;
	/**
	 * Timeout zum anzeigen eines Tooltips bei Mouseover
	 * @var integer
	 */
	private $Timeout = 1;
	
	/**
	 * Objekte laden, nichts zu laden
	 */
	public function loadObjects() {}
	
	/**
	 * Das Timeout verändern.
	 * @param integer Value, Timeout in Sekunden
	 */
	public function setTimeout($Value) {
		$this->Timeout = getInt($Value);
	}
	
	/**
	 * Template laden
	 * @param meta Meta, Referenz zum Metadaten Objekt
	 */
	public function loadMeta($Meta) {
		$Meta->addJavascript('/scripts/controls/tooltip.js',true);
	}
	
	/**
	 * Neues Tooltip Control hinzufügen
	 * @param string Name, eindeutiger Name des Controls
	 * @param string Text, Text für das Tooltip (HTML erlaubt)
	 * @param string Title, Fenstertitel für das Tooltip
	 * @param integer Width, Breite des Tooltips (defaukt: 300)
	 * @param integer Height, Höhe des Tooltips (default: 0, dynamic)
	 * @return integer, Eindeutiger Index des Controls
	 */
	public function add($Name,$Text,$Title = '', $Width = 300,$Height = 0) {
		$nReturnIndex = 0;
		if (!$this->exists($Name)) {
			$Control['Text'] = $this->sanitizeString($Text);
			$Control['Title'] = $this->sanitizeString($Title);
			$Control['Name'] = $Name;
			$Control['Width'] = $Width;
			$Control['Height'] = $Height;
			$Control['Event'] = false;
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
		// Datenhaltung initialisieren
		$Output .= '
		<script type="text/javascript">
			var TooltipData,ttpContainer,ttpTitle,ttpContent,ttpTimeoutFunction;
			var ttpIsOpened,ttpCurrentName,ttpPersistent,ttpClose;
			TooltipData = new Object();
			TooltipData["Option_Timeout"] = '.$this->Timeout.';
			addEvent(window, "load", function() {addCloseEvent();}, false);
			addEvent(window, "load", function() {addTooltipObjects();}, false);
		</script>';
		// Ausgaben des Container DIVs
		$Output .= '
		<div id="tooltipContainer" style="display:none;">
			<div id="tooltipHead">
				<div id="tooltipClose">&nbsp;</div>
				<span id="tooltipTitle">&nbsp;</span>
			</div>
			<div id="tooltipContent">&nbsp;</div>
		</div>';
		$this->isInitialized = true;
		return($Output);
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @param string Name, Name des Controls
	 * @return string HTML Code zum darstellen des Controls
	 */
	public function get($Name) {
		$Output = '';
		if (!$this->isInitialized) {
			$Output .= $this->initialize();
		}
		// Event hinzufügen, wenn nicht schon geschehen
		$nID = $this->getIdByName($Name);
		if (!$this->Controls[$nID]['Event']) {
			$Output .= '
			<script type="text/javascript">
				// Events hinzufügen zum Objekt
				addEvent(window, "load", function() {addTooltipEvent("'.$this->Controls[$nID]['Name'].'");}, false);
				// Daten für diesen Event definieren
				TooltipData["'.$this->Controls[$nID]['Name'].'_Width"] = '.$this->Controls[$nID]['Width'].';
				TooltipData["'.$this->Controls[$nID]['Name'].'_Height"] = '.$this->Controls[$nID]['Height'].';
				TooltipData["'.$this->Controls[$nID]['Name'].'_Text"] = "'.$this->Controls[$nID]['Text'].'";
				TooltipData["'.$this->Controls[$nID]['Name'].'_Title"] = "'.$this->Controls[$nID]['Title'].'";
			</script>
			';
		}
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
		return($sString);
	}
}