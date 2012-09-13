// Simplen Request ohne Antwort ausführen
function simpleAjaxRequest(url) {
	new Ajax.Request(url, { method: 'get' });
}

// Ajaxrequest mit Antwort ausführen
var answerObj = null;
function ajaxRequest(url,answerID) {
	// Progressbar in der ID anzeigen
	answerObj = $(answerID);
	answerObj.innerHTML = ''+
	'<img src="/images/media/progressbar.gif" height="10">';
	// Funktion beim erfolreichen Ende des Requests
	successFunction = function(transport) {	answerObj.innerHTML = transport.responseText; }
	// Fehlerfunktion
	failureFunction = function(transport) {	answerObj.innerHTML = 'error occured'; }
	// Request absetzen
	new Ajax.Request(url, {
		method: 'get',
		onSuccess: successFunction,
		onFailure: failureFunction
	});
}