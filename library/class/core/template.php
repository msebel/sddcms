<?php
require_once(BP.'/library/class/templateParser/compressorParser.php');
require_once(BP.'/library/class/templateParser/emailParser.php');
require_once(BP.'/library/class/templateParser/sddcodeParser.php');
/**
 * Template Verarbeitung.
 * Templateengine, welche die HTML Daten im Design Verzeichnis der Webseite liest und
 * am Ende den gesamten Output einfüllt Wird automatisch instanziert ($tpl) und inkludiert
 * Menu, Teaser, Meta (erstellt weitere Objekte)
 * @author Michael Sebel <michael@sebel.ch>
 */
class template {
	
	/**
	 * Gibt an, ob eine Startseite geladen wird.
	 * @var voolean
	 */
	private $bStartpage = false;
	
	/**
	 * Name des Design Files (HTML Code).
	 * @var string
	 */
	private $sDesignFile = 'design.htm';
	
	/**
	 * Name des Popup Design Files (HTML Code).
	 * @var string
	 */
	private $sPopupFile = 'popup.htm';
	
	/**
	 * Name des Print Design Files (HTML Code).
	 * @var string
	 */
	private $sPrintFile = 'print.htm';
	
	/**
	 * Name des Files für den top-HTML Code.
	 * @var string
	 */
	private $sTopTemplate = 'top.htm';
	
	/**
	 * Name des Files für den Footer-HTML Code.
	 * @var string
	 */
	private $sFooterTemplate = 'footer.htm';
	
	/**
	 * ID des Designs.
	 * @var integer
	 */
	private $nDesignID = 0;
	
	/**
	 * HTML Code der vom Template geladen wurde.
	 * @var string
	 */
	private $templateContent = '';
	
	/**
	 * Buffer für Meta (Head) HTML Code.
	 * @var string
	 */
	private $bufferMeta = '';
	
	/**
	 * Buffer für Menu HMTL Code.
	 * @var string
	 */
	private $bufferMenu = '';
	
	/**
	 * Buffer für Top HMTL Code.
	 * @var string
	 */
	private $bufferTop = '';
	
	/**
	 * Buffer für Footer HMTL Code.
	 * @var string
	 */
	private $bufferFooter = '';
	
	/**
	 * Buffer für Content HMTL Code.
	 * @var string
	 */
	private $bufferContent = '';
	
	/**
	 * Buffer für Teaser HMTL Code.
	 * @var string
	 */
	private $bufferTeaser = '';
	
	/**
	 * Buffer für Startseite HMTL Code.
	 * @var string
	 */
	private $bufferStart = '';
	
	/**
	 * Referenz zum Teaserobjekt.
	 * @var teaserParser
	 */
	private $teaser;
	/**
	 * Gibt an, ob ein Spezialdesign geladen wurde
	 * @var boolean
	 */
	private $isSpecialDesign = false;
	/**
	 * Originales Design, wenn temporärer wechsel
	 * @var int
	 */
	private $originalDesign = 0;
	/**
	 * Liste von parsern
	 * @var array
	 */
	private $Parsers = array();
	/**
	 * Menuobjekt
	 * @var menu
	 */
	private $Menu = null;
	/**
	 * Access Objekt
	 * @var access
	 */
	private $Access = null;
	/**
	 * Gibt an, ob das Cache File neugeschrieben werden muss
	 * @var bool
	 */
	public $rebuild = false;
	/**
	 * Gibt an, ob aus dem Cache geladen wird
	 * @var bool
	 */
	public $useCache = false;
	
	/**
	 * Template konstruieren.
	 */
	public function __construct(access &$Access) {
		$this->Access = $Access;
		// Prüfen ob Start- oder Contentseite
		$this->checkStartpage();
		// Design setzen
		$this->nDesignID = page::design();
		// Templatefile einlesen
		$sPath = BP.'/design/'.$this->nDesignID.'/'.$this->sDesignFile;
		$this->templateContent = @file_get_contents($sPath);
		// Fehlerausgabe
		if ($this->templateContent == false) {
			throw new sddCoreException('Could not load template File '.$this->sDesignFile);
		}
		// Footer und Top einlesen
		$this->readFooter();
		$this->readTop();
		// Parameter Design nehmen, wenn grösser 0
		if (getInt($_GET['useDesign']) > 0) {
			$this->setDesign(getInt($_GET['useDesign']));
		}
		// Parser registrieren
		array_push($this->Parsers,new emailParser());
		array_push($this->Parsers,new sddcodeParser());
		array_push($this->Parsers,new compressorParser());
	}
	
