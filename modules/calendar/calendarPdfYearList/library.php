<?php
// PDF Objekt
require_once(BP.'/modules/calendar/calendarPdfYearList/calendarYearPdf.php');
// Klasse
class calendarPdfYearList extends yearlyCalendar {
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// Daten dem PDF Generator geben
		$PDF = new calendarYearPdf();
		$PDF->setExportList($this);
		$PDF->generate();
		$PDF->Output();
	}
}