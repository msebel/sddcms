// Register anzeigen
function setTdRegister(regid,mapid) {
	// Elemente holen
	var route = document.getElementById('iRoute');
	var desc = document.getElementById('iDesc');
	var both = document.getElementById('iBoth');
	var mapdiv = document.getElementById('Map' + mapid);
	var routediv = document.getElementById('Route' + mapid);
	// Alle deaktivieren
	setDeactivated(route,desc,both);
	switch(regid) {
		case 1:
			route.className = 'cNavSelected';
			mapdiv.style.display = 'block';
			routediv.style.display = 'none';
			break;
		case 2:
			desc.className = 'cNavSelected';
			mapdiv.style.display = 'none';
			routediv.style.display = 'block';
			break;
		case 3:
			both.className = 'cNavSelected';
			mapdiv.style.display = 'block';
			routediv.style.display = 'block';
			break;
	}
}

// Deaktiviere alle Elemente
function setDeactivated(ele1,ele2,ele3) {
	ele1.className = 'cNav';
	ele2.className = 'cNav';
	ele3.className = 'cNav';
}