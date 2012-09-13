<?php
/**
 * Bietet numerischen Operationen an.
 * @author Michael Sebel <michael@sebel.ch>
 */
class numericOps {
	
	/**
	 * Dezimalzahl erhalten auf Kommastelle geschnitten.
	 * Dies ist keine korrekte Rundungsfunktion! Es kommt in
	 * jedem Fall eine Zahl zurück, auch wenn Strings
	 * übergeben werden (0.00, je nach nPrecision).
	 * @param mixed nValue, Zahl die zu trimmen ist
	 * @param integer nPrecision, Anzahl Kommastellen zum abschneiden
	 */
	public static function getDecimal($nValue,$nPrecision = 0) {
		$sValue = (string) (float) $nValue;
		if (strlen($sValue) == 0) $sValue = '0';
		// Allfällige Kommas durch Punkt ersetzen
		$sValue = str_replace(',','.',$sValue);
		// Anzahl Punkte zählen und validieren
		$nCountDots = 0;
		$sValue = str_replace('.','.',$sValue,$nCountDots);
		if ($nCountDots == 0) $sValue .= '.';
		if ($nCountDots > 1) $sValue = '0.';
		// Nachkommastellen zählen
		$nLastIndex = strlen($sValue) - 1;
		$nLastDot = strripos($sValue,'.');
		$nAftercomma = $nLastIndex - $nLastDot;
		// Fehlende Nachkommastellen mit 0 aufüllen
		if ($nAftercomma < $nPrecision) {
			$nMissing = $nPrecision - $nAftercomma;
			for ($i = 0;$i < $nMissing;$i++) {
				$sValue .= '0';
			}
		}
		// überflüssige Nachkommastellen abschneiden
		if ($nAftercomma > $nPrecision) {
			$nCutIndex = $nLastIndex - ($nAftercomma - $nPrecision);
			$sValue = substr($sValue,0,$nCutIndex+1);
		}
		// Dezimalstring zurückgeben
		return($sValue);
	}
	
	/**
	 * Interger Zahl auf Maximum und Minimum validieren.
	 * Minimum zurückgeben, wenn Grösse unter-/überschritten.
	 * @param mixed nValue, Zu validierende Zahl
	 * @param integer nMin, Minimaler Zahlenwert
	 * @param integer nMax, Maximaler Zahlenwert
	 * @return integer validierter Wert, min. Wert wenn ungültig.
	 */
	public static function validateNumber($nValue,$nMin,$nMax) {
		$nValue = getInt($nValue);
		$nMin 	= getInt($nMin);
		$nMax 	= getInt($nMax);
		$bValid = true;
		// Zu gross? Zu klein?
		if ($nValue > $nMax) $bValid = false;
		if ($nValue < $nMin) $bValid = false;
		// Wenn ungütlig, Minimum zurückgeben
		if (!$bValid) $nValue = $nMin;
		return($nValue);
	}

	/**
	 * Wandelt 1/0 (bzw. irgendwas sonst) zu true/false um
	 * @param int $value Zu verwandelnder Wert
	 * @return bool true/false jenachdem ob 1/0 (bzw. irgendwas sonst) übergeben wird
	 */
	public static function getBoolFromInt($value) {
		if (getInt($value) == 1) {
			return(true);
		} else {
			return(false);
		}
	}

	/**
	 * Wandelt true/false (bzw. irgendwas sonst) zu 1/0 um
	 * @param bool $value Zu verwandelnder Wert
	 * @return int 1/0 jenachdem ob true/false (bzw. irgendwas sonst) übergeben wird
	 */
	public static function getIntFromBool($value) {
		if ($value) {
			return(1);
		} else {
			return(0);
		}
	}
}