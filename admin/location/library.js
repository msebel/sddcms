var queryID = '';
var KEY_ENTER = 13;

// Registriert die Events auf das Query Formular
function addQueryEvent(id) {
	queryID = 'query';
	var query = document.getElementById(queryID);
	// Blur Event
	query.observe('blur', queryClick); 
	query.observe('keydown', queryKeyDown); 
}

// Mausevent auf Query Formular
function queryClick(event) {
	searchQuery();
}

// Key Event auf Query
function queryKeyDown(event) {
	if (event.keyCode == KEY_ENTER) {
		searchQuery();
	}
}

// Holt eine Text Resource aus der Datenbank
function getResource(nResID) {
	myRequest = new Ajax.Request(
		'/library/class/ajaxRequest/call.php?ResJavascript', {
		method: 'post',
		asynchronous: false,
		parameters: 'resource=' + nResID
	});
	return(myRequest.transport.responseText);
}

// Sucht nach der eingegebenen Query
function searchQuery() {
	var query = document.getElementById(queryID);
	new Ajax.Request('call.php?query='+escape(query.value), {
		onSuccess: function(response) {
			var result = response.responseText.split(';');
			var latitude = result[0];
			var longitude = result[1];
			var divResult = document.getElementById('divResult');
			// Erfolg anzeigen
			if (longitude > 0 && latitude > 0) {
				// in die Formulare
				document.getElementById('latitude').value = latitude;
				document.getElementById('longitude').value = longitude;
				// Icon anzeigen
				var text = getResource(868);
				divResult.innerHTML = ''+
				'<img src="/images/icons/action_go.gif" alt="' + text + '" title="' + text + '">' +
				'<div style="float:right;margin-left:5px;">' + text + '</div>';
			} else {
				// Icon anzeigen
				var text = getResource(869);
				divResult.innerHTML = ''+
				'<img src="/images/icons/action_notgo.gif" alt="' + text + '" title="' + text + '">' +
				'<div style="float:right;margin-left:5px;">' + text + '</div>';
			}
		}
	});
}

// Öffnet die File Library
function openFileLibrary() {
	openWindow('/admin/library/index.php?id=0&mode=view','FileLibrary',950,700);
}

// Speichert das File aus der Library in ein lokales Formularfeld
function filelibraryAction(sFile) {
	document.getElementById('iconurl').value = sFile
}