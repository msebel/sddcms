<?php
class calendarPdf extends fPdf {
	/**
	 * Maximale Anzahl Zeilen pro Seite
	 */
	const MAX_LINES = 49;
	/**
	 * pdfExport Objekt, welches alle Daten
	 * für den Export enthalten sollte
	 * @var calendarPdfList
	 */
	private $ExportList;
	/**
	 * Pattern für die Zellenbreiten
	 * @var array
	 */
	private $Pattern = array();
	/**
	 * Zählt die gezeichneten Zeilen
	 */
	private $Lines = 0;
	
	/**
	 * Objekt ExportList holen
	 * @param calendarPdfList List, Objekt welches Kalenderdaten enthält
	 */
	public function setExportList(calendarPdfList &$List) {
		$this->ExportList = $List;
	}
	
	/**
	 * Zeichnet den Footer der PDF Seite wird von
	 * der FPDF Basis immer automatisch aufgerufen
	 */
	public function Footer() {
		$this->SetDrawColor(0,0,0);
		$this->SetFont('helvetica','',8);
		$this->SetY(-10);
		$this->SetX(7);
	    // Linken und Rechten Text anzeigen
	    $this->Cell(15,0,$this->ExportList->Res->normal(757,page::language()),0,0,'L');
	    $this->Cell(140);
	    $this->Cell(39,0,date('d.m.Y',time()),0,0,'R');
	    // Linie Zeichnen
	    $this->Line(8,283,200,283);
	}
	
	/**
	 * Zeichnet den Header der PDF Seite wird von
	 * der FPDF Basis immer automatisch aufgerufen
	 */
	public function Header() {
		$this->SetDrawColor(0,0,0);
		$this->SetFont('helvetica','',8);
	    // Etwas nach Rechts und Text ausgeben
	    $nMonth = getInt($_GET['month']);
	    $nYear = getInt($_GET['year']);
	    $sMonth = dateOps::getMonthName($nMonth,$this->ExportList->Res);
	    stringOps::htmlEntRev($sMonth);
	    $this->Cell(13,0,$this->ExportList->Res->normal(756,page::language()),0,0,'L');
	    $this->Cell(140);
	    $this->Cell(37,0,$sMonth.' '.$nYear,0,0,'R');
	    // Linie Zeichnen
	    $this->Line(8,15,200,15);
	    $this->SetY(20);
	}
	
	/**
	 * Kontrollfunktion, die den Export steuert
	 */
	public function generate() {
		$this->initializePDF();
		$this->printTitle();
		for ($i = 0;$i < $this->ExportList->Days; $i++) {
			if ($this->checkNext($i)) {
				$this->printTitle();
			} 
			$this->printDays($i);
		}
	}
	
	/**
	 * Initialisiert die PDF Datei und setzt deren Paramater
	 * zudem wird das Zellen-zeichnen Pattern eingefüllt
	 */
	private function initializePDF() {
		// Dokument üffnen und erste Seite hinzufügen
		$this->Open();
		$sTag = $this->ExportList->Res->normal(756,page::language());
		$this->author 	= 'sddCMS Version '.VERSION;
		$this->creator 	= 'sddCMS Version '.VERSION;
		$this->subject 	= $sTag;
		$this->title 	= $sTag;
		$this->keywords = $sTag;
	}
	
	/**
	 * Zeichnet eine Tabellenzeile
	 * @param int nDay, Tag der gezeichnet wird
	 */
	private function printDays($nDay) {
		$bFirstDate = true;
		// Leere Daten einfügen, wenn keine Vorhande
		if (count($this->ExportList->Day[$nDay]['Events']) == 0) {
			$Event['cal_Title'] = '';
			$Event['cal_Location'] = '';
			array_push($this->ExportList->Day[$nDay]['Events'],$Event);
		}
		// Daten durchgehen
		foreach ($this->ExportList->Day[$nDay]['Events'] as $Event) {
			// Erstellen der Titelzeile, fette Schrift mit hinterlegung
			$this->SetFont('helvetica','',8);
			// Titelschriften erstellen
			if ($bFirstDate) {
				$sDayNr = getInt($nDay)+1;
				if ($sDayNr < 10) $sDayNr = '0' . $sDayNr;
				$sDayNr .= '.';
				$sDayView = $this->ExportList->Day[$nDay]['WeekdayShort'];
				$bFirstDate = false;
			} else {
				$sDayNr = '';
				$sDayView = '';
			}
			// Von / Bis erstellen
			if ($Event['cal_Start'] != NULL) {
				$sVon = dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_CLOCK,
					$Event['cal_Start']
				);
			}
			$sBis = '';
			if ($Event['cal_End'] != NULL) {
				$sBis = dateOps::convertDate(
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
			$this->Cell(8,0,$sDayView,0,0,'L');
			$this->Cell(8,0,$sDayNr,0,0,'L');
			$this->Cell(15,0,$sVon,0,0,'L');
			$this->Cell(15,0,$sBis,0,0,'L');
			$this->Cell(70,0,$Event['cal_Title'],0,0,'L');
			$this->Cell(65,0,$sLocation,0,0,'L');
			// Linie darunter
			$this->Line(8,$this->y+2.5,200,$this->y+2.5);
			// Nochmal etwas vorrücken
			$this->SetY($this->y+5);
			$this->Lines++;
		}
	}
	
	/**
	 * Zeichnet die Title Bar und Positioniert
	 * für die nächsten zu druckenden Daten
	 */
	private function printTitle() {
		// Neue Seite hinzufügen, Liniencounter zurücksetzen
		$this->AddPage('P','');
		$this->Lines = 0;
		// Erstellen der Titelzeile, fette Schrift mit hinterlegung
		$this->SetFont('helvetica','B',8);
		$this->SetY($this->y+5.0);
		// Grauer Balken
		$this->SetDrawColor(190,190,190);
		$this->SetFillColor(230,230,230);
		$this->Rect(8,22,192,6,'F');
		// Titelschriften erstellen
		$this->Cell(16,0,$this->ExportList->Res->normal(365,page::language()),0,0,'L');
		$this->Cell(15,0,$this->ExportList->Res->normal(728,page::language()),0,0,'L');
		$this->Cell(15,0,$this->ExportList->Res->normal(729,page::language()),0,0,'L');
		$this->Cell(70,0,$this->ExportList->Res->normal(730,page::language()),0,0,'L');
		$this->Cell(65,0,$this->ExportList->Res->normal(579,page::language()),0,0,'L');
		// Nochmal etwas vorrücken
		$this->SetY($this->y+10.0);
	}
	
	/**
	 * Prüft, ob eine neue Seite nötig ist
	 * @param integer i, Index im Tagearray
	 */
	private function checkNext($i) {
		$NewPage = false;
		// Lines für den nächsten Tag zählen, wenn keine Events
		// Trotzdem eine Line rechnen, da diese leer gedruckt wird
		$nNextLines = count($this->ExportList->Day[$i]['Events']);
		if ($nNextLines == 0) $nNextLines = 1;
		// Berechnen des Maximum
		if (($nNextLines + $this->Lines) > self::MAX_LINES) {
			$NewPage = true;
		}
		return($NewPage);
	}
}