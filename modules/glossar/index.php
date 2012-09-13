<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/glossar') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das Glossar Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewCmsGlossary extends abstractSddView {

	// Glossar Konfiguration
	private $Config;
	// Collection möglicher Buchstaben
	private $LetterCollection;
	// Gesuchte Buchstaben (einer oder drei)
	private $Letters;
	// Gewünschter Range (Range ID oder einzeln)
	private $Range;

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		// Library mit Objekten bestücken
		$this->loadConfig();
		$this->initialize();

		$out = '';
		// Content darstellen HTML, Header, Content
		$this->showHtml($out);
		$this->showHeader($out);
		$this->showContent($out);

		return($out);
	}

	// Konfiguration laden
	private function loadconfig() {
		$Config = array();
		$nMenuID = page::menuID();
		pageConfig::get($nMenuID,$this->Conn,$Config);
		$this->Config = $Config;
	}

	// Glossar Initialisieren
	private function initialize() {
		// Buchstabenarray setzen
		$this->initLetters();
		// Collection initialisieren
		$this->initCollection();
	}

	// HTML aus der Konfiguration darstellen oder Pagetitel
	private function showHtml(&$out) {
		if (strlen($this->Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($this->Config['htmlCode']['Value']);
			$out.= '<div class="divEntryText">'.$this->Config['htmlCode']['Value'].'</div>';
		}
	}

	// Header anzeigen A-C oder ABC je nach Konfig
	public function showHeader(&$out) {
		switch (getInt($this->Config['viewType']['Value'])) {
			case 2:
				$this->showAlphabeticalHeader($out);
				break;
			case 1:
				$this->showArrangedHeader($out);
				break;
		}
	}

	// Glossareinträge anzeigen
	private function showContent(&$out) {
		// Alle Buchstaben verarbeiten
		$nCount = 0;
		foreach ($this->Letters as $Letter) {
			// Einträge für diesen Buchstaben anzeigen
			$this->showEntries($out,$nCount,$Letter);
		}
		// Wenn keine Einträge, melden
		if ($nCount == 0) {
			$out .= '<p>'.$this->Res->html(483,page::language()).'.</p>';
		}
	}

	// Einträge eines Buchstabens anzeigen
	private function showEntries(&$out,&$nCount,$Letter) {
		// Gewünschte Buchstaben für IN() Statement
		$letterIn = $this->LetterCollection[$Letter];
		$Entries = '';
		// Glossareinträge lesen
		$sSQL = "SELECT con_Date,con_ShowDate,con_Title,con_Content FROM tbcontent
		WHERE mnu_ID = ".page::menuID()." AND con_Active = 1 AND
		LOWER(LEFT(con_Title,1)) IN (".$letterIn.") ORDER BY con_Title ASC";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$nCount++;
			stringOps::htmlViewEnt($row['con_Content']);
			$sDate = '';
			if (getInt($row['con_ShowDate']) == 1) {
				$sDate = '('.dateOps::convertDate(
					dateOps::SQL_DATETIME,
					dateOps::EU_DATE,$row['con_ShowDate']
				).')';
			}
			$Entries .= '
			<div class="cDivider"></div>
			<div class="newsContent">
				<strong>'.$row['con_Title'].'</strong> '.$sDate.'<br>
				'.$row['con_Content'].'
			</div>
			';
		}
		// Buchstabe gross schreiben
		$Letter = strtoupper($Letter);
		// Sonderzeichen, wenn $
		if ($Letter == '$') {
			$Letter = $this->Res->html(484,page::language());
		}
		// Zahlen, wenn 0
		if ($Letter == '0') {
			$Letter = $this->Res->html(485,page::language());
		}
		// Alles anzeigen, wenn etwas vorhanden
		if (strlen($Entries) > 0) {
			$out .= '
			<div class="cDivider"></div>
			<div class="newsHead">'.$Letter.'</div>';
			$out .= $Entries;
		}
	}

	// Header nach Alphabet zeigen
	private function showAlphabeticalHeader(&$out) {
		$out .= '
		<br>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				'.$this->insertLetterRange('a','A').'
				'.$this->insertLetterRange('b','B').'
				'.$this->insertLetterRange('c','C').'
				'.$this->insertLetterRange('d','D').'
				'.$this->insertLetterRange('e','E').'
				'.$this->insertLetterRange('f','F').'
				'.$this->insertLetterRange('g','G').'
				'.$this->insertLetterRange('h','H').'
				'.$this->insertLetterRange('i','I').'
				'.$this->insertLetterRange('j','J').'
				'.$this->insertLetterRange('k','K').'
				'.$this->insertLetterRange('l','L').'
				'.$this->insertLetterRange('m','M').'
				'.$this->insertLetterRange('n','N').'
				'.$this->insertLetterRange('o','O').'
				'.$this->insertLetterRange('p','P').'
				'.$this->insertLetterRange('q','Q').'
				'.$this->insertLetterRange('r','R').'
				'.$this->insertLetterRange('s','S').'
				'.$this->insertLetterRange('t','T').'
				'.$this->insertLetterRange('u','U').'
				'.$this->insertLetterRange('v','V').'
				'.$this->insertLetterRange('w','W').'
				'.$this->insertLetterRange('x','X').'
				'.$this->insertLetterRange('y','Y').'
				'.$this->insertLetterRange('z','Z').'
				'.$this->insertLetterRange('0','0-9').'
				'.$this->insertLetterRange('$','#').'
			</tr>
		</table>
		<br>
		';
	}

	// Prüfen ob aktueller Buchstabe ausgewählt ist
	private function insertLetterRange($sLetter,$sDisplay) {
		$sClass = 'cNav';
		$a_Start = '<a href="'.$this->link('letter='.$sLetter).'">';
		$a_End = '</a>';

		if (($_GET['letter'] == $sLetter) || (strlen($_GET['letter']) == 0 && $sLetter == 'a')) {
			$sClass .= 'Selected';
			// Kein Link
			$a_Start = ''; $a_End = '';
		}
		$sRow = '<td class="'.$sClass.'">'.$a_Start.$sDisplay.$a_End.'</a></td>';
		return($sRow);
	}

	// Header nach Staffelung zeigen
	private function showArrangedHeader(&$out) {
		$out .= '
		<br>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				'.$this->insertRange(0,'A - C').'
				'.$this->insertRange(1,'D - F').'
				'.$this->insertRange(2,'G - I').'
				'.$this->insertRange(3,'J - L').'
				'.$this->insertRange(4,'M - O').'
				'.$this->insertRange(5,'P - S').'
				'.$this->insertRange(6,'T - V').'
				'.$this->insertRange(7,'W - Z').'
				'.$this->insertRange(8,'0 - 9').'
				'.$this->insertRange(9,'#').'
			</tr>
		</table>
		<br>
		';
	}

	// Prüfen ob aktueller Range ausgewählt ist
	private function insertRange($nRange,$sDisplay) {
		$sClass = 'cNav';
		$a_Start = '<a href="'.$this->link('range='.$nRange).'">';
		$a_End = '</a>';

		if (getInt($_GET['range']) == $nRange) {
			$sClass .= 'Selected';
			// Kein Link
			$a_Start = ''; $a_End = '';
		}
		$sRow = '<td class="'.$sClass.'">'.$a_Start.$sDisplay.$a_End.'</a></td>';
		return($sRow);
	}

	// Anzusehende Buchstaben setzen
	private function initLetters() {
		// Array initialisieren
		$this->Letters = array();
		// Ansichtstyp Default bestimmen
		switch (getInt($this->Config['viewType']['Value'])) {
			case 2:
				// Ansicht einzeln
				$Range = substr($_GET['letter'],0,1);
				if (strlen($Range) == 0) $Range = 'a';
				// Anhand des Letters, das Array erstellen
				array_push($this->Letters,$Range);
				break;
			case 1:
				// Ansicht gestaffelt
				$Range = getInt($_GET['range']);
				// Anhand des Ranges, Buchstaben bestimmen
				$this->setLettersByRange($Range);
		}
	}

	// Buchstaben anhand eines Ranges setzen
	private function setLettersByRange($Range) {
		switch ($Range) {
			case 0:
				array_push($this->Letters,'a');
				array_push($this->Letters,'b');
				array_push($this->Letters,'c');
				break;
			case 1:
				array_push($this->Letters,'d');
				array_push($this->Letters,'e');
				array_push($this->Letters,'f');
				break;
			case 2:
				array_push($this->Letters,'g');
				array_push($this->Letters,'h');
				array_push($this->Letters,'i');
				break;
			case 3:
				array_push($this->Letters,'j');
				array_push($this->Letters,'k');
				array_push($this->Letters,'l');
				break;
			case 4:
				array_push($this->Letters,'m');
				array_push($this->Letters,'n');
				array_push($this->Letters,'o');
				break;
			case 5:
				array_push($this->Letters,'p');
				array_push($this->Letters,'q');
				array_push($this->Letters,'r');
				array_push($this->Letters,'s');
				break;
			case 6:
				array_push($this->Letters,'t');
				array_push($this->Letters,'u');
				array_push($this->Letters,'v');
				break;
			case 7:
				array_push($this->Letters,'w');
				array_push($this->Letters,'x');
				array_push($this->Letters,'y');
				array_push($this->Letters,'z');
				break;
			case 8:
				array_push($this->Letters,'0');
				break;
			case 9:
				array_push($this->Letters,'$');
				break;
		}
	}

	// Collection aller Buchstaben setzen, Sie bestimmt
	// für welchen Rangebuchstaben unterbuchstaben mäglich sind
	// denn A ist nicht immer = a sondern auch ä,à,â
	private function initCollection() {
		$this->LetterCollection = array(
			'a' => "'a','ä','à','â','á'",
			'b' => "'b'",
			'c' => "'c','¢','ç'",
			'd' => "'d'",
			'e' => "'e','é','ë','é','ê','€'",
			'f' => "'f'",
			'g' => "'g'",
			'h' => "'h'",
			'i' => "'i','í','ï'",
			'j' => "'j'",
			'k' => "'k'",
			'l' => "'l'",
			'm' => "'m'",
			'n' => "'n','ñ'",
			'o' => "'o','ö','ó','ò'",
			'p' => "'p'",
			'q' => "'q'",
			'r' => "'r'",
			's' => "'s'",
			't' => "'t'",
			'u' => "'u','ü','ú','ù'",
			'v' => "'v'",
			'w' => "'w'",
			'x' => "'x'",
			'y' => "'y','ÿ'",
			'z' => "'z'",
			'0' => "'0','1','2','3','4','5','6','7','8','9'",
			'$' => "'§','°','+','*','%','&','/','(',')','=','?','$','#','£','<','>'",
		);
	}
}