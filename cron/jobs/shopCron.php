<?php
class shopCron extends cmsSchedule {
	
	// Ausführungsfunktion Überschreiben
	public function execute() {
		// Alle Order die auf "Warenkorb" Status sind und älter als ein Tag
		$stmt = $this->Conn->prepare('SELECT sho_ID FROM tbshoporder WHERE sho_State = 0 AND sho_Date < :sho_Date');
		$stmt->bind('sho_Date',dateOps::getTime(dateOps::SQL_DATETIME,time()-(24*60*60),PDO::PARAM_STR));
		$stmt->select();
		while ($row = $stmt->fetch()) {
			// Löschen der Artikel
			$this->Conn->command('DELETE FROM tbshoporderarticle WHERE sho_ID = '.$row['sho_ID']);
			// Löschen des Orders selbst
			$this->Conn->command('DELETE FROM tbshoporder WHERE sho_ID = '.$row['sho_ID']);
		}
	}
}