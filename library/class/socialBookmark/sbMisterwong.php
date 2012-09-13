<?php 
/**
 * Implementation Facebook URL Bookmark
 */
class sbMisterwong implements socialBookmark {
	
	/**
	 * Ressourcen Objekt für Outputs
	 * @var resources
	 */
	private $Res = null;
	
	/**
	 * Erstellt das Objekt und referenziert Res Objekt
	 * @param resources Res Ressourcen Objekt
	 */
	public function __construct(resources &$Res) {
		$this->Res = $Res;
	}
	
	/**
	 * Implemetiere get Funktion um HTML zurückzugeben
	 * @param string sURL URL die verlinkt wird
	 * @param string sTitle Titel für die URL (optional)
	 * @return string HTML Code mit Verlinkung
	 */
	public function get($sURL,$sTitle) {
		return('
		<a href="http://www.mister-wong.de/add_url/'.$sURL.'" target="_blank" 
		title="'.$this->Res->html(854,page::language()).' mister-wong.de"
		alt="'.$this->Res->html(854,page::language()).' mister-wong.de">
			<img src="/images/social/misterwong.png" border="0"
			alt="'.$this->Res->html(854,page::language()).' mister-wong.de" 
			title="'.$this->Res->html(854,page::language()).' mister-wong.de">
		</a>
		');
	}
}