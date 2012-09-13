var colorSelected = '#879DFF';
var colorHover = '#FFCF3F';
var colorFallback = 'transparent';
var answerObj = null;

function hoverFileIn(nIdx) {
	var ID = 'file_' + nIdx;
	var row = document.getElementById(ID);
	if (ID != selectedFileID) {
		row.style.backgroundColor = colorHover;
	}
}

function hoverFileOut(nIdx) {
	var ID = 'file_' + nIdx;
	var row = document.getElementById(ID);
	if (ID != selectedFileID) {
		row.style.backgroundColor = colorFallback;
	}
}

function selectFile(nIdx) {
	// Zuerst alles zurücksetzen
	if (selectedFileID.length > 0) {
		row = document.getElementById(selectedFileID);
		row.style.backgroundColor = colorFallback;
	}
	// Dann aktuelles File auswählen
	var ID = 'file_' + nIdx;
	row = document.getElementById(ID);
	row.style.backgroundColor = colorSelected;
	// Daten zwischenspeichern
	selectedFileID = ID;
	selectedFile = document.fileExplorer.elements['filename_' + nIdx].value;
	selectedType = document.fileExplorer.elements['filetype_' + nIdx].value;
}

// Directory nach vorne wechseln
function changeDirectory(directory) {
	// Request absetzen
	new Ajax.Request('/modules/fileexchange/ajax/changeDirectory.php?'+url+'&directory='+directory, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true') 
				location.reload();
		}
	});
}

// Gewähltes Directory auswählen
function moveNext() {
	if (selectedType == 'folder') {
		changeDirectory(selectedFile);
	}
}

// Eines zurück gehen (übergeordnet)
function moveUp() {
	// Request absetzen
	new Ajax.Request('/modules/fileexchange/ajax/backDirectory.php?'+url, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true')
				location.reload();
		}
	});
}

//In den root Ordner zurück
function moveToRootFolder() {
	// Request absetzen
	new Ajax.Request('/modules/fileexchange/ajax/rootDirectory.php?'+url, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true')
				location.reload();
		}
	});
}

// Datei an den Editor geben
function saveSubmit() {
	var sPath = '';
	sPath += document.getElementById('currentRoot').value;
	sPath += document.getElementById('currentFolder').value;
	if (selectedType == 'file') {
		window.opener.filelibraryAction(sPath + selectedFile);
		window.close();
	}		
}