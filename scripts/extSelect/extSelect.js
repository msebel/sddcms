// Wird onChange an der Selectbox ausgef�hrt und handelt
// alle m�glichen Optionen des Selektors
function makeSelection(myForm,selectorName) {
	// Form Element holen
	var selector = myForm.elements[selectorName];
	var selection = parseInt(selector.value);
	// Diverse vorg�nge ausf�hren, je nach Selektion
	if (newValuesAllowed(selectorName,myForm)) {
		if (selection <  0) addNewOption(selector,myForm);
	}
	if (selection == 0) resetAll(selector,myForm);
	if (selection >  0) addOption(selector,selection,myForm);
	// Auswahl des Selektors aufheben
	unSelect(selector,false);
}

// Deselektiert alle Optionen die momentan ausgew�hlt sind
// und setzt wieder die Standard Klasse
function unSelect(selector,makeWhite) {
	for (var i=0;i < selector.length; i++) {
		selector.options[i].selected = false;
		if (makeWhite) {
			selector.options[i].className = 'extSelectUnselect';
		}
	}
	// IE �berlisten damit er die Selektion ganz entfernt
	if (document.all) selector.selectedIndex = -1;
	selector.blur();
}

// Setzt alle eingaben zur�ck indem es unter anderem die Selektion leert
// aber auch die neu eingegebene Value l�scht und ausblendet
function resetAll(selector,myForm) {
	var selectorName = selector.name;
	var options = selector.options.length;
	// Alle Options weiss f�rben und deselektieren
	unSelect(selector,true);
	// Auswahl l�schen
	myForm.elements[selectorName+'_selectedValues'].value = '';
	if (newValuesAllowed(selector.name,myForm)) {
		myForm.elements[selectorName+'_newField'].value = '0';
		// Neue Diagnose ausblenden
		document.getElementById(selectorName+'_newTextBox').style.display = 'none';
	}
}

// Bringt das Textfeld zum Vorschein um einen neuen Wert in die Selectbox hinzuzuf�gen
function addNewOption(selector,myForm) {
	var selectorName = selector.name;
	var option = document.getElementById(selectorName+'_OptionNew');
	option.className = 'extSelectSelection';
	document.getElementById(selectorName+'_newTextBox').style.display = 'block';
	myForm.elements[selectorName+'_newField'].value = '1';
	myForm.elements[selectorName+'_newValue'].value = '';
}

// F�gt eine Option der gesamten Selektion hinzu
function addOption(selector,nSelection,myForm) {
	var selectorName = selector.name;
	var option = document.getElementById(selectorName+"_Option"+nSelection);
	var selectedVal = myForm.elements[selectorName+'_selectedValues'].value;
	if (selectedVal.search(nSelection+';') == -1) {
		option.className = 'extSelectSelection';
		// Wert der Liste anh�ngen
		selectedVal += nSelection+';';
	} else {
		option.className = 'extSelectUnselect';
		// Wert aus der Liste entfernen
		selectedVal = selectedVal.replace(nSelection+';','');
	}
	// Selektionsliste anpassen
	myForm.elements[selectorName+'_selectedValues'].value = selectedVal;
}

// Gibt an ob neue Werte f�r die Selectbox erlaubt sind
// Momentan kann man nur einen neuen Wert angeben
function newValuesAllowed(selectorName,myForm) {
	var allowed = false;
	// Options holen und zu einem Integer parsen
	var newValues = myForm.elements[selectorName+'_newValues'].value;
	newValues = parseInt(newValues);
	// Wenn Option 1 ist, neuen Eintrag erlauben
	if (newValues == 1) {
		allowed = true;
	}
	return(allowed);
}

// Erstellt einen neuen Selektor
function newSelector(selectorName,selectorOptions,allowNew,allowNone,nSize,sKeine,sAndere) {
	sHtml = '';
	// Selector erstellen
	sHtml += '<select name="'+selectorName+'" size="'+nSize+'" class="extSelectSelector" onChange="makeSelection(this.form,this.name)">\n';
	// Option "Keine" hinzuf�gen
	if (allowNone == true) {
		sHtml += '<option value="0" id="'+selectorName+'_Option0" class="extSelectUnselect">'+sKeine+'</option>\n';
	}
	// Optionen hinzuf�gen
	for (i = 0;i < selectorOptions.length;i++) {
		allowed = true;
		// Konditionen f�r nicht erlaubt
		if (selectorOptions[i].value <= 0) {
			allowed = false;
		}
		// Option hinzuf�gen
		if (allowed) {
			sHtml += '<option value="'+selectorOptions[i].value+'" id="'+selectorName+'_Option'+selectorOptions[i].value+'" class="extSelectUnselect">'+selectorOptions[i].desc+'</option>\n';
		}
	}
	// Option "Neues Wert" hinzuf�gen
	if (allowNew == true) {
		sHtml += '<option value="-1" id="'+selectorName+'_OptionNew" class="extSelectUnselect">'+sAndere+' ...</option>\n';
	}
	// Select abschliessen
	sHtml += '</select>';
	// New Textbox hinzuf�gen
	sHtml += '<div id="'+selectorName+'_newTextBox" style="display:none;">\n' +
	'<input type="text" class="newTextCss" value="" name="'+selectorName+'_newValue">\n' +
	'</div>';
	// Hidden Fields f�r Optionen gestalten
	if (allowNew == true) {
		sHtml += '<input type="hidden" name="'+selectorName+'_newValues" value="1">';
	} else {
		sHtml += '<input type="hidden" name="'+selectorName+'_newValues" value="0">';
	}
	// Selektierte Werte einf�gen
	sHtml += '<input type="hidden" name="'+selectorName+'_selectedValues" value="">';
	// Option f�r neues feld einf�gen
	sHtml += '<input type="hidden" name="'+selectorName+'_newField" value="0">';
	// Das ganze HTML direkt ausgeben
	document.write(sHtml);
}

// Objekt f�r Optionen des Selektors
// value = muss numerisch sein, sonst kann nichts selektiert werden
// value muss dazu gr�sser als 0 sein
// desc = wird zwischen die Options Tags geschrieben
function selectOption(value,desc) {
	this.value = parseInt(value);
	this.desc = desc;
}