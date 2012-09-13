<?php
/**
 * Erweiterung des BaseControl um einen Kalender.
 * Lässt auf die hinzugefügten Kalenderelemente und
 * deren mit einer id gekennzeichneten Objekte per
 * Klick einen DHTML Kalender erscheinen.
 * @author Michael Sebel <michael@sebel.ch>
 */
class calendarControl extends abstractControl {
	
	/**
	 * Objekte laden, nichts zu laden
	 */
	public function loadObjects() {}
	
	/**
	 * Template laden
	 * @param meta Meta, Referenz zum Metadaten Objekt
	 */
	public function loadMeta($Meta) {
		$Meta->addJavascript('/scripts/controls/calendar.js',true);
	}
	
	/**
	 * Neues Control hinzufügen
	 * @param string Name, Name des hinzuzufügenden Objektes
	 * @return integer Index des hinzugefügten Objektes
	 */
	public function add($Name,$nTopOffset = 30, $nLeftOffset = 0,$zIndex = 10000) {
		$nReturnIndex = 0;
		if (!$this->exists($Name)) {
			$Control['HTML'] = '
			<img src="/images/icons/calendar.png" id="'.$Name.'_Icon">
			<script type="text/javascript">
				jQuery(function() {
				  new DatePicker({
            relative : "'.$Name.'",
            language : "'.$this->GetLanguage().'",
            externalControl : "'.$Name.'_Icon",
            zindex : '.$zIndex.',
            leftOffset : '.$nLeftOffset.',
            topOffset : '.$nTopOffset.'
          });
        });
		    </script>';
			$Control['Name'] = $Name;
			array_push($this->Controls,$Control);
			$nReturnIndex = count($this->Controls) - 1;
		} else {
			$nReturnIndex = $this->getIdByName($Name);
		}
		return($nReturnIndex);
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @param string Name, Name des auszugebenden Objektes
	 * @return string HTML Code zur Darstellung des Objektes
	 */
	public function get($Name) {
		$myControl = '';
		foreach ($this->Controls as $Control) {
			if ($Control['Name'] == $Name) {
				$myControl = $Control['HTML']; break;
			}
		}
		return($myControl);
	}

	/**
	 * Gibt den Sprachcode für den Kalender zurück
	 * @return string de/en, je Nach sprache
	 */
	private function GetLanguage() {
		switch (page::language()) {
			case LANG_EN: $sLang=  'en'; break;
			case LAND_DE:
			default:
				$sLang = 'de';
		}
		return($sLang);
	}
}