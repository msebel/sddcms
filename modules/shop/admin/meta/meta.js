// Handelt das NewField Window
var AddNewFieldClass = Class.create({

    // Verschiedene Farben
    overColor : '#FFCF3F',
    selectColor : '#879DFF',
    backColor : 'transparent',

    initialize : function() {
        // Auf alle Felder einen Event spitzen
        $$('.cAddNewFieldShop').each(function(div) {
            div.isSelected = false;
            div.observe('mouseover',this.MouseOver.bind(this));
            div.observe('mouseout',this.MouseOut.bind(this));
            div.observe('click',this.Click.bind(this));
        },this);
    },

    // Mouse Over Funktion über Div
    MouseOver : function(event) {
        var div = event.element();
        if (!div.isSelected) {
            div.style.backgroundColor = this.overColor;
        }
    },

    // Mouse Out Funktion über Div
    MouseOut : function(event) {
        var div = event.element();
        if (!div.isSelected) {
            div.style.backgroundColor = this.backColor;
        }
    },

    // Angeklicktes Div speichern
    Click : function(event) {
        var div = event.element();
        $('sdfType').value = div.id;
        // Alle Divs deselektieren
        $$('.cAddNewFieldShop').each(function(div) {
            div.isSelected = false;
            div.style.backgroundColor = this.backColor;
        },this);
        // Dieses Div selektieren
        div.isSelected = true;
        div.style.backgroundColor = this.selectColor;
    }
});