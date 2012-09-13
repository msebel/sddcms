<?php
// Klasse für Formular Generator
class moduleForm extends commonModule {
	
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
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Zugriff checken
	public function checkContentAccess() {
		$nMenuID = getInt($_GET['id']);
		$nContentID = getInt($_GET['content']);
		// Zählen ob diese Kombination existiert
		$sSQL = "SELECT COUNT(cse_ID) FROM tbcontentsection
		WHERE mnu_ID = $nMenuID AND cse_ID = $nContentID";
		$nResult = $this->Conn->getCountResult($sSQL);
		// Wenn Ergebniss nicht 1, auf Startseite gehen
		if ($nResult != 1) {
			logging::error('form access denied');
			$this->setErrorSession($this->Res->html(56,page::language()));
			session_write_close();
			redirect('location: /admin/content/index.php?id='.page::menuID());
		}
	}
	
	// Formular speichern
	public function saveFormFields() {
		// Email Adresse speichern
		$this->saveEmail($bError);
		// Wie viele Forms sind angekommen?
		$nCount = count($_POST['ffi_ID']);
		// Alle Formulare durchgehen
		$bError = false;
		for ($i = 0; $i < $nCount;$i++) {
			$this->saveField($bError,$i);
		}
		$nConID = getInt($_GET['content']);
		// Je nach Fehler weiterleiten
		if ($bError == false) {
			// Erfolg speichern und weiterleiten
			logging::debug('saved forms');
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/content/form.php?id='.page::menuID().'&content='.$nConID); 
		} else {
			// Fehler speichern und weiterleiten
			logging::error('error saving forms');
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/content/form.php?id='.page::menuID().'&content='.$nConID); 
		}
	}
	
	// Ein Formularfeld Speichern
	private function saveField(&$bError,$nOrder) {
		$nFormID = getInt($_POST['ffi_ID'][$nOrder]);
		$nChange = getInt($_POST['ffi_Changed'][$nOrder]);
		$nDelete = getInt($_POST['ffi_Deleted'][$nOrder]);
		$sType = $_POST['ffi_Type'][$nOrder];
		$this->Conn->escape($sType);
		// Andere werte nur holen wenn veränderung
		if ($nChange == 1) {
			$nWidth = getInt($_POST['ffi_Width'][$nOrder]);
			$nRequired = getInt($_POST['ffi_Required'][$nOrder]);
			if ($sType == 'captcha') $nRequired = 1;
			$sName = $_POST['ffi_Name'][$nOrder];
			$sDesc = $_POST['ffi_Desc'][$nOrder];
			$sClass = $_POST['ffi_Class'][$nOrder];
			$sValue = $_POST['ffi_Value'][$nOrder];
			// HTML entfernen und Escapen
			stringOps::noHtml($sName);	$this->Conn->escape($sName);
			stringOps::noHtml($sDesc);	$this->Conn->escape($sDesc);
			stringOps::noHtml($sClass);	$this->Conn->escape($sClass);
			stringOps::noHtml($sValue);	$this->Conn->escape($sValue);
		}
		// Ist der Typ korrekt?
		$bTypeOk = self::validateFormType($sType);
		// Typ Abchecken, nur wenn ok etwas tun
		if ($bTypeOk == true) {
			if ($nDelete == 1) {
				$sSQL = "DELETE FROM tbformfield WHERE ffi_ID = $nFormID";
			} elseif ($nChange == 1) {
				$sSQL = "UPDATE tbformfield SET
				ffi_Sortorder = $nOrder, ffi_Width = $nWidth,
				ffi_Name = '$sName', ffi_Desc = '$sDesc',
				ffi_Type = '$sType', ffi_Class = '$sClass',
				ffi_Value = '$sValue', ffi_Required = $nRequired
				WHERE ffi_ID = $nFormID";
			} else {
				// Nur den Sortorder updaten
				$sSQL = "UPDATE tbformfield SET ffi_Sortorder = $nOrder WHERE ffi_ID = $nFormID";
			}
			// Den Befehl ausführen
			$this->Conn->execute($sSQL);
		} else {
			// Den ByRef Error auf True stellen
			$bError = true;
		}
	}
	
