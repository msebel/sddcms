<?php
class contentLib {
	
	public function getElementInContent(dbConn &$Conn) {
		// Content holen
		$nContentID = getInt($_SESSION['ActualContentID']);
		// Element mit diesem Content als Owner holen
		$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = $nContentID";
		$nElementID = $Conn->getFirstResult($sSQL);
		// Wenn ergebnislos neues Element erstellen
		if ($nElementID == NULL && !isset($_SESSION['ActualElementID'])) {
			$sSQL = "INSERT INTO tbelement (owner_ID)
			VALUES (".$nContentID.")";
			$nElementID = $Conn->insert($sSQL);
		} elseif (isset($_SESSION['ActualElementID'])) {
			$nElementID = getInt($_SESSION['ActualElementID']);
		}
		return($nElementID);
	}
	
	public function generateHtml(dbConn &$Conn,$nElementID) {
		// Daten abholen
		$sData['ele_File'] 		= stringOps::getPostEscaped('sFile',$Conn);
		$sData['ele_XLFile'] 	= stringOps::getPostEscaped('sFileXL',$Conn);
		$sData['ele_Type'] 		= stringOps::getPostEscaped('nType',$Conn);
		$sData['ele_Width'] 	= stringOps::getPostEscaped('nWidth',$Conn);
		$sData['ele_Height'] 	= stringOps::getPostEscaped('nHeight',$Conn);
		$sData['ele_Desc'] 		= stringOps::getPostEscaped('sDesc',$Conn);
		$sData['ele_Thumb'] 	= stringOps::getPostEscaped('nThumb',$Conn);
		$sData['ele_Skin'] 		= stringOps::getPostEscaped('sSkin',$Conn);
		$sData['ele_Align'] 	= stringOps::getPostEscaped('sAlign',$Conn);
		// HTML Daten
		$out = '';
		require_once(BP.'/library/class/mediaManager/mediaConst.php');
		// Objektdaten holen
		// Pfad eruieren
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{ELE_ID}",$nElementID,$sPath);
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		// Typ switchen und anzeigen
		switch (getInt($sData['ele_Type'])) {
			// Bild einbinden
			case mediaConst::TYPE_PICTURE:
				// Wenn Thumb
				if ($sData['ele_Thumb'] == 1) {
					$out .= '<a href="'.$sPath.$sData['ele_XLFile'].'" rel="lightbox" target="_blank" title="'.$sData['ele_Desc'].'">';
				}
				switch ($sData['ele_Align']) {
					case 'right':
						$sMargin = '3px 0px 3px 3px'; break;
					case 'left':
						$sMargin = '3px 3px 3px 0px'; break;
					case 'none':
					default:
						$sData['ele_Align'] = 'none';
						$sMargin = '3px 3px 3px 3px';
				}
				// Image anzeigen
				$out .= '<img src="'.$sPath.$sData['ele_File'].'" border="0"  alt="'.$sData['ele_Desc'].'" title="'.$sData['ele_Desc'].'" style="float:'.$sData['ele_Align'].';margin:'.$sMargin.';">';
				// Wenn Thumb, abschliessen
				if ($sData['ele_Thumb'] == 1) $out .= '</a>';
				break;
			// Flash Video einbinden
			case mediaConst::TYPE_FLASHVIDEO:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getFlvPlayerCode(
					$sPath.$sData['ele_File'],
					$sData['ele_Width'],
					$sData['ele_Height'],
					$sData['ele_Skin'],
					$sData['ele_Align'],
					$out
				);
				break;	
			// Flashdatei einbinden
			case mediaConst::TYPE_FLASH:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getSwfCode(
					$sPath.$sData['ele_File'],
					$sData['ele_Width'],
					$sData['ele_Height'],
					$sData['ele_Align'],
					$out
				);
				break;
			// Musikdatei einbinden
			case mediaConst::TYPE_MUSIC:
				require_once(BP.'/library/class/mediaManager/flashCode.php');
				flashCode::getMp3PlayerCode(
					$sPath.$sData['ele_File'],
					$sData['ele_Width'],
					$sData['ele_Skin'],
					$sData['ele_Align'],
					$out
				);
				break;
			// Files zum Download anbieten
			case mediaConst::TYPE_VIDEO:
			case mediaConst::TYPE_OTHER:
				$out .= '<a href="/library/class/core/download.php?file='.$nElementID.'&name='.$sData['ele_File'].'">'.$sData['ele_File'].'</a>';
				break;
		}
		// Html zur�ckgeben
		return($out);
	}
}