<?php
/**
 * Basisklasse für Admin/View Module.
 * Für den standard Code von sddCMS. Für individuelle 
 * Module ist die additionalModule Klasse (eingeschränkter) zu nutzen.
 * @author Michael Sebel <michael@sebel.ch>
 * @abstract
 */
abstract class commonModule {
	/**
	 * Konstruktor, wird nicht verwendet.
	 * Leer, auch Child Klassen weisen einen leeren Konstruktor auf
	 */
	public function __construct() {}
	
	/**
	 * Definiert die zu ladenden Objekte.
	 * Sollte direkt nach der Instanz aufgerufen werden, damit Abhängikeiten 
	 * erfüllt werden. Ist dazu gedacht zumindest $Conn und $Res zu liefern
	 * @abstract
	 */
	abstract public function loadObjects();
	
	/**
	 * Aktuelles Paging zurücksetzen, da neuer Record oder Records gelöscht wurde(n).
	 * @final
	 */
	final public function resetPaging() {
		if (isset($_SESSION['paging'][''.page::menuID()])) {
			unset($_SESSION['paging'][''.page::menuID()]);
		}
	}

	/**
	 * Gibt den letzten Fehler in $SESSION['errorSession'] zurück und unsettet die Session danach.
	 * @return string Error Message(s)
	 * @final
	 */
	final public function showErrorSession() {
		$sError = '';
		if (isset($_SESSION['errorSession'])) {
			$sError = $_SESSION['errorSession'];
			// Fehler nicht nochmal zeigen
			unset($_SESSION['errorSession']);
		} 
		return($sError);
	}
	
	/**
	 * Schaut ob fehler vorhanden sind.
	 * Gibt entsprechend Meldung, wenn der Fehler eine Erfolgsmeldung ist, 
	 * wird diese ausgegeben und nur in diesem Falle wird die errorSession geunsettet.
	 * @return string HTML Code (Errormessage(s)) mit Zeit
	 * @final
	 */
	final public function checkErrorSession(resources &$Res) {
		$sHtml = '';
		if (isset($_SESSION['errorSession'])) {
			$sHtml .= $Res->html(34,page::language());
			// Wenn die Meldung eine Speicherung und kein Error ist,
			// wird dies angezeigt und die restlichen evtl. Fehler geunsettet
			if ($_SESSION['errorSession'] == $Res->html(57,page::language())) {
				$sHtml = $Res->html(57,page::language());
				unset($_SESSION['errorSession']);
			}
			$sHtml .= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
		}
		return ($sHtml);
	}
	
	/**
	 * Setzt eine errorSession
	 * Nur ein Fehler, oder mehrere Fehler als
	 * Array übergebbar. Mehrere werden Listenförmig dargestellt
	 * @param array/string arrErrors Fehlermeldung(en)
	 */
	final public function setErrorSession($arrErrors) {
		$sError = '';
		// Errors generieren mit Array oder String
		if (is_array($arrErrors)) {
			foreach ($arrErrors as $sItem) {
				$sError .= '- '.$sItem.'<br>';
			}
			$sError .= '<br>';
		} else {
			$sError .= $arrErrors;
		}
		$_SESSION['errorSession'] = $sError;
	}
	
	/**
	 * Checkt ob eine errorSession vorhanden ist.
	 * @return boolean True wenn Fehler vorhanden
	 */
	public function hasErrorSession() {
		$bError = false;
		if (isset($_SESSION['errorSession'])) {
			$bError = true;
		}
		return($bError);
	}
}