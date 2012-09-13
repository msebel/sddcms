<?php
class calendarYearControl extends yearlyCalendar {
	
	private $Tooltip;
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// HTML Code einfügen
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($this->Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$this->Config['htmlCode']['Value'].'</div>';
		}
		// Tooltip Objekt initialisieren
		$this->Tooltip = htmlControl::tooltip();
		$out .= $this->Tooltip->initialize();
		// Druckbutton erstellen
		if ($this->Printable) {
			$this->appendPrintbutton($out);
		}
		// Navigation hinzufügen
		$this->appendNavigation($out);
		$out .= '<div id="calendarContainerSmall">';
		// Alle Monate durchgehen
		for ($i = 0;$i < 12;$i++) {
			$this->Day = $this->Months[$i];
			$this->Month = ($i+1);
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
			// Gruppen div pro drei Kalender erstellen
			if ($this->Month < 12 && $this->Month % 3 == 0) {
				$out .= '</div>';
				$out .= '<div id="calendarContainerSmall">';
			}
		}
		$out .= '</div>';
	}
	
	// Navigation darstellen
	private function appendNavigation(&$out) {
		$nNextYear = $this->Year+1;
		$nPrevYear = $this->Year-1;
		$out .= '
		<div class="calendarNavigation">
			<strong>'.$this->Res->html(802,page::language()).' '.$this->Year.'</strong> - 
			<a href="'.$this->View->link('year='.$nPrevYear).'">'.$this->Res->html(595,page::language()).'</a> | 
			<a href="'.$this->View->link('year='.$nNextYear).'">'.$this->Res->html(596,page::language()).'</a>
		</div>
		';
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
		$Content .= '<tr><td width="100">&nbsp;</td>';
		$Content .= '<td><a class="cMoreLink" href="/modules/calendar/event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$this->Res->html(442,page::language()).'</a></td></tr>';
		
		$Content .= '</table>';
	}
	
	// Leere Boxen einfüllen
	private function appendEmptyBoxes(&$out,$nCount) {
		for ($i = 0;$i < $nCount;$i++) {
			$out .= '
			<div class="calendarEmptyBoxSmall">&nbsp;</div>';
		}
	}
}