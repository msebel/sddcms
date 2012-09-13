// Für Vergleichsauswahl die veränderte Checkbox anklicken
function addCompare(box) {
	// Vergleichsfelder holen
	var comparison = $('comparison').value;
	var boxes = document.getElementsByName('compares');
	// Auswahl oder Deselektierung?
	if (box.checked) {
		// Letztes Semikolon entfernen
		if (comparison.length > 0) comparison += ';';
		comparison += box.value;
		addCompareAdd(box,comparison,boxes);
	} else {
		addCompareRemove(box,comparison,boxes);
	}
}

// Fügt die gewählte Box in die Liste hinzu
function addCompareAdd(box,comparison,boxes) {
	var comparisons = comparison.split(';');
	var newcomp = '';
	var i;
	// Je nach Menge was tun
	switch (comparisons.length) {
		case 1:
		case 2:
			$('comparison').value = comparison; break;
		case 3:
			// Alles deselektieren
			for (i = 0;i < boxes.length;i++) {
				boxes[i].checked = false;
			}
			// 2 und 3 selektieren / speichern
			for (i = 1;i < comparisons.length;i++) {
				comparison = getCompareBox(comparisons[i],boxes);
				comparison.checked = true;
				newcomp += comparison.value + ';';
			}
			newcomp = newcomp.substring(0,newcomp.length-1);
			$('comparison').value = newcomp;
			break;
	}
}

// Entfernt die angeklickte Box aus der Liste
function addCompareRemove(box,comparison,boxes) {
	var comparisons = comparison.split(';');
	var newcomp = '';
	var i = 0;
	// Liste ohne die aktuelle Value machen
	for (i = 0;i < boxes.length;i++) {
		if (boxes[i].value != box.value) {
			if (boxes[i].checked) newcomp += boxes[i].value + ';';
		}
	}
	// Alles deselektieren
	for (i = 0;i < boxes.length;i++) {
		boxes[i].checked = false;
	}
	// Value speichern
	newcomp = newcomp.substring(0,newcomp.length-1);
	$('comparison').value = newcomp;
	// Aus der Liste wieder selektieren
	var newlist = newcomp.split(';');
	if (newcomp.length > 0) {
		for (i = 0;i < newlist.length;i++) {
			comparison = getCompareBox(newlist[i],boxes);
			comparison.checked = true;
		}
	}
}

// Sucht in der Liste eine Box
function getCompareBox(value,boxes) {
	var comparison = null;
	for (var i = 0;i < boxes.length;i++) {
		if (boxes[i].value == value) {
			comparison = boxes[i];
		}
	}
	return(comparison);
}

// Events registrieren für Vergleich
function registerDiff(id) {
	var oDiff = $('iDiffImg');
	// Pointer einfügen
	oDiff.onmouseover = function () {
		SetPointer('iDiffImg','pointer');
	};
	oDiff.onmouseout = function () {
		SetPointer('iDiffImg','default');
	};
	// Click Event, starten des Diffs (URL aufruf)
	oDiff.onclick = function () {
		callDiff(id);
	};
}

// Einen Vergleich aufrufen
function callDiff(id) {
	var ids = $('comparison').value.split(';');
	// Nur, wenn zwei IDs vorhanden sind
	if (ids.length == 2) {
		// Link erstellen
		var link = '/modules/wiki/writer/diff.php?id=' + id;
		link += '&first=' + ids[0];
		link += '&second=' + ids[1];
		// Diesen Link aufrufen
		document.location.href = link;
	} else {
		// Meldung ausgeben, zwei Versionen auswählen
		myRequest = new Ajax.Request(
			'/library/class/ajaxRequest/call.php?ResJavascript', {
			method: 'post',
			asynchronous: false,
			parameters: 'resource=975'
		});
		alert(myRequest.transport.responseText);
	}
}