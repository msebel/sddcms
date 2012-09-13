<?php
class calendarMonthList extends monthlyCalendar {
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// HTML Code einfügen
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($this->Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$this->Config['htmlCode']['Value'].'</div>';
		}
		// Erstellen des Druckbuttons
		if ($this->Printable) {
			$this->appendPrintbutton($out);
		}
		// Navigation erstellen
		$this->appendNavigation($out);
		// Alle Tage des Monats durchgehen
		$this->appendDays($out);
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
	
	// Tage ausgeben
	private function appendDays(&$out) {
		$tabs = new tabRowExtender();
		$out .= '<br><br>
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td colspan="2">'.$this->Res->html(365,page::language()).'</td>
			<td>'.$this->Res->html(728,page::language()).'</td>
			<td>'.$this->Res->html(729,page::language()).'</td>
			<td>'.$this->Res->html(730,page::language()).'</td>
			<td>'.$this->Res->html(579,page::language()).'</td>
		</tr>
		';
		// Erste 15 Tage anzeigen
		for ($i = 1;$i <= $this->Days;$i++) {
			$this->appendDay($i,$tabs,$out);
		}
		$out .= '</table>';
	}
	
	// Einen Tag ausgeben
	private function appendDay($nDay,&$tabs,&$out) {
		// Daten zusammentragen
		$bDayPrinted = false;
		$sWeekDay = $this->Day[$nDay-1]['WeekdayShort'];
		foreach ($this->Day[$nDay-1]['Events'] as $Event) {
			// Daten formatieren
			$sFrom = dateOps::convertDate(
				dateOps::SQL_DATETIME,
				dateOps::EU_CLOCK,
				$Event['cal_Start']
			);
			$sTo = '';
			if ($Event['cal_End'] !== NULL) {
				$sTo = dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_CLOCK,
					$Event['cal_End']
				);
			}
			$sLocation = '';
			if (strlen($Event['cal_City']) > 0) {
				$sLocation .= $Event['cal_City'];
			}
			if (strlen($Event['cal_Location']) > 0) {
				if (strlen($sLocation) > 0) $sLocation .= ', ';
				$sLocation .= $Event['cal_Location'];
			}
			// Daten ausgeben
			$out .= '
			<tr class="'.$tabs->getLine().'">
				<td width="25" class="tabRowLight">'.$this->appendDayShort($sWeekDay,$bDayPrinted).'</td>
				<td width="25" class="tabRowLight">'.$this->appendDayNumber($nDay,$bDayPrinted).'</td>
				<td width="50">'.$sFrom.'</td>
				<td width="50">'.$sTo.'</td>
				<td><a class="cMoreLink" href="/modules/calendar/event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$Event['cal_Title'].'</a></td>
				<td>'.$sLocation.'</td>
			</tr>
			';
			$bDayPrinted = true;
		}
		// Wenn Tag nicht ausgegeben, nachholen (leer)
		if (!$bDayPrinted) {
			$out .= '
			<tr class="'.$tabs->getLine().'">
				<td width="25" class="tabRowLight">'.$this->appendDayShort($sWeekDay,$bDayPrinted).'</td>
				<td width="25" class="tabRowLight">'.$this->appendDayNumber($nDay,$bDayPrinted).'</td>
				<td colspan="4">&nbsp;</td>
			</tr>
			';
		}
	}
	
	// Abkürzung für einen Tag zurückgeben
	private function appendDayShort($sWeekDay,$bPrint) {
		if (!$bPrint) {
			$out = $sWeekDay;
		} else {
			$out = '&nbsp;';
		}
		return($out);
	}
	
	// Führende Null anfügen und Datum ausgeben wenn erwünscht
	private function appendDayNumber($nDay,$bPrint) {
		// Führende Null anfügen
		if (getInt($nDay) < 10) {
			$nDay = '0'.$nDay;
		}
		// Datum ausgeben, wenn erwünscht
		if (!$bPrint) {
			$out = $nDay.'.';
		} else {
			$out = '&nbsp;';
		}
		return($out);
	}
}