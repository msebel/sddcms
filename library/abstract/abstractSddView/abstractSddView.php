<?php
/**
 * Basisklasse für Viewmodule
 * @author Michael Sebel <michael@sebel.ch>
 */
abstract class abstractSddView {
	
	/**
	 * Ressourcen Objekte für Sprachverwaltung
	 * @var resources
	 */
	protected $Res = NULL;
	/**
	 * Datenbankverbindung zur Instanz
	 * @var dbConn
	 */
	protected $Conn = NULL;
	/**
	 * Datenbankverbindung zum Kunden
	 * @var dbConn 
	 */
	protected $CustConn = NULL;
	/**
	 * Menu Objekt welches vom Menuinterface abstammt
	 * @var menuInterface
	 */
	protected $Menu = NULL;
	/**
	 * Das aktuell verarbeitete Menuobjekt
	 * @var menuObject
	 */
	protected $CurrentMenu = NULL;
	/**
	 * Template Objekt
	 * @var template
	 */
	protected $Tpl = NULL;
	/**
	 * Subtemplate Objekt zum erstellen der View
	 * @var templateImproved
	 */
	protected $Template = NULL;
	/**
	 * The individual Template Path
	 * @var string
	 */
	protected $Individualpath = '';
	
	/**
	 * Lädt Resourcen und Datenbankverbindungen
	 */
	public function __construct($tpl) {
		$this->Res = singleton::resources();
		$this->Conn = singleton::conn();
		$this->CustConn = singleton::custconn();
		$this->Menu = singleton::menu();
		$this->CurrentMenu = $this->Menu->CurrentMenu;
		$this->Tpl = $tpl;
		// Template Basis laden
		$this->loadTemplate();
	}

	/**
	 * Funktion, welche den Output handelt. inkl. Cache.
	 * @return string HTML Code für den Website output
	 */
	public function getModule() {
		if (option::get('caching') == 1) {
			// Code für diese Seite generieren
			$cachefile = BP.'/mandant/'.page::mandant().'/cache/'.page::menuID().'/'.md5($_SERVER['REQUEST_URI']).'.html';
			// Gibt es die Datei und ist sie noch nicht zu alt?
			$too_old = (time() - fileOps::getStamp($cachefile)) > 86400;
			if (!file_exists($cachefile) || $too_old) {
				// Datei neu bauen, deshalb Content holen
				$this->Tpl->rebuild = true;
				return($this->getOutput());
			} else {
				// Nicht neu bauen, natives laden aus Cache
				$this->Tpl->useCache = true;
				return false;
			}
		} else {
			return($this->getOutput());
		}
	}
	
	/**
	 * Lädt das Templatesystem und das Basistemplate
	 */
	public function loadTemplate($name = 'index') {
		$file = $this->getTemplateFile($name);
		$this->Template = new templateImproved($file);
	}
	
	/**
	 * Definiert den individuellen Pfad für Kundenmodule
	 * @param string $path Pfad zu den Templates mit Slash am Ende
	 */
	public function setIndividualPath($path) {
		$this->Individualpath = $path;
	}
	
	/**
	 * Lädt ein individuelles Kundentemplate
	 * @param type $name Name des Templates
	 */
	public function loadIndividualTemplate($name) {
		$file = $this->getIndividualTemplateFile($name);
		$this->Template = new templateImproved($file);
	}
	
	/**
	 * PFad zu einem individuellen Kundentemplate zurückgeben
	 * @param string $name Name der zu ladenden Datei
	 * @return string vollständiger Pfad zur Datei
	 */
	public function getIndividualTemplateFile($name) {
		return($this->Individualpath.$name.'.html');
	}
	
	/**
	 * Templatefile aus dem richtigen Ort laden, also im Design wenn vorhanden
	 * und im Standard Folder wenn es nicht vorhanden ist als Fallback
	 * @param string $name Name des Files im richtigen Menutyp Ordner
	 * @return string Vollständiger Pfad je nach dem
	 */
	public function getTemplateFile($name) {
		$sName = $this->CurrentMenu->Type.'/'.$name.'.html';
		// Schauen ob wir es im Design Ordner finden
		$sPath = BP.'/design/'.page::design().'/templates/'.$sName;
		// Wenn es nicht existiert, Standard nehmen
		if (!file_exists($sPath)) {
			$sPath = BP.'/resource/template/'.$sName;
		}
		return($sPath);
	}
	
	/**
	 * Gibt einen Link innerhalb des Moduls zurück
	 * @param string $param Parameter z.B. p1=2&p3=4&p5=55, ohne & oder ? am Anfang
	 * @return string Verlinkbare Seite mit schöner URL
	 */
	public function link($param = '') {
		if (strlen($this->CurrentMenu->Path) > 0) {
			$link = '/'.$this->CurrentMenu->Path;
			if (strlen($param) > 0) $link .= '?'.$param;
		} else {
			$link = '/controller.php?id='.page::menuID();
			if (strlen($param) > 0) $link .= '&'.$param;
		}
		// Diverse Preview Parameter hinzufügen, sofern nötig
		if (isset($_GET['cmspreview'])) {
			$link .= '&showTeaser&runParser&cmspreview';
		}
		return($link);
	}
	
	/**
	 * Template Objekt
	 * @return templateImproved
	 */
	public function getTemplate() {
		return($this->Template);
	}
}