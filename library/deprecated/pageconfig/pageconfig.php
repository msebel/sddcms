<?php
// Funktionen um die Pageoptionen zu verändern
class modulePageconfig extends commonModule {
	
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
	
	// Optionen speichern
	public function editPage() {
		$Errors = array();
		// Parameter Validieren
		$nStart 	= $this->validateStart($Errors);
		$nTeaserID  = $this->validateTeaser($Errors);
		$sTitle 	= $_POST['title'];
		$sName 		= $_POST['pagename'];
		$sAuthor 	= $_POST['metaauthor'];
		$sVerify 	= $_POST['verify'];
		$sMetadesc 	= $_POST['metadesc'];
		$sMetakeys 	= $_POST['metakeys'];
		// HTML entfernen und Escapen
		stringOps::noHtml($sTitle);	
		stringOps::noHtml($sName);	
		stringOps::noHtml($sAuthor);
		stringOps::noHtml($sVerify);
		stringOps::noHtml($sMetadesc);	
		stringOps::noHtml($sMetakeys);
		// Meta Informationen validieren (In Tags sind keine " erlaubt)
		$sMetadesc 	= str_replace('"','',stripslashes($sMetadesc));
		$sMetakeys 	= str_replace('"','',stripslashes($sMetakeys));
		$sVerify 	= str_replace('"','',stripslashes($sVerify));
		$sAuthor 	= str_replace('"','',stripslashes($sAuthor));
		$sName 		= str_replace('"','',stripslashes($sName));
		// Slashes wieder hinzufügen für einfache Hochkommata
		$sTitle 	= addslashes($sTitle);
		$sName 		= addslashes($sName);
		$sAuthor 	= addslashes($sAuthor);
		$sVerify 	= addslashes($sVerify);
		$sMetadesc 	= addslashes($sMetadesc);
		$sMetakeys 	= addslashes($sMetakeys);
		// Daten speichern wenn keine Fehler
		if (count($Errors) == 0) {
			$sSQL = "UPDATE tbmandant SET
			man_Start = $nStart, man_Title = '$sTitle', tas_ID = '$nTeaserID',
			man_Metaauthor = '$sAuthor', man_Verify = '$sVerify',
			man_Metadesc = '$sMetadesc', man_Metakeys = '$sMetakeys'
			WHERE man_ID = ".page::mandant();
			$this->Conn->command($sSQL);
			$sSQL = "UPDATE tbpage SET page_Name = '$sName' 
			WHERE page_ID = ".page::ID();
			$this->Conn->command($sSQL);
			// Zum speichern alle Slashes wieder entfernen
			$sTitle 	= stripslashes($sTitle);
			$sName 		= stripslashes($sName);
			$sAuthor 	= stripslashes($sAuthor);
			$sVerify 	= stripslashes($sVerify);
			$sMetadesc 	= stripslashes($sMetadesc);
			$sMetakeys 	= stripslashes($sMetakeys);
			// Page Session überschreiben
			$_SESSION['page']['metadesc'] 	= $sMetadesc;
			$_SESSION['page']['metakeys'] 	= $sMetakeys;
			$_SESSION['page']['name'] 		= $sName;
			$_SESSION['page']['author'] 	= $sAuthor;
			$_SESSION['page']['verify'] 	= $sVerify;
			$_SESSION['page']['title'] 		= $sTitle;
			$_SESSION['page']['start'] 		= $nStart;
			$_SESSION['page']['teaserID']	= $nTeaserID;
			// Standardteaser und Zeit löschen
			unset($_SESSION['standardteaser']);
			unset($_SESSION['standardteaser_time']);
			// Weiterleiten zum Index, Erfolg ausgeben
			logging::debug('saved page configuration');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/page/index.php?id='.page::menuID());
		} else {
			logging::error('error saving page configuration');
			$this->setErrorSession($Errors);
		}
	}
	
	// Teaser Dropdown erhalten
	public function getTeaserOptions() {
		$nTeaserID = page::teaserID();
		$out = '';
		// Kein Teaser Option einfügen
		$out .= '<option value="0"'.checkDropDown(0,$nTeaserID).'>'.$this->Res->html(402,page::language()).'</option>'."\n";
		// Teaser des Mandanten lesen
		$sSQL = "SELECT tas_ID,tas_Desc FROM tbteasersection WHERE man_ID = ".page::mandant();
		$nRes = $this->Conn->execute($sSQL);
		while ($row = $this->Conn->next($nRes)) {
			$out .= '<option value="'.$row['tas_ID'].'"'.checkDropDown($row['tas_ID'],$nTeaserID).'>'.$row['tas_Desc'].'</option>'."\n";
		}
		return($out);
	}
	
	// Startseite prüfen
	private function validateStart(&$Errors) {
		$nStart  = getInt($_POST['start']);
		// Prüfen ob Startseite dem Mandanten gehört
		$sSQL = "SELECT COUNT(mnu_ID) AS mnu_ID FROM tbmenu 
		WHERE man_ID = ".page::mandant()." AND mnu_ID = $nStart";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1)  {
			// Fehler
			array_push($Errors,$this->Res->html(320,page::language()));
		}
		// Startseite zurückgeben
		return($nStart);
	}
	
	// Standard Teaser ID prüfen
	private function validateTeaser(&$Errors) {
		$nTeaserID  = getInt($_POST['teaser']);
		// Prüfen ob Startseite dem Mandanten gehört
		$sSQL = "SELECT COUNT(tas_ID) AS tas_ID FROM tbteasersection 
		WHERE man_ID = ".page::mandant()." AND tas_ID = $nTeaserID";
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1 && $nTeaserID != 0)  {
			// Fehler
			array_push($Errors,$this->Res->html(403,page::language()));
		}
		// Teaser ID zurückgeben
		return($nTeaserID);
	}
}