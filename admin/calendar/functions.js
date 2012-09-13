function chooseType(nID) {
	// Formularfeld anpassen
	$('viewType').value = nID;
	// Alles Bilde als 'deselektiert' anzeigen
	for (i = 1;i <= 6;i++) {
		$('vs'+i).src = 'images/calview'+i+'.gif';
	}
	// Anderes Bild laden
	$('vs'+nID).src = 'images/calviewchoosen'+nID+'.gif';
}