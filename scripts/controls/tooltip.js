// Tooltip von Hand schliessen
function addCloseEvent() {
	ttpClose = document.getElementById('tooltipClose');
	ttpClose.onclick = evtCloseTooltip;
	ttpClose.onmouseover = function(e) {
		ttpClose.style.cursor = 'pointer';
	}
	ttpClose.onmouseout = function(e) {
		ttpClose.style.cursor = 'normal';
	}
}

// Tooltip schliessen
function evtCloseTooltip() {
	ttpPersistent = false;
	ttpIsOpened = false;
	ttpCurrentName = '';
	ttpContainer.hide();
}

// Tooltip mit Events ausstatten
function addTooltipEvent(sID) {
	var ttpObj = document.getElementById(sID);
	ttpObj.onmouseover = function(e) {
		ttpTimeoutFunction = function () {evtShowTooltip(e,sID,ttpObj);}
		setTimeout('ttpTimeoutFunction()',TooltipData["Option_Timeout"]);
		
	};
	ttpObj.onmouseout = function(e) {
		ttpTimeoutFunction = function() {return(false);}
		if (!ttpPersistent) evtCloseTooltip();
		ttpObj.style.cursor = 'normal';
	};
	ttpObj.onmousemove = function(e) {
		if (!ttpPersistent) evtAlignTooltip(e,sID);
	};
	ttpObj.onclick = function(e) {
		ttpTimeoutFunction = function() {return(false);}
		evtShowTooltip(e,sID,ttpObj);
		ttpPersistent = !ttpPersistent;
	}
}

// Tooltip Objekte erstellen
function addTooltipObjects() {
	// Container erstellen und ausblenden
	ttpContainer = document.getElementById('tooltipContainer');
	ttpContainer.style.display = 'none';
	// Objekte im Container definieren
	ttpTitle = document.getElementById('tooltipTitle');
	ttpContent = document.getElementById('tooltipContent');
	// Container verschiebbar machen
	new Draggable('tooltipContainer');
	// Variablen initialisieren
	ttpPersistend = false;
	ttpIsOpened = false;
	ttpCurrentName = '';
}

function evtShowTooltip(e,sID,ttpObj) {
	// Variablen setzen
	ttpPersistent = false;
	ttpIsOpened = true;
	ttpCurrentName = sID;
	// Mauszeiger erstellen
	ttpObj.style.cursor = 'pointer';
	// X-Koordinate berechnen
	nMarginX = parseInt(TooltipData[ttpCurrentName + "_Width"] / 2);
	// Koordinaten holen
	if (document.all) {
		// IE schafft es leider erst beim alignen -.-
	} else {
		ttpContainer.style.top = (e.pageY+20) + 'px';
		ttpContainer.style.left = (e.pageX-nMarginX) + 'px';
	}
	if (TooltipData[ttpCurrentName + "_Width"] > 0) {
		ttpContainer.style.width = TooltipData[ttpCurrentName + "_Width"] + 'px';
	}
	if (TooltipData[ttpCurrentName + "_Height"] > 0) {
		ttpContainer.style.height = TooltipData[ttpCurrentName + "_Height"] + 'px';
	}
	ttpTitle.innerHTML = TooltipData[ttpCurrentName + "_Title"];
	ttpContent.innerHTML = TooltipData[ttpCurrentName + "_Text"];
	ttpContainer.appear();
}

function evtAlignTooltip(e,sID) {
	// Koordinaten redefinieren
	ttpCurrentName = sID;
	nMarginX = parseInt(TooltipData[ttpCurrentName + "_Width"] / 2);
	if (document.all) {
		e = window.event;
		var offset = document.viewport.getScrollOffsets();
		ttpContainer.style.top = (e.clientY + offset.top +20) + 'px';
		ttpContainer.style.left = (e.clientX + offset.left - nMarginX) + 'px';
	} else {
		ttpContainer.style.top = (e.pageY+20) + 'px';
		ttpContainer.style.left = (e.pageX-nMarginX) + 'px';
	}
}

Event.observe(window,'load',function() {
	var ttContainer = $('tooltipContainer');
	$$('body')[0].insert({
		bottom: ttContainer
	});
})