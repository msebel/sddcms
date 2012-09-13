<?php
class logFlusher extends cmsSchedule {
	
	// Ausführungsfunktion Überschreiben
	public function execute() {
		// Alles was älter ist als 7 Tage
		$now = getdate();
		$nTime = time() - (60 * 60 * 24 * 7);
		$sDate = dateOps::getTime(dateOps::SQL_DATETIME,$nTime);
		$this->Conn->setInstanceDB();
		// Instanzdatenbank optimieren
		$sSQL = "DELETE FROM tblogging WHERE log_Date <= '$sDate'"; 
		$this->out = $this->Conn->command($sSQL).' rows deleted';
	}
}