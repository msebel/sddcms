<?php
/**
 * Simple View Klasse von der alle Shopviews abstammen
 * @author Michael Sebel <michael@sebel.ch
 */
abstract class abstractShopView {
	
	/**
	 * Datenbankverbidung
	 * @var dbConn
	 */
	protected $Conn = NULL;
	/**
	 * Ressourcen Objekt
	 * @var resources
	 */
	protected $Res = NULL;
	/**
	 * Templatesystem
	 * @var templateImproved
	 */
	protected $Tpl = NULL;

	/**
	 * Basiskonstruktor, erstellt conn und res Objekt
	 * @param string $name Name des Haupttemplates
	 */
	public function __construct($name) {
		// Objekte erstellen
		$this->Conn = database::getConnection();
		$this->Res = getResources::getInstance($this->Conn);
		// Template erstellen/einlesen
		$tPath = shopStatic::getTemplate($name);
		$this->Tpl = new templateImproved($tPath);
	}

	/**
	 * Gibt die Metadaten für die View zurück ist per Default
	 * das was im meta.html Template steht
	 * @return string HTML für Metadaten
	 */
	public function getMeta() {
		$tPath = shopStatic::getTemplate('meta');
		$meta = new templateImproved($tPath);
		return($meta->output());
	}

	/**
	 * Checkt, ob aktuell ein User eingeloggt ist und leitet weiter,
	 * auf die Zugriffsseite (Error) sofern das der Fall ist
	 * @param bool $bRedirect Gibt an, ob return oder redirect
	 * @return bool Gibt an ob Zugriff oder nicht (true=OK)
	 */
	protected function userCheck($bRedirect) {
		// Eingeloggte User holen
		$User = shopStatic::getLoginUser();
		if (!$User instanceof shopUser) {
			// Redirect oder Meldung
			if ($bRedirect) redirect('location: /error.php?type=noAccess');
			// Oder false liefern falls kein Redirect stattfand
			return(false);
		}
		// Wenn wir soweit sind, ist alles OK
		return(true);
	}

	public abstract function getContent();
}
?>
