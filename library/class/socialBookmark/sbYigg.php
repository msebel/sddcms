<?php 
/**
 * Implementation Yigg URL Bookmark
 */
class sbYigg implements socialBookmark {
	
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
		<a href="http://yigg.de/neu?exturl='.$sURL.'" target="_blank" 
		title="'.$this->Res->html(854,page::language()).' yigg.de"
		alt="'.$this->Res->html(854,page::language()).' yigg.de">
			<img src="/images/social/yigg.png" border="0"
			alt="'.$this->Res->html(854,page::language()).' yigg.de" 
			title="'.$this->Res->html(854,page::language()).' yigg.de">
		</a>
		');
	}
}