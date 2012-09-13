<?php 
class moduleFilelibrary extends commonModule {
	
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
	// Publike Objekte
	public $FileUpload;
	public $Directories;
	public $Files;
	public $Options;
	
	// Objekte laden, überschrieben von Mutterklasse
	public function loadObjects() {
		$this->Conn	=& func_get_arg(0);	// $Conn
		$this->Res	=& func_get_arg(1);	// $Res
	}
	
	// Klasse initialisieren
	public function initialize() {
		// Klassenlibraries laden
		require_once(BP.'/admin/library/fileUpload/library.php');
		require_once(BP.'/admin/library/directories/library.php');
		require_once(BP.'/admin/library/files/library.php');
		require_once(BP.'/admin/library/libraryOptions/library.php');
		// Bibliothek Session initialisieren
		if (!isset($_SESSION['filelibrary_'.page::menuID()])) {
			// Bibliotheken Ordner erstellen, wenn nicht vorhanden
			$sRootFolder = '/page/'.page::id().'/library/';
			// Wenn anderer Folder in Session, diesen nehmen (Von Caller)
			if (isset($_SESSION['rootfolder_'.page::menuID()])) {
				$sRootFolder = $_SESSION['rootfolder_'.page::menuID()];	
			}
			if (!file_exists(BP.$sRootFolder)) {
				mkdir(BP.$sRootFolder,0755,true);
			}
			// Optionsobjekt initialisieren (Sessioned)
			$this->Options = new libraryOptions();
			$this->Options->set('rootFolder',BP.$sRootFolder);	// Ausgangsposition
			$this->Options->set('relativeFolder',$sRootFolder);	// Relativer Pfad zum Einfügen
			$this->Options->set('currentFolder','');			// Aktueller Ordner
			$this->Options->set('copyFile','');					// Zu kopierendes File
			$this->Options->set('copyType','');					// Kopierart (copy,cut)
			// Modus (Admin oder View (aus Editor))
			switch ($_GET['mode']) {
				case 'view':
					$this->Options->set('mode','view'); break;
				case 'admin':
				default:
					$this->Options->set('mode','admin');break;
			}
			// Session schliessen und auf Startseite Library weiterleiten
			session_write_close();
			redirect('location: /admin/library/index.php?id='.page::menuID());
		}
		// Lokale Objekte erstellen
		$this->Options = new libraryOptions();
		$this->Directories = new directories($this->Options);
		$this->Files = new files($this->Options);
		$this->FileUpload = new fileUpload($this->Options);
	}
	
	// Einen neuen Ordner erstellen
	public function createFolder() {
		$sDirectory = $_POST['newFolderName'];
		stringOps::alphaNumFiles($sDirectory);
		$sCurrent = $this->getCurrentFolder();
		// Erstellen wenn der Ordner eine länge aufweist und nicht existiert
		if (strlen($sDirectory) > 0 && !file_exists($sCurrent.$sDirectory)) {
			mkdir($sCurrent.$sDirectory,0755,false);
			$this->setErrorSession($this->Res->html(57,page::language()));
			session_write_close();
		}
		// Erfolg melden und weiterleiten
		redirect('location: /admin/library/index.php?id='.page::menuID());
	}
	
	// Gibt einen "Save" Button aus, um ein File auszuwählen (nur View)
	public function getModeButtons() {
		$out = '';
		if ($this->Options->get('mode') == 'view') {
			$out .= '
			<div class="cToolbarItem">
				<a href="#" onClick="javascript:saveSubmit();">
				<img src="/images/icons/disk.png" alt="'.$this->Res->html(36,page::language()).'" title="'.$this->Res->html(36,page::language()).'" border="0"></a>
			</div>
			<div class="cToolbarItem">
				<a href="#" onclick="javascript:window.close();">
				<img src="/images/icons/door_out.png" alt="'.$this->Res->html(37,page::language()).'" title="'.$this->Res->html(37,page::language()).'" border="0"></a>
			</div>
			';
		}
		return($out);
	}
	
	// Checkt ab, ob Buttons als "disabled" gezeigt werden müssen
	public function checkDisabledIcon($Type) {
		$sDisabled = '';
		// Typen durchgehen
		switch ($Type) {
			case 'paste':
				// Wenn kein zu kopierendes File vorhanden ist
				if (strlen($this->Options->get('copyFile')) == 0) {
					$sDisabled = '_disabled';
				}
				break;
			case 'root':
			case 'back':
				// Wenn wir und schon im root folder befinden
				if (strlen($this->Options->get('currentFolder')) == 0) {
					$sDisabled = '_disabled';
				}
				break;
		}
		return($sDisabled);
	}
	
	// Datei umbenennen
	public function renameFile() {
		// Komplette Pfade erstellen
		$sDir = $this->getCurrentFolder();
		$sFile = $_POST['originalFile'];
		$sNew = $_POST['renamedFile'];
		// Type der Datei (file, dir) herausfinden und switchen
		if (file_exists($sDir.$sFile) && strlen($sFile) > 0) {
			switch (filetype($sDir.$sFile)) {
				case 'file':
					// Beim letzten Punkt abschneiden und validieren
					$nLastDot = strripos($sNew,'.');
					$sExtension = substr($sNew,$nLastDot);
					// Nur alphanumerische Zeichen im Filenamen
					$sFilename = substr($sNew,$nLastDot);
					stringOps::alphaNumFiles($sFilename);
					// Länge des Filenamen und Extension prüfen
					if (strlen($sFilename) == 0 || !$this->extensionAllowed($sExtension)) {
						$sNew = '';
					} else {
						stringOps::alphaNumFiles($sNew);
					}
					break;
				case 'dir':
					// Einfach validieren
					stringOps::alphaNumFiles($sNew);
					break;
			}
			// Resultat umbenennen, wenn vorhanden
			if (strlen($sNew) > 0) {
				rename($sDir.$sFile,$sDir.$sNew);
				$sMessage = $this->Res->html(57,page::language());
			} else {
				// Fehler: Ungültiger Dateiname
				$sMessage = $this->Res->html(694,page::language());
			}
			// Daten gespeichert und so...
			$this->setErrorSession($sMessage);
			session_write_close();
		}
		redirect('location: /admin/library/index.php?id='.page::menuID());
	}
	
