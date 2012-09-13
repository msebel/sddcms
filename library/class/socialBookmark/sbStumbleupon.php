<?php 
/**
 * Implementation Stumbleupon URL Bookmark
 */
class sbStumbleupon implements socialBookmark {
	
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
		<a href="http://www.stumbleupon.com/submit?url='.$sURL.'&title='.$sTitle.'" target="_blank" 
		title="'.$this->Res->html(854,page::language()).' stumbleupon.com"
		alt="'.$this->Res->html(854,page::language()).' stumbleupon.com">
			<img src="/images/social/stumbleupon.png" border="0"
			alt="'.$this->Res->html(854,page::language()).' stumbleupon.com" 
			title="'.$this->Res->html(854,page::language()).' stumbleupon.com">
		</a>
		');
	}
}