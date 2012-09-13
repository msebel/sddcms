<?php
/**
 * Gibt fertigen HTML Code zurück um Inhaltselemente anzuzeigen.
 * Dies sind etwa Content, Medien, News oder Formulare.
 * @author Michael Sebel <michael@sebel.ch>
 */
class contentView {
	
	/**
	 * Definiert HTML Inhalte
	 * @var integer
	 */
	const TYPE_CONTENT = 1;
	/**
	 * Definiert ein Medium (Bild, Video, Musik etc.)
	 * @var integer
	 */
	const TYPE_MEDIA = 2;
	/**
	 * Definiert ein Formular (Mail)
	 * @var integer
	 */
	const TYPE_FORM = 3;
	
	/**
	 * Holt ein Element und generiert dessen HTML Code mit verschachtelten Funktionen.
	 * @param integer nCseID, Contentsektion ID
	 * @param integer nConID, ID des zugehörigen Contents
	 * @param integer nType, Typ des Contents (TYPE_ Konstanten)
	 * @param string out, Output in den HTML angehängt werden kann
	 * @param dbConn Conn, Datenbank Objekt
	 */
	public static function getElement($nCseID,$nConID,$nType,&$out,dbConn &$Conn) {
		$out .= '<div id="content_'.$nCseID.'">';
		switch ($nType) {
			case self::TYPE_CONTENT:	self::getContentElement($nConID,$out,$Conn);	break;
			case self::TYPE_MEDIA:		self::getMediaElement($nCseID,$out,$Conn);		break;
			case self::TYPE_FORM:		self::getFormElement($nCseID,$out,$Conn);		break;
		}
		$out .= '</div>';
	}
	
	/**
	 * Holt ein Contentelement.
	 * @param integer nOwner, Besitzer ID des Elementes
	 * @param string out, Output in den HTML angehängt werden kann
	 * @param dbConn Conn, Datenbank Objekt
	 */
	public static function getContentElement($nOwner,&$out,dbConn &$Conn) {
		$sSQL = "SELECT con_Date,con_Modified,con_Active,con_ShowName,con_ShowDate,
		con_ShowModified,con_Title,con_Content FROM tbcontent WHERE con_ID = $nOwner";
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) $sData = $row;
		// Schauen ob der Titel als h1 angezeigt wird
		$sTitle = stringOps::htmlEnt($sData['con_Title']);
		if ($sData['con_ShowName'] == 1) {
			$out .= '<h1>'.$sTitle.'</h1>';
		}
		// Content Kodieren
		stringOps::htmlViewEnt($sData['con_Content']);
		// [MORE] Variable aussortierten
		$sData['con_Content'] = str_replace('[MORE]','',$sData['con_Content']);
		// Content anhängen
		$out .= $sData['con_Content'];
		
