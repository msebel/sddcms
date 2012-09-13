<?php
class moduleSearch extends commonModule {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	private $out;
	private $keyword;
	private $results = array();
	private $found;
	private $paging;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Suche starten
	public function search(&$out) {
		$this->out =& $out;
		// Suchwort holen
		if ($this->checkKeyword() && $this->loadResults()) {
			// Suchformular erneut anzeigen
			$this->showSearchForm();
			// Suchergebnisse für anzeige vorbereiten
			$this->prepareResults();
			$this->sortByRelevance();
			// Ergebnisbalken und Paging generieren
			$this->generatePaging();
			// Suchergebnisse anzeigen
			$this->showResults();
		} else {
			// Nur Suchformular anzeigen
			$this->showSearchForm();
		}
	}
	
	// Suchwort validieren und Meldungen ausgeben
	private function checkKeyword() {
		if (isset($_POST['searchSubmit'])) {
			$this->keyword = trim($_POST['searchKeywords']);
			$_SESSION['searchKeywords'] = trim($_POST['searchKeywords']);
		} else {
			$this->keyword = $_SESSION['searchKeywords'];
		}
		// Suche starten, wenn Begriff korrekt ist
		$bKeywordCorrect = true;
		// Leerer Suchbegriff
		if (strlen($this->keyword) == 0) {
			$bKeywordCorrect = false;
			$this->out .= $this->Res->html(498,page::language());
		}
		// Mindstens 4 Zeichen angeben
		if (strlen($this->keyword) < 4 && $bKeywordCorrect) {
			$bKeywordCorrect = false;
			$this->out .= $this->Res->html(499,page::language());
		}
		return($bKeywordCorrect);
	}
	
	// Suchformular anzeigen
	private function showSearchForm() {
		$this->out .= '
		<br>
		<br>
		<form action="/modules/search/results.php" method="post">
			'.$this->Res->html(497,page::language()).': 
			<input type="text" name="searchKeywords" style="width:200px"> 
			<input type="submit" name="searchSubmit" value="Go" class="cButton">
		</form>
		';
	}
	
	// Suchresultate für aktuelle Seite holen
	private function loadResults() {
		$this->found = 0;
		$bResult = false;
		// Paging vorbereiten
		$nPage = getInt($_GET['page']);
		if ($nPage == 0) $nPage = 1;
		$nLimitEnd = $nPage * 10;
		$nLimitBegin = $nLimitEnd - 10;
		// SQL Abfrage nach Suchbegriff abfeuern
		$this->Conn->escape($this->keyword);
		$sSQL = "SELECT tbcontent.con_Content,tbcontent.mnu_ID,
		IFNULL(tbcontent.con_Title,'') AS con_Title FROM tbcontent 
		INNER JOIN tbmenu ON tbcontent.mnu_ID = tbmenu.mnu_ID
		WHERE tbcontent.con_Content LIKE '%".$this->keyword."%' 
		AND tbmenu.man_ID = ".page::mandant()." AND tbcontent.con_Active = 1
		ORDER BY con_ID LIMIT $nLimitBegin,$nLimitEnd";
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$this->found++; $bResult = true;
			array_push($this->results,$row);
		}
		// Fehlermeldung, wenn keine Resultate vorhanden
		if (!$bResult) {
			$this->out .= $this->Res->html(500,page::language());
		} 
		return($bResult);
	}
	
	// Suchergebnisse vorbereiten für Anzeige
	private function prepareResults() {
		// Alle Resultate einzeln bearbeiten
		for ($i = 0;$i < count($this->results);$i++) {
			// HTML Code aus Content entfernen
			stringOps::noHtml($this->results[$i]['con_Content']);
			// Vorkommen der Keywords für Relevanz zählen
			$nRelevance = 0;
			str_replace(
				$this->keyword,
				'',
				$this->results[$i]['con_Content'],
				$nRelevance
			);
			$this->results[$i]['Relevance'] = $nRelevance;
			// Content trimmen wenn zu lang
			if (strlen($this->results[$i]['con_Content']) > 300) {
				$this->results[$i]['con_Content'] = substr(
					$this->results[$i]['con_Content'],
					0,
					300
				);
				$this->results[$i]['con_Content'] .= '...';
			}
			// Verbleibende Keywords im Content markieren
			$this->results[$i]['con_Content'] = str_ireplace(
				$this->keyword,
				'<span style="font-weight:bold;color:#aa0000">'.$this->keyword.'</span>',
				$this->results[$i]['con_Content']
			);
		}
	}
	
	// Resultate nach Relevanz sortieren
	private function sortByRelevance() {
		$bFound = true;
		while ($bFound == true) {
			// found für nächstes zurücksetzen
			$bFound = false;
			for ($i = 0;$i < count($this->results)-1;$i++) {
				// Prüfen ob String 1 grösser als String 2
				$this->Count++;
				if ($this->results[$i]['Relevance'] < $this->results[$i+1]['Relevance']) {
					// Values tauschen
					$new = $this->results[$i];
					$this->results[$i] = $this->results[$i+1];
					$this->results[$i+1] = $new;
					$bFound = true;
				}
			}
		}
	}
	
	// REsultate anzeigen (wie News)
	private function showResults() {
		$this->out .= $this->paging;
		$this->out .= '<div id="searchResults" style="padding-top:20px;">';
		foreach ($this->results as $Result) {
			$this->out .= '
			<div class="newsHead">
				'.$Result['con_Title'].'
			</div>
			<div class="newsContent">
				<p>'.trim($Result['con_Content']).' 
				<a href="/controller.php?id='.$Result['mnu_ID'].'" class="cMoreLink">'.$this->Res->html(442,page::language()).'</a>
				</p>
			</div>
			<div class="cDivider"></div>
			';
		}
		$this->out .= '</div>';
		$this->out .= $this->paging;
	}
	
	// Paging Funktionalität
	private function generatePaging() {
		$sSQL = "SELECT COUNT(tbcontent.con_ID) FROM tbcontent 
		INNER JOIN tbmenu ON tbcontent.mnu_ID = tbmenu.mnu_ID
		WHERE tbcontent.con_Content LIKE '%".$this->keyword."%' 
		AND tbmenu.man_ID = ".page::mandant()." AND tbcontent.con_Active = 1";
		$nResultsTotal = $this->Conn->getFirstResult($sSQL);
		// 100 Ergebnisse als Maximum (10 Seiten)
		if ($nResultsTotal > 100) $nResultsTotal = 100;
		// Tabellenkopf generieren
		$this->paging .= '
		<table class="pagingTable" width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td>';
		// Seiten anzeigen
		$nPage = getInt($_GET['page']);
		if ($nPage == 0) $nPage = 1;
		$nPages = numericOps::getDecimal(($nResultsTotal / 10),0)+1;
		if ($nPages > 1) {
			$this->paging .= 'Seite: ';
			for ($i = 1;$i <= $nPages;$i++) {
				// Link oder aktuelle Seite
				if ($nPage == $i) {
					$this->paging .= '<a class="cLinkPaging" href="?page='.$i.'"><strong>'.$i.'</strong></a> ';
				} else {
					$this->paging .= '<a class="cLinkPaging" href="?page='.$i.'">'.$i.'</a> ';
				}
			}
		} else {
			$this->paging .= '&nbsp;';
		}
		// Tabelle abschliessen
		$this->paging .= '
			</td>
		</tr>
		</table>
		';
	}
}