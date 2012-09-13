<?php
/**
 * Erstellt einfache und komplete AJAX Requests.
 * Abstrahiert die Javascript Funktionen, daher muss
 * das ajax.js aus dem Systemordner hierfür eingefügt sein.
 * @author Michael Sebel <michael@sebel.ch>
 */
class ajaxRequest {
	
	/**
	 * Einfacher AJAX Request.
	 * @param string URL Requestadresse mit GET Parametern
	 * @return string Javascript Code
	 */
	public static function simple($URL) {
		return("javascript:simpleAjaxRequest('$URL');");
	}
	
	/**
	 * Komplexer Ajaxrequest mit Feedback.
	 * Erstellt einen Request auf die gegebene URL und stellt
	 * im Blockelement ResponseID eine Progressbar. Sobald der
	 * Request Erfolgreich abgeschlossen wirde, wird die Antwort
	 * in das Blockelement ResponseID geschrieben. Schlägt der
	 * Request fehl erscheint darin die 'error occured' Message
	 * @param string URL, Abzusetzende URL mit GET Paraetern
	 * @param string ResponseID, ID eines HTML Blockelementes
	 * @return string Javascript Code
	 */
	public static function response($URL,$ResponseID) {
		$js = "javascript:ajaxRequest(";
		$js.= "'$URL','$ResponseID');";
		return($js);
	}
	
	/**
	 * Aufruf in die Ajax API von sddCMS.
	 * Ruft den in Call beschriebenen Ajax Befehl auf und gibt
	 * dessen Ergebnis in die ResponseID, wenn diese vorhanden ist.
	 * @param string call, Name der API Befehls
	 * @param string params, Parameter im Querystring format
	 * @param string ResponseID, HTML definerte ID für Resultat
	 */
	public static function callApi($call,$params = '',$ResponseID = '') {
		stringOps::alphaNumLow($call);
		$params = addslashes($params);
		$js = "javascript:ajaxApiCall(";
		$js.= "'$call','$params','$ResponseID');";
		return($js);
	}
}