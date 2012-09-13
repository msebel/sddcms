<?php 
/**
 * Erweiterung des BaseControl um Tagclouds.
 * Generiert aus gegebenen Begriffen um Parametern
 * eine Tagcloud und gibt deren HTML Code zurück
 * @author Michael Sebel <michael@sebel.ch>
 */
class tagcloudControl extends abstractControl {
	
	/**
	 * Objekte laden, nichts zu laden
	 */
	public function loadObjects() {}
	
	/**
	 * Template laden
	 * @param meta Meta, Referenz zum Metadaten Objekt
	 */
	public function loadMeta($Meta) {}
	
	/**
	 * Neues Control hinzufügen
	 * @param string Name, eindeutige Name des Objektes
	 * @param string URL, Link auf Begriffe, Keyword wird angehängt
	 */
	public function add($Name,$URL) {
		if (!$this->exists($Name)) {
			$this->Controls[$Name] = array();
			$this->Controls[$Name]['URL'] = $URL;
			$this->Controls[$Name]['Priorities'] = array(3,3,3,3,3);
			$this->Controls[$Name]['Keywords'] = array();
			$this->Controls[$Name]['Keycount'] = array();
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
	 * @param string Name, Objekt welchem das Keyword zugeordnet wird
	 * @param string Keyword, Wort, welches hinzugefügt wird
	 */
	public function addKeyword($Name,$Keyword) {
		$Control =& $this->Controls[$Name];
		$nIndex = array_search($Keyword,$Control['Keywords']);
		// Wenn nicht vorhanden, einfügen
		if ($nIndex === false) {
			// Keyword pushend und counter auf 1 setzen
			array_push($Control['Keywords'],$Keyword);
			array_push($Control['Keycount'],1);
		} else {
			// Counter für den Index erhöhen
			$Control['Keycount'][$nIndex]++;
		}
	}
	
	/**
	 * Control aus einer Liste zurückgeben
	 * @param string Name, Name des zurückzugebenden Objektes
	 * @return string HTML Code zum darstellen der Tagcloud
	 */
	public function get($Name) {
		$out = '';
		$break = false;
		$Links = array();
		$Control =& $this->Controls[$Name];
		$nKeywords = count($Control['Keywords']);
		$nCount = 0;
		// Sortieren
		$this->sortByKeycount($Name);
		// Prioritäten durchgehen und Keywords ausgeben
		for ($prio = 0;$prio < count($Control['Priorities']);$prio++) {
			// Anzahl Keywords durchgehen
			for ($word = 0;$word < $Control['Priorities'][$prio];$word++) {
				$Keyword = $Control['Keywords'][$nCount];
				$nCount++;
				// Wenn alle Wörter durch, loops brechen
				if ($nCount == $nKeywords) $break = true;
				// Schlüsselwort mit Priorität ausgeben
				$Link = ' <a class="cTagcloud'.($prio+1).'" href="'.$Control['URL'].$Keyword.'">'.stringOps::htmlEnt($Keyword).'</a>';
				array_push($Links,$Link);
				// Loop brechen wenn keine Wörter mehr
				if ($break) break;
			}
			// Loop brechen wenn keine Wörter mehr
			if ($break) break;
		}
		// Gesammelte Links (wenn vorhanden) mischen
		if (count($Links) > 0) {
			srand((float)microtime() * 1000000);
			shuffle($Links);
			foreach($Links as $Link) {
				$out .= $Link;
			}
		}
		return($out);
	}
	
	/**
	 * Tag cloud Keyword prioritäten ändern
	 * @param string Name, Name des zuzordnenden Objektes
	 * @param integer p1, Anzahl Links mit Priorität 1
	 * @param integer p2, Anzahl Links mit Priorität 2
	 * @param integer p3, Anzahl Links mit Priorität 3
	 * @param integer p4, Anzahl Links mit Priorität 4
	 * @param integer p5, Anzahl Links mit Priorität 5
	 */
	public function changePriorities($Name,$p1,$p2,$p3,$p4,$p5) {
		$this->Controls[$Name]['Priorities'] = array($p1,$p2,$p3,$p4,$p5);
	}
	
	/**
	 * Keywords nach vorkommen sortieren
	 * @param string Name, Name des zu sortierenden Tagcloudobjektes
	 */
	private function sortByKeycount($Name) {
		array_multisort(
			$this->Controls[$Name]['Keycount'],
			SORT_DESC,
			$this->Controls[$Name]['Keywords']
		);
	}
}