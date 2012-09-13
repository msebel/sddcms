<?php
class mediaConst {
	/**
	 * Konstanten Array mit erlaubten Dateiendungen
	 * @var array
	 */
	public static $AllowedExt = array(
		// Bearbeitbare Bildtypen
		'.jpg','.jpeg','.gif','.png',
		// Nicht bearbeitbare, downloadbare Bildtypen
		'.bmp','.psd','.ids','.jfif','.tiff','.tif',
		'.tga','.dds','.eps','.pxr','.pict','.pbm',
		// Microsoft Office typen
		'.doc','.xls','.ppt','.mdb','.pub','.vsd',
		'.docx','.xlsx','.pptx','.accdb','.pubx','.vsdx',
		'.dot','.xlt','.pot','.vst','.dotx','.xltx','.potx','.vstx',
		// OpenOffice Typen und Vorlagen
		'.odt','.ott','.odp','.otp','.ods','.ots',
		'.odb','.odg','.otg','.odf','.sxm','.mml',
		// Weitere g�ngige Formate
		'.zip','.gz','.rar','.bzip','.pdf','.swf',
		'.txt','.xml','.jar',
		// Musikmedientypen
		'.mp3','.wma','.ogg','.wav',
		// Videotypen
		'.mpg','.mpeg','.wmv','.flv','.divx','.avi',
	);
	
	/**
	 * Dateipfad mit ersetzbaren Variablen
	 * @var string
	 */
	const FILEPATH 			= '/page/{PAGE_ID}/element/{ELE_ID}/';
	/**
	 * Anzahl Dateien pro Seite im Mediamanager
	 * @var integer
	 */
	const FILESPERPAGE 		= 3;
	/**
	 * Breite der Thumbnails im Mediamanager
	 * @var integer
	 */
	const THUMB_WIDTH 		= 150;
	/**
	 * Maximale Dateigrösse für Bilddateien
	 * @var integer
	 */
	const MAXSIZE_GRAPHICS 	= 4096;
	/**
	 * Maximale Dateigrösse für alle anderen Dateien
	 * @var integer
	 */
	const MAXSIZE_FILES 	= 20480;
	/**
	 * Suffix für XL Version eines BIldes
	 * @var string
	 */
	const XL_SUFFIX 		= '__xlv';
	/**
	 * Bild anhand der Breite verkleinern/vergrössern
	 * @var integer
	 */
	const BY_WIDTH 			= 1;
	/**
	 * Bild anhand der Höhe verkleinern/vergrössern
	 * @var integer
	 */
	const BY_HEIGHT 		= 2;
	/**
	 * Bild anhand der längeren Kante verkleinern/vergrössern
	 * @var integer
	 */
	const BY_LONGEREDGE 	= 3;
	/**
	 * Bild anhand eines Prozentwertes verkleinern/vergrössern
	 * @var integer
	 */
	const BY_PERCENT 		= 4;
	/**
	 * Medientyp Bild (png, gif, jpg)
	 * @var integer
	 */
	const TYPE_PICTURE 		= 1;
	/**
	 * Medientyp Flash (swf)
	 * @var integer
	 */
	const TYPE_FLASH 		= 2;
	/**
	 * Medientyp Flashvideo (flv)
	 * @var integer
	 */
	const TYPE_FLASHVIDEO 	= 3;
	/**
	 * Andere bekannte Dateitypen
	 * @var integer
	 */
	const TYPE_OTHER 		= 4;
	/**
	 * Dateien die überwacht werden (Traffic, Downloads)
	 * @var integer
	 */
	const TYPE_COUNTED 		= 5;
	/**
	 * Unbekannte Dateitypen
	 * @var integer
	 */
	const TYPE_UNKNOWN 		= 6;
	/**
	 * Medientyp Video (wmv, avi, etc.)
	 * @var integer
	 */
	const TYPE_VIDEO 		= 7;
	/**
	 * Medientyp Musik (mp3)
	 * @var integer
	 */
	const TYPE_MUSIC 		= 8;
}