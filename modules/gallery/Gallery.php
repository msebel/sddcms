<?php
abstract class Gallery {
	
	public $Conn;			// Datenbankverbindung
	public $thumbWidth;		// Breite der Thumbnails
	public $thumbHeight;		// Höhe der Thumbnails
	public $Files;			// Galleryfile Fileobjekte
	
	public function buildFilelist() {
		$nElement = galleryConst::getMenuElement($this->Conn);
		$sFolder = BP.'/page/'.page::ID().'/element/'.$nElement.'/';
		$Filenames = array();
		if (file_exists($sFolder)) {
			// Folder durchgehen
			if ($resDir = opendir($sFolder)) {
		        while (($sFile = readdir($resDir)) !== false) {
		        	array_push($Filenames,$sFile);
		        }
		        // Ordnen der Filenamen
		        sort($Filenames,SORT_STRING);
		        // Galleryfiles speichern
		        foreach ($Filenames as $sFile) {
		        	if (filetype($sFolder.$sFile) == 'file') {
		        		$GalleryFile = new GalleryFile($sFile,$sFolder);
		        		if ($GalleryFile->isValid) {
		        			array_push($this->Files,$GalleryFile);
		        		}
		        	}
		        }
		    }
		    // Ordner schliessen und rückgabe
		    closedir($resDir);
		    // Matching von Bildtexten
			$sSQL = "SELECT gal_Text,gal_File FROM tbgallery 
			WHERE mnu_ID = ".page::menuID();
			$nRes = $this->Conn->execute($sSQL);
			while ($row = $this->Conn->next($nRes)) {
				// Direktes Matching
				for ($i = 0;$i < count($this->Files);$i++) {
					if ($this->Files[$i]->Filename == $row['gal_File']) {
						$this->Files[$i]->Description = $row['gal_Text'];
						break;
					}
				}
			}
		}
	}
	
	// Methode die den HTML Output der Galerie in
	// die übergebene Buffervariable $out spitzt
	abstract public function appendHtml(&$out);
	abstract public function outputPlain();
}