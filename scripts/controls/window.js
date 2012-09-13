// Window von Hand schliessen
function addWindowCloseEvent() {
	wndClose = document.getElementById('windowClose');
	wndClose.onclick = evtCloseWindow;
	wndClose.onmouseover = function(e) { wndClose.style.cursor = 'pointer'; }
	wndClose.onmouseout = function(e) { wndClose.style.cursor = 'normal'; }
}

// Window schliessen
function evtCloseWindow() {
	wndIsOpened = false;
	wndCurrentName = '';
	Effect.Fade(wndOverlay,{ duration: 0.5, from: 0.8, to: 0.0 });
	Effect.Fade(wndContainer,{ duration: 1.0, from: 1.0, to: 0.0 });
}

// Overlay initialisieren
function initializeOverlay() {
	wndOverlay = document.getElementById('windowOverlay');
	wndOverlay.style.display = 'none';
	wndOverlay.style.position = 'fixed';
	wndOverlay.style.top = '0px';
	wndOverlay.style.left = '0px';
	wndOverlay.style.width = document.viewport.getWidth() + 'px';
	wndOverlay.style.height = document.viewport.getHeight() + 'px';
}

// Tooltip mit Events ausstatten
function addWindowEvent(sID) {
	var wndObj = document.getElementById(sID);
	wndObj.onclick = function(e) { evtShowWindow(e,sID,wndObj); }
	wndObj.onmouseover = function(e) { wndObj.style.cursor = 'pointer'; }
	wndObj.onmouseout = function(e) { wndObj.style.cursor = 'normal'; }
}

// Tooltip Objekte erstellen
function addWindowObjects() {
	// Container erstellen und ausblenden
	wndContainer = document.getElementById('tooltipContainer');
	wndContainer.style.display = 'none';
	// Objekte im Container definieren
	wndContent = document.getElementById('tooltipContent');
	wndTitle = document.getElementById('tooltipTitle');
	wndHead = document.getElementById('tooltipHead');
	// Cursor bei mousover/out
	wndHead.onmouseover = function(e) { wndHead.style.cursor = 'move'; }
	wndHead.onmouseout = function(e) { wndHead.style.cursor = 'normal'; }
	// Variablen initialisieren
	wndIsOpened = false;
	wndCurrentName = '';
}

function evtShowWindow(e,sID,wndObj) {
	// Variablen setzen
	wndIsOpened = true;
	wndCurrentName = sID;
	// Koordinaten berechnen
	var width = WindowData[wndCurrentName + "_Width"];
	var height = WindowData[wndCurrentName + "_Height"];
	var screenwidth = windowWidth();
	var screenheight = windowHeight();
	// Position mittig definieren
	wndContainer.style.width = width + 'px';
	wndContainer.style.height = height + 'px';
	wndContainer.style.top = parseInt((screenheight - height) / 2) + 'px';
	wndContainer.style.left = parseInt((screenwidth - width) / 2) + 'px';
	// Content anzeigen und zeigen
	wndContent.innerHTML = WindowData[wndCurrentName + "_HTML"];
	wndTitle.innerHTML = WindowData[wndCurrentName + "_Title"];
	// Effekte zum darstellen starten
	Effect.Appear(wndOverlay,{ duration: 0.5, from: 0.0, to: 0.8 });
	Effect.Appear(wndContainer,{ duration: 1.0, from: 0.0, to: 1.0 });
	// Wenn nötig leicht verzögert die Follow Function aufrufen
	if (WindowData[wndCurrentName + "_Follow"].length > 0) {
		setTimeout(WindowData[wndCurrentName + "_Follow"],500);
	}
}

document.observe('keydown', windowKeyBoardAction); 
function windowKeyBoardAction(event) {
    var keycode = event.keyCode;

    var escapeKey;
    if (event.DOM_VK_ESCAPE) {  // mozilla
        escapeKey = event.DOM_VK_ESCAPE;
    } else { // ie
        escapeKey = 27;
    }

    var key = String.fromCharCode(keycode).toLowerCase();
    if (keycode == escapeKey){
        evtCloseWindow();
    } 
}

function updateRenameForms() {
	document.renameFileForm.originalFile.value = selectedFile;
	document.renameFileForm.renamedFile.value = selectedFile;
}

function windowWidth() {
	var width = 0;
	if(typeof(window.innerWidth) == 'number' ) {
		width = window.innerWidth;
	} else if (document.documentElement && (document.documentElement.clientWidth)) {
		width = document.documentElement.clientWidth;
	} else if (document.body && (document.body.clientWidth)) {
		width = document.body.clientWidth;
	}
	return(width);
}

function windowHeight() {
	var height = 0;
	if (typeof(window.innerWidth ) == 'number') {
		height = window.innerHeight;
	} else if( document.documentElement && (document.documentElement.clientHeight)) {
		height = document.documentElement.clientHeight;
	} else if( document.body && (document.body.clientHeight)) {
		height = document.body.clientHeight;
	}
	return(height);
}
