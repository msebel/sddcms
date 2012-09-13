<?php 
/**
 * Erweiterung des BaseControl um einen Multiselektor.
 * Der Multiselektor muss nicht mit CTRL bedient werden.
 * Er sendet für jeden Klick eine Anfrage per AJAX an 
 * den Server, welcher diese gleich speichern sollte.
 * @author Michael Sebel <michael@sebel.ch>
 */
class selectorControl extends abstractControl {
	
	/**
	 * Gibt an ob die Komponente initlaisiert ist
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
		$Meta->addJavascript('/scripts/controls/selector.js',true);
	}
	
	/**
	 * Neues Control hinzufügen
	 * @param string Name, eindeutige Name des Controls
	 * @param string Param, Parameter für den AJAX Request
	 * @param string URL, URL für den AJAX Request
	 * @param integer Width, Breite der Selectbox (default: 300px)
	 * @param integer Rows, Anzahl angezeigter Zeilen ohne scroll (default: 5)
	 * @param string Color, Hintergrundfarbe selektierter Elemente
	 */
	public function add($Name,$Param,$URL,$Width = 300,$Rows = 5,$Color = '#48D') {
		if (!$this->exists($Name)) {
			$this->Controls[$Name] = array();
			$this->Controls[$Name]['Param'] = $Param;
			$this->Controls[$Name]['Width'] = $Width;
			$this->Controls[$Name]['URL'] = $URL;
			$this->Controls[$Name]['Color'] = $Color;
			$this->Controls[$Name]['MaxRows'] = $Rows;
			$this->Controls[$Name]['RowCount'] = 0;
			$this->Controls[$Name]['Rows'] = array();
		}
	}
	
	/**
	 * Schauen ob ein Control schon existiert
	 * @param string Name, Name des zu findenden Objektes
	 * @return boolean True, wenn das Objekt existiert
	 */
	public function exists($Name) {
		return(isset($this->Controls[$Name]));
	}
	
	/**
	 * Zeile zu Selektor hinzufügen
	 * @param string Name, Objekt welches die Row hinzugefügt bekommt
	 * @param string Value, Wert für den AJAX Request (ID)
	 * @param string Text, Text für das Selektorelement
	 * @param boolean isSelected, Gibt an ob das Element vorselektiert ist
	 */
	public function addRow($Name,$Value,$Text,$isSelected) {
		$this->Controls[$Name]['RowCount']++;
		$Row['RowID'] = $this->Controls[$Name]['RowCount'];
		$Row['Value'] = $Value;
		$Row['Text'] = $Text;
		$Row['Selected'] = $this->getBoolString($isSelected);
		array_push($this->Controls[$Name]['Rows'],$Row);
	}
	
	/**
	 * Initialisieren des Controls
	 * @param string Name, Name des Controls
	 * @return string HTML Code zum initialisieren
	 */
	public function initialize($Name) {
		$Output = '';
		// Datenhaltung initialisieren
		$Output .= '
		<script type="text/javascript">
			var selectorData = new Object();
			var selectorConfig = new Object();
		</script>';
		$this->isInitialized = true;
		return($Output);
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @param string Name, Name des auszugebenden Controls
	 * @return string HTML Code für das Control
	 */
	public function get($Name) {
		$out = '';
		if (!$this->isInitialized) $out .= $this->initialize($Name);
		$Control =& $this->Controls[$Name];
		// HTML Tabelle ohne Events generieren
		$Style .= 'width:'.$Control['Width'].'px;';
		$Style .= 'height:'.($Control['MaxRows'] * 20).'px;';
		$Style .= 'overflow:auto;';
		$Style .= 'border:1px solid #ccc;';
		$out .= '<div style="'.$Style.'">';
		$out .= '<table width="100%" cellpadding="2" cellspacing="0" border="0">';
		foreach ($Control['Rows'] as $Row) {
			$Additional = '';
			if ($Row['Selected'] == 'true') {
				$Additional = 'background-color:'.$Control['Color'].';';
			}
			$out .= '
			<tr id="'.$Name.'_'.$Row['RowID'].'" style="height:20px;'.$Additional.'">
				<td>'.$Row['Text'].'</td>
			</tr>';
		}
		$out .= '</table></div>';
		// Per Javascript Events und Daten speichern
		$out .= '<script type="text/javascript">
			// Selektordaten speichern
			selectorConfig["'.$Name.'_Color"] = "'.$Control['Color'].'";
			selectorConfig["'.$Name.'_URL"] = "'.$Control['URL'].'";
			selectorConfig["'.$Name.'_Param"] = "'.$Control['Param'].'";
		';
		foreach ($Control['Rows'] as $Row) {
			$TRID = $Name.'_'.$Row['RowID'];
			$out .= '
			selectorData["'.$TRID.'_value"] = '.$Row['Value'].'
			selectorData["'.$TRID.'_selected"] = '.$Row['Selected'].'
			addEvent(window, "load", function() {addTrEvent("'.$TRID.'","'.$Name.'");}, false);
			';
		}
		$out .= '</script>';
		return($out);
	}
	
	/**
	 * Boolean als String zurückgeben
	 * @param boolean Bool, ein Boolean wert (true/false)
	 * @return string 'true' oder 'false', je nach Eingangswert
	 */
	private function getBoolString($Bool) {
		if ($Bool) {
			return('true');
		} else {
			return('false');
		}
	}
}