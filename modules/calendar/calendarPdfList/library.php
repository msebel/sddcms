<?php
// PDF Objekt
require_once(BP.'/modules/calendar/calendarPdfList/calendarPdf.php');
// Klasse
class calendarPdfList extends monthlyCalendar {
	
	// HTML des Kalenders
	public function appendHtml(&$out) {
		// Daten dem PDF Generator geben
		$PDF = new calendarPdf();
		$PDF->setExportList($this);
		$PDF->generate();
		$PDF->Output();
	}
}