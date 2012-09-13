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
	// Check auf änderungen an Icons
	checkIcons();
}

function evtRegisterIcons() {
	icoCopy = document.getElementById('icoCopy');
	icoCut = document.getElementById('icoCut');
	icoNext = document.getElementById('icoNext');
	icoPaste = document.getElementById('icoPaste');
}

function checkIcons() {
	// Wenn Ordner, kopieren nicht möglich
	if (selectedType == 'folder') {
		icoCopy.src = '/images/icons/page_white_copy_disabled.png';
		icoCut.src = '/images/icons/cut_disabled.png';
		icoNext.src = '/images/icons/resultset_next.png';
	}
	// Wenn File, kopieren möglich
	if (selectedType == 'file') {
		icoCopy.src = '/images/icons/page_white_copy.png';
		icoCut.src = '/images/icons/cut.png';
		icoNext.src = '/images/icons/resultset_next_disabled.png';
	}
}

// Directory nach vorne wechseln
function changeDirectory(directory) {
	// Request absetzen
	new Ajax.Request('ajax/changeDirectory.php?'+url+'&directory='+directory, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true') {
				location.href = 'index.php?'+url;
			}
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
	new Ajax.Request('ajax/backDirectory.php?'+url, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true') {
				location.href = 'index.php?'+url;
			}
		}
	});
}

//In den root Ordner zurück
function moveToRootFolder() {
	// Request absetzen
	new Ajax.Request('ajax/rootDirectory.php?'+url, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true') {
				location.href = 'index.php?'+url;
			}
		}
	});
}

// Formular zum umbenennen aktualisieren
function updateRenameForms() {
	document.renameFileForm.renamedFile = selectedFile;
	document.renameFileForm.originalFile = selectedFile;
}

// Optionen für File upload setzen
function changeOptions() {
	var sFileName = document.uploadFileForm.uploadedFile.value;
	// Nur was tun, wenn input vorhanden
	if (sFileName.length > 0) {
		// Kovertieren zu kleinbuchstaben
		sFileName = sFileName.toLowerCase();
		if (sFileName.endsWith('zip')) {
			Effect.Appear('fileOptionZip');
		} else {
			Effect.Fade('fileOptionZip');
		}
	}
}

// Progressbar simulieren für Fileupload
function startProgressBar() {
	// Progressbar initialisieren
	$('fileOptionZip').style.display = 'none';
	myProgressBar = $('progressBar');
	myProgressBarLeft = $('progressBarLeft');
	myProgressMessage = $('progressMessage');
	// Events zum aufbauen der Bar registrieren
	myProgressMessage.style.display = 'block';
	myProgressBar.style.display = 'block';
	myProgressBar.style.width = '5px';
	myProgressBar.style.borderRight = '0px none';
	myProgressBarLeft.style.display = 'block';
	myProgressBarLeft.style.width = '195px';
	myProgressBarLeft.style.borderLeft = '0px none';
	setInterval('expandProgressBar()',1000);
	// Formular submitten
	document.uploadFileForm.submit()
}

// Progressbar erweitern
function expandProgressBar() {
	myProgressBar = $('progressBar');
	myProgressBarLeft = $('progressBarLeft');
	width = myProgressBar.style.width.replace('px','');
	width = parseInt(width) + 2;
	if (width < 200) {
		myProgressBar.style.width = width + 'px';
		myProgressBarLeft.style.width = (200 - width) + 'px';
	}
}

// Löschformular updaten, je nach Dateityp (file/dir)
function updateDeleteForms() {
	// Progressbar in der ID anzeigen
	answerObj = $('deleteInformation');
	// Funktion beim erfolreichen Ende des Requests
	successFunction = function(transport) {	
		answerObj.innerHTML = transport.responseText; 
		$('deleteButtons').style.display = 'block';
		$('deletedFile').value = selectedFile;
	}
	// Fehlerfunktion
	failureFunction = function(transport) {	answerObj.innerHTML = 'error occured'; }
	// Request absetzen
	new Ajax.Request('ajax/deleteInformation.php?'+url+'&file='+selectedFile, {
		method: 'get',
		onSuccess: successFunction,
		onFailure: failureFunction
	});
}

// Selektiertes File "kopieren" (Serverseitig)
function copySelectedFile() {
	// Nur etwas tun, wenn ein File selektiert ist
	if (selectedType == 'file') {
		// Request absetzen
		new Ajax.Request('ajax/copyFile.php?'+url+'&file='+selectedFile, {
			method: 'get',
			onSuccess: function (transport) {
				// Reload, wenn Directory gewechselt wurde
				if (transport.responseText == 'true') {
					Effect.Pulsate(icoCopy.id, { pulses: 3, duration: 1.0 });
					icoPaste.src = '/images/icons/paste_plain.png';
				}
			}
		});
	}
}

// Selektiertes File "ausschneiden" (Serverseitig)
function cutSelectedFile() {
	// Nur etwas tun, wenn ein File selektiert ist
	if (selectedType == 'file') {
		// Request absetzen
		new Ajax.Request('ajax/cutFile.php?'+url+'&file='+selectedFile, {
			method: 'get',
			onSuccess: function (transport) {
				// Reload, wenn Directory gewechselt wurde
				if (transport.responseText == 'true') {
					Effect.Pulsate(icoCut.id, { pulses: 3, duration: 1.0 });
					icoPaste.src = '/images/icons/paste_plain.png';
				}
			}
		});
	}
}

// Datei in der Zwischenablage am aktuellen Ort einfügen
function pasteFile() {
	new Ajax.Request('ajax/pasteFile.php?'+url, {
		method: 'get',
		onSuccess: function (transport) {
			// Reload, wenn Directory gewechselt wurde
			if (transport.responseText == 'true') {
				location.href = 'index.php?'+url;
			}
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