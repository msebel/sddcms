<?php
class contentLib {
	
	public function generateHtml(dbConn &$Conn,$nElementID) {
		// Daten abholen
		require_once(BP.'/library/class/mediaManager/mediaConst.php');
		$sData['ele_File'] 		= stringOps::getPostEscaped('sFile',$Conn);
		$sData['ele_Type']		= self::guessFiletype($sData['ele_File']);
		// HTML Daten
		$out = '';
		// Typ switchen und anzeigen
		switch (getInt($sData['ele_Type'])) {
			// Bild einbinden
			case mediaConst::TYPE_PICTURE:
				// Image anzeigen
				$out .= '<img src="'.$sPath.$sData['ele_File'].'" border="0"  alt="" title="" style="float:left;margin:3px 3px 3px 0px;">';
				break;
			// Flash Video einbinden
			case mediaConst::TYPE_FLASHVIDEO:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getFlvPlayerCode(
					$sPath.$sData['ele_File'],400,300,'light','left',$out
				);
				break;	
			// Flashdatei einbinden
			case mediaConst::TYPE_FLASH:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getSwfCode(
					$sPath.$sData['ele_File'],640,480,'left',$out
				);
				break;
			// Musikdatei einbinden
			case mediaConst::TYPE_MUSIC:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getMp3PlayerCode(
					$sPath.$sData['ele_File'],350,'light','left',$out
				);
				break;
			// Files zum Download anbieten
			case mediaConst::TYPE_VIDEO:
			case mediaConst::TYPE_OTHER:
				$out .= '<a href="/library/class/core/library.php?file='.str_replace('/page/'.page::id().'/library/','',$sData['ele_File']).'">'.basename($sData['ele_File']).'</a>';
				break;
		}
		// Html zur�ckgeben
		return($out);
	}
	
	private static function guessFiletype($sFile) {
		$sExtension = strtolower(substr($sFile,strripos($sFile,'.')));
		switch ($sExtension) {
			case '.jpg':
			case '.gif':
			case '.png':
				$nType = mediaConst::TYPE_PICTURE;
				break;
			case '.flv':
				$nType = mediaConst::TYPE_FLASHVIDEO;
				break;
			case '.mp3':
				$nType = mediaConst::TYPE_MUSIC;
				break;
			case '.swf':
				$nType = mediaConst::TYPE_FLASH;
				break;
			default:
				$nType = mediaConst::TYPE_OTHER;
		}
		return($nType);
	}
}