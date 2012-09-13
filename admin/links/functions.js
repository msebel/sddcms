function resetLink() {
	$('lnkClickValue').innerHTML = '0';
}

function chooseType(nID) {
	// Formularfeld anpassen
	$('viewType').value = nID;
	// Alles Bilde als 'deselektiert' anzeigen
	for (i = 1;i <= 3;i++) {
		$('vs'+i).src = 'images/linkview'+i+'.gif';
	}
	// Anderes Bild laden
	$('vs'+nID).src = 'images/linkviewchoosen'+nID+'.gif';
}