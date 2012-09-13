<?php
/**
 * Exception für Datenbank Fehler
 */
class sddDbException extends Exception implements sddException {
	
	/**
     * SQL String der zum Fehler führte
     * @var string
	 */
	private $sql = '';
	
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
		$err = '<pre>SQL Error at:
		';
		$stack = $this->getTrace();
		// Rückwärts gehen für sinnvollen Ablauf
		for ($i = count($stack)-1;$i >= 0;$i--) {
			$err.= "\r\n".'#'.$i.' '.$stack[$i]['file'].'('.$stack[$i]['line'].'): ';
			$err.= $stack[$i]['class'].$stack[$i]['type'].$stack[$i]['function'].'();';
		}
		$err.= "
		\r\nMalicious SQL Code because ".$this->message.":
		\r\n".str_replace("\t","",$this->sql);
		$err.= '</pre>';
		return($err);
	}
	
	/**
	 * Eingeben des SQL Strings
	 * @param string sSQL, String der zum Fehler führte
	 */
	public function setSql($sSQL) {
		$this->sql = $sSQL;
	}
	
}