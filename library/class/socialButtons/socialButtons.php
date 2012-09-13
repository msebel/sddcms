<?php 
require_once(BP.'/library/class/socialButtons/socialButtonList.php');
require_once(BP.'/library/class/socialBookmark/sbFacebook.php');
require_once(BP.'/library/class/socialBookmark/sbDigg.php');
require_once(BP.'/library/class/socialBookmark/sbDelicious.php');
require_once(BP.'/library/class/socialBookmark/sbStumbleupon.php');
require_once(BP.'/library/class/socialBookmark/sbSlashdot.php');
require_once(BP.'/library/class/socialBookmark/sbBloglines.php');
require_once(BP.'/library/class/socialBookmark/sbMisterwong.php');
require_once(BP.'/library/class/socialBookmark/sbGoogle.php');
require_once(BP.'/library/class/socialBookmark/sbYigg.php');
require_once(BP.'/library/class/socialBookmark/sbLive.php');
require_once(BP.'/library/class/socialBookmark/sbTwitter.php');

/**
 * Statische Klasse welche Social Bookmark Listen zur Verfügung stellt.
 * @author Michael Sebel <michael@sebel.ch>
 */
class socialButtons {
	
	/**
	 * SocialBookmark Liste für den Blog
	 * @var socialButtonList Liste von SocialBookmarks
	 */
	private static $Blog = null;
	
	/**
	 * SocialBookmark Liste für die News
	 * @var socialButtonList Liste von SocialBookmarks
	 */
	private static $News = null;
	
	/**
	 * Gibt die SB Liste für den Blog zurück
	 * @param resources Res Ressourcen Objekt
	 * @return socialButtonList Liste von SocialBookmarks
	 */
	public static function blog(resources &$Res) {
		if (self::$Blog == null) {
			self::createBlog($Res);
		}
		return(self::$Blog);
	}
	
	/**
	 * Gibt die SB Liste für die News zurück
	 * @param resources Res Ressourcen Objekt
	 * @return socialButtonList Liste von SocialBookmarks
	 */
	public static function news(resources &$Res) {
		if (self::$News == null) {
			self::createNews($Res);
		}
		return(self::$News);
	}
	
	/**
	 * Erstellt die Liste für den Blog
	 * @param resources Res Ressourcen Objekt
	 */
	private static function createBlog(resources &$Res) {
		self::$Blog = new socialButtonList();
		self::$Blog->add(new sbFacebook($Res));
		self::$Blog->add(new sbTwitter($Res));
		self::$Blog->add(new sbDigg($Res));
		self::$Blog->add(new sbDelicious($Res));
		self::$Blog->add(new sbGoogle($Res));
		self::$Blog->add(new sbBloglines($Res));
		self::$Blog->add(new sbStumbleupon($Res));
		self::$Blog->add(new sbLive($Res));
		self::$Blog->add(new sbMisterwong($Res));
		self::$Blog->add(new sbSlashdot($Res));
		self::$Blog->add(new sbYigg($Res));
	}
	
	/**
	 * Erstellt die Liste für die News
	 * @param resources Res Ressourcen Objekt
	 */
	private static function createNews(resources &$Res) {
		self::$News = new socialButtonList();
		self::$News->add(new sbFacebook($Res));
		self::$News->add(new sbTwitter($Res));
		self::$News->add(new sbDigg($Res));
		self::$News->add(new sbDelicious($Res));
		self::$News->add(new sbGoogle($Res));
		self::$News->add(new sbStumbleupon($Res));
		self::$News->add(new sbLive($Res));
		self::$News->add(new sbMisterwong($Res));
		self::$News->add(new sbSlashdot($Res));
		self::$News->add(new sbYigg($Res));
	}
}