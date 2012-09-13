<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/calendar') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

// Libraries laden (deprecated)
library::req('/modules/calendar/monthlyCalendar');	// Basisklasse für Monatskalender
library::req('/modules/calendar/calendarMonthControl/library');
library::req('/modules/calendar/calendarMonthList/library');
library::req('/modules/calendar/calendarMonthTemplated/library');
library::req('/modules/calendar/calendarMonthListFull/library');
library::req('/modules/calendar/calendarPdfList/library');

library::req('/modules/calendar/yearlyCalendar');	// Basisklasse für Jahreskalender
library::req('/modules/calendar/calendarYearControl/library');
library::req('/modules/calendar/calendarYearList/library');
library::req('/modules/calendar/calendarPdfYearList/library');

/**
 * Viewmodul für die Kalenderansicht(en)
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewEventCalendar extends abstractSddView {
	
	
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}
	
	public function getOutput() {
		// Konfiguration initialisieren
		$Config = array();
		pageConfig::get(page::menuID(),$this->Conn,$Config);

		// Je nach Modus eine Galerie starten
		switch ($Config['viewType']['Value']) {
			case calendarConst::TYPE_YEARCONTROL:
				$Calendar = new calendarYearControl($this->Conn,$this->Res,$Config,$this);
				break;
			case calendarConst::TYPE_YEARLIST:
				$Calendar = new calendarYearList($this->Conn,$this->Res,$Config,$this);
				break;
			case calendarConst::TYPE_MONTHLIST:
				$Calendar = new calendarMonthList($this->Conn,$this->Res,$Config,$this);
				break;
			case calendarConst::TYPE_MONTHLISTFULL:
				$Calendar = new calendarMonthListFull($this->Conn,$this->Res,$Config,$this);
				break;
			case calendarConst::TYPE_MONTH_TEMPLATED:
				$Calendar = new calendarMonthTemplated($this->Conn,$this->Res,$Config,$this);
				break;
			case calendarConst::TYPE_MONTHCONTROL:
			default:
				$Calendar = new calendarMonthControl($this->Conn,$this->Res,$Config,$this);
				break;
		}

		// Drucken?
		if (isset($_GET['print'])) {
			$this->Tpl->setEmpty();
			if (isset($_GET['month'])) {
				$Calendar =  new calendarPdfList($this->Conn,$this->Res,$Config);
			} else {
				$Calendar =  new calendarPdfYearList($this->Conn,$this->Res,$Config);
			}
		}

		// Galerie HTML zurückbekommen
		$Calendar->appendHtml($out);
		// HTML kodieren
		stringOps::htmlViewEnt($out);
		return($out);
	}
}

/**
 * Simple Konstantenklasse
 * @author Michael Sebel <michael@sebel.ch>
 */
class calendarConst {
	const TYPE_MONTHCONTROL = 1;
	const TYPE_MONTHLIST = 2;
	const TYPE_MONTHLISTFULL = 5;
	const TYPE_MONTH_TEMPLATED = 6;
	const TYPE_YEARCONTROL = 3;
	const TYPE_YEARLIST = 4;
}