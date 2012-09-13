<?php
// Basislinkliste
abstract class yearlyCalendar {
	
	public $Conn;		// DB Verbindung
	public $Res;		// Sprachressourcen
	public $Data;		// Recordset
	public $Config;		// Konfiguration
	public $Month;		// Zu zeigender Monat
	public $Year;		// Zu zeigendes Jahr
	public $Days;		// Tage in diesem Monat
	public $Day;		// Array aller Tage (mit Eventobjekten drin)
	public $Months;		// Array aller Tage per Monat
	public $Printable;	// Gibt an, ob PDF druckbar oder nicht
	public $View;		// View Objekt
	
	// Erster und einziger Konstruktor
	final public function __construct(dbConn &$Conn,resources &$Res,&$Config,&$View) {
		$this->Conn = $Conn;
		$this->Res = $Res;
		$this->Config = $Config;
		$this->View = $View;
		$this->localConfiguration();
		$this->Months = array();
		$this->defineDate();	// Jahr definieren
		// Alle Monate durchgehen
		for ($i = 0; $i < 12;$i++) {
			$this->Month = ($i+1);
			$this->defineArrays();	// Array aller Tage mit Infos generieren
			$this->loadEvents();	// Recordset int $this->Data erstellen
			$this->assignEvents();	// Events in das $Day Array einfüllen
			array_push($this->Months,$this->Day);
		}
	}
	
	// Lokale Konfiguration laden
	final private function localConfiguration() {
		$this->Printable = false;
		if ($this->Config['pdfPrint']['Value'] == 1) {
			$this->Printable = true;
		}
	}
	
	// Linkdaten in korrekter Reihenfolge laden
	final private function loadEvents() {
		$sSQL = "SELECT cal_ID,kca_ID AS cal_Type,tbkalender.ele_ID,cal_Start,cal_End,cal_Title,
		cal_Location,cal_City,tbelement.ele_File,cal_Text FROM tbkalender 
		LEFT JOIN tbelement ON tbkalender.ele_ID = tbelement.ele_ID
		WHERE (cal_Active = 1 AND mnu_ID = ".page::menuID().")";
		// Einschränken auf Monat / Jahr
		$sDateBegin = mktime(0,0,0,$this->Month,1,$this->Year);
		$sDateBegin = dateOps::getTime(dateOps::SQL_DATETIME,$sDateBegin);
		$sDateEnd	= mktime(23,59,59,$this->Month,$this->Days,$this->Year);
		$sDateEnd	= dateOps::getTime(dateOps::SQL_DATETIME,$sDateEnd);
		$sSQL .= " AND (cal_Start BETWEEN '$sDateBegin' AND '$sDateEnd')";
		// Einschränken auf Startdatum, wenn konfiguriert
		if ($this->Config['showOldDates']['Value'] == 1) {
			$sSQL .= " AND (cal_Start > '".dateOps::getTime(dateOps::SQL_DATETIME)."')";
		}
		$this->Data = array();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			array_push($this->Data,$row);
		}
	}
	
	// Events in das $this->Day array einfügen
	final private function assignEvents() {
		foreach ($this->Data as $Event) {
			// Tag des Events umwandeln in Index des Day Arrays
			$nIndex = dateOps::getStamp(dateOps::SQL_DATETIME,$Event['cal_Start']);
			$nIndex = getInt(date('j',$nIndex)) - 1;
			// In den Event behälter des Tages pushen
			array_push($this->Day[$nIndex]['Events'],$Event);
			sort($this->Day[$nIndex]['Events']);
		}
	}
	
	// Definiert Month und Year Variable nach Konfig / Queryparams
	final private function defineDate() {
		// Grundsätzlich aktuelles Jahr von Query nehmen
		$this->Year = getInt($_GET['year']);
		// Wenn Jahr nicht definiert, aktuelles nehmen
		if ($this->Year == 0) {
			$this->Year = date('Y',time());
		}
	}
	
	// Definiert den Tagearray
	final private function defineArrays() {
		// Wie viele Tage hat der aktuelle Monat?
		$nTime = mktime(0,0,0,$this->Month,1,$this->Year);
		$this->Days = date('t',$nTime);
		// Array für Events vorbereiten
		$this->Day = array();
		for ($i = 1;$i <= $this->Days;$i++) {
			// Array für Events (Wird später befüllt)
			$Day['Events'] = array();
			// Zeitstempel dieses Tages definieren
			$nTime = mktime(0,0,0,$this->Month,$i,$this->Year);
			// Wochentag als Zahl und Strings definieren
			$nNumericDay = date('w',$nTime);
			if ($nNumericDay == 0) $nNumericDay = 7; // Sonntag
			$Day['WeekdayNumber'] = $nNumericDay;
			$Day['WeekdayShort']  = dateOps::getWeekdayShort($nNumericDay,$this->Res);
			$Day['WeekdayLong']   = dateOps::getWeekday($nNumericDay,$this->Res);
			$Day['DayOfMonth']	  = $i;
			// Ins Array einfügen
			array_push($this->Day,$Day);
		}
	}
	
	// Druckbutton drucken
	final public function appendPrintbutton(&$out) {
		$out .= '
		<div id="printButton"
			onClick="javascript:openWindow(\''.$this->View->link('year='.$this->Year.'&print').'\',\'printWindow\',950,700);"
			onMouseout="SetPointer(this.id,\'default\');"
			onMouseover="SetPointer(this.id,\'pointer\');">
			<div style="float:left;">
				<img src="/images/icons/printer.png" alt="'.$this->Res->html(849,page::language()).'" title="'.$this->Res->html(849,page::language()).'">
			</div>
			<div style="float:left;padding-left:5px;font-weight:bold;">'.$this->Res->html(849,page::language()).'</div>
		</div>
		';
	}
	
	// Ausgabe des HTML Codes
	abstract public function appendHtml(&$out);
}