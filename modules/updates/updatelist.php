<?php
// Basislinkliste
abstract class Updatelist {
	
	public $Conn;		// DB Verbindung
	public $Res;		// Sprachressourcen
	public $Config;		// Konfiguration
	public $Data;		// Recordset
	
	// Erster und einziger Konstruktor
	final public function __construct(dbConn &$Conn,resources &$Res, &$Config) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->Config = $Config;
		$this->loadLinks();
	}
	
	// Linkdaten in korrekter Reihenfolge laden
	final private function loadLinks() {
		// Grundsätzlich alle Updates anzeigen
		$sSQL = "SELECT lnk_ID,lnk_Clicks,lnk_Name,lnk_Target,
		lnk_URL,lnk_Desc,lnk_Date FROM tblink 
		WHERE lnk_Active = 1 AND mnu_ID = ".page::menuID()." ";
		// Einschränken auf Datum, wenn konfiguriert
		if ($this->Config['futureUpdates']['Value'] == 0) {
			// Heutiges Datum in der Nacht
			$sTodayNight = dateOps::getTime(dateOps::SQL_DATE).' 23:59:59'; 
			$sSQL .= "AND lnk_Date < '$sTodayNight' ";
		}
		// Ordnen nach Datum absteigend
		$sSQL .= "ORDER BY lnk_Date DESC";
		$this->Data = array();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			// Daten verarbeiten
			$row['lnk_Date'] = dateOps::convertDate(
				dateOps::SQL_DATETIME,	
				dateOps::EU_DATE,
				$row['lnk_Date']
			);
			stringOps::htmlViewEnt($row['lnk_Desc']);
			stringOps::htmlViewEnt($row['lnk_Name']);
			// Daten in Set speichern
			array_push($this->Data,$row);
		}
	}
	
	final public function getLink(&$row) {
		$File = 'out.php?id='.page::menuID().'&link='.$row['lnk_ID'];
		$File = ' href="'.$File.'"';
		$Title = ' title="'.$row['lnk_Desc'].'"';
		$Target = '';
		if (strlen($row['lnk_Target']) > 0) {
			$Target = ' target="'.$row['lnk_Target'].'"';
		}
		$Link = '<a'.$Target.$Title.$File.'>'.$row['lnk_Name'].'</a>&nbsp;';
		return($Link);
	}
	
	abstract public function appendHtml(&$out);
}