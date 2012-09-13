/**
 * Hauptklasse für die Detailformulare, handelt die Blocks
 * und deren Speicherfunktionen/Effekte
 */
var DetailFormMainClass = Class.create({

    FirstBlockID : 0,
    Links : [],
    cBLOCKS : 4,

    /**
     * Konstruktor, bekommt den offenen Block
     * @param openBlock ID des am Anfang zu öffnenden Blocks
     */
    initialize : function(openBlock) {
        this.FirstBlockID = openBlock;
        // Links herausfinden und darin Divs speichern
        for (i = 1;i <= this.cBLOCKS;i++) {
            link = $('open_' + i);
            link.blockID = i;
            link.openingDiv = $('block_' + i);
            link.openingDiv.open = false;
            link = this.addEvents(link);
            this.Links[i] = link;
        }
        Event.observe(window,'load',this.realInitialize.bind(this));
    },

    /**
     * Fügt dem Link die nötigen Events hinzu
     */
    addEvents : function(link) {
        link.observe('click',function(event) {
            link = event.element();
            if (link.openingDiv.open) {
                this.closeBlock(link.blockID);
            } else {
                this.openBlock(link.blockID);
            }
        }.bind(this));
        link.close = function() {
            link.openingDiv.blindUp();
            link.openingDiv.open = false;
            link.innerHTML = '+';
        };
        link.open = function() {
            link.openingDiv.blindDown();
            link.openingDiv.open = true;
            link.innerHTML = '-';
            // Offene ID setzen für reload
            new Ajax.Request('rest/setopenblock/', {
                parameters : {block : link.blockID}
            });
        };
        return(link);
    },

    /**
     * Konstruktor Folge sobald Window fertig geladen ist
     */
    realInitialize : function() {
        this.openCurrentBlock();
		// Zusätzliche Objekte erstellen
		this.MetaFieldHandler = new MetaFieldClass();
    },

    /**
     * Öffnet den aktuellen Block
     */
    openCurrentBlock : function() {
        this.openBlock(this.FirstBlockID);
    },

    /**
     * Öffnet den gewünschten Block
     * @param id ID des zu öffnenden Blocks
     */
    openBlock : function(id) {
        // Offene Blocks schliessen
        this.closeBlocks();
        // blocks holen, anzeigen, Previous ausblenden
        this.Links[id].open();
    },

    /**
     * Schliesst einen Block
     * @param id ID des Blocks
     */
    closeBlock : function(id) {
        this.Links[id].close();
    },

    /**
     * Schliesst alle Blocks die offen sind
     */
    closeBlocks : function() {
        this.Links.each(function(link) {
            if (link.openingDiv.open) {
                this.closeBlock(link.blockID);
            }
        },this);
    }
});

/**
 * Klasse welchen den Meta Feld Handler repräsentiert
 */
var MetaFieldClass = Class.create({

	/**
	 * Lesen der HTML Elemente
	 */
	initialize : function() {
		this.Select = $('sdfID');
		this.Container = $('newMetaInputContainer');
		this.SubmitPane = $('newMetaSubmitContainer');
		this.HiddenInfo = $('newMetaHiddenInfo');
		// Events hinzufügen
		this.SubmitPane.visible = false;
		this.Select.observe('change',this.selectChange.bind(this));
	},

	/**
	 * Event bei Change des Select
	 * @param event Event Objekt
	 */
	selectChange : function(event) {
		var json = this.Select.getValue();
		var Field = json.replace(/##/g, '"').evalJSON();
		// Anzeige, je nach Typ
		this.showInput(Field);
		// Einblenden des Submit
		this.showSubmit();
	},

	/**
	 * Ein Input Feld anzeigen, je nach Typ
	 * @param Field Feldinformationen als Objekt
	 */
	showInput : function(Field) {
		switch (Field.sdf_Type) {
			case '0':
				this.showTextbox(Field);break;
			case '1':
				this.showSelect(Field);break;
			case '2':
				this.showCheckboxes(Field);break;
		}
		this.HiddenInfo.value = Field.sdf_Type;
	},

	/**
	 * Ein Textbox Feld anzeigen
	 * @param Field Feldinformationen als Objekt
	 */
	showTextbox : function(Field) {
		this.Container.update('');
		var input = new Element('input', {
			name : 'metaValue',
			style : 'width:150px;',
			value : Field.sdf_Default
		});
		this.Container.appendChild(input);
	},

	/**
	 * Ein Select Feld anzeigen
	 * @param Field Feldinformationen als Objekt
	 */
	showSelect : function(Field) {
		this.showWaiting();
		this.CurrentField = Field;
		// Ajax Request mit Detaildaten des Feldes
		new Ajax.Request('rest/dynamicvalues/', {
			parameters : {fieldID : Field.sdf_ID},
			onSuccess : this.responseSelect.bind(this)
		});
	},

	/**
	 * Antwortfunktion von Ajax Request aus showSelect
	 */
	responseSelect : function(transport) {
		this.Container.update('');
		// Selektor erstellen
		var select = new Element('select', {
			name : 'metaValue',
			style : 'width:150px;'
		});
		select.preselect = this.CurrentField.sdf_Default;
		// Array bauen aus JSON Resultat (Ist ein Array)
		var values = transport.responseText.evalJSON();
		// Optionen hinzufügen
		values.each(function(option) {
			var added = document.createElement('option');
			added.text = option.Value;
			added.value = option.ID;
			// Einfügen
			try {
				this.add(added,null);
			} catch (ex) {
				this.add(added);
			}
			// Aktuelles selektieren, wenn übereinstimmung
			if (option.Value == this.preselect) {
				this.selectedIndex = this.length-1;
			}
		}, select);
		// Select dem Container hinzufügen
		this.Container.appendChild(select);
	},

	/**
	 * Ein Checkboxen Feld anzeigen
	 * @param Field Feldinformationen als Objekt
	 */
	showCheckboxes : function(Field) {
		this.showWaiting();
		this.CurrentField = Field;
		// Ajax Request mit Detaildaten des Feldes
		new Ajax.Request('rest/dynamicvalues/', {
			parameters : {fieldID : Field.sdf_ID},
			onSuccess : this.responseCheck.bind(this)
		});
	},

	/**
	 * Antwortfunktion von Ajax Request aus showSelect
	 */
	responseCheck : function(transport) {
		this.Container.update('');
		// Alle Values holen
		var values = transport.responseText.evalJSON();
		// Checkboxen erstellen
		values.each(function(value) {
			var check = new Element('input', {
				type : 'checkbox',
				name : 'metaValue[]',
				value : value.ID
			});
			this.insert({bottom : check});
			this.insert({bottom : ' ' + value.Value + '<br>'});
		}, this.Container);
	},

	/**
	 * Zeigt ein drehendes Wartesymbölchen
	 */
	showWaiting : function() {
		this.Container.update('');
		div = new Element('div', { });
		div.className = 'cWaitingIcon';
		div.innerHTML = '&nbsp;';
		this.Container.appendChild(div);
	},

	/**
	 * Submit einmalig anzeigen (Danach wird
	 * es nicht mehr ausgeblendet)
	 */
	showSubmit : function() {
		if (!this.SubmitPane.visible) {
			this.SubmitPane.visible = true;
			this.SubmitPane.appear();
		}
	}
});