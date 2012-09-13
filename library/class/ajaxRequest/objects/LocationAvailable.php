<?php
/**
 * Gibt den HTML Code für eine gegebene Section zurück
 * @author Michael Sebel <michael@sebel.ch>
 */
class LocationAvailable extends baseRequest {
	
	/**
	 * Gibt den HTML Code für eine gegebene Section zurück
	 */
	public function output() {
		// Welches Objekt nutzen?
		$sClass = stringOps::getPostEscaped('class',$this->Conn);
		$sQuery = stringOps::getPostEscaped('query',$this->Conn);
		switch ($sClass) {
			case 'google': $Coord = new googleCoordinate($sQuery);
		}
		// Erfolg oder Misserfolg melden
		if ($Coord->getLatitude() > 0 && $Coord->getLongitude() > 0) {
			$sText = $this->Res->html(868,page::language());
			$out = '<div style="float:left;">
			<img src="/images/icons/action_go.gif" alt="'.$sText.'" title="'.$sText.'"></div>
			<div style="float:left;margin-left:5px;">'.$sText.'</div>';
		} else {
			$sText = $this->Res->html(869,page::language());
			$out = '<div style="float:left;">
			<img src="/images/icons/action_notgo.gif" alt="'.$sText.'" title="'.$sText.'"></div>
			<div style="float:left;margin-left:5px;">'.$sText.'</div>';
		}
		// Header und Daten ausgeben
		header('Content-type: text/html; charset=ISO-8859-1');
		echo $out;
	}
}