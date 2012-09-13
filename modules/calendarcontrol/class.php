<?php
require_once(BP.'/modules/calendarcontrol/monthlyTeaserCalendar.php');

class TeaserCalendarControl extends monthlyTeaserCalendar implements teaser {
	
	private $Title;		// Titel des Teasers
	private $TapID;		// ID der Applikation
	private $Tooltip;	// Tooltip Instanz
	
	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}
	
	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
		// Muss Meta hier erneut globalisieren (Keiner weiss warum..)
		$this->Tooltip = htmlControl::tooltip();
		// Teaser Konfigurieren
		$this->Config = array();
		teaserConfig::get($this->TapID,$this->Conn,$this->Config);
		$this->defineDate();
		$this->defineArrays();
		$this->loadEvents(
			$this->Config['menuSource']['Value'],
			$this->constrainEventTypes()
		);
		$this->assignEvents();	// Events in das $Day Array einfüllen
	}
	
	// HTML Code einfüllen
	public function appendHtml(&$out) {
		// Javascript für Tooltips, kann wegen Session evtl. nicht geladen werden
		$out .= '<script type="text/javascript" src="/scripts/controls/tooltip.js"></script>';
		// Anzahl Tage definieren für Berechnung
		$this->Days = date('t',mktime(0,0,0,$this->Month,1,$this->Year));
		// Anzahl leerfächer Monatsbeginn/-ende
		$nOffsetBegin = $this->Day[0]['WeekdayNumber'] - 1;
		$nOffsetEnd = 7 - $this->Day[$this->Days-1]['WeekdayNumber'];
		// Kalenderanzeige erstellen
		$this->appendHeader($out);
		$this->appendEmptyBoxes($out,$nOffsetBegin);
		$this->appendDays($out);
		$this->appendEmptyBoxes($out,$nOffsetEnd);
		$this->appendFooter($out);
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
	
	// Header (Tage und Container) erstellen
	private function appendHeader(&$out) {
		$out .= '
		<div class="calendarSubcontainer">
			<div class="calendarSubcontainerTitle">'.dateOps::getMonthName($this->Month,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(1,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(2,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(3,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(4,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(5,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(6,$this->Res).'</div>
			<div class="calendarHeaderDaySmall">'.dateOps::getWeekdayShort(7,$this->Res).'</div>
		';
	}
	
	// Footer erstellen (Container abschliessen
	private function appendFooter(&$out) {
		$out .= '</div>';
	}
	
	// Einzelne Tage und deren Events einfüllen
	private function appendDays(&$out) {
		$nDayIndex = 0;
		foreach ($this->Day as $Day) {
			$EventCount = count($Day['Events']);
			$sClass = 'calendarDayEmptySmall';
			if ($EventCount > 0) {
				$sClass = 'calendarDayEventSmall';
			}
			$out .= '<div class="'.$sClass.'" id="eventDay_'.$this->Month.'_'.$nDayIndex.'">
			'.$Day['DayOfMonth'].'&nbsp;';
			// Events einfügen wenn vorhanden
			if ($EventCount == 1) {
				$this->insertEvents($out,$nDayIndex,$Day,false);
			} elseif ($EventCount > 1) {
				$this->insertEvents($out,$nDayIndex,$Day,true);
			}
			$out .= '</div>';
			$nDayIndex++;
		}
	}
	
	// Event Tooltip für einen Tag einfügen
	private function insertEvents(&$out,$DayIndex,$Day,$Multiple) {
		// Mehrere Termin an diesem Tag?
		if ($Multiple) {
			// Titel des Tooltips generieren
			$sDate = $Day['DayOfMonth'].'.'.$this->Month.'.'.$this->Year;
			$sTitle = $this->Res->html(578,page::language()).' ';
			$sTitle.= $Day['WeekdayLong'].', '.$sDate;
			// Inhalt initialisieren
			$sContent = '';
			// Events hinzufügen
			foreach ($Day['Events'] as $Event) {
				$this->insertEvent($sContent,$Event,$Multiple);
			}
		} else {
			// Einzelevent anzeigen
			$sTitle = $Day['Events'][0]['cal_Title'];
			$this->insertEventData($sContent,$Day['Events'][0],$Multiple);
		}
		// Tooltip erstellen und ausgeben
		$this->Tooltip->add('eventDay_'.$this->Month.'_'.$DayIndex,$sContent,$sTitle,350,0);
		$out .= $this->Tooltip->get('eventDay_'.$this->Month.'_'.$DayIndex);
	}
	
	// Einzelnen Event in Content einfüllen
	private function insertEvent(&$Content,&$Event,$Multiple) {
		$Content .= '<div class="calendarEventSmall">';
		$this->insertEventData($Content,$Event,$Multiple);
		$Content .= '</div>';
	}
	
	// Eventdaten einfügen
	private function insertEventData(&$Content,&$Event,$Multiple) {
		$Content .= '<table cellpadding="0" cellspacing="0" border="0" class="calendarEventTable">';
		// Titel des Events
		if (strlen($Event['cal_Title']) > 0 && $Multiple) {
			$Content .= '<tr><td width="100">&nbsp;</td>';
			$Content .= '<td><strong>'.$Event['cal_Title'].'</strong></td></tr>';
		}
		// Beginn
		$nBeginStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Event['cal_Start']);
		$sDate = dateOps::getTime(dateOps::EU_DATE,$nBeginStamp);
		$sTime = dateOps::getTime(dateOps::EU_CLOCK,$nBeginStamp);
		$Content .= '<tr><td width="100">'.$this->Res->html(557,page::language()).':</td>';
		$Content .= '<td>'.$sDate.' um '.$sTime.' Uhr</td></tr>';
		// Ende
		if ($Event['cal_End'] != NULL) {
			$nBeginStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Event['cal_End']);
			$sDate = dateOps::getTime(dateOps::EU_DATE,$nBeginStamp);
			$sTime = dateOps::getTime(dateOps::EU_CLOCK,$nBeginStamp);
			$Content .= '<tr><td width="100">'.$this->Res->html(558,page::language()).':</td>';
			$Content .= '<td>'.$sDate.' '.$this->Res->html(580,page::language()).' ';
			$Content .= $sTime.' '.$this->Res->html(581,page::language()).'</td></tr>';
		}
		// Ort
		$sLocation = '';
		if (strlen($Event['cal_City']) > 0) {
			$sLocation .= $Event['cal_City'];
		}
		if (strlen($Event['cal_Location']) > 0) {
			if (strlen($sLocation) > 0) $sLocation .= ', ';
			$sLocation .= $Event['cal_Location'];
		}
		if (strlen($sLocation) > 0) {
			$Content .= '<tr><td width="100">'.$this->Res->html(579,page::language()).':</td>';
			$Content .= '<td>'.$sLocation.'</td></tr>';
		}
		$Content .= '</table>';
	}
	
	// Leere Boxen einfüllen
	private function appendEmptyBoxes(&$out,$nCount) {
		for ($i = 0;$i < $nCount;$i++) {
			$out .= '
			<div class="calendarEmptyBoxSmall">&nbsp;</div>';
		}
	}
	
	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}
}