// überprüft den Pfad der hochzuladenden Datei, 
// und zeigt entsprechend Optionen an
function checkFile() {
	var sFileName = document.fileUpload.uploadFile.value;
	// Nur was tun, wenn input vorhanden
	if (sFileName.length > 0) {
		// Kovertieren zu kleinbuchstaben
		sFileName = sFileName.toLowerCase();
		if (sFileName.endsWith('jpg') || sFileName.endsWith('gif') || sFileName.endsWith('jpg')) {
			document.getElementById('fileOptions_Choose').style.display = 'none';
			document.getElementById('fileOptions_None').style.display = 'none';
			document.getElementById('fileOptions_Zip').style.display = 'none';
			document.getElementById('fileOptions_Picture').style.display = 'block';
		} else if (sFileName.endsWith('zip')) {
			document.getElementById('fileOptions_Choose').style.display = 'none';
			document.getElementById('fileOptions_None').style.display = 'none';
			document.getElementById('fileOptions_Zip').style.display = 'block';
			document.getElementById('fileOptions_Picture').style.display = 'none';
		} else {
			document.getElementById('fileOptions_Choose').style.display = 'none';
			document.getElementById('fileOptions_None').style.display = 'block';
			document.getElementById('fileOptions_Zip').style.display = 'none';
			document.getElementById('fileOptions_Picture').style.display = 'none';
		}
	}
}

// Verhindert Inputs in das fileUpload Feld
function noInputs() {
	document.fileUpload.fileUpload.value = '';
}

// Zeigt die Bildoptionen an
function toggleOptions() {
	Effect.toggle('resizeOptions','blind');
}

// Mediamanager Tabellenzelle mouse In
function mmTbCellIn (cell) {
	// Nicht �ndern wenn ausgew�hlt
	if (!isSelectedFile(cell)) {
		cell.style.backgroundColor = '#FFCF3F';
		cell.style.cursor = 'pointer';
	}
}
// Mediamanager Tabellenzelle mouse Out
function mmTbCellOut (cell) {
	var fallbackColor = document.getElementById('fallbackColor').value;
	// Nur zur�cksetzen, wenn nicht ausgew�hlt
	if (!isSelectedFile(cell)) {
		cell.style.backgroundColor = fallbackColor;
		cell.style.cursor = 'default';
	}
}

// Mediamanager Klick in Tabellenzelle
function mmTbCellClick (cell) {
	// Aktuell angeklickte Zelle normalisieren
	var actualElement = document.fileSelector.selectedFile.value;
	var fallbackColor = document.getElementById('fallbackColor').value;
	if (actualElement.length > 0) {
		var previous = document.getElementById(actualElement);
		previous.style.backgroundColor = fallbackColor;
	}
	// Diese Zelle highlighten
	cell.style.backgroundColor = '#879DFF';
	// Zwischenspeichern der Auswahl
	document.fileSelector.selectedFile.value = cell.id;
	// Wenn aktuelles Element vorhanden, checken ob das gleiche
	// File zweimal geklickt wurde
	if (actualElement.length > 0) {
		var previous = document.getElementById(actualElement);
		if (previous.id == cell.id) {
			cell.style.backgroundColor = fallbackColor;
			// Zwischenspeichern der Auswahl
			document.fileSelector.selectedFile.value = '';
		}
	}
}

// Herausfinden ob die Zelle markiert ist
function isSelectedFile(cell) {
	var result = false;
	var selected = document.fileSelector.selectedFile.value;
	var mouseover = cell.id;
	if (mouseover == selected) {
		result = true;
	}
	return(result);
}

// Reloaded die Seite mit Anweisung Callback
// um die Daten per js zur�ckzugeben
function jsSave() {
	var file = document.fileSelector.selectedFile.value;
	// Nur wenn eine Auswahl vorhanden ist
	if (file.length > 0) {
		document.jsSaveForm.selectedFile.value = file;
		document.jsSaveForm.submit();
	}
}

// Submitted das fileSelector Fomular
function dbSave() {
	document.fileSelector.submit();
}