		// Daten zu Europäischen Daten konvertieren
		if ($sData['con_Date'] != NULL) {
			$sDate = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATETIME,$sData['con_Date']);
		}
		if ($sData['con_Modified'] != NULL) {
			$sModified = dateOps::convertDate(dateOps::SQL_DATETIME,dateOps::EU_DATETIME,$sData['con_Modified']);
		}
		// Anzeige definieren
		$nModified = $sData['con_ShowModified'];
		$nDate = $sData['con_ShowDate'];
		// Wenn beides
		if ($nDate == 1) {
			$Res = new resources($Conn);
			$out .= '<p><em>'.$Res->html(448,page::language()).': '.$sDate.'</em></p>';
		} elseif ($nModified == 1) {
			$Res = new resources($Conn);
			$out .= '<p><em>'.$Res->html(449,page::language()).': '.$sModified.'</em></p>';
		}
	}
	
	/**
	 * Newseinträge darstellen.
	 * @param array Data, Datenarray eines Datensatzes der tbnews Tabelle
	 * @param string out, Output in den HTML angehängt werden kann
	 * @param resources Res, Mehrsprachen Objekt
	 * @param array NewsConfig, Konfiguration der Newsseite von pageConfig
	 */
	public static function getNews(&$Data,&$out,resources &$Res,$NewsConfig) {
		// Je nach Konfig, bis zum [MORE] Code kürzen
		$sMoreLink = '';
		if ($NewsConfig['shortnews']['Value'] == 1) {
			$nPos = strpos($Data['con_Content'],'[MORE]');
			if ($nPos > 0) {
				$Data['con_Content'] = substr($Data['con_Content'],0,$nPos);
				// Nun gibs noch einen More Link
				$menu = singleton::currentmenu();
				$sMoreLink = ' <a href="'.$menu->getLink('news='.$Data['con_ID']).'" class="cMoreLink">'.$Res->html(442,page::language()).'</a>';
			}
		}
		// newsHead String erstellen, je nach Config ohne Namen
		$sNewsHead = '';
		if (strlen($Data['con_Title'])) {
			$sNewsHead .= $Data['con_Title'];
			if ($Data['con_ShowDate'] == 1) $sNewsHead .= ', ';
		}
		// Zeit konvertieren
		if ($Data['con_ShowDate'] == 1) {
			$nStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Data['con_ViewDate']);
			$sNewsHead .= dateOps::getTime(dateOps::EU_DATE,$nStamp);
			$sNewsHead .= ' '.$Res->html(380,page::language()).' ';
			$sNewsHead .= dateOps::getTime(dateOps::EU_CLOCK,$nStamp);
		}
		// Erfassernamen hinzufügen
		if ($NewsConfig['showName']['Value'] == 1) {
			$sNewsHead .= ' '.$Res->html(381,page::language()).' '.$Data['usr_Name'];
		}
		// News ausgeben
		$out.= '
		<div class="newsHead">
			'.$sNewsHead.'
		</div>
		<div class="newsContent">
			'.$Data['con_Content'].'
			<p>'.$sMoreLink.'</p>
		</div>
		<div class="cDivider"></div>
		';
	}
	
	/**
	 * Holt ein Contentelement (Media).
	 * @param integer nOwner, Besitzer ID des Elementes
	 * @param string out, Output in den HTML angehängt werden kann
	 * @param dbConn Conn, Datenbank Objekt
	 */
	private static function getMediaElement($nOwner,&$out,dbConn &$Conn) {
		require_once(BP.'/library/class/mediaManager/mediaConst.php');
		// Objektdaten holen
		$sData = array();
		$sSQL = "SELECT ele_ID,ele_Downloads,ele_Width,ele_Height,ele_Type,
		ele_Thumb,ele_Align,ele_Skin,ele_Target,ele_File,ele_Desc
		FROM tbelement WHERE owner_ID = $nOwner";
		$nRes = $Conn->execute($sSQL);
		while ($row = $Conn->next($nRes)) $sData = $row;
		// Pfad eruieren
		$sPath = mediaConst::FILEPATH;
		$sPath = str_replace("{ELE_ID}",$sData['ele_ID'],$sPath);
		$sPath = str_replace("{PAGE_ID}",page::ID(),$sPath);
		// Typ switchen und anzeigen
		switch (getInt($sData['ele_Type'])) {
			// Bild einbinden
			case mediaConst::TYPE_PICTURE:
				// Wenn Thumb
				if ($sData['ele_Thumb'] == 1) {
					$out .= '<a href="'.self::getXLVersion($sPath.$sData['ele_File']).'" rel="lightbox" target="_blank" title="'.$sData['ele_Desc'].'">';
				}
				// Image anzeigen
				$out .= '<img src="'.$sPath.$sData['ele_File'].'" border="0"  alt="'.$sData['ele_Desc'].'" title="'.$sData['ele_Desc'].'" style="float:'.$sData['ele_Align'].';margin:3px;">';
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
				$out .= '<a href="/library/class/core/download.php?file='.$nOwner.'&name='.$sData['ele_File'].'">'.$sData['ele_File'].'</a>';
				break;
		}
	}
	
	/**
	 * Holt ein Contentelement (Formular).
	 * @param integer nCseID, Section ID des Formulars
	 * @param string out, Output in den HTML angehängt werden kann
	 * @param dbConn Conn, Datenbank Objekt
	 */
	private static function getFormElement($nCseID,&$out,dbConn &$Conn) {
		// Formularcode Klasse laden
		require_once(BP.'/library/class/mediaManager/formCode.php');
		// Ressourcen holen
		$Res = getResources::getInstance($Conn);
		
		// Zeit nehmen für Spam Bots
		$_SESSION['emailFormTime_'.$nCseID] = time();
		$_SESSION['emailFormBack_'.$nCseID] = singleton::menu()->CurrentMenu->getLink();
		$_SESSION['emailFormConfirm_'.$nCseID] = false;
		$_SESSION['emailFormSubject_'.$nCseID] = $Res->normal(203,page::language()).' - '.page::domain();
		// URL zum Posten erstellen
		$posturl = '/library/class/phpMailer/sendMail.php?mail='.$nCseID;
		// Entscheiden ob Nativ oder mit Content geladen werden soll
		$sSQL = 'SELECT con_ID FROM tbcontentsection WHERE cse_ID = '.$nCseID;
		$nConID = getInt($Conn->getFirstResult($sSQL));
		if ($nConID > 0) {
			// Content laden
			$sSQL = 'SELECT con_Content FROM tbcontent WHERE con_ID = '.$nConID;
			$sContent = $Conn->getFirstResult($sSQL);
			// POST Url ersetzen
			$sContent = str_replace('{POSTURL}', $posturl, $sContent);
			// Fehlermeldungen ausgeben, falls vorhanden
			if (isset($_SESSION['emailFormError_'.$nCseID])) {
				$out .= '
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					'.formCode::getErrorMessages($nCseID).'
				</table>
				';
			}
			// Und fertig...
			$out .= $sContent;
		} else {
			// Formulartabelle starten
			$out .= '
			<form name="sendMail" action="'.$posturl.'" method="post">
			<table border="0" cellpadding="3" cellspacing="0" width="100%">
			';
			// Fehlermeldungen ausgeben
			if (isset($_SESSION['emailFormError_'.$nCseID])) {
				$out .= formCode::getErrorMessages($nCseID);
			}
			// Alle Formularelemente durchgehen
			$sSQL = "SELECT ffi_Width,ffi_Required,ffi_Name,ffi_Desc,ffi_Type,
			ffi_Class,ffi_Value,ffi_Options FROM tbformfield
			WHERE cse_ID = $nCseID ORDER BY ffi_Sortorder ASC";
			$nRes = $Conn->execute($sSQL);
			while ($row = $Conn->next($nRes)) {
				switch($row['ffi_Type']) {
					case 'text': 		$out .= formCode::getTextfield($row,$nCseID); 	break;
					case 'textarea': 	$out .= formCode::getTextarea($row,$nCseID); 	break;
					case 'radio':		$out .= formCode::getRadio($row,$nCseID);		break;
					case 'checkbox':	$out .= formCode::getCheckbox($row,$nCseID);	break;
					case 'hidden':		$out .= formCode::getHidden($row,$nCseID);		break;
					case 'submit':		$out .= formCode::getSubmit($row,$nCseID);		break;
					case 'dropdown':	$out .= formCode::getDropdown($row,$nCseID);	break;
					case 'captcha':		$out .= formCode::getCaptcha($row,$nCseID);		break;
				}
			}
			// Formulartabelle beenden
			$out .= '
			</table>
			</form>
			';
		}
	}
	
	/**
	 * XL Version eines Bild holen.
	 * @param string sFilename, Name des Originalbildes
	 * @return string XL_Name des Bildes aus Mediamanager
	 */
	private static function getXLVersion ($sFilename) {
		// Extension eruieren und XL damit ersetzen
		$sExt = self::getExtension($sFilename);
		$sXLFile = str_replace($sExt,mediaConst::XL_SUFFIX.$sExt,$sFilename);
		return($sXLFile);
	}
	
	/**
	 * Extension eines Files holen.
	 * @param string sFile, Dateiname mit oder ohne Pfad
	 * @return string Dateiendung inklusive Punkt (zb. '.jpg')
	 */
	private static function getExtension($sFile) {
		return(substr($sFile,strripos($sFile,'.')));
	}
}