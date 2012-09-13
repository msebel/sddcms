<?php
class calendarMonthListFull extends monthlyCalendar {
	
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
		// Tabelle beginnen
		$out .= '
		<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr class="tabRowHead">
			<td>'.$this->Res->html(365,page::language()).'</td>
			<td>'.$this->Res->html(728,page::language()).'</td>
			<td>'.$this->Res->html(729,page::language()).'</td>
			<td>'.$this->Res->html(730,page::language()).'</td>
			<td>'.$this->Res->html(579,page::language()).'</td>
			<td>&nbsp;</td>
		</tr>
		';
		// Alle Tage des Monats aufzeigen
		for ($i = 1;$i <= $this->Days;$i++) {
			$this->appendDay($i,$tabs,$out);
		}
		// Tabelle beenden
		$out .= '</table>';
	}
	
	// Einen Tag ausgeben
	private function appendDay($nDay,&$tabs,&$out) {
		// Daten zusammentragen
		$tabs = new tabRowExtender();
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
			// Flyer HTML erstellen
			$sFlyerHtml = '&nbsp;';
			if ($Event['ele_ID'] > 0 && strlen($Event['ele_File']) > 0) {
				$sLink = '/page/'.page::id().'/element/'.$Event['ele_ID'].'/'.$Event['ele_File'];
				// Schauen ob Lightbox möglich
				$sRel = '';
				$sExt = stringOps::getExtension($Event['ele_File']);
				switch (strtolower($sExt)) {
					case '.jpg':
					case '.png':
					case '.gif':
						$sRel = ' rel="lightbox"';
						break;
				}
				$sFlyerHtml = '<a href="'.$sLink.'"'.$sRel.' target="_blank">Flyer</a>';
			}
			// Daten ausgeben
			if (strlen($Event['cal_Text']) == 0) {
				$sClass = ' class="'.$tabs->getLine().'"';
			}
			$sLocation = '';
			if (strlen($Event['cal_City']) > 0) {
				$sLocation .= $Event['cal_City'];
			}
			if (strlen($Event['cal_Location']) > 0) {
				if (strlen($sLocation) > 0) $sLocation .= ', ';
				$sLocation .= $Event['cal_Location'];
			}
			$out .= '
			<tr'.$sClass.'>
				<td width="50" class="tabRowLight">'.$this->appendDateShort($Event).'</td>
				<td width="50">'.$sFrom.'</td>
				<td width="50">'.$sTo.'</td>
				<td><a class="cMoreLink" href="/modules/calendar/event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$Event['cal_Title'].'</a></td>
				<td>'.$sLocation.'</td>
				<td>'.$sFlyerHtml.'</td>
			</tr>
			';
			// Beschreibung ausgeben, wenn vorhanden
			if (strlen($Event['cal_Text']) > 0) {
				$out .= '
				<tr class="'.$tabs->getLine().'">
					<td width="50" class="tabRowLight">&nbsp;</td>
					<td colspan="5">'.$Event['cal_Text'].'</td>
				</tr>
				';
			}
		}
	}
	
	// Abkürzung für einen Tag zurückgeben
	private function appendDateShort(&$Event) {
		$sDate = '';
		// Euro Datum erstellen
		$sDate = dateOps::convertDate(
			dateOps::SQL_DATETIME,
			dateOps::EU_DATE,
			$Event['cal_Start']
		);
		// Und Jahreszahl entfernen
		$sDate = str_replace('.'.$this->Year,'',$sDate);
		return($sDate);
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