<?php

class jobScheduler {

	// Variablen
	private $Conn;
	private $Types;
	private $Res;
	private $Output;
	private $Jobs = array();

	// Konstanten
	const TYPE_DAILY = 1;
	const TYPE_WEEKLY = 2;
	const TYPE_MONTHLY = 3;

	public function __construct(dbConn &$Conn,resources &$Res) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->Output = '';
		// Cron Path setzen und Files includen
		$this->initialize();
		// Scheduling starten
		$this->run();
	}

	public function getOutput() {
		return($this->Output);
	}

	// Scheduler vorbereiten
	private function initialize() {
		// Datenbank global setzen
		$this->Conn->setGlobalDB();
	}

	// Scheduler ausführen
	private function run() {
		// Timeout etwas höher setzen als normal ...
		set_time_limit(0);
		// Bestimmen, welche Jobs ausgeführt werden
		$this->setTypes();
		// Jobs holen und ausführen
		$this->searchJobs();
		$this->executeJobs();
	}

	// Jobs suchen und speichern
	private function searchJobs() {
		// SQL Abfrage für Jobs definieren
		$sSQL = "SELECT crn_Scriptname,crn_Email,crn_Classname
		FROM tbcron WHERE crn_active = 1";
		// SQL Abfrage mit Jobtypen einschränken
		$this->appendTypeSQL($sSQL);
		// Abfrage starten und Jobs ausführen
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Jobs,$row);
		}
	}

	// Jobs ausführen
	private function executeJobs() {
		// Datenbank auf Instanz setzen
		$this->Conn->setInstanceDB();
		// Jobs ausführen wenn vorhanden
		foreach ($this->Jobs as $Job) {
			// Job prüfen und ausführen wenn keine Exception
			try {
				$this->checkJob($Job);
				// Job ausführen
				$this->executeJob($Job);
			} catch (Exception $e) {
				// Mail an den Administrator mit Infos
				$this->sendError($Job);
			}
		}
	}

	// Einen Job definitiv ausführen
	private function executeJob(&$Job) {
		eval('$myJob = new '.$Job['crn_Classname'].'($this->Conn,$this->Res);');
		$this->Output .= 'Executing Job "'.$Job['crn_Classname'].'"';
		$myJob->setEmail($Job['crn_Email']);
		$myJob->execute();
		$this->Output .= $myJob->sendOutput($Job['crn_Classname']);
	}

	// Fehlermeldung per Mail senden
	private function sendError(&$Job) {
		$Mail = new phpMailer();
		$Mail->From = 'jobScheduler@sdd1.ch';
		$Mail->FromName = 'jobScheduler@sdd1.ch';
		$Mail->Subject = 'Error In jobScheduler';
		$Mail->AddAddress('michael@sebel.ch');
		$Mail->Body = '
		Fehler im jobScheduler:

		$Job[\'crn_Scriptname\'] = '.$Job['crn_Scriptname'].'
		';
		$Mail->Send();
	}

	// Prüfen ob ein Job korrekt ist
	private function checkJob(&$Job) {
		// Prüfen ob das File existiert
		if (!file_exists(BP.'/cron/jobs/'.$Job['crn_Scriptname'])) {
			throw new Exception();
		}
		// File includieren
		require_once(BP.'/cron/jobs/'.$Job['crn_Scriptname']);
		// Schauen ob die Jobklasse nun existiert
		if (!class_exists($Job['crn_Classname'],false)) {
			throw new Exception();
		}
		// Schauen ob cmsScheduler geerbt wurde
		if (!(get_parent_class($Job['crn_Classname']) == 'cmsSchedule')) {
			throw new Exception();
		}
	}

	// SQL Teil für Typen anbinden
	private function appendTypeSQL(&$sSQL) {
		$sSQL .= ' AND (';
		// Typen einbinden
		foreach ($this->Types as $Type) {
			$sSQL .= 'crn_Type = '.$Type.' OR ';
		}
		// Letzes OR wieder entfernen
		$nLength = strlen($sSQL);
		$sSQL = substr($sSQL,0,$nLength - 4);
		$sSQL .= ')';
	}

	// Auszuführende Tasks bestimmen
	private function setTypes() {
		$this->Types = array();
		// Tägliche Jobs immer ausführen
		array_push($this->Types,self::TYPE_DAILY);
		// Wenn Sonntag, Weekly Tasks erledigen
		$nDayOfWeek = date('w',time());
		if ($nDayOfWeek == 0) {
			array_push($this->Types,self::TYPE_WEEKLY);
		}
		// Wenn erster Monatstag, Monthly Tasks erledigen
		$nDayOfMonth = date('j',time());
		if ($nDayOfMonth == 1) {
			array_push($this->Types,self::TYPE_MONTHLY);
		}
	}
}