	// Email Adresse speichern
	private function saveEmail (&$bError) {
		// ID des ersten Formfields holen
		$nCseID = getInt($_GET['content']);
		$sEmail = stringOps::getPostEscaped('email',$this->Conn);
		// Wenn Email Korrekt ...
		if (stringOps::checkEmail($sEmail)) {
			// ... Email speichern
			$sSQL = "UPDATE tbformfield SET ffi_Email = '$sEmail' WHERE cse_ID = $nCseID";
			$this->Conn->execute($sSQL);
			logging::debug('form answer e-mail saved');
		} else {
			$bError = true;
		}
	}
	
	// Ein neues Form Element erstellen
	public function addFormField() {
		// Daten abholen
		$sFormName = $_POST['newFormName'];
		$sFormType = $_POST['newFormType'];
		stringOps::noHtml($sFormName);	$this->Conn->escape($sFormName);
		stringOps::noHtml($sFormType);	$this->Conn->escape($sFormType);
		$nCseID = getInt($_GET['content']);
		$nMenuID = page::menuID();
		// Formnamen validieren
		if (strlen($sFormName) == 0) {
			$sFormName = "< " . $this->Res->normal(211,page::language()) . " >";
		}
		// Formularfeldtyp abfragen
		$bValid = self::validateFormType($sFormType);
		// Erforderlich?
		$nRequired = 0;
		if ($sFormType == 'captcha') $nRequired = 1;
		// Neues Feld erstellen mit standardwerten
		if ($bValid == true) {
			// Maximale Sortierung holen
			$sSQL = "SELECT MAX(ffi_Sortorder) FROM tbformfield WHERE cse_ID = $nCseID";
			$nSort = getInt($this->Conn->getFirstResult($sSQL)) + 1;
			$sSQL = "INSERT INTO tbformfield (cse_ID,mnu_ID,ffi_Width,
			ffi_Sortorder,ffi_Required,ffi_Name,ffi_Desc,ffi_Type) 
			VALUES ($nCseID,$nMenuID,180,$nSort,$nRequired,'form_$nSort','$sFormName','$sFormType')";
			$this->Conn->execute($sSQL);
			// Erfolg speichern und weiterleiten
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
			redirect('location: /admin/content/form.php?id='.page::menuID().'&content='.$nCseID); 
		} else {
			// Fehler speichern und weiterleiten
			$this->setErrorSession($this->Res->html(185,page::language()));
			session_write_close();
			redirect('location: /admin/content/form.php?id='.page::menuID().'&content='.$nCseID); 
		}
	}
	
	// Validieren einer Menu/Feld Kombination
	// Wenn nicht ok, weiterleiten auf Error Seite
	public function checkDropdownAccess($nConID,$nFieldID) {
		$sSQL = "SELECT COUNT(ffi_ID) FROM tbformfield
		WHERE ffi_ID = $nFieldID AND cse_ID = $nConID
		AND mnu_ID = ".page::menuID();
		$nReturn = $this->Conn->getCountResult($sSQL);
		if ($nReturn != 1) {
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Optionen für das Select anhand des Token Textes generieren
	public function loadSelectOptions(&$Options) {
		$nFieldID = getInt($_GET['field']);
		$sSQL = "SELECT ffi_Options FROM tbformfield WHERE ffi_ID = $nFieldID";
		$OptionText = $this->Conn->getFirstResult($sSQL);
		// Verarbeiten, wenn Inhalt vorhanden
		if (strlen($OptionText) > 0) {
			$OptionPair = explode(formCode::DROPDOWN_FIELD_DELIMITER,$OptionText);
			foreach ($OptionPair as $PairText) {
				$NameValue = explode(formCode::DROPDOWN_VALUE_DELIMITER,$PairText);
				$Option['value'] = $NameValue[0];
				$Option['text'] = $NameValue[1];
				array_push($Options,$Option);
			}
		}
	}
	
	// Neue leere Option hinzufügen
	public function addDropdownOption() {
		// Aktuellen Text lesen
		$nFieldID = getInt($_GET['field']);
		$sSQL = "SELECT ffi_Options FROM tbformfield WHERE ffi_ID = $nFieldID";
		$OptionText = $this->Conn->getFirstResult($sSQL);
		// Standardwerte erstellen
		$sNewValue = '< '.$this->Res->html(768,page::language()).' >';
		$sNewText = '< '.$this->Res->html(769,page::language()).' >';
		// Anhängen und Updaten des Wertes
		$OptionText .= formCode::DROPDOWN_FIELD_DELIMITER.$sNewValue;
		$OptionText .= formCode::DROPDOWN_VALUE_DELIMITER.$sNewText;
		$sSQL = "UPDATE tbformfield SET ffi_Options = '$OptionText'
		WHERE ffi_ID = $nFieldID";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/content/select.php?id='.page::menuID().'&content='.getInt($_GET['content']).'&field='.$nFieldID);
	}
	
	// Aktuelle Werteliste speichern
	public function saveDropdownOptions(&$Options) {
		$OptionText = '';
		$nFieldID = getInt($_GET['field']);
		for ($i = 0;$i < count($Options);$i++) {
			$Options[$i]['value'] = $this->validateValue($_POST['value_'.$i]);
			$Options[$i]['text'] = $this->validateValue($_POST['text_'.$i]);
			$OptionText .= formCode::DROPDOWN_FIELD_DELIMITER.$Options[$i]['value'];
			$OptionText .= formCode::DROPDOWN_VALUE_DELIMITER.$Options[$i]['text'];
		}
		// Ersten Delimiter entfernen
		$OptionText = substr($OptionText,strlen(formCode::DROPDOWN_FIELD_DELIMITER));
		// Updaten der Daten
		$sSQL = "UPDATE tbformfield SET ffi_Options = '$OptionText'
		WHERE ffi_ID = $nFieldID";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/content/select.php?id='.page::menuID().'&content='.getInt($_GET['content']).'&field='.$nFieldID);
	}
	
	// Einen Wert in den Optionen löschen
	public function deleteDropdownOption(&$Options) {
		// Neuen Optionentext ohne den gewählten Index erstellen
		$OptionText = '';
		$nFieldID = getInt($_GET['field']);
		$nDeleteIdx = getInt($_GET['delete']);
		for ($i = 0;$i < count($Options);$i++) {
			if ($i != $nDeleteIdx) {
				$OptionText .= formCode::DROPDOWN_FIELD_DELIMITER.$Options[$i]['value'];
				$OptionText .= formCode::DROPDOWN_VALUE_DELIMITER.$Options[$i]['text'];
			}
		}
		// Ersten Delimiter entfernen
		$OptionText = substr($OptionText,strlen(formCode::DROPDOWN_FIELD_DELIMITER));
		// Updaten der Daten
		$sSQL = "UPDATE tbformfield SET ffi_Options = '$OptionText'
		WHERE ffi_ID = $nFieldID";
		$this->Conn->command($sSQL);
		// Erfolg melden und Weiterleiten
		$this->setErrorSession($this->Res->html(57,page::language()));
		session_write_close();
		redirect('location: /admin/content/select.php?id='.page::menuID().'&content='.getInt($_GET['content']).'&field='.$nFieldID);
	}
	
	// Empfängeradresse des Formulars herausfinden
	public function getEmail() {
		$nCseID = getInt($_GET['content']);
		$nMenuID = page::menuID();
		$sEmail = '';
		// Distinktierend Abfragen und erste Adressen nehmen, ansonsten
		// kommt der obige leere String zurück
		$sSQL = "SELECT DISTINCT(ffi_Email) AS ffi_Email FROM tbformfield
		WHERE (LENGTH(ffi_Email) > 0 AND ffi_Email IS NOT NULL)
		AND (cse_ID = $nCseID AND mnu_ID = $nMenuID)";
		$sResult = $this->Conn->getFirstResult($sSQL);
		if ($sResult != NULL) {
			$sEmail = $sResult;
		}
		return($sEmail);
	}
	
	// Optionsfeld validieren
	private function validateValue($Value) {
		// Entfernen von Delimiterzeichen
		$Value = str_replace(formCode::DROPDOWN_FIELD_DELIMITER,'',$Value);
		$Value = str_replace(formCode::DROPDOWN_VALUE_DELIMITER,'',$Value);
		// Noch ein bisschen validieren
		$Value = trim($Value);
		$Value = stringOps::chopString($Value,50);
		stringOps::noHtml($Value);
		stringOps::htmlEntRev($Value);
		$this->Conn->escape($Value);
		// Validierten Wert zurückgeben
		return($Value);
	}
	
	// Formularfeld Typ prüfen
	private function validateFormType($sFormType) {
		switch ($sFormType) {
			case 'text':
			case 'textarea':
			case 'submit':
			case 'radio':
			case 'checkbox':
			case 'hidden':
			case 'dropdown':
			case 'captcha':
				$bValid = true;		break;
			default:
				$bValid = false;	break;
		}
		return($bValid);
	}
}