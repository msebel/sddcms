<?php
class moduleBlogComments extends commonModule {
	
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
	/**
	 * Aktuell bearbeiteter Blogeintrag für Backlinks
	 * @var integer
	 */
	public $CurrentEntry;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $this->Conn
		$this->Res	=& func_get_arg(1);	// $this->Res
	}
	
	public function LoadData(&$CommentData, $nPpP) {
		// SQL erstellen
		$sSQL = "SELECT com_ID,com_Time,com_Name,com_Content,com_Active FROM tbkommentar
		WHERE owner_ID = ".$this->CurrentEntry." ORDER BY com_ID DESC";
		$PagingEngine = new paging($this->Conn,'index.php?id='.page::menuID());
		$PagingEngine->start($sSQL,$nPpP);
		$nRes = $this->Conn->execute($PagingEngine->getSQL());
		while ($row = $this->Conn->next($nRes)) {
			array_push($CommentData,$row);
		}
		// Paging Engine HTML zurückgeben
		return($PagingEngine->getHtml());
	}
	
	public function showCommentsAdmin(&$CommentData,&$out) {
		// TabRowExtender für Kommentare
		$GBTabRow = new tabRowExtender('forumRowOdd','forumRowEven');
		$out .= '<table width="100%" cellpadding="5" cellspacing="1" class="forumTable">';
		$nCount = 0;
		foreach ($CommentData as $Post) {
			$nCount++;
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
			$sParam = '&entry='.$this->CurrentEntry;
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
		// Wenn nichts, Meldung ausgeben
		if ($nCount == 0) {
			$sClass = $GBTabRow->getSpecial();
			$out .= '
			<tr class="'.$sClass.'">
				<td>'.$this->Res->html(637,page::language()).'</td>
			</tr>
			';
		}
		$out .= '</table>';
	}
	
	// Prüft ob Kommentar zum aktuellen Menu gehört
	public function isMenuComment($nComID) {
		$sSQL = "SELECT COUNT(com_ID) FROM tbkommentar WHERE
		com_ID = $nComID AND owner_ID = ".$this->CurrentEntry;
		$nResult = $this->Conn->getCountResult($sSQL);
		$bResult = false;
		// True zurückgeben, wenn Element gefunden
		if ($nResult == 1) $bResult = true;
		return($bResult);
	}
	
	// Löscht einen Eintrag
	public function deletePost() {
		$nComID = getInt($_GET['delete']);
		// Löschen wenn erlaubt
		if ($this->isMenuComment($nComID)) {
			$sSQL = "DELETE FROM tbkommentar WHERE com_ID = $nComID";
			$this->Conn->command($sSQL);
			// Erfolgsmeldung
			logging::debug('deleted blog comment');
			$this->setErrorSession($this->Res->html(146,page::language()));
			$this->resetPaging();
			session_write_close();
			redirect('location: /admin/blog/comments/index.php?id='.page::menuID().'&entry='.$this->CurrentEntry);
		} else {
			// Fehlermeldung
			logging::error('error deleting blog comment');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
			redirect('location: /admin/blog/comments/index.php?id='.page::menuID().'&entry='.$this->CurrentEntry);;
		}
	}
	
	// Setzt einen Eintrag aktiv oder inaktiv
	public function toggleActive() {
		// Aktuellen Status holen
		$nComID = getInt($_GET['toggleActive']);
		$sSQL = "SELECT com_Active FROM tbkommentar WHERE com_ID = $nComID";
		$nActive = $this->Conn->getFirstResult($sSQL);
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
			redirect('location: /admin/blog/comments/index.php?id='.page::menuID().'&entry='.$this->CurrentEntry);
		} else {
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/blog/comments/index.php?id='.page::menuID().'&entry='.$this->CurrentEntry);
		}
	}
}