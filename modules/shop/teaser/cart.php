<?php
/**
 * Teaser zur Anzeige des Warenkorbs
 * @author Michael Sebel <michael@sebel.ch>
 */
class shopCartTeaser implements teaser {

	/**
	 * Ressourcen
	 * @var resources
	 */
	private $Res;
	/**
	 * Datenbankverbindung
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Titel des Teasers
	 * @var string
	 */
	private $Title;
	/**
	 * Metadaten Objekt
	 * @var meta
	 */
	private $Meta;

	// Konstruieren
	public function __construct() {
		$this->Meta = singleton::meta();
		// Direkt ein Skript hinzufügen, welches den Korb aktualisiert
		$this->Meta->addJavascript('/modules/shop/js/cart.js');
	}

	// Daten setzen
	public function setData(dbconn &$Conn,resources &$Res,&$Title) {
		$this->Res = $Res;
		$this->Conn = $Conn;
		$this->Title = $Title;
		// Mit dem ClassLoader die nötigen Klassen laden
		require_once(BP.'/modules/shop/system.php');
	}

	// ID des Elements setzen
	public function setID($tapID) {
		$this->TapID = $tapID;
	}

	// Definieren ob Output vorhanden sein wird
	public function hasOutput() {
		return(true);
	}

	// HTML Code einfüllen
	public function appendHtml(&$out) {
		// Hier wird nur der Container ausgegeben und das
		// Objekt instanziert, der Rest immer per JS
		$out .= '
		<h2>'.$this->Res->html(1166,page::language()).'</h2>
		<div id="shopCartContainer">
			<div id="shopCartError"></div>
			<div id="shopCartArticles"></div>
			<div id="shopCartTotal"></div>
			<div id="shopCartLink">
				<a href="/modules/shop/view/cart.php?id='.page::menuID().'">
				'.$this->Res->html(1094, page::language()).'</a>
			</div>
		</div>
		<script type="text/javascript">
			try {
				var gTeaserCart = new teaserCartClass();
			} catch (exception) {}
		</script>
		';
	}
}