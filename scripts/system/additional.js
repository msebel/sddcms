function getLastChar (sString, sChar) {
	var nLastDot = 0;
	var nThisDot = 0;
	var lastDot = false;
	while (lastDot === false) {
		// Prüfen ob überhaupt punkte vorhanden sind
		if (sString.length > 0) {
			if (sString.indexOf(sChar) >= 0) {
				nThisDot = sString.indexOf(sChar,nLastDot+1);
				if (nThisDot == -1) {
					// Letzter Punkt war der letzte, loop verlassen
					lastDot = true;
				} else {
					nLastDot = nThisDot;
				}
			} else {
				lastDot = true;
			}
		} else {
			lastDot = true;
		}
	}
	return(nLastDot);
}

function getFileNameOnly(sFilename) {
	var nLastDot = getLastChar(sFilename,"/");
	var sFileNewName = sFilename.substr(nLastDot+1,sFilename.length-1);
	return(sFileNewName);
}

function getExtension(sFilename) {
	var nDot = 0;
	var sExt = '';
	nDot = getLastChar(sFilename,'.');
	sExt = sFilename.substring(nDot+1);
	return(sExt.toLowerCase());
}