<?php
class calendarYearList extends yearlyCalendar {
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// HTML Code einfügen
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($this->Config['htmlCode']['Value']);
			$out .= '<div class="divEntryText">'.$this->Config['htmlCode']['Value'].'</div>';
		}
		// Druckbutton erstellen
		if ($this->Printable) {
			$this->appendPrintbutton($out);
		}
		// Navigation erstellen
		$this->appendNavigation($out);
		// Beginn der Tabelle
		$out .= '<br>
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr class="tabRowHead">
			<td>'.$this->Res->html(365,page::language()).'</td>
			<td>'.$this->Res->html(728,page::language()).'</td>
			<td>'.$this->Res->html(729,page::language()).'</td>
			<td>'.$this->Res->html(730,page::language()).'</td>
			<td>'.$this->Res->html(579,page::language()).'</td>
			<td>&nbsp;</td>
		</tr>
		';
		// Alle Monate durchgehen
		for ($i = 0;$i < 12;$i++) {
			$this->Day = $this->Months[$i];
			$this->Month = ($i+1);
			// Anzahl Tage definieren für Berechnung
			$this->Days = date('t',mktime(0,0,0,$this->Month,1,$this->Year));
			$this->appendDays($out);
		}
		$out .= '</table>';
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
	
	// Tage ausgeben
	private function appendDays(&$out) {
		$tabs = new tabRowExtender();
		for ($i = 1;$i <= $this->Days;$i++) {
			$this->appendDay($i,$tabs,$out);
		}
	}
	
	// Einen Tag ausgeben
	private function appendDay($nDay,&$tabs,&$out) {
		// Daten zusammentragen
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
				<td width="50" class="tabRowLight">'.$this->appendDateShort($Event).'</td>
				<td width="50">'.$sFrom.'</td>
				<td width="50">'.$sTo.'</td>
				<td><a class="cMoreLink" href="/modules/calendar/event.php?id='.page::menuID().'&event='.$Event['cal_ID'].'">'.$Event['cal_Title'].'</a></td>
				<td>'.$sLocation.'</td>
				<td>'.$sFlyerHtml.'</td>
			</tr>
			';
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
}