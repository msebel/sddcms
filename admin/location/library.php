<?php
// Klasse zum speichern von Locations und Routen
class moduleLocation extends commonModule {
	
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
	 * ID der aktuellen Karte
	 * @var int
	 */
	private $MapID;
	/**
	 * Datentyp für Locations
	 * @var int
	 */
	const ROW_TYPE_LOCATION = 1;
	/**
	 * Datentyp für Routen
	 * @var int
	 */
	const ROW_TYPE_ROUTE = 2;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Holt die Karten ID und speichert sie lokale
	public function initialize() {
		$this->MapID = $this->getMapId();
	}
	
	// Lädt eine Liste der Locations und Routen (Nach Name sortiert oder Typ)
	public function loadData(&$Data) {
		$nMapID = $this->MapID;
		$sSQL = "SELECT mlc_ID AS ID, mlc_Name AS Name, 1 AS Type
		FROM tblocation WHERE map_ID = $nMapID AND mlc_Type = ".mapOps::TYPE_LOCATION."
		UNION SELECT mrt_ID AS ID, mrt_Name AS Name, 2 AS
		TYPE FROM tbroute WHERE map_ID = $nMapID
		ORDER BY Name ASC, Type ASC";
		$paging = new paging($this->Conn,'index.php?id='.page::menuID());
		$paging->start($sSQL,10);
		$nRes = $this->Conn->execute($paging->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			// Wenn Name leer, standard Text
			if (strlen($row['Name']) == 0) {
				$row['Name'] = '< '.$this->Res->html(425,page::language()).' >';
			}
			array_push($Data,$row);
		}
		return($paging->getHtml());
	}
	
	// Fügt eine Location an die Karte hinzu
	public function addLocation() {
		mapOps::addLocation($this->Conn,$this->MapID,mapOps::TYPE_LOCATION);
		$this->resetPaging();
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/index.php?id='.page::menuID());
	}
	
	// Fügt der aktuellen Route in Via hinzu
	public function addVia() {
		// Koordinaten holen
		$nMrtID = getInt($_GET['mrt']);
		$nBack = getInt($_GET['back']);
		// Via Location hinzufügen
		$nMlcID = mapOps::addLocation($this->Conn,$this->MapID,mapOps::TYPE_ROUTEVIA);
		mapOps::addRouteLocationConnection($this->Conn,$nMrtID,$nMlcID);
		// Sortierung korrigieren und speichern
		$FixedRoute = mapOps::fixRouteorder($nMrtID,$this->Conn);
		mapOps::saveRouteArray($FixedRoute,$this->Conn);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/route.php?id='.page::menuID().'&back='.$nBack.'&mrt='.$nMrtID);
	}
	
	// Fügt eine Route an die Karte hinzu
	public function addRoute() {
		$nRouteID = mapOps::addRoute($this->Conn,$this->MapID);
		// Start und End Location erstellen
		$nStartID = mapOps::addLocation($this->Conn,$this->MapID,mapOps::TYPE_ROUTESTART);
		$nEndID = mapOps::addLocation($this->Conn,$this->MapID,mapOps::TYPE_ROUTEEND);
		// In die Verbindungstabelle einfügen
		mapOps::addRouteLocationConnection($this->Conn,$nRouteID,$nStartID);
		mapOps::addRouteLocationConnection($this->Conn,$nRouteID,$nEndID);
		// Erfolg melden und Weiterleiten
		$this->resetPaging();
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/index.php?id='.page::menuID());
	}
	
	// Route speichern (Routenübersicht)
	public function saveRoute($nMrtID) {
		// Aktuelle Daten laden und präparieren
		$Data = array();
		$nback = getInt($_GET['back']);
		$RouteData = mapOps::loadRouteLocations($nMrtID,$this->Conn);
		mapOps::prepareRouteArray($RouteData,$Data);
		// Updaten von Start und Ziel (Nur Name)
		$Data['start']['mlc_Name'] = stringOps::getPostEscaped('startname',$this->Conn);
		$Data['goal']['mlc_Name'] = stringOps::getPostEscaped('goalname',$this->Conn);
		// Vias anpassen (Name / Sortierung)
		$nSort = 1;
		for ($i = 0;$i < count($_POST['viaid']);$i++) {
			$this->updateViaById($_POST['viaid'][$i],$Data,++$nSort,$i);
		}
		// Speichern der Daten
		mapOps::saveRouteArray($Data,$this->Conn);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/route.php?id='.page::menuID().'&back='.$nBack.'&mrt='.$nMrtID);
	}
	
