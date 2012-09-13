<?php
class calendarMonthTemplated extends monthlyCalendar {
	
	/**
	 * Template Objekt aus der View
	 * @var templateImproved
	 */
	private $template = NULL;
	
	// PHP DOC? Bestimmt nicht.
	public function __construct(dbConn &$Conn,resources &$Res,&$Config,&$View) {
		parent::__construct($Conn, $Res, $Config, $View);
		$this->template = $this->View->getTemplate();
	}
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// HTML Code einfügen
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			$this->template->addData('ENTRY_TEXT', $this->Config['htmlCode']['Value']);
		}
		// Erstellen des Druckbuttons
		if ($this->Printable) {
			$this->appendPrintbutton();
		}
		// Navigation erstellen
		$this->appendNavigation();
		// Alle Tage des Monats durchgehen
		$this->appendDays();
		$out .= $this->template->output();
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
		$this->template->addArray(array(
			'MONTH_NAME' => dateOps::getMonthName($this->Month,$this->Res),
			'YEAR' => $this->Year,
			'PREV_LINK' => $this->View->link('month='.$nPrevMonth.'&year='.$nPrevYear),
			'NEXT_LINK' => $this->View->link('month='.$nNextMonth.'&year='.$nNextYear),
			'PREV_TEXT' => $this->Res->html(595,page::language()),
			'NEXT_TEXT' => $this->Res->html(596,page::language())
		));
	}
	
	// Tage ausgeben
	private function appendDays() {
		$this->template->addArray(array(
			'DATE_TEXT' => $this->Res->html(365,page::language()),
			'FROM_TEXT' => $this->Res->html(728,page::language()),
			'TO_TEXT' => $this->Res->html(729,page::language()),
			'EVENT_TEXT' => $this->Res->html(730,page::language()),
			'LOCATION_TEXT' => $this->Res->html(579,page::language())
		));
		$tabs = new tabRowExtender('calendarOddTableRow', 'calendarEvenTableRow');
		$listfile = $this->View->getTemplateFile('calendar-list');
		$list = new templateList(new templateImproved($listfile));
		// Liste aller Tage anzeigen
		for ($i = 1;$i <= $this->Days;$i++) {
			$this->appendDay($i,$tabs,$list);
		}
		// Liste im Main Template hinzufügen
		$this->template->addList('CALENDAR_LIST', $list);
	}
	
	// Einen Tag ausgeben
	private function appendDay($nDay,&$tabs,templateList &$list) {
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
			$list->addData(array(
				'TOGGLED_CLASS' => $tabs->get(),
				'LINED_CLASS' => 'calendarLineTableRow',
				'DAY_SHORT' => $this->appendDayShort($sWeekDay,$bDayPrinted),
				'DAY_NUMBER' => $this->appendDayNumber($nDay,$bDayPrinted),
				'DATE_FROM' => $sFrom,
				'DATE_TO' => $sTo,
				'EVENT_LINK' => '/modules/calendar/event.php?id='.page::menuID().'&event='.$Event['cal_ID'],
				'TITLE' => $Event['cal_Title'],
				'LOCATION' => $sLocation,
			));
			$bDayPrinted = true;
		}
		// Wenn Tag nicht ausgegeben, nachholen (leer)
		if (!$bDayPrinted) {
			$list->addData(array(
				'TOGGLED_CLASS' => $tabs->get(),
				'LINED_CLASS' => 'calendarLineTableRow',
				'DAY_SHORT' => $this->appendDayShort($sWeekDay,$bDayPrinted),
				'DAY_NUMBER' => $this->appendDayNumber($nDay,$bDayPrinted),
				'DATE_FROM' => '&nbsp;',
				'DATE_TO' => '&nbsp;',
				'EVENT_LINK' => '#',
				'TITLE' => '&nbsp;',
				'LOCATION' => '&nbsp;',
			));
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
	
	// Überschreibt die eigentliche Methode
	public function appendPrintbutton() {
		$out = '';
		parent::appendPrintbutton($out);
		$this->template->addData('PRINT_BUTTON', $out);
	}
}