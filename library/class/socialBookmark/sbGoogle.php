<?php 
/**
 * Implementation Google URL Bookmark
 */
class sbGoogle implements socialBookmark {
	
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
		<a href="http://www.google.com/bookmarks/mark?op=add&hl=de&bkmk='.$sURL.'&title='.$sTitle.'"
		title="'.$this->Res->html(854,page::language()).' google.com" target="_blank" 
		alt="'.$this->Res->html(854,page::language()).' google.com">
			<img src="/images/social/google.png" border="0"
			alt="'.$this->Res->html(854,page::language()).' google.com" 
			title="'.$this->Res->html(854,page::language()).' google.com">
		</a>
		');
	}
}