	// Alle Einträge speichern
	public function saveMap() {
		// Zählen wie viele Form Elemente vorhanden sind
		$nForms = count($_POST['id']);
		// Diese alle speichern
		for ($i = 0;$i < $nForms;$i++) {
			$nID = getInt($_POST['id'][$i]);
			$nType = getInt($_POST['type'][$i]);
			$sName = $_POST['name'][$i];
			$this->Conn->escape($sName);
			// Je nach Typ updaten
			switch ($nType) {
				case self::ROW_TYPE_LOCATION:
					$this->saveLocationOverview($nID,$sName); break;
				case self::ROW_TYPE_ROUTE:
					$this->saveRouteOverview($nID,$sName); break;
			}
		}
		// Erfolg ausgeben und weiterleiten
		logging::debug('saved locations and routes');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/index.php?id='.page::menuID());
	}
	
	// Eine Route und sämtliche Verbundenen Daten löschen
	public function deleteRoute() {
		$nDeleteID = getInt($_GET['deleteroute']);
		// Gehört die Route zur aktuellen Karte?
		$sSQL = "SELECT COUNT(mrt_ID) FROM tbroute
		WHERE mrt_ID = $nDeleteID AND map_ID = ".$this->MapID;
		$nResult = $this->Conn->getCountResult($sSQL);
		// Route löschen
		if ($nResult == 1) {
			mapOps::deleteRoute($nDeleteID,$this->Conn);
			$this->resetPaging();
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/location/index.php?id='.page::menuID());
		} else {
			// Misserfolg ...
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/location/index.php?id='.page::menuID());
		}
	}
	
	// Eine Location löschen
	public function deleteLocation() {
		$nDeleteID = getInt($_GET['deletelocation']);
		// Gehört die Location zur aktuellen Karte?
		$sSQL = "SELECT COUNT(mlc_ID) FROM tblocation
		WHERE mlc_ID = $nDeleteID AND map_ID = ".$this->MapID;
		$nResult = $this->Conn->getCountResult($sSQL);
		// Location löschen
		if ($nResult == 1) {
			mapOps::deleteLocation($nDeleteID,$this->Conn);
			$this->resetPaging();
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/location/index.php?id='.page::menuID());
		} else {
			// Misserfolg ...
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/location/index.php?id='.page::menuID());
		}
	}
	
	// Löschen eines Vias einer Route
	public function deleteVia($nMrtID) {
		$nDeleteID = getInt($_GET['deletevia']);
		$nBack = getInt($_GET['back']);
		// Prüfen ob das Via zur angegebenen Route gehört
		$sSQL = "SELECT COUNT(mrl_ID) FROM tbroute_location
		WHERE mrt_ID = $nMrtID AND mlc_ID = $nDeleteID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Location löschen
		if ($nResult == 1) {
			mapOps::deleteRouteLocation($nDeleteID,$this->Conn);
			// Sortierung korrigieren und speichern
			$FixedRoute = mapOps::fixRouteorder($nMrtID,$this->Conn);
			mapOps::saveRouteArray($FixedRoute,$this->Conn);
			// Erfolg melden und Weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/location/route.php?id='.page::menuID().'&back='.$nBack.'&mrt='.$nMrtID);
		} else {
			// Misserfolg ...
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/location/route.php?id='.page::menuID().'&back='.$nBack.'&mrt='.$nMrtID);
		}
	}
	
	// Eine Location anhand der ID laden
	public function loadLocation($nMlcID,&$Data) {
		$Data = mapOps::loadLocation($nMlcID,$this->Conn);
	}
	
	// Eine Location anhand der ID laden
	public function loadRoute($nMrtID,&$Data) {
		// Locations dieser Route laden
		$RouteData = mapOps::loadRouteLocations($nMrtID,$this->Conn);
		mapOps::prepareRouteArray($RouteData,$Data);
	}
	
