function checkLinkOption (nLinkInt, nLinkExt) {
	var nLink = document.menuEdit.type.value;
	// Erstmal beides disablen
	document.menuEdit.external.disabled = true;
	document.menuEdit.redirect.disabled = true;
	// Prüfen ob Interner Link
	if (document.menuEdit.type.value == nLinkInt) {
		document.menuEdit.redirect.disabled = false;
		document.menuEdit.external.value = '';
	}
	// Prüfen ob externer Link
	if (document.menuEdit.type.value == nLinkExt) {
		document.menuEdit.external.disabled = false;
		// Wenn noch nichts drin ...
		if (document.menuEdit.external.value.length == 0) {
			document.menuEdit.external.value = 'http://';
		}
	}
}