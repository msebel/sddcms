<?php 
/**
 * Implementiert eine Gruppe von Social
 * Bookmarking Links (und Icons)
 * @author Michael Sebel <michael@sebel.ch>
 */
class socialButtonList {
	
	/**
	 * 
	 * @var string URL für die Links
	 */
	private $Url = '';
	/**
	 * 
	 * @var string Titel des Links
	 */
	private $Title = '';
	/**
	 * 
	 * @var array Liste der SocialBookmark Objekte
	 */
	private $List = array();
	
	/**
	 * Fügt ein SocialBookmark der Liste hinzu
	 * @param socialBookmark oBm Ein socialBookmark objekt
	 */
	public function add(socialBookmark $oBm) {
		array_push($this->List,$oBm);
	}
	
	/**
	 * Setzt den Titel der Bookmarks
	 * @param string sTitle Titel des Bookmarks
	 */
	public function setTitle($sTitle) {
		$this->Title = $sTitle;
		stringOps::urlEncode($this->Title);
	}
	
	/**
	 * Setzt die URL des Bookmarks
	 * @param string sURL URL des Bookmarks
	 */
	public function setUrl($sUrl) {
		$this->Url = $sUrl;
	}
	
	/**
	 * Gibt das HTML für alle Bookmarks in der Liste aus
	 * @return string HTML aller Bookmarks
	 */
	public function output() {
		$sHTML = '<div class="socialButtonList">';
		foreach($this->List as $bookmark) {
			$sHTML .= $bookmark->get(
				$this->Url,
				$this->Title
			);
		}
		$sHTML .= '</div>';
		return($sHTML);
	}
}