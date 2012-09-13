<?php 
class directories {
	
	public $Options;
	public $Data = array();
	
	public function __construct (&$Options) {
		$this->Options = $Options;
		$this->loadData();
	}
	
	public function loadData() {
		$sFolder = $this->Options->get('rootFolder');
		$sFolder.= $this->Options->get('currentFolder');
		// Diesen Ordner nach weitern Ordnern durchsuchen
		$aFolders = array();
		// Folder durchgehen
		if ($resDir = opendir($sFolder)) {
	        while (($sFile = readdir($resDir)) !== false) {
	        	if (filetype($sFolder . $sFile) == 'dir') {
	        		if ($sFile != '.' && $sFile != '..') {
		        		array_push($aFolders,$sFile);
		        	}
	        	}
	        }
	    }
	    // Ordner schliessen
	    closedir($resDir);
	    // Namen sortieren und in die Daten pushen
	    natcasesort($aFolders);
	    foreach ($aFolders as $FolderName) {
	    	$Directory['Name'] = $FolderName;
	    	$Directory['Date'] = date("d.m.Y, H:i", filemtime($sFolder.$FolderName));
	    	array_push($this->Data,$Directory);
	    }
	}
}