	// Eine Location speichern
	public function saveLocation($nMlcID) {
		// Koordinaten holen
		$nMrtID = getInt($_GET['mrt']);
		$nBack = getInt($_GET['back']);
		$nLat = numericOps::getDecimal($_POST['latitude'],7);
		$nLng = numericOps::getDecimal($_POST['longitude'],7);
		// Query / HTML / Icon holen und escapen
		$sHtml = stringOps::getPostEscaped('html',$this->Conn);
		$sQuery = stringOps::getPostEscaped('query',$this->Conn);
		$sIcon = stringOps::getPostEscaped('iconurl',$this->Conn);
		stringOps::noHtml($sQuery);
		stringOps::noHtml($sIcon);
		stringOps::htmlEntRev($sHtml);
		// Daten für Location bereit machen
		$savedFields = array();
		mapOps::addSaveable('mlc_Html',"'$sHtml'",$savedFields);
		mapOps::addSaveable('mlc_Icon',"'$sIcon'",$savedFields);
		mapOps::addSaveable('mlc_Query',"'$sQuery'",$savedFields);
		mapOps::addSaveable('mlc_Longitude',"$nLng",$savedFields);
		mapOps::addSaveable('mlc_Latitude',"$nLat",$savedFields);
		mapOps::saveLocation($nMlcID,$savedFields,$this->Conn);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/location.php?id='.page::menuID().'&back='.$nBack.'&mlc='.$nMlcID.'&mrt='.$nMrtID);
	}
	
	// Zugriff auf eine Location prüfen
	public function checkLocationAccess($nMlcID) {
		$sSQL = "SELECT COUNT(mlc_ID) FROM tblocation
		WHERE mlc_ID = $nMlcID AND map_ID = ".$this->MapID;
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1) {
			logging::error('location/menu access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Zugriff auf eine Route prüfen
	public function checkRouteAccess($nMrtID) {
		$sSQL = "SELECT COUNT(mrt_ID) FROM tbroute
		WHERE mrt_ID = $nMrtID AND map_ID = ".$this->MapID;
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1) {
			logging::error('route/menu access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Konfiguration neu erstellen
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,3)) {
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'altCssMap',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'altCssRoute',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Konfiguration speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		$Config['htmlCode']['Value'] = $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Klassen speichern
		$Config['altCssMap']['Value'] = $_POST['altCssMap'];
		stringOps::alphaNumOnly($Config['altCssMap']['Value']);
		$Config['altCssRoute']['Value'] = $_POST['altCssRoute'];
		stringOps::alphaNumOnly($Config['altCssRoute']['Value']);
		// Konfiguration speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Speichern des Zoomfaktors
		$nZoom = getInt($_POST['zoom']);
		$nZoom = numericOps::validateNumber($nZoom,1,100);
		$this->setMapData('map_Zoom',$nZoom);
		// Erfolg speichern und weiterleiten
		logging::debug('saved map config config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/location/config.php?id='.page::menuID());
	}
	
	// Feld der Karte speichern
	public function setMapData($name,$value) {
		$sSQL = "UPDATE tbmap SET $name = $value WHERE map_ID = ".$this->MapID;
		$this->Conn->command($sSQL);
	}
	
	// Feld der Karte zurückgeben
	public function getMapData($name) {
		$sSQL = "SELECT $name FROM tbmap WHERE map_ID = ".$this->MapID;
		return($this->Conn->getFirstResult($sSQL));
	}
	
	// Editlink in der Übersicht anhand Typ generieren
	public function getEditLink($nType,$nID) {
		$sLink = '';
		switch (getInt($nType)) {
			case self::ROW_TYPE_LOCATION:
				$sLink = 'location.php?id='.page::menuID().'&mlc='.$nID; 
				$sLink.= '&back='.self::ROW_TYPE_LOCATION;
				break;
			case self::ROW_TYPE_ROUTE:
				$sLink = 'route.php?id='.page::menuID().'&mrt='.$nID; 
				$sLink.= '&back='.self::ROW_TYPE_ROUTE;
				break;
		}
		return($sLink);
	}
	
	// Backlink aus dem Location admin
	public function getLocationBacklink() {
		$sLink = '';
		$nBack = getInt($_GET['back']);
		switch ($nBack) {
			case self::ROW_TYPE_LOCATION:
				$sLink = 'index.php?id='.page::menuID().'&back='.$nBack;
				break;
			case self::ROW_TYPE_ROUTE:
				$sLink = 'route.php?id='.page::menuID().'&back='.$nBack.'&mrt='.getInt($_GET['mrt']); 
				break;
		}
		return($sLink);
	}
	
	// Editlink in der Übersicht anhand Typ generieren
	public function getDeleteLink($nType,$nID) {
		$sLink = '';
		switch (getInt($nType)) {
			case self::ROW_TYPE_LOCATION:
				$sLink = 'index.php?id='.page::menuID().'&deletelocation='.$nID; break;
			case self::ROW_TYPE_ROUTE:
				$sLink = 'index.php?id='.page::menuID().'&deleteroute='.$nID; break;
		}
		return($sLink);
	}
	
	// Je nach Typ, Icon zurückgeben
	public function getIcon($nType) {
		switch (getInt($nType)) {
			case self::ROW_TYPE_LOCATION:
				$sIcon = '/images/icons/world.png';
				$sText = $this->Res->html(878,page::language()); 
				break;
			case self::ROW_TYPE_ROUTE:
				$sIcon = '/images/icons/map.png';
				$sText = $this->Res->html(879,page::language()); 
				break;
		}
		$sIcon = '<img src="'.$sIcon.'" alt="'.$sText.'" title="'.$sText.'">';
		return($sIcon);
	}
	
	// Code für Location je nach Typ
	public function getAddLocationCode($nType,&$Data) {
		switch (getInt($nType)) {
			case self::ROW_TYPE_LOCATION:
				$out = '
				<tr>
					<td colspan="2">
						<br>'.$this->Res->html(893,page::language()).':<br><br>
						'.editor::getSized('Config','html',page::language(),$Data['mlc_Html'],'100%','180').'
					</td>
				</tr>
				'; 
				break;
			case self::ROW_TYPE_ROUTE:
				$out = '
				<tr>
					<td colspan="2">
						<input type="hidden" name="html" value="">
					</td>
				</tr>
				'; 
				break;
		}
		return($out);
		
	}
	
	// Updaten eines Vias anhand der geposteten ID im Datenarray
	private function updateViaById($nViaID,&$Data,$nSort,$nIndex) {
		// Vias durchgehen
		for ($i = 0;$i < count($Data['vias']);$i++) {
			// Ist es das gewünschte Via?
			if ($Data['vias'][$i]['mlc_ID'] == $nViaID) {
				$Data['vias'][$i]['mlc_Name'] = $_POST['vianame'][$nIndex];
				$Data['vias'][$i]['mlc_Sortorder'] = $_POST['sort'][$nIndex];
			}
		}
	}
	
	// Karten ID holen (Erstellen wenn nicht vorhanden)
	private function getMapId() {
		$nMenuID = page::menuID();
		// Prüfen ob es einen Eintrag gibt
		$sSQL = "SELECT COUNT(mam_ID) FROM tbmap_menu WHERE mnu_ID = $nMenuID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn nicht genau ein Resultat
		if ($nResult != 1) {
			// Neuer Eintrag in Karten-, Verbindungstabelle
			$nMapID = mapOps::addMapWithMenu($nMenuID,$this->Conn);
		} else {
			// Aktuelle ID holen
			$sSQL = "SELECT map_ID FROM tbmap_menu WHERE mnu_ID = $nMenuID";
			$nMapID = $this->Conn->getFirstResult($sSQL);
		}
		return($nMapID);
	}
	
	// Speichern einer Location aus der Übersicht
	private function saveLocationOverview($nID,$sName) {
		$savedFields = array();
		mapOps::addSaveable('mlc_Name',"'$sName'",$savedFields);
		mapOps::saveLocation($nID,$savedFields,$this->Conn);
	}
	
	// Speichern einer Route aus der Übersicht
	private function saveRouteOverview($nID,$sName) {
		$savedFields = array();
		mapOps::addSaveable('mrt_Name',"'$sName'",$savedFields);
		mapOps::saveRoute($nID,$savedFields,$this->Conn);
	}
}