	/**
	 * Anderes Design File laden
	 * @param $sFile Name des zu ladenden Files, [a-zA-Z0-9] erlaubt
	 */
	public function setDesignFile($sFile) {
		stringOps::alphaNumFiles($sFile);
		$sPath = BP.'/design/'.$this->nDesignID.'/'.$sFile;
		$this->templateContent = @file_get_contents($sPath);
		// Fehlerausgabe
		if ($this->templateContent == false) {
			echo 'Could not load template File '.$sFile;
		}
	}
	
	/**
	 * Design temporär ändern
	 * @param $nDesignID
	 * @return unknown_type
	 */
	public function setDesign($nDesignID) {
		$nDesignID = getInt($nDesignID);
		// Originales Design speichern
		if (!$this->isSpecialDesign) {
			$this->originalDesign = $this->nDesignID;
			$this->nDesignID = $nDesignID;
		}
		// Neues Design in der Session anpassen
		$_SESSION['page']['design'] = $nDesignID;
		$this->isSpecialDesign = true;
		$this->setDesignFile($this->sDesignFile);
		// Footer und Top laden
		$this->readFooter();
		$this->readTop();
	}
	
	/**
	 * Teaerobjekt setzen
	 * @param teaserParser Teaser, Referenz zum Teaser Objekt
	 */
	public function setTeaser(&$Teaser) {
		$this->teaser = $Teaser;
	}
	
	/**
	 * Footer einlesen.
	 */
	public function readFooter() {
		$sPath = BP.'/mandant/'.page::mandant().'/include/'.$this->sFooterTemplate;
		// Content einlesen wenn vorhanden
		if (file_exists($sPath)) {
			$this->bufferFooter = file_get_contents($sPath);
		}
	}
	
	/**
	 * Top einlesen.
	 */
	public function readTop() {
		$sPath = BP.'/mandant/'.page::mandant().'/include/'.$this->sTopTemplate;
	// Content einlesen wenn vorhanden
		if (file_exists($sPath)) {
			$this->bufferTop = file_get_contents($sPath);
		}
	}
	
	/**
	 * Template für Popup laden.
	 */
	public function setPopup() {
		$sPath = BP.'/design/'.$this->nDesignID.'/'.$this->sPopupFile;
		$this->templateContent = file_get_contents($sPath);
		// Fehlerausgabe
		if ($this->templateContent == false) {
			echo 'Could not load template File '.$this->sPopupFile;
		}
	}
	
	/**
	 * Template leeren, wenn keine Ausgabe erwünscht und nur Funktionalität gebraucht wird.
	 */
	public function setEmpty() {
		$this->templateContent = "";
	}
	
	/**
	 * Template für Print laden.
	 */
	public function setPrint() {
		$sPath = BP.'/design/'.$this->nDesignID.'/'.$this->sPrintFile;
		$this->templateContent = file_get_contents($sPath);
		// Fehlerausgabe
		if ($this->templateContent == false) {
			echo 'Could not load template File '.$this->sPrintFile;
		}
	}
	
