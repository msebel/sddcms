<?php
class calendarMonthControl extends monthlyCalendar {
	
	private $Tooltip;
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// HTML Code einfügen
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($this->Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$this->Config['htmlCode']['Value'].'</div>';
		}
		// Ein paar Objekte initialisieren
		$this->Tooltip = htmlControl::tooltip();
		$out .= $this->Tooltip->initialize();
		// Anzahl leerfächer Monatsbeginn/-ende
		$nOffsetBegin = $this->Day[0]['WeekdayNumber'] - 1;
		$nOffsetEnd = 7 - $this->Day[$this->Days-1]['WeekdayNumber'];
		// Druckbutton erstellen
		if ($this->Printable) {
			$this->appendPrintbutton($out);
		}
		// Kalenderanzeige erstellen
		$this->appendNavigation($out);
		$this->appendHeader($out);
		$this->appendEmptyBoxes($out,$nOffsetBegin);
		$this->appendDays($out);
		$this->appendEmptyBoxes($out,$nOffsetEnd);
		$this->appendFooter($out);
	}
	
	// Navigation darstellen
	private function appendNavigation(&$out) {
		$nNextYear = $this->Year;
		$nNextMonth = $this->Month + 1;
		if ($nNextMonth == 13)  {
			$nNextMonth = 1; $nNextYear++;
		}
		$nPrevYear = $this->Year;
		$nPrevMonth = $this->Month - 1;
		if ($nPrevMonth == 0)  {
			$nPrevMonth = 12; $nPrevYear--;
		}
		$out .= '
		<div class="calendarNavigation">
			<strong>'.dateOps::getMonthName($this->Month,$this->Res).' '.$this->Year.'</strong> - 
			<a href="'.$this->View->link('month='.$nPrevMonth.'&year='.$nPrevYear).'">'.$this->Res->html(595,page::language()).'</a> | 
			<a href="'.$this->View->link('month='.$nNextMonth.'&year='.$nNextYear).'">'.$this->Res->html(596,page::language()).'</a>
		</div>
		';
	}
	
	// Header (Tage und Container) erstellen
	private function appendHeader(&$out) {
		$out .= '
		<div id="calendarContainer">
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(1,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(2,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(3,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(4,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(5,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(6,$this->Res).'</div>
			<div class="calendarHeaderDay">'.dateOps::getWeekdayShort(7,$this->Res).'</div>
		';
	}
	
	// Footer erstellen (Container abschliessen
	private function appendFooter(&$out) {
		$out .= '
		</div>';
	}
	
	// Einzelne Tage und deren Events einfüllen
	private function appendDays(&$out) {
		$nDayIndex = 0;
		foreach ($this->Day as $Day) {
			$EventCount = count($Day['Events']);
			$sClass = 'calendarDayEmpty';
			if ($EventCount > 0) {
				$sClass = 'calendarDayEvent';
			}
			$out .= '<div class="'.$sClass.'" id="eventDay_'.$nDayIndex.'">
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
		$this->Tooltip->add('eventDay_'.$DayIndex,$sContent,$sTitle,350,0);
		$out .= $this->Tooltip->get('eventDay_'.$DayIndex);
	}
	
	// Einzelnen Event in Content einfüllen
	private function insertEvent(&$Content,&$Event,$Multiple) {
		$Content .= '<div class="calendarEvent">';
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
		// Details und Anmeldung
		$Content .= '<tr><td width="100">&nbsp;</td>';
		$Content .= '<td><a class="cMoreLink" href="event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$this->Res->html(442,page::language()).'</a></td></tr>';
		$Content .= '</table>';
	}
	
	// Leere Boxen einfüllen
	private function appendEmptyBoxes(&$out,$nCount) {
		for ($i = 0;$i < $nCount;$i++) {
			$out .= '
			<div class="calendarEmptyBox">&nbsp;</div>';
		}
	}
}