function addTrEvent(sTRName,sSelector) {
	myObj = document.getElementById(sTRName);
	// Click Event anwenden
	myObj.onclick = function(e) {
		evtTrClick(e,sTRName,sSelector);
	}
	myObj.onmouseover = function(e) {
		document.getElementById(sTRName).style.cursor = 'pointer';
	}
	myObj.onmouseout = function(e) {
		document.getElementById(sTRName).style.cursor = 'normal';
	}
}

function evtTrClick(e,sTRName,sSelector) {
	myObj = document.getElementById(sTRName);
	// Farbe switchen
	if (selectorData[myObj.id + '_selected']) {
		selectorData[myObj.id + '_selected'] = false;
		myObj.style.backgroundColor = 'transparent';
	} else {
		selectorData[myObj.id + '_selected'] = true;
		myObj.style.backgroundColor = selectorConfig[sSelector + '_Color'];
	}
	// Request absetzen
	var URL = selectorConfig[sSelector + '_URL'];
	var Param = selectorConfig[sSelector + '_Param'];
	var Request = URL;
	// Param anhängen
	if (Param.length > 0) {
		Request += '?' + Param;
	} else {
		Request += '?id=0';
	}
	// Angeklickte Value und Modus anhängen
	var value = selectorData[myObj.id + '_value'];
	var mode = getMode(myObj.id);
	// Request beenden
	Request += '&bindID=' + value + '&mode=' + mode;
	new Ajax.Request(Request, { method: 'get' });
}

function getMode(id) {
	if (selectorData[id + '_selected']) {
		return('select');
	} else {
		return('unselect');
	}
}