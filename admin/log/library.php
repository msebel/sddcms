<?php
class moduleLog extends commonModule {
	
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
	/**
	 * Gibt an, ob SQL Statement neu gebaut werden muss
	 * @var boolean
	 */
	private $Rebuild = false;
	/**
	 * Gibt Sortierung aufsteigend an für SQL Statements
	 * @var integer
	 */
	const DIRECTION_ASC = 1;
	/**
	 * Gibt Sortierung absteigend an für SQL Statements
	 * @var integer
	 */
	const DIRECTION_DESC = 2;
	/**
	 * Feldsortierung nach Log ID
	 * @var integer
	 */
	const FIELD_NR = 1;
	/**
	 * Feldsortierung nach Kategorie
	 * @var integer
	 */
	const FIELD_CATEGORY = 2;
	/**
	 * Feldsortierung nach Benutzerinfo
	 * @var integer
	 */
	const FIELD_USER = 3;
	/**
	 * Feldsortierung nach Menuinfo
	 * @var integer
	 */
	const FIELD_MENU = 4;
	/**
	 * Feldsortierung nach Fehlermeldung
	 * @var integer
	 */
	const FIELD_ERROR = 5;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Konstruktor, setzt Rebuild, wenn nötig
	public function __construct() {
		// errorlog Session erstellen wenn nötig
		if (!isset($_SESSION['errorlog'])) {
			$_SESSION['errorlog'] = array();
		}
		// Prüfen ob SQL Statement vorhanden
		if (!isset($_SESSION['errorlog']['sql'])) {
			$this->Rebuild = true;
		}
	}
	
	// Nach einem Feld in eine Richtung sortieren
	// Beachten der FIELD_ und DIRECTION_ Konstanten
	public function addSort($Field,$Direction) {
		$nField = getInt($Field);
		$nDirection = getInt($Direction);
		// Feldnamen holen
		$sFieldName = $this->getFieldNameByType($nField);
		// Entsprechenden Sort setzen
		switch ($nDirection) {
			case self::DIRECTION_ASC:
			case self::DIRECTION_DESC:
				break;
			default:
				// ASC als Standard
				$nDirection = self::DIRECTION_ASC;
		}
		// Sortierung setzen/überschreiben
		unset($_SESSION['errorlog']['sort']);
		$_SESSION['errorlog']['sort'][$sFieldName] = $nDirection;
		$this->Rebuild = true;
	}
	
	// Sortierungsicon anzeigen für ein Feld
	public function getSortIcon($sField) {
		$nField = getInt($sField);
		$sFieldName = $this->getFieldNameByType($nField);
		// Schauen, in welche Richtung sortiert wird
		$nSort = getInt($_SESSION['errorlog']['sort'][$sFieldName]);
		// Je nach Status die Grafik definieren
		$sImage = '/images/icons/sort_asc.png';
		$sText = $this->Res->html(845,page::language());
		$nNextSort = self::DIRECTION_ASC;
		switch ($nSort) {
			case self::DIRECTION_ASC:
				$sImage = '/images/icons/sort_desc.png';
				$sText = $this->Res->html(844,page::language());
				$nNextSort = self::DIRECTION_DESC;
				break;
			case self::DIRECTION_DESC:
				$sImage = '/images/icons/sort_asc.png';
				$sText = $this->Res->html(845,page::language());
				$nNextSort = self::DIRECTION_ASC;
				break;
		}
		// Grafik mit Sortierlink zurückgeben
		$sLink = '<a href="index.php?id='.page::menuID().'';
		$sLink.= '&field='.$nField.'&type='.$nNextSort.'&sort" ';
		$sLink.= 'alt="'.$sText.'" title="'.$sText.'">';
		$sLink.= '<img src="'.$sImage.'" border="0"></a>';
		return($sLink);
	}
	
	// Einen Filter zurückbekommen
	public function getFilter($sName) {
		$sFilter = (string) $_SESSION['errorlog']['filter'][$sName];
		if (empty($sFilter) || $sFilter == NULL) {
			$sFilter = '';
		}
		return($sFilter);
	}
	
	// Filter neu setzen
	public function setFilter() {
		// Filter speichern (Suchwort)
		$_SESSION['errorlog']['filter']['search'] = addslashes($_POST['searchword']);
		// Filter zur Datumseinschränkung speichern
		$sStart = addslashes($_POST['datefrom']);
		$sEnd = addslashes($_POST['dateto']);
		// Prüfen und einfüllen
		$_SESSION['errorlog']['filter']['datefrom'] = NULL;
		if (stringOps::checkDate($sStart,dateOps::EU_FORMAT_DATE)) {
			$_SESSION['errorlog']['filter']['datefrom'] = $sStart;
		}
		$_SESSION['errorlog']['filter']['dateto'] = NULL;
		if (stringOps::checkDate($sEnd,dateOps::EU_FORMAT_DATE)) {
			$_SESSION['errorlog']['filter']['dateto'] = $sEnd;
		}
		// Rebuild erzwingen
		$this->Rebuild = true;
	}
	
	// Filter leeren (Zurücksetzen)
	public function resetFilter() {
		// Alle Filter löschen
		unset($_SESSION['errorlog']['filter']);
		// Rebuild erzwingen
		$this->Rebuild = true;
	}
	
	// Daten für den Log laden
	public function loadLog(&$Data) {
		// Paging Engine erstellen
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		// Prüfen, ob neu gebildet wird
		if (!$this->Rebuild) {
			$PagingEngine->start($_SESSION['errorlog']['sql'],15,true);
		} else {
			$sSQL = $this->rebuildSql();
			$PagingEngine->start($sSQL,15,false);
		}
		// Daten abfüllen
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($Row = $this->Conn->next($nRes)) {
			array_push($Data,$Row);
		}
		// HTML Code für Seitennavi zurückgeben
		return($PagingEngine->getHtml());
	}
	
