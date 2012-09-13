<?php 
class files {

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
		$aFiles = array();
		// Folder durchgehen
		if ($resDir = opendir($sFolder)) {
	        while (($sFile = readdir($resDir)) !== false) {
	        	if (filetype($sFolder . $sFile) == 'file') {
		        	array_push($aFiles,$sFile);
	        	}
	        }
	    }
	    // Ordner schliessen
	    closedir($resDir);
	    // Namen sortieren und in die Daten pushen
	    natcasesort($aFiles);
	    foreach ($aFiles as $FileName) {
	    	$File['Name'] = $FileName;
	    	$File['Date'] = date("d.m.Y, H:i", filemtime($sFolder.$FileName));
	    	// Datengrösse
	    	$File['Size'] = (string) filesize($sFolder.$FileName) / 1024;
			$File['Size'] = numericOps::getDecimal($File['Size'],2);
			$File['Size'] .= ' KB';
	    	array_push($this->Data,$File);
	    }
	}
}