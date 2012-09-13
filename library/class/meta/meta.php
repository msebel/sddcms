<?php
/**
 * Klasse zum hinzufügen von Head Informationen.
 * Support für script, link, meta, title Tags.
 * @author Michael Sebel <michael@sebel.ch>
 */
class meta {
	/**
	 * Referenz zum Templateobjekt, in welches die Tags eingefüllt werden
	 * @var template
	 */
	private $tpl;
	/**
	 * Gibt an, ob Prototype aktiviert ist (Standardmässig ja)
	 * @var bool
	 */
	public $prototypeEnabled = true;
	/**
	 * Sammelt alle Skripts, damit jedes nur einmal daher kommt
	 * @var array skript md5 hashes  => skriptname
	 */
	public $scripts = array();
	/**
	 * Sammelt alle CSS dateien, damit jede nur einmal eingebunden wird
	 * @var array md5 hasehs => filename
	 */
	private $cssfiles = array();
	/**
	 * Hashes der Skripts die erst vor schliessendem Body eingebaut werden
	 * @var array md5 Hashes aus scripts
	 */
	private $bottomScripts = array();
	
	/**
	 * Meta Objekt erstellen
	 * @param template tpl, Referenz zum Templateobjekt
	 */
	public function __construct(template &$tpl) {
		$this->tpl = $tpl;
	}

	/**
	 * Liefert HTML Code zur Einbindung der Bottom Scripts zurück
	 * @return string HTML Code zur JS einbindung
	 */
	public function getBottomScripts() {
		$html = '';
		foreach ($this->bottomScripts as $hash) {
			$html .= '<script type="text/javascript" src="'.$this->scripts[$hash].'"></script>'."\n";
		}
		return $html;
	}
	
	/**
	 * Fügt ein Javascript File ein.
	 * <script type="text/javascript" src="[sPath]"></script>.
	 * @param string sPath, Pfad zum JS File (relativ / absolut)
	 */
	public function addJavascript($sPath,$bottom = false) {
		$hash = md5($sPath);
		if (!isset($this->scripts[$hash])) {
			if ($bottom) {
				// Skript aufbefahren für späteres laden direkt vor </body>
				$this->bottomScripts[] = $hash;
			} else {
				// Oben anfügen, deswegen in den Meta Bereich
				$this->tpl->aMeta('<script type="text/javascript" src="'.$sPath.'"></script>');
			}
			$this->scripts[$hash] = $sPath;
		}
	}
	
	/**
	 * Fügt ein CSS File in den Kopf ein.
	 * <link rel="stylesheet" type="text/css" href="[sPath]">.
	 * @param string sPath, Pfad zum CSS File (relativ / absolut)
	 */
	public function addCSS($sPath) {
		$hash = md5($sPath);
		if (!isset($this->cssfiles[$hash])) {
			$this->tpl->aMeta('<link rel="stylesheet" type="text/css" href="'.$sPath.'">');
			$this->cssfiles[$hash] = $sPath;
		}
	}
	
	/**
	 * Fügt ein CSS File nur für den IE 6 in den Kopf ein.
	 * <link rel="stylesheet" type="text/css" href="[sPath]">.
	 * @param string sPath, Pfad zum CSS File (relativ / absolut)
	 */
	public function addIe6CSS($sPath) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') !== false) {
			$this->tpl->aMeta('<link rel="stylesheet" type="text/css" href="'.$sPath.'">');
		}
	}
	
	/**
	 * Fügt einen Metatag in den Kopf ein.
	 * <meta name="'[sName]" content="[sContent]">.
	 * @param string sName, Name des Metatags
	 * @param string sContent, Inhalt des Metatags
	 */
	public function addMeta($sName,$sContent) {
		$this->tpl->aMeta('<meta name="'.$sName.'" content="'.$sContent.'">');
	}
	
	/**
	 * Fügt einen Metatag (http-equiv) in den Kopf ein.
	 * <meta name="[sEquiv]" content="[sContent]">.
	 * @param string sEquiv, Name des Metatags
	 * @param string sContent, Inhalt des Metatags
	 */
	public function addEquiv($sEquiv,$sContent) {
		$this->tpl->aMeta('<meta http-equiv="'.$sEquiv.'" content="'.$sContent.'">');
	}
	
	/**
	 * Fügt den Titel Tag ein.
	 * @param string sTitle, Inhalt für den Titel Tag
	 */
	public function setTitle($sTitle) {
		$this->tpl->aMeta('<title>'.$sTitle.'</title>');
	}
	
	/**
	 * Fügt ein Favicon hinzu.
	 * <link rel="shortcut icon" href="[sPath]">.
	 * @param string sPath, Pfad zum Favicon (absolut / relativ)
	 */
	public function addFavicon($sPath) {
		$this->tpl->aMeta('<link rel="shortcut icon" href="'.$sPath.'">');
	}
	
	/**
	 * Fügt den Google verify-v1 Tag ein, wenn er für die Seite hinterlegt ist
	 */
	public function addVerify() {
		if (strlen(page::verify()) > 0) {
			$this->addMeta('verify-v1',page::verify());
		}
	}
	
	/**
	 * Gibt die Sprache des Contents im ISO Format zurück
	 * @return string Sprache im ISO Format (de,en etc.)
	 */
	public function getContentLanguage() {
		$nLang = page::language();
		switch ($nLang) {
			case LANG_DE:	$sLang = "de"; break;
			case LANG_EN:	$sLang = "en"; break;
			default:		$sLang = "de"; break;
		}
		return($sLang);
	}
}