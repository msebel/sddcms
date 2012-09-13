<?php
/*
 * cmsSchedule
 * Jobs, die vom jobScheduler aufgerufen werden, müssen eine
 * Klasse cmsJob haben die diese Klasse erbt
 */
abstract class cmsSchedule {

	/**
	 * Datenbankverbindung (Keine Garantie auf welche Datenbank eingestellt!)
	 * @var dbConn
	 */
	protected $Conn;
	/**
	 * Objekt für Resourcen
	 * @var resources
	 */
	protected $Res;
	/**
	 * Output Email Adresse
	 * @var string
	 */
	protected $Email = '';
	/**
	 * Output Buffer für Mail
	 * @var string
	 */
	protected $out = '';

	// Final, nicht veränderbar, übergibt die Datenbankverbindung
	// sowie ein Objekt zur internationalisierung von Texten
	final function __construct(dbConn &$Conn, resources &$Res) {
		$this->Res = $Res;
		$this->Conn = $Conn;
	}

	// Setzt die Emailadresse für den Output, nicht überschreibbar
	final function setEmail(&$Email) {
		$this->Email = $Email;
	}

	// Sendet den Output des Jobs an die gegebene Adresse
	// oder verwirft den Output. Die geschützte Variable
	// $out repräsentiert den Output
	final function sendOutput($JobName) {
		if (stringOps::checkEmail($this->Email)) {
			$Mail = new phpMailer();
			$Mail->From = 'jobScheduler@sdd1.ch';
			$Mail->FromName = 'jobScheduler@sdd1.ch';
			$Mail->Subject = 'jobScheduler Task results \''.$JobName.'\'';
			$Mail->AddAddress($this->Email);
			$Mail->Body = $this->out;
			$Mail->Send();
			// Leeren String zurückgeben
			return('');
		} else {
			return($this->out);
		}
	}

	// Execute, muss implementiert werden. Diese Funktion
	// repräsentiert die Ausführung des Tasks
	abstract function execute();
}