	// Datei löschen
	public function deleteFile() {
		// Komplette Pfade erstellen
		$sDir = $this->getCurrentFolder();
		$sFile = $_POST['deletedFile'];
		// Type der Datei (file, dir) herausfinden und switchen
		if (file_exists($sDir.$sFile) && strlen($sFile) > 0) {
			switch (filetype($sDir.$sFile)) {
				case 'file':
					// Nur alphanumerische Zeichen im Filenamen
					stringOps::alphaNumFiles($sFile);
					// Löschen, wenn es existiert
					if (file_exists($sDir.$sFile)) {
						unlink($sDir.$sFile);
					}
					break;
				case 'dir':
					// Einfach validieren
					stringOps::alphaNumFiles($sFile);
					// Rekursiv löschen wenn der Ordner existiert
					if (file_exists($sDir.$sFile)) {
						$this->removeDirectory($sDir.$sFile);
					}
					break;
			}
			logging::debug('deleted file/folder');
			$this->setErrorSession($this->Res->html(146,page::language()));
			session_write_close();
		} else {
			logging::error('error deleting file/folder');
			$this->setErrorSession($this->Res->html(55,page::language()));
			session_write_close();
		}
		redirect('location: /admin/library/index.php?id='.page::menuID());
	}
	
	// Zugriff prüfen
	public function controlAccess(&$Access) {
		if ($Access->getAccessType() != 1) {
			logging::debug('file library admin access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}
	
	// Eine Datei hochladen
	public function uploadFile() {
		$sDir = $this->getCurrentFolder();
		$sMessage = $this->FileUpload->start($sDir,$this->Res);
		// Zip wenn erwünscht entpacken
		if ($this->FileUpload->isZip() && getInt($_POST['unpackZip']) == 1) {
			$this->unpack($this->FileUpload->getFilename());
		}
		// Wenn keine Message, Erfolg
		if (strlen($sMessage) == 0) {
			$sMessage = $this->Res->html(57,page::language());
		}
		// Meldung speichern und neu laden
		logging::debug('uploaded file');
		$this->setErrorSession($sMessage);
		session_write_close();
		redirect('location: /admin/library/index.php?id='.page::menuID());
	}
	
	// Ordner mit allen Inhalten löschen
	private function removeDirectory($dir) {
		if (!$dh = opendir($dir)) return;
		while (false !== ($obj = readdir ($dh))) {
			if ($obj == '.' || $obj == '..') continue;
			if (!@unlink($dir.'/'.$obj)) $this->removeDirectory ($dir.'/'.$obj);
		}
		closedir($dh);
		rmdir($dir);
	}
	
	// Aktuellen Ordner holen
	private function getCurrentFolder() {
		$sDirectory = '';
		$sDirectory .= $this->Options->get('rootFolder');
		$sDirectory .= $this->Options->get('currentFolder');
		return($sDirectory);
	}
	
	// Prüfen ob die übergebene Extension erlaubt ist
	// Extension mus inkl "." übergeben werden
	private function extensionAllowed($sExt) {
		$Allowed = false;
		// Erlaubte Endungen durchgehen
		foreach($this->Options->AllowedExtensions as $Extension) {
			if ($Extension == $sExt) {
				$Allowed = true; break;
			}
		}
		return($Allowed);
	}
	
	// Einen Zip Ordner komplett entpacken. 
	// Ordnerstruktur wird dabei zerstört.
	private function unpack($sFile) {
		$zipRes = zip_open($sFile);
		if (!is_int($zipRes)) {
			while ($zipEntry = zip_read($zipRes)) {
				$sName = zip_entry_name($zipEntry);
				// Prüfen dass es kein Ordner ist
				if ($sName[strlen($sName)-1] != '/') {
					$sName = strtolower(basename($sName));
					$sExt = strtolower(substr($sName, strripos($sName, '.')));
					$sName = substr($sName,0,strripos($sName, '.'));
					$this->sanitizeFilename($sName);
					$sName = $sName.$sExt;
					if ($this->extensionAllowed($this->getExtension($sName))) {
						// Namen zum zurückgeben speichern
						$DataAppender = '';
						while ($Data = zip_entry_read($zipEntry)) {
							$DataAppender .= $Data;
						}
						file_put_contents($this->getCurrentFolder().$sName,$DataAppender);
						unset($DataAppender);
					}
				}
			}
			// Archiv schliessen und löschen
			zip_close($zipRes);
			unlink($sFile);
		}
	}
	
	// Schwer verträgliche Zeichen entfernen
	private function sanitizeFilename(&$sName) {
		stringOps::alphaNumFiles($sName);
		$sName = strtolower($sName);
	}
	
	// Generiert die Extension aus einem File
	private function getExtension($sFile) {
		return(substr($sFile,strripos($sFile,'.')));
	}
}