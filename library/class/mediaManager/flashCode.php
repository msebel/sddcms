<?php
/**
 * Bietet Code um Flash Dateien anzuzeigen.
 * Unterstützt werden SWF, FLV und MP3 Dateien, während
 * die beiden letzteten im FLVPlayer eingebettet sind
 * @author Michael Sebel <michael@sebel.ch>
 */
class flashCode {
	
	/**
	 * Standard FLV Code erstellen
	 * @param string sFile, absoluter Pfad zum Videofile
	 * @param int nWidth, Breite des Players
	 * @param integer nHeight, Höhe des Players inkl. Controls
	 * @param string sSkin, Aussehen des Players
	 * @param strring sAlign, 'right' oder 'left' umfliessend
	 * @param string out, Variable für den HTML Output
	 */
	public static function getFlvPlayerCode($sFile,$nWidth,$nHeight,$sSkin,$sAlign,&$out) {
		// Align anpassen
		switch ($sAlign) {
			case 'right':
				$sMargin = '3px 0px 3px 3px'; break;
			case 'left':
				$sMargin = '3px 3px 3px 0px'; break;
			default:
				$sAlign = 'none';
				$sMargin = '3px 3px 3px 3px';
		}
		$out .= '
		<div style="float:'.$sAlign.';margin:'.$sMargin.';">
			<embed 
			  src="/scripts/flvplayer/mediaplayer.swf" 
			  width="'.$nWidth.'" height="'.$nHeight.'" 
			  allowscriptaccess="always"
			  allowfullscreen="true"
			  type="application/x-shockwave-flash" 
			  flashvars="file='.$sFile.'&autostart=false&showdigits=false&stretch=fit'.self::getSkin($sSkin).'&image='.self::getImageVersion($sFile).'"
			/>
		</div>
		';
	}
	
	/**
	 * Standard Mp3 Code erstellen
	 * @param string sFile, Absoluter Pfad zur Musikdatei
	 * @param integer nWidth, Breite des Players (Höhe fix)
	 * @param string sSkin, Aussehen des Players
	 * @param string sAlign, 'left' oder 'right' umfliessend
	 * @param string out, Variable für HTML Output
	 */
	public static function getMp3PlayerCode($sFile,$nWidth,$sSkin,$sAlign,&$out) {
		// Align anpassen
		switch ($sAlign) {
			case 'right':
				$sMargin = '3px 0px 3px 3px'; break;
			case 'left':
				$sMargin = '3px 3px 3px 0px'; break;
			default:
				$sAlign = 'none';
				$sMargin = '3px 3px 3px 3px';
		}
		$out .= '
		<div style="float:'.$sAlign.';margin:'.$sMargin.';">
			<embed 
			  src="/scripts/flvplayer/mediaplayer.swf" 
			  width="'.$nWidth.'" height="20"
			  allowscriptaccess="always"
			  allowfullscreen="false"
			  type="application/x-shockwave-flash" 
			  flashvars="file='.$sFile.'&autostart=false&showdigits=true'.self::getSkin($sSkin).'&image='.self::getImageVersion($sFile).'"
			/>
		</div>
		';
	}
	
	/**
	 * Skins erstellen für FLV/MP3 Player.
	 * Vorhandene Skins:
	 * - light, dark, silver, green
	 * - pink, pink2, lightblue, yellow,
	 * - darkred, darkgreen, darkblue
	 * @param string sSkin, Name des gewünschten Skins
	 */
	public static function getSkin($sSkin) {
		$sCode = '';
		switch($sSkin) {
			case 'light':		$sCode = '&backcolor=0xFFFFFF&frontcolor=0x000000&lightcolor=0xAAAAAA&screencolor=0xFAFAFA'; break;
			case 'dark':		$sCode = '&backcolor=0x333333&frontcolor=0x888888&lightcolor=0xAAAAAA&screencolor=0x000000'; break;
			case 'silver':		$sCode = '&backcolor=0xCCCCCC&frontcolor=0x888888&lightcolor=0xCCCCCC&screencolor=0xCCCCCC'; break;
			case 'pink':		$sCode = '&backcolor=0xFF66FF&frontcolor=0x000000&lightcolor=0xFFFFFF&screencolor=0xFFAAFF'; break;
			case 'pink2':		$sCode = '&backcolor=0x000000&frontcolor=0xFFFFFF&lightcolor=0x990099&screencolor=0xFFDDFF'; break;
			case 'lightblue':	$sCode = '&backcolor=0xEEEEEE&frontcolor=0x999999&lightcolor=0x666666&screencolor=0xCCCCFF'; break;
			case 'green':		$sCode = '&backcolor=0x66FF66&frontcolor=0x009900&lightcolor=0xFFFFFF&screencolor=0xCCFFCC'; break;
			case 'yellow':		$sCode = '&backcolor=0xFFFF66&frontcolor=0x666600&lightcolor=0xFFFFFF&screencolor=0xFFFFBB'; break;
			case 'darkred':		$sCode = '&backcolor=0x331111&frontcolor=0x888888&lightcolor=0xAA0000&screencolor=0x440000'; break;
			case 'darkgreen':	$sCode = '&backcolor=0x113311&frontcolor=0x888888&lightcolor=0x00AA00&screencolor=0x004400'; break;
			case 'darkblue':	$sCode = '&backcolor=0x111133&frontcolor=0x888888&lightcolor=0x0000AA&screencolor=0x000044'; break;
			default: 			$sCode = '&backcolor=0xDDDDDD&frontcolor=0x000000&lightcolor=0x777777&screencolor=0x222222'; break;
		}
		return($sCode);
	}
	
	/**
	 * Standard SWF Code erstellen
	 * @param string sFile, absoluter Pfad zum Flashfile
	 * @param int nWidth, Breite des Players
	 * @param integer nHeight, Höhe des Players inkl. Controls
	 * @param strring sAlign, 'right' oder 'left' umfliessend
	 * @param string out, Variable für den HTML Output
	 */
	public static function getSwfCode($sFile,$nWidth,$nHeight,$sAlign,&$out) {
		// Align anpassen
		switch ($sAlign) {
			case 'right':
				$sMargin = '3px 0px 3px 3px'; break;
			case 'left':
				$sMargin = '3px 3px 3px 0px'; break;
			default:
				$sAlign = 'none';
				$sMargin = '3px 3px 3px 3px';
		}
		$out .= '
		<div style="float:'.$sAlign.';margin:'.$sMargin.';">
			<embed src="'.$sFile.'" 
			  width="'.$nWidth.'" height="'.$nHeight.'"
			  allowscriptaccess="always" 
			  type="application/x-shockwave-flash"
			  pluginspage="http://www.macromedia.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash"
			/>
		</div>
		';
	}
	
	/**
	 * Image Hintergrundbild für Videofile holen.
	 * @param string sFile, Videofile
	 * @return string PFad zum Hintergrundfile
	 */
	public static function getImageVersion($sFile) {
		$sImage = substr($sFile,0,strripos($sFile,'.'));
		$sImage .= '.jpg';
		return($sImage);
	}
}