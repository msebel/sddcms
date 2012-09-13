// Bestätigen, dass ein Element importiert werden soll
function confirmImport(sMessage,nID,nTeaser,nTapID) {
	confirmed = confirm(sMessage);
	if (confirmed) {
		url  = '/admin/teaser/elements.php';
		url += '?id=' + nID;
		url += '&teaser=' + nTeaser;
		url += '&import=' + nTapID;
		document.location.href = url;
	}
}

// Bestätigen, dass ein importieres Element gelöscht werden soll
function deleteImportedConfirm(url,sMessage) {
	confirmed = confirm(sMessage);
	if (confirmed) {
		document.location.href = url;
	}
}