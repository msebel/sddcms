<?php
// Compatibility wenn diese Datei noch aufgerufen wird
if (stristr($_SERVER['REQUEST_URI'],'modules/fileexchange') !== false && !$FAILWHALE) {
	header('HTTP/1.1 301 Moved Permanently');
  header('Location: /controller.php?id='.$_GET['id']);
	exit;
}

/**
 * Viewmodul für das Fileexchange Modul
 * @author Michael Sebel <michael@sebel.ch>
 */
class viewFileExchange extends abstractSddView {

	// Lokal verwendete Objekte
	public $FileUpload;
	public $Directories;
	public $Files;
	public $Options;

	// Template System instanzieren
	public function __construct(template $tpl) {
		parent::__construct($tpl);
		$this->moduleInit();
	}

	// Initialisiert die Pfad Session
	private function moduleInit() {
		if (!isset($_SESSION['rootfolder_'.page::menuID()])) {
			// Schauen ob das Menu schon ein Element besitzt
			$sSQL = "SELECT ele_ID FROM tbelement WHERE owner_ID = ".page::menuID();
			$nEleID = getInt($this->Conn->getFirstResult($sSQL));

			// Wenn nicht vorhanden, erstellen
			if ($nEleID == 0) {
				$sSQL = "INSERT INTO tbelement (owner_ID,ele_Size,ele_Links,ele_Type,
				ele_Library,ele_Thumb,ele_Target,ele_File,ele_Desc,ele_Longdesc) VALUES
				(".page::menuID().",0,1,4,0,0,'','','','')";
				// Datensatz erstellen und neue ID zurückgeben
				$nEleID = $this->Conn->insert($sSQL);
				// Pfad erstellen wenn nicht existent
				$sPath = BP.'/page/'.page::ID().'/element/'.$nEleID.'/';
				if (!file_Exists($sPath)) mkdir($sPath,0755,true);
			}

			// Pfad basteln
			$_SESSION['rootfolder_'.page::menuID()] = '/page/'.page::ID().'/element/'.$nEleID.'/';
			session_write_close();
			header('location: /controller.php?id='.page::menuID());
		}
	}

	// Gibt den letzten Fehler in $SESSION['errorSession'] zurück und unsettet die Session danach.
	private function showErrorSession() {
		$sError = '';
		if (isset($_SESSION['errorSession'])) {
			$sError = $_SESSION['errorSession'];
			// Fehler nicht nochmal zeigen
			unset($_SESSION['errorSession']);
		}
		return($sError);
	}

	// Checkt ob eine errorSession vorhanden ist.
	public function hasErrorSession() {
		$bError = false;
		if (isset($_SESSION['errorSession'])) {
			$bError = true;
		}
		return($bError);
	}

	// Per Helper Klassen den Content holen und zurückgeben
	public function getOutput() {
		singleton::meta()->addJavascript('/modules/fileexchange/filelibrary.js',true);
		// Zugriff auf Bibliothek prüfen und initialisieren
		$this->initialize();

		// Meldung generieren wenn vorhanden
		$sMessage = '';
		if ($this->hasErrorSession() == true) {
			$sMessage = $this->showErrorSession();
			$sMessage.= ' - '.dateOps::getTime(dateOps::EU_CLOCK);
		}

		// Toolbar und Register
		$out = '
		<form name="fileExplorer" method="post" action="index.php?id='.page::menuID().'">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cNavSelected" width="150">'.singleton::currentmenu()->Name.'</td>
				<td class="cNav">&nbsp;</td>
			</tr>
		</table>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="cToolbar">
					<div class="cToolbarItem">
						&nbsp;
					</div>
					<div class="cToolbarItem">
						<a href="#" onclick="javascript:moveToRootFolder()">
						<img src="/images/icons/resultset_first'.$this->checkDisabledIcon('root').'.png" alt="'.$this->Res->html(680,page::language()).'" title="'.$this->Res->html(680,page::language()).'" border="0"></a>
					</div>
					<div class="cToolbarItem">
						<a href="#" onclick="javascript:moveUp()">
						<img src="/images/icons/resultset_previous'.$this->checkDisabledIcon('back').'.png" alt="'.$this->Res->html(681,page::language()).'" title="'.$this->Res->html(681,page::language()).'" border="0"></a>
					</div>
					<div class="cToolbarItem">
						<a href="#" onclick="javascript:moveNext()">
						<img src="/images/icons/resultset_next_disabled.png" alt="'.$this->Res->html(688,page::language()).'" title="'.$this->Res->html(688,page::language()).'" border="0" id="icoNext"></a>
					</div>
					<div class="cToolbarItem">
						<img src="/images/icons/toolbar-line.gif" alt="|">
					</div>
					<div class="cToolbarItem">
						<a href="#" onClick="javascript:showHelp()">
						<img src="/images/icons/help.png" alt="'.$this->Res->html(8,page::language()).'" title="'.$this->Res->html(8,page::language()).'" border="0">
						</a>
					</div>
					<div class="cToolbarError">
						&nbsp;'.$sMessage.'
					</div>
				</td>
			</tr>
		</table>
		<br>
		';

		// Darstellung der Bibliothek (Einleitung)
		$out .= '
		<div style="width:100%;padding-bottom:5px;overflow:auto;">
			Aktueller Ordner: /'.$this->Options->get('currentFolder').'
			<input type="hidden" id="currentFolder" value="'.$this->Options->get('currentFolder').'">
			<input type="hidden" id="currentRoot" value="/page/'.page::id().'/library/">
		</div>
		<div class="tabRowHead" style="width:100%;height:20px;padding-top:5px;">
			<div style="width:25px;float:left;">&nbsp;</div>
			<div style="width:260px;float:left;"><strong>'.$this->Res->html(682,page::language()).'</strong></div>
			<div style="width:120px;float:left;"><strong>'.$this->Res->html(686,page::language()).'</strong></div>
			<div style="width:100px;float:left;"><strong>'.$this->Res->html(683,page::language()).'</strong></div>
		</div>

		<div id="contentTable">';
		$nCount = 0;
		// Ordner, nach Alphabet
		foreach ($this->Directories->Data as $Directory) {
			$nCount++;
			$out .= '
			<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;"
				onmouseover="hoverFileIn('.$nCount.')" onmouseout="hoverFileOut('.$nCount.')"
				onclick="selectFile('.$nCount.')" ondblclick="changeDirectory(\''.$Directory['Name'].'\')"
				id="file_'.$nCount.'">
				<div id="file_'.$nCount.'" style="width:25px;float:left;">
					<img src="/images/icons/folder.png">
					<input type="hidden" name="filename_'.$nCount.'" value="'.$Directory['Name'].'">
					<input type="hidden" name="filetype_'.$nCount.'" value="folder">
				</div>
				<div style="width:260px;float:left;white-space:nowrap;overflow:hidden;">'.$Directory['Name'].'</div>
				<div style="width:120px;float:left;white-space:nowrap;overflow:hidden;">'.$Directory['Date'].'</div>
				<div style="width:100px;float:left;white-space:nowrap;overflow:hidden;">-</div>
			</div>';
		}
		// Files nach Alphabet
		foreach ($this->Files->Data as $File) {
			$nCount++;
			$sOnDblClick = '';
			// Doppelklick wählt Datei aus, wenn view (editor)
			if ($this->Options->get('mode') == 'view') {
				$sOnDblClick = 'onDblClick="javascript:selectFile('.$nCount.');saveSubmit();"';
			}
			$out .= '
			<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;"
				onmouseover="hoverFileIn('.$nCount.')" onmouseout="hoverFileOut('.$nCount.')"
				onclick="selectFile('.$nCount.')" '.$sOnDblClick.' id="file_'.$nCount.'">
				<div style="width:25px;float:left;">
					<img src="/images/icons/page_white_text.png">
					<input type="hidden" name="filename_'.$nCount.'" value="'.$File['Name'].'">
					<input type="hidden" name="filetype_'.$nCount.'" value="file">
				</div>
				<div style="width:260px;float:left;white-space:nowrap;overflow:hidden;">
					<a href="'.$this->Options->get('relativeFolder').$this->Options->get('currentFolder').$File['Name'].'" target="_blank">'.$File['Name'].'</a>
				</div>
				<div style="width:120px;float:left;white-space:nowrap;overflow:hidden;">'.$File['Date'].'</div>
				<div style="width:100px;float:left;white-space:nowrap;overflow:hidden;">'.$File['Size'].'</div>
			</div>';
		}
		// Abschliessen
		if ($nCount == 0) {
			$out .= '
			<div style="width:100%;height:20px;padding-top:5px;border-bottom:1px solid #ddd;">
				<div style="width:25px;float:left;">&nbsp;</div>
				<div style="width:485px;float:left;">
					'.$this->Res->html(684,page::language()).'
				</div>
			</div>';
		}
		// Abschluss und Steuerdaten
		$out .= '</div>
		<script type="text/javascript">
			var selectedFileID = "";
			var selectedFile = "";
			var selectedType = "";
			var countFiles = '.$nCount.';
			var url = "id='.page::menuID().'";
			var relative = "'.$this->Options->get('relativeFolder').'";
		</script>
		</form>';

		// Help Dialog
		$TabRow = new tabRowExtender();
		$out .= '
		<div id="helpDialog" style="display:none">
			<br>
			<br>
			<table width="100%" border="0" cellpadding="3" cellspacing="0">
				<tr class="tabRowHead">
					<td width="25">&nbsp;</td>
					<td>'.$this->Res->html(22,page::language()).'</td>
				</tr>
				<tr class="'.$TabRow->get().'">
					<td><img src="/images/icons/resultset_first.png" title="'.$this->Res->html(680,page::language()).'" alt="'.$this->Res->html(680,page::language()).'"></td>
					<td>'.$this->Res->html(720,page::language()).'.</td>
				</tr>
				<tr class="'.$TabRow->get().'">
					<td><img src="/images/icons/resultset_previous.png" title="'.$this->Res->html(681,page::language()).'" alt="'.$this->Res->html(681,page::language()).'"></td>
					<td>'.$this->Res->html(721,page::language()).'.</td>
				</tr>
				<tr class="'.$TabRow->get().'">
					<td><img src="/images/icons/resultset_next.png" title="'.$this->Res->html(688,page::language()).'" alt="'.$this->Res->html(688,page::language()).'"></td>
					<td>'.$this->Res->html(722,page::language()).'.</td>
				</tr>
			</table>
		</div>
		';
		return($out);
	}

	// Klasse initialisieren
	public function initialize() {
		// Klassenlibraries laden
		require_once(BP.'/modules/fileexchange/fileUpload/library.php');
		require_once(BP.'/modules/fileexchange/directories/library.php');
		require_once(BP.'/modules/fileexchange/files/library.php');
		require_once(BP.'/modules/fileexchange/libraryOptions/library.php');
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
			redirect('location: /controller.php?id='.page::menuID());
		}
		// Lokale Objekte erstellen
		$this->Options = new libraryOptions();
		$this->Directories = new directories($this->Options);
		$this->Files = new files($this->Options);
		$this->FileUpload = new fileUpload($this->Options);
	}

	// Einen neuen Ordner erstellen
	private function createFolder() {
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
	private function getModeButtons() {
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
	private function checkDisabledIcon($Type) {
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
	private function renameFile() {
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
	private function deleteFile() {
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
	private function controlAccess(&$Access) {
		if ($Access->getAccessType() != 1) {
			logging::debug('file library admin access denied');
			redirect('location: /error.php?type=noAccess');
		}
	}

	// Eine Datei hochladen
	private function uploadFile() {
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