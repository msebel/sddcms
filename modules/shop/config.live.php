<?php
/**
 * Shopkonfiguration (LIVE Version)
 */
class shopModuleConfig {
	
	/**
	 * Pfad zu dem Templates
	 * @var string
	 */
	const TEMPLATE_PATH = '/modules/shop/templates/';
	/**
	 * Pfad zu dem Mail Templates
	 * @var string
	 */
	const MAIL_PATH = '/modules/shop/templates/mail/';
	/**
	 * Elementpfad. Parameter: {PAGE} und {ELEMENT}
	 * @var string
	 */
	const ELEMENT_PATH = '/page/{PAGE}/element/{ELEMENT}/';
	/**
	 * Breite der Thumbs
	 * @var int
	 */
	const THUMB_WIDTH = 80;
	/**
	 * Höhe der Thumbs (Nur für Detailsicht)
	 * @var int
	 */
	const THUMB_HEIGHT = 60;
	/**
	 * Breite der Mittleren Version in Detailsicht
	 * @var int
	 */
	const RESIZE_WIDTH = 250;
	/**
	 * Breite der Originalbilder
	 * @var int
	 */
	const ORIGINAL_WIDTH = 850;
	/**
	 * Sortierfeld für die Artikelliste (list.php). Inkl ASC/DESC
	 * @var string
	 */
	const LIST_SORTFIELD = 'sha_Purchased DESC';
	/**
	 * Artikel pro Seite für die Artikelliste (list.php)
	 * @var int
	 */
	const ARTICLES_PER_PAGE = 15;
	/**
	 * Mail Adresse für Mailings vom Shop
	 * @var string
	 */
	const MAIL_FROM = 'michael@sebel.ch';
	/**
	 * Mail Name für Mailings vom Shop
	 * @var string
	 */
	const MAIL_FROMNAME = 'Michaels Onlineshop';
}