	/**
	 * Buffer in das Template schreiben.
	 */
	public function write() {
		if ($this->useCache) {
			// File laden und ausgeben
			$file = BP.'/mandant/'.page::mandant().'/cache/'.page::menuID().'/'.md5($_SERVER['REQUEST_URI']).'.html';
			// Caching headers
			header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + (24 * 60 * 60)) . ' GMT');
			header('Cache-Control: public, max-age='.(24 * 60 * 60));
			header('Pragma: public');
			echo file_get_contents($file);
			// Mehr machen wir gar nicht. Ende Feuer.
			exit;
		}
		if ($this->bStartpage == true) {
			// Startseiteninfo, Link zur Mandantenstartseite setzen
			$this->bufferStart = page::start();
			$this->templateContent = str_replace('{START}',$this->bufferStart,$this->templateContent);
		} else {
			$this->includePhpFiles();
			// Content Variablen füllen
			$this->templateContent = str_replace('{TOP}',$this->bufferTop,$this->templateContent);
			$this->templateContent = str_replace('{FOOTER}',$this->bufferFooter,$this->templateContent);
			$this->templateContent = str_replace('{META}',$this->bufferMeta,$this->templateContent);
			// Design mit dynamischem DIV Ausstatten
			$this->fillDynamicDiv();
			// Teaser/Menu/Content einfüllen
			$this->templateContent = str_replace('{TEASER}',$this->bufferTeaser,$this->templateContent);
			$this->templateContent = str_replace('{MENU}',$this->bufferMenu,$this->templateContent);
			$this->templateContent = str_replace('{CONTENT}',$this->bufferContent,$this->templateContent);
		}
		// Design Nummer zurücksetzen wenn nötig
		if ($this->isSpecialDesign) {
			$_SESSION['page']['design'] = $this->originalDesign;
		}
		// Parser drüber lassen (Nur in View)
		if ($this->Access->getControllerAccessType() != 1 || isset($_GET['runParser'])) {
			foreach ($this->Parsers as $parser) {
				$parser->parse($this->templateContent);
			}
		}
		// Bottom Javascripts einbinden direkt vor schliessendem body Tag
		$this->includeBottomJs();
		// Seiteninhalt ausgeben
		echo $this->templateContent;
		// Evtl. noch ein Cache File schreiben
		if ($this->rebuild) {
			$folder = BP.'/mandant/'.page::mandant().'/cache/'.page::menuID().'/';
			if (!file_exists($folder)) mkdir($folder,0777);
			file_put_contents($folder.md5($_SERVER['REQUEST_URI']).'.html',$this->templateContent);
		}
	}

	/**
	 * Includiert die JS die am Bottom kommen sollen
	 */
	private function includeBottomJs() {
		$this->templateContent = str_replace(
			'</body>',
			singleton::meta()->getBottomScripts().'</body>',
			$this->templateContent
		);
	}
	
	/**
	 * Dynamisches Div füllen.
	 */
	private function fillDynamicDiv() {
		$nWidth = 0;
		if ($this->teaser->Elements == 0) {
			// Kein Teaser
			$nWidth = page::allwidth();
		} else {
			$nWidth = page::contentwidth();
		}
		// Breite einfüllen, wenn vorhanden
		if ($nWidth > 0) {
			$this->templateContent = str_replace(
				'{DYN_DIV}',
				' style="width:'.$nWidth.'px"',
				$this->templateContent
			);
		} else {
			$this->templateContent = str_replace(
				'{DYN_DIV}',
				'',
				$this->templateContent
			);
		}
	}
	
	/**
	 * Findet heraus, ob die Startseite angezeigt werden muss und setzt den Boolean entsprechend.
	 */
	private function checkStartpage() {
		$menuID = page::menuID();
		$sIndividual = page::individual();
		// Prüfen ob keine MenuId vorhanden
		if (empty($menuID)) {
			// Prüfen, ob Startpage string vorhanden
			if (!empty($sIndividual)) {
				echo $sIndividual;
				$this->bStartpage = true;
				$this->sDesignFile = page::start();
			}
		}
	}
	
	/**
	 * Inkludiert PHP Skripte in den Content
	 */
	private function includePhpFiles() {
		$regex = '/{PHP:(.*?)}/';
		preg_match_all($regex,$this->templateContent,$result);
		// Alle Resultate durchgehen
		for ($i = 0; $i < count($result[0]);$i++) {
			$file = $result[1][$i].'.php';
			$replace = $result[0][$i];
			$out = '';
			// File einbinden (muss $out variable füllen)
			require(BP.'/mandant/'.page::mandant().'/include/'.$file);
			// Ersetzen im Content
			$this->templateContent = str_replace($replace,$out,$this->templateContent);
		}
	}
	
	/**
	 * Referenz zum Menuobjekt setzen
	 * @param menu Menu Referenz zum Menuobjekt
	 */
	public function setMenu(menuInterface &$Menu) {
		$this->Menu = $Menu;
	}
	
	/**
	 * Meta Buffer erweitern.
	 */
	public function aMeta($string) {
		$this->bufferMeta .= $string."\n";
	}
	
	/**
	 * Menu Buffer erweitern.
	 */
	public function aMenu($string) {
		$this->bufferMenu .= $string."\n";
	}
	
	/**
	 * Content Buffer erweitern (alias: aContent).
	 */
	public function aC($string) {
		$this->bufferContent .= $string."\n";
	}
	
	/**
	 * Content Buffer erweitern.
	 */
	public function aContent($string) {
		$this->aC($string);
	}
	
	/**
	 * Teaser Buffer erweitern.
	 */
	public function aTeaser($string) {
		$this->bufferTeaser .= $string."\n";
	}
}