	// Einzelnen Eintrag laden
	public function loadLogEntry(&$Data) {
		$nLogID = getInt($_GET['error']);
		// Alle Felder selektieren
		$sSQL = "SELECT log_ID,man_ID,mnu_ID,usr_ID,log_Type,log_Date,
		log_Userinfo,log_Menuinfo,log_Error,log_Referer,log_Urlinfo,
		log_Postdata,log_Getdata,log_Sessiondata FROM tblogging
		WHERE log_ID = $nLogID";
		// Ausführen und speichern der Daten
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$Data = $row;
		}
	}
	
	// Einen Log Eintrag löschen
	public function deleteLog() {
		$nDeleteID = getInt($_GET['delete']);
		$sSQL = "SELECT COUNT(log_ID) FROM tblogging
		WHERE log_ID = $nDeleteID AND man_ID = ".page::mandant();
		// Löschen, wenn genau ein Resultat
		$nResult = $this->Conn->getCountResult($sSQL);
		if ($nResult == 1) {
			$sSQL = "DELETE FROM tblogging WHERE log_ID = $nDeleteID";
			$this->Conn->command($sSQL);
			// Erfolg melden und weiterleiten
			logging::debug('deleted log entry');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/log/index.php?id='.page::menuID()); 
		} else {
			// Erfolg melden und weiterleiten
			logging::error('error deleting log entry');
			$this->setErrorSession($this->Res->html(55,page::language())); 
			session_write_close();
			redirect('location: /admin/log/index.php?id='.page::menuID()); 
		}
	}
	
	// Kategorienbezeichnung anhand Fehlertyp
	public function getCategoryByType($nType) {
		$nType = getInt($nType);
		$sError = '';
		switch ($nType) {
			case logging::TYPE_INFO:
				$sError = $this->Res->html(840,page::language());
				break;
			case logging::TYPE_DEBUG:
				$sError = $this->Res->html(841,page::language());
				break;
			case logging::TYPE_ERROR:
				$sError = $this->Res->html(842,page::language());
				break;
			case logging::TYPE_FATAL:
				$sError = $this->Res->html(843,page::language());
				break;
		}
		return($sError);
	}
	
	// SQL Query anhand Filter/Sortierungen neu bauen
	private function rebuildSql() {
		// Grundquery definieren
		$sSQL = "SELECT log_ID,man_ID,mnu_ID,usr_ID,log_Userinfo,
		log_Menuinfo,log_Type,log_Error FROM tblogging
		WHERE man_ID = ".page::mandant()." ";
		// Einschränkungen durch Filter
		$sSearch = $_SESSION['errorlog']['filter']['search'];
		$sFrom = $_SESSION['errorlog']['filter']['datefrom'];
		$sTo = $_SESSION['errorlog']['filter']['dateto'];
		$this->Conn->escape($sSearch);
		$this->Conn->escape($sFrom);
		$this->Conn->escape($sTo);
		// Suchwort anhängen wenn vorhanden
		if ($sSearch != NULL) {
			$sSQL .= " AND (
				log_Error LIKE '%$sSearch%' OR
				log_Userinfo LIKE '%$sSearch%' OR
				log_Menuinfo LIKE '%$sSearch%'
			) ";
		}
		// Datumseinschränkungen hinzufügen
		if ($sFrom != NULL || $sTo != NULL) {
			// Wenn nur Start
			if ($sFrom != NULL && $sTo == NULL) {
				$sSQL .= "AND (log_Date >= '$sFrom') ";
			}
			// Wenn nur Ende
			if ($sFrom == NULL && $sTo != NULL) {
				$sSQL .= "AND (log_Date <= '$sTo') ";
			}
			// Wenn beides
			if ($sFrom != NULL && $sTo != NULL) {
				$sSQL .= "AND (log_Date BETWEEN '$sFrom' AND '$sTo') ";
			}
		}
		// Sortierungen mappen
		if (!isset($_SESSION['errorlog']['sort'])) {
			$_SESSION['errorlog']['sort'] = array();
		}
		if (count($_SESSION['errorlog']['sort'])) {
			$sSQL .= 'ORDER BY';
			// Felder durchgehen
			foreach ($_SESSION['errorlog']['sort'] as $Field => $Sort) {
				$sWord = $this->getDirectionByType($Sort);
				$sSQL .= " $Field $sWord,";
			}
			// Letztes Komma entfernen
			$sSQL = substr($sSQL,0,(strlen($sSQL)-1));
		}
		// SQL in Session speichern und zurückgeben
		$_SESSION['errorlog']['sql'] = $sSQL;
		return($sSQL);
	}
	
	// Feldnamen anhand Typ zurückgeben
	private function getFieldNameByType($nType) {
		$nType = getInt($nType);
		$sName = 'undefined';
		// Name herausfinden
		switch ($nType) {
			case self::FIELD_CATEGORY: 
				$sName = 'log_Type';
				break;
			case self::FIELD_ERROR:
				$sName = 'log_Error';
				break;
			case self::FIELD_MENU:
				$sName = 'log_Menuinfo';
				break;
			case self::FIELD_NR:
				$sName = 'log_ID';
				break;
			case self::FIELD_USER:
				$sName = 'log_Userinfo';
				break;
		}
		return($sName);
	}
	
	// Feldsortierung anhand Typ zurückgeben
	private function getDirectionByType($nType) {
		$nType = getInt($nType);
		$sName = 'ASC';
		// Name herausfinden
		switch ($nType) {
			case self::DIRECTION_ASC: 
				$sName = 'ASC';
				break;
			case self::DIRECTION_DESC:
				$sName = 'DESC';
				break;
		}
		return($sName);
	}
}