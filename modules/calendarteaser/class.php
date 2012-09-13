<?php
class TeaserCalendar implements teaser {
	
	private $Res;		// Resourcen
	private $Conn;		// DB Connection
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $Config;	// Konfiguration
	
	// Konstruieren
	public function __construct() {
		
	}
	
	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}
	
	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		$this->Config = array();
		teaserConfig::get($this->TapID,$this->Conn,$this->Config);
		if ($this->Config != false) {
			// SQL Zeit von "in einer Stunde"
			$sDateMin = dateOps::getTime(dateOps::SQL_DATETIME,time()+3600);
			// Kalendereinträge darstellen
			$sSQL = "SELECT cal_Location,cal_City,cal_Title,cal_Start FROM tbkalender
			WHERE mnu_ID = ".$this->Config['menuSource']['Value']." AND cal_Active = 1 
			AND cal_Start > '$sDateMin' ".$this->constrainEventTypes()." 
			ORDER BY cal_Start ASC LIMIT 0,".$this->Config['eventCount']['Value'];
			$nRes = $this->Conn->execute($sSQL);
			$nCount = 0;
			while ($row = $this->Conn->next($nRes)) {
				$nCount++;
				// Texte formatieren
				stringOps::htmlViewEnt($row['cal_City']);
				stringOps::htmlViewEnt($row['cal_Location']);
				stringOps::htmlViewEnt($row['cal_Title']);
				// Datum zur Darstellung
				$sDate = dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_DATE,
					$row['cal_Start']
				);
				// Timestamp des Datums holen und Jahr/Monat für Link definieren
				$nStamp = dateOps::getStamp(dateOps::EU_DATE,$sDate);
				$sLink = '/modules/calendar/index.php?id='.$this->Config['menuSource']['Value'];
				$sLink.= '&month='.getInt(date('m',$nStamp));
				$sLink.= '&year='.getInt(date('Y',$nStamp));
				$out .= '<p><a class="cMore" href="'.$sLink.'">'.$sDate.'</a>:<br>';
				if (strlen($row['cal_Title']) > 0) $out .= $row['cal_Title'].'<br>';
				if (strlen($row['cal_City']) > 0) $out .= $row['cal_City'].'<br>';
				if (strlen($row['cal_Location']) > 0) $out .= $row['cal_Location'].'<br>';
				// Letztes <br> entfernen und <p> abschliessen
				$out = substr($out,0,strlen($out)-4);
				$out.= '</p>';
			}
			// Meldung wenn keine Termine
			if ($nCount == 0) {
				$out .= '<p>'.$this->Res->html(597,page::language()).'</p>';
			}
		} else {
			$out .= '<p>'.$this->Res->html(598,page::language()).'</p>';
		}
	}
	
	// Eventtypen einschränken
	private function constrainEventTypes() {
		$sConstraint = '';
		$doConstraint = false;
		$bFirstDone = false;
		$doConstraint |= ($this->Config['viewConcerts']['Value'] == 1);
		$doConstraint |= ($this->Config['viewOthers']['Value'] == 1);
		// Einschränken, wenn erwünscht
		if ($doConstraint) {
			$sConstraint .= ' AND cal_Type IN(';
			if ($this->Config['viewConcerts']['Value'] == 1) {
				$sConstraint .= '0'; $bFirstDone = true;
			}
			if ($bFirstDone) $sConstraint .= ',';
			if ($this->Config['viewOthers']['Value'] == 1) {
				$sConstraint .= '1';
			}
			$sConstraint .= ')';
		}
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
}