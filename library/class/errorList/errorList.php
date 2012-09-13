<?php
/**
 * Repräsentiert eine Liste von Fehlern
 * @author Michael Sebel <michael@sebel.ch>
 */
class errorList {
	
	/**
	 * Liste der Fehler
	 * @var array
	 */
	private $Errors = NULL;
	
	/**
	 * Erstellt eine leere Fehlerliste
	 */
	public function __construct() {
		$this->Errors = array();
	}
	
	/**
	 * Fügt einen Error hinzu
	 * @param string sError, Fehlermeldung
	 */
	public function add($sError) {
		array_push($this->Errors,$sError);
	}
	
	/**
	 * Fügt einen Error direkt aus dem Resourcen hinzu
	 * @param int nRes, ID aus der Resourcen Tabelle
	 * @param resources Res, Instanz eines Resourcen Objekts
	 */
	public function addResource($nRes, resources &$Res) {
		$sError = $Res->html(getInt($nRes),page::language());
		$this->add($sError);
	}
	
	/**
	 * Speichert die Fehler in eine eindeutige Session
	 */
	public function toSession() {
		$sToken = stringOps::getRandom(20);
		$_SESSION['errorList'][$sToken] = $this->Errors;
		return($sToken);
	}
	
	/**
	 * Gibt an, ob die Liste Fehler entählt
	 */
	public function hasErrors() {
		return(($this->countErrors() > 0));
	}
	
	/**
	 * Gibt an wie viele Fehler die Liste enthält
	 */
	public function countErrors() {
		return(count($this->Errors));
	}
	
	/**
	 * Gibt die Fehlerliste als klassierte Liste aus
	 */
	public function getErrorList() {
		$out = '<ul class="cErrorList">';
		foreach ($this->Errors as $sError) {
			$out .= '<li class="cErrorListEntry">'.$sError.'</li>';
		}
		$out.= '</ul>';
		return($out);
	}
	
	/**
	 * Gibt die Fehlerliste anhand einer gespeicherten Liste aus
	 * Die Liste wird nur einmal ausgegeben, und dann gelöscht. Das
	 * kann man mit der Angabe von $bPreserve = true verhindern
	 * @param unknown_type $sToken
	 */
	public static function getErrorByToken($sToken,$bPreserve = false) {
		$out = '';
		if (isset($_SESSION['errorList'][$sToken])) {
			$out = '<ul class="cErrorList">';
			foreach ($_SESSION['errorList'][$sToken] as $sError) {
				$out .= '<li class="cErrorListEntry">'.$sError.'</li>';
			}
			$out.= '</ul>';
			if (!$bPreserve) unset($_SESSION['errorList'][$sToken]);
		}
		return($out);
	}
}