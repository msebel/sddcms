<?php

class mergingEditor {
	
	/**
	 * Originaltext
	 * @var string
	 */
	private $myOriginal = '';
	/**
	 * Neuer Text
	 * @var string
	 */
	private $myNew = '';
	/**
	 * Meta Tag Objekt
	 * @var meta
	 */
	private $myMeta = null;
	
	/**
	 * Initialisiert den Editor
	 * @param string Original, Originaltext
	 * @param string New, Neuer Text
	 */
	public function __construct($Original,$New,meta &$Meta) {
		$this->myOriginal = $Original;
		$this->myNew = $New;
		$this->myMeta = $Meta;
	}
	
	/**
	 * Gibt den Merge der beiden Text aus
	 * @return string HTML Code zur Anzeige
	 */
	public function output() {
		$out = '';
		// Arrays aller Wörter erstellen
		$OriginalWords = $this->getArray($this->myOriginal);
		$NewWords = $this->getArray($this->myNew);
		// Unterschiedliche Teile berechnen
		$OriginalParts = array();
		$NewParts = array();
		$this->mergeParts(
			$OriginalWords,$NewWords,
			$OriginalParts,$NewParts
		);
		// Daraus Divs erstellen und ausgeben
		$out .= '
		<div style="width:98%">
			<div style="width:48%;float:left;">
				<div style="float:left;background-color:#ffff00;">
					'.$this->myOriginal.'
				</div>
			</div>
			<div style="width:48%;float:left;">
				'.$this->getDivs($OriginalParts,$NewParts).'
			</div>
		</div>
		';
		// Javascript Objekte erstellen
		
		return($out);
	}
	
	/**
	 * Erstellt ein vergleichbares Array aus dem gegebenen Text
	 * @param string text, Daraus wird ein Array gemacht
	 * @return array, Vergleichbares Array
	 */
	private function getArray($text) {
		return(explode(' ',$text));
	}
	
	/**
	 * Vergleicht zwei Wörter/Zeichen Arrays
	 * @param array OrigArr, Originales Array
	 * @param array NewArr, zu vergleichendes Array
	 * @param array Orig, Referenz: Hier die Teile des Originals
	 * @param array New, Referenz: Hier die teile des Neuen
	 */
	private function mergeParts(&$OrigArr,&$NewArr,&$Orig,&$New) {
		// Differenzen links/rechts herausfinden
		$this->mergeDifference($Orig,$OrigArr,$NewArr);
	}
	
	/**
	 * Berechnet die Differenzen zwischen zwei Arrays
	 * @param array Differences, Unterschiede werden hier gespeichert
	 * @param array Left, Linke Seite des Vergleichs
	 * @param array Right, Rechte Seite des Vergleichs
	 */
	private function mergeDifference(&$Differences,&$Left,&$Right) {
		$bFinished = false;
		$sAppend = '';
		$nLIdx = 0;
		$nRIdx = 0;
		$nCount = count($Right);
		$nLeftCount = count($Left);
		$nSafety = 1000000;
		while (!$bFinished) {
			// Schauen ob gleich
			if ($Left[$nLIdx] == $Right[$nRIdx]) {
				$sAppend .= $Right[$nRIdx].' ';
				$nLIdx++; $nRIdx++;
			} else {
				if (strlen($sAppend) > 0) {
					$Element['text'] = $sAppend;
					$Element['type'] = 'same';
					array_push($Differences,$Element);
				}
				$sAppend = '';
				// Weiter anfügen, bis wieder eine Übereinstimmung kommt
				$bBreak = false;
				for ($j = $nRIdx;$j < $nCount;$j++) {
					for ($i = $nLIdx;$i < $nLeftCount;$i++) {
						// Prüfen ob gleich
						if ($Left[$nLIdx] == $Right[$nRIdx]) {
							$bBreak = true; break;
						}
					}
					if ($bBreak) break;
					$sAppend .= $Right[$nRIdx].' ';
					$nRIdx++;
				}
				// Wenn etwas vorhanden
				if (strlen($sAppend) > 0) {
					$Element['text'] = $sAppend;
					$Element['type'] = 'different';
					array_push($Differences,$Element);
				}
				$sAppend = ''; 
			}
			// Wenn der Index zu gross ist, aufhören
			if (($nRIdx-1) == $nCount) $bFinished = true;
			// Eventuell durch Sicherung beenden
			if (--$nSafety == 0) $bFinished = true;
		}
		// Wenn es noch zu Appenden hat, hinzufügen
		if (strlen($sAppend) > 0) {
			$Element['text'] = $sAppend;
			$Element['type'] = 'same';
			array_push($Differences,$Element);
		}
		// Rest des Linken Index sammeln
		$sAppend = '';
		for ($i = $nLIdx;$i < $nLeftCount;$i++) {
			$sAppend .= $Left[$i].' ';
		}
		// Auch das hinzufügen
		$Element['text'] = $sAppend;
		$Element['type'] = 'different';
		array_push($Differences,$Element);
	}
	
	/**
	 * Gefärbte Divs ausgeben (Vergleichsdivs)
	 * @param array Left, Zu zeigende Seite
	 * @param array Right, Vergleich um gleiche Teile festzustellen
	 * @return string, Gefärbte Divs
	 */
	private function getDivs(&$Left,&$Right) {
		$out = '';
		// Alle Teile durchgehen
		for ($i = 0;$i < count($Left);$i++) {
			$out .= '
			<div style="float:left;background-color:'.$this->getDivClass($Left[$i]).'">
				'.$Left[$i]['text'].'
			</div>
			';
		}
		return($out);
	}
	
	/**
	 * Gibt die Klasse zurück, je nachdem, ob der Text im Stack gefunden wurde
	 * @param string $needle
	 * @param array $stack
	 * @return string, Klasse für Rot (Unterschied) oder Gelb (Identisch)
	 */
	private function getDivClass($needle) {
		/*$class = '#ff0000';
		foreach($stack as $diff) {
			if ($needle == $diff) {
				$class = '#ffff00';
			}
		}
		return($class);*/
		$class = '#ff0000';
		if ($needle['type'] == 'same') {
			$class = '#ffff00';
		}
		return($class);
	}
}