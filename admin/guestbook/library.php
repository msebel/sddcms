<?php
class moduleGuestbook extends commonModule {
	
	/**
	 * Referenz zum Datenbankobjekt
	 * @var dbConn
	 */
	private $Conn;
	/**
	 * Referenz zum Sprachressourcenobjekt
	 * @var resources
	 */
	private $Res;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	public function LoadData(&$GBData, &$GBConfig) {
		// SQL erstellen
		$sSQL = "SELECT com_ID,com_Time,com_Name,com_Content,com_Active FROM tbkommentar
		WHERE owner_ID = ".page::menuID()." ORDER BY com_ID DESC";
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		$PagingEngine->start($sSQL,$GBConfig['postsPerPage']['Value']);
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			array_push($GBData,$row);
		}
		// Paging Engine HTML zurückgeben
		return($PagingEngine->getHtml());
	}
	
	public function showCommentsAdmin(&$GBData,&$out) {
		// TabRowExtender für Gästebucheinträge
		$GBTabRow = new tabRowExtender('forumRowOdd','forumRowEven');
		$out .= '<table width="100%" cellpadding="5" cellspacing="1" class="forumTable">';
		foreach ($GBData as $Post) {
			// Zeilenklasse herausfinden
			if ($Post['com_Active'] == 0) {
				// Nur so tun damit Farbe wechselt
				$sClass = $GBTabRow->getSpecial();
				// Aber eigentlich inaktiv klasse nehmen
				$sClass = 'forumRowInactive';
				$sImage = 'page_go.png';
				$sDesc = $this->Res->html(324,page::language());
			} else {
				$sClass = $GBTabRow->getSpecial();
				$sImage = 'page_error.png';
				$sDesc = $this->Res->html(325,page::language());
			}
			// Zeit und Datum herausfinden
			$nStamp = dateOps::getStamp(dateOps::SQL_DATETIME,$Post['com_Time']);
			$sDate = dateOps::getTime(dateOps::EU_DATE,$nStamp);
			$nTime = dateOps::getTime(dateOps::EU_CLOCK,$nStamp);
			$sParam = $this->getPageParam();
			stringOps::htmlViewEnt($Post['com_Name']);
			stringOps::htmlViewEnt($Post['com_Content']);
			// Post anzeigen
			$out .= '
			<tr class="'.$sClass.'">
				<td width="20%" valign="top">
					'.$this->Res->html(326,page::language()).' 
					'.$Post['com_Name'].'<br>
					<br>
					<em>
					'.$sDate.' <br>
					'.$this->Res->html(327,page::language()).'
					'.$nTime.'
					</em>
					<br>
				</td>
				<td valign="top">
					<div style="float:right;margin:5px;">
						<a href="index.php?id='.page::menuID().'&toggleActive='.$Post['com_ID'].$sParam.'" title="'.$sDesc.'">
						<img src="/images/icons/'.$sImage.'" alt="'.$sDesc.'" title="'.$sDesc.'" border="0"></a>
					</div>
					<div style="float:right;margin:5px;">
						<a href="javascript:deleteConfirm(\'index.php?id='.page::menuID().'&delete='.$Post['com_ID'].$sParam.'\',\'Post '.$this->Res->html(326,page::language()).' '.$Post['com_Name'].'\','.page::language().')" title="'.$this->Res->html(157,page::language()).'">
						<img src="/images/icons/page_delete.png" alt="'.$this->Res->html(157,page::language()).'" title="'.$this->Res->html(157,page::language()).'" border="0"></a>
					</div>
					'.$Post['com_Content'].'
				</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
	
	// Prüft ob Kommentar zum aktuellen Menu gehört
	public function isMenuComment($nComID) {
		$sSQL = "SELECT COUNT(com_ID) FROM tbkommentar WHERE
		com_ID = $nComID AND owner_ID = ".page::menuID();
		$nResult = $this->Conn->getCountResult($sSQL);
		$bResult = false;
		// True zurückgeben, wenn Element gefunden
		if ($nResult == 1) $bResult = true;
		return($bResult);
	}
	
	// Löscht einen Eintrag
	public function deletePost() {
		$nComID = getInt($_GET['delete']);
		// Seitenparameter holen
		$sParam = $this->getPageParam();
		// Löschen wenn erlaubt
		if ($this->isMenuComment($nComID)) {
			$sSQL = "DELETE FROM tbkommentar WHERE com_ID = $nComID";
			$this->Conn->command($sSQL);
			// Erfolgsmeldung
			logging::debug('deleted guestbook entry');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/guestbook/index.php?id='.page::menuID().$sParam);
		} else {
			// Fehlermeldung
			logging::error('error deleting guestbook entry');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/guestbook/index.php?id='.page::menuID().$sParam);
		}
	}
	
	// Setzt einen Eintrag aktiv oder inaktiv
	public function toggleActive() {
		// Aktuellen Status holen
		$nComID = getInt($_GET['toggleActive']);
		$sSQL = "SELECT com_Active FROM tbkommentar WHERE com_ID = $nComID";
		$nActive = $this->Conn->getFirstResult($sSQL);
		// Seitenparameter holen
		$sParam = $this->getPageParam();
		// Status umkehren
		switch ($nActive) {
			case 0:  $nActive = 1; break;
			case 1:  $nActive = 0; break;
			default: $nActive = 0;
		}
		// Neuen Status geben wenn erlaubt
		if ($this->isMenuComment($nComID)) {
			$sSQL = "UPDATE tbkommentar SET com_Active = $nActive WHERE com_ID = $nComID";
			$this->Conn->command($sSQL);
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/guestbook/index.php?id='.page::menuID().$sParam); 
		} else {
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/guestbook/index.php?id='.page::menuID().$sParam); 
		}
	}
	
	// Holt den Page Parameter oder gibt leeren String zurück
	public function getPageParam() {
		$sParam = '';
		if (isset($_GET['page'])) {
			$sParam = '&page='.getInt($_GET['page']);
		}
		return($sParam);
	}
	
	// Gästebuch Konfig initialisieren
	public function initConfig(&$Config) {
		$nMenuID = page::menuID();
		// Standardwerte erstellen für Konfiguration wenn nicht vorhanden
		if (!pageConfig::hasConfig($nMenuID,$this->Conn,6)) {
			pageConfig::setConfig($nMenuID,$this->Conn,1,pageConfig::TYPE_NUMERIC,'useCaptcha',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,0,pageConfig::TYPE_NUMERIC,'activationNeeded',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,10,pageConfig::TYPE_NUMERIC,'postsPerPage',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,300,pageConfig::TYPE_NUMERIC,'SpamLockSecs',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_VALUE,'emailAddress',$Config);
			pageConfig::setConfig($nMenuID,$this->Conn,'',pageConfig::TYPE_TEXT,'htmlCode',$Config);
		} else {
			// Konfiguration laden
			pageConfig::get($nMenuID,$this->Conn,$Config);
		}
	}
	
	// Gästebuch Konfig speichern
	public function saveConfig(&$Config) {
		$nMenuID = page::menuID();
		// Parameter anpassen
		$Config['useCaptcha']['Value'] 			= stringOps::getBoolInt($_POST['useCaptcha']);
		$Config['activationNeeded']['Value'] 	= stringOps::getBoolInt($_POST['activationNeeded']);
		$Config['postsPerPage']['Value'] 		= stringOps::getPosInt($_POST['postsPerPage']);
		$Config['emailAddress']['Value'] 		= $this->validateEmail($_POST['emailAddress']);
		$Config['SpamLockSecs']['Value'] 		= stringOps::getPosInt($_POST['SpamLockSecs']);
		$Config['htmlCode']['Value'] 			= $_POST['htmlCode'];
		stringOps::htmlEntRev($Config['htmlCode']['Value']);
		// Speichern
		pageConfig::saveConfig($nMenuID,$this->Conn,$Config);
		// Paging zurücksetzen, da die Einstellung evtl. änderte
		$this->resetPaging();
		// Erfolg speichern und weiterleiten
		logging::debug('saved guestbook config');
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/guestbook/config.php?id='.page::menuID()); 
	}
	
	// Email validieren
	private function validateEmail($Email) {
		if (stringOps::checkEmail($Email) == false) {
			$Email = ''; // Leeren String liefern
		}
		return($Email);
	}
}