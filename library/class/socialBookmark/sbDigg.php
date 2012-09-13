<?php 
/**
 * Implementation Digg URL Bookmark
 */
class sbDigg implements socialBookmark {
	
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
		<a href="http://digg.com/submit?phase=2&url='.$sURL.'" target="_blank" 
		title="'.$this->Res->html(854,page::language()).' digg.com"
		alt="'.$this->Res->html(854,page::language()).' digg.com">
			<img src="/images/social/digg.png" border="0"
			alt="'.$this->Res->html(854,page::language()).' digg.com" 
			title="'.$this->Res->html(854,page::language()).' digg.com">
		</a>
		');
	}
}