<?php
/**
 * Exception für Core Fehler
 */
class sddCoreException extends Exception implements sddException {
	
	/**
	 * Erstellt die Exception
	 * @param string message, Fehlernachricht
	 */
	public function __construct($message) {
		// Originalen Konstruktor aufrufen
		parent::__construct($message);
	}
	
	/**
	 * Implementation der Stackfunktion
	 */
	public function getStackTraceFormatted() {
		$err = '<pre>Core Error at:
		';
		$stack = $this->getTrace();
		// Rückwärts gehen für sinnvollen Ablauf
		for ($i = count($stack)-1;$i >= 0;$i--) {
			$err.= "\r\n".'#'.$i.' '.$stack[$i]['file'].'('.$stack[$i]['line'].'): ';
			$err.= $stack[$i]['class'].$stack[$i]['type'].$stack[$i]['function'].'();';
		}
		$err.= "\r\n\r\nMessage: \r\n".$this->getMessage();
		$err.= '</pre>';
		return($err);
	}
}