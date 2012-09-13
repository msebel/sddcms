var WikiEditor = Class.create();

WikiEditor.prototype = {
	
	id : '',
	area : null,
	
	// Konstruktor
	initialize : function () {
	
	},
	
	// Initialisiert den Editor
	initializeEditor : function (id) {
		// Funktion zum registrieren erstellen
		this.id = id;
		this.area = $(this.id);
	},
	
	// Buttonevents registrieren
	registerButtons : function () {
		this.registerButton('h1',this.insertH1);
		this.registerButton('h1',this.insertH1);
		this.registerButton('h2',this.insertH2);
		this.registerButton('h3',this.insertH3);
		this.registerButton('bold',this.insertBold);
		this.registerButton('italic',this.insertItalic);
		this.registerButton('line',this.insertLine);
		this.registerButton('link',this.insertLink);
	},
	
	// Events eines Button registrieren
	registerButton : function (buttonid,fClick) {
		var button = $(buttonid);
		button.onclick = fClick;
		button.onmouseover = function () { SetPointer(buttonid,'pointer') };
		button.onmouseout = function() { SetPointer(buttonid,'default') };
	},
	
	// Einen Tag in die Selektion oder an Position des Cursors einfügen
	insertWikiTag : function (tagbef,tagaft) {
		var sWord = '';
		var sText = this.area.value;
		// Selektierte Koordinaten holen
		this.setSelection();
		nStart = this.area.selectionStart;
		nEnd = this.area.selectionEnd;
		// Wenn beides Gleich, Default Wort nehmen
		sWord = 'default';
		if (nStart < nEnd) {
			sWord = sText.substring(nStart,nEnd);
		}
		// Vor und nach Selektierung in Variable schreiben
		var sBefore = sText.substring(0,nStart);
		var sAfter = sText.substring(nEnd,sText.length);
		// Wert zusammenbauen und wieder in Area speichern
		sText = sBefore + tagbef + sWord + tagaft + sAfter;
		this.area.value = sText;
	},
	
	// Einen Tag in die Selektion oder an Position des Cursors einfügen
	insertWikiValue : function (value) {
		var sText = this.area.value;
		// Selektierte Koordinaten holen
		this.setSelection();
		nStart = this.area.selectionStart;
		// Vor und nach Cursor in Variable schreiben
		var sBefore = sText.substring(0,nStart);
		var sAfter = sText.substring(nStart,sText.length);
		// Wert zusammenbauen und wieder in Area speichern
		sText = sBefore + value + sAfter;
		this.area.value = sText;
	},
	
	// Selektion der Textarea holen
	setSelection : function () {
		if (document.selection) {
			var range = document.selection.createRange();
			// Dummy für IE erstellen
			var storedRange = range.duplicate();
			// Den Text markieren und den Dummy als Ende setzen
			storedRange.moveToElementText(this.area);
			storedRange.setEndPoint('EndToEnd',range);
			// Daraus Start und Ende berechnen (Gecko Props nutzen)
			this.area.selectionStart = storedRange.text.length - range.text.length;
			this.area.selectionEnd = this.area.selectionStart + range.text.length;
		}
		// Area Objekt zurückgeben für Statische Aufrufen
		return(this.area);
	},
	
	// H1 einfügen oder markierung nutzen
	insertH1 : function () {
		Editor.insertWikiTag('=','=');
	},
	
	// H2 einfügen oder markierung nutzen
	insertH2 : function () {
		Editor.insertWikiTag('==','==');
	},
	
	// H3 einfügen oder markierung nutzen
	insertH3 : function () {
		Editor.insertWikiTag('===','===');
	},
	
	// Fettschrift einfügen oder markierung nutzen
	insertBold : function () {
		Editor.insertWikiTag("'''","'''");
	},
	
	// Kursivschrift einfügen oder markierung nutzen
	insertItalic : function () {
		Editor.insertWikiTag("''","''");
	},
	
	// Linie einfügen
	insertLine : function () {
		Editor.insertWikiValue("----");
	},
	
	// Link einfügen oder markierung nutzen
	insertLink : function () {
		Editor.insertWikiTag("[[","]]");
	}
	
};