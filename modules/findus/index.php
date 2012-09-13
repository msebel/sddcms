<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/findus') !== false) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das Find-Us Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewMapFindus extends abstractSddView {

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		// Javascript einbinden
		singleton::meta()->addJavascript('/modules/findus/index.js',true);

		// Konfiguration initialisieren
		$Config = array();
		pageConfig::get(page::menuID(),$this->Conn,$Config);

		// HTML Code einfügen
		if (strlen($Config['htmlCode']['Value']) > 0) {
			stringOps::htmlViewEnt($Config['htmlCode']['Value']);
			$text = '
			<tr>
				<td colspan="2">
					<div class="divEntryText">'.$Config['htmlCode']['Value'].'</div>
				</td>
			</tr>
			';
		}

		// Formular zur Eingabe der Adresse
		$out = '
		<form method="post" action="'.$this->link().'">
		<table width="100%" cellpadding="0" cellspacing="2" border="0">
			'.$text.'
			<tr>
				<td width="100">
					'.$this->Res->html(870,page::language()).':
				</td>
				<td>
					<input type="text" name="startAddress" class="cRouteInput">
					<input type="submit" name="cmdSend" value="'.$this->Res->html(871,page::language()).'" class="cButton">
				</td>
			</tr>
		</table>
		</form>
		';

		// Resultat generieren
		if (isset($_POST['startAddress'])) {
			$map = new googleMap();
			// Navigation Karte, Route, Beides
			$out .= '<br>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="cFindUsNavi">
				<tr>
					<td id="iRoute" class="cNavSelected" width="150">
						<a href="javascript:setTdRegister(1,\''.$map->getProperty('MapID').'\');">'.$this->Res->html(872,page::language()).'</a>
					</td>
					<td id="iDesc" class="cNav" width="150">
						<a href="javascript:setTdRegister(2,\''.$map->getProperty('MapID').'\');">'.$this->Res->html(873,page::language()).'</a>
					</td>
					<td id="iBoth" class="cNav" width="150">
						<a href="javascript:setTdRegister(3,\''.$map->getProperty('MapID').'\');">'.$this->Res->html(874,page::language()).'</a>
					</td>
					<td class="cNav">&nbsp;</td>
				</tr>
			</table>
			';
			// Koordinaten holen
			$sQuery = stringOps::getPostEscaped('startAddress',$this->Conn);
			$coordStart = new googleCoordinate($sQuery);
			$coordEnd = new googleCoordinate($Config['goalAddress']['Value']);
			// Route erstellen
			$map->setProperty('RouteClass','cGoogleRouteFindUs');
			$nRouteID = $map->createRoute();
			$map->setStart($coordStart,$nRouteID);
			$map->setEnd($coordEnd,$nRouteID);
			// Karte in den Output geben
			$out .= $map->output();
		} else {
			// Nur das Ziel zeigen
			$map = new googleMap();
			$coordStart = new googleCoordinate($Config['goalAddress']['Value']);
			$map->addLocation($coordStart, $Config['goalAddress']['Value']);
			$map->setZoom(80);
			$out .= '<br>'.$map->output();
		}
		return($out);
	}
}