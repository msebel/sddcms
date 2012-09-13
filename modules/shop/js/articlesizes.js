/**
 * Kontrolliert die Artikelgrössen und passt Preise an
 * @author Michael Sebel <michael@sebel.ch>
 */
var ArticleSizeController = Class.create({

	initialize : function(className) {
		this.searchClass = className;
		Event.observe(window,'load',this.__construct.bind(this));
	},

	__construct : function() {
		this.articles = $$('.' + this.searchClass);
		this.addText = getResource(1098);
		// Auf allen die nötigen Daten registrieren
		this.articles.each(function(article) {
			// ID des Selects lesen und Preis ID davon holen
			idPrefix = article.id.split('_')[0];
			priceID = idPrefix + '_Price';
			sizeID = idPrefix + '_Size';
			countID = idPrefix + '_Count';
			submitID = idPrefix + '_Submit';
			// Dein Preiscontainer Referenzieren
			article.priceContainer = $(priceID);
			article.sizeChoosen = $(sizeID);
			article.articleCount = $(countID);
			article.submit = $(submitID);
			article.price = article.priceContainer.innerHTML;
			article.addText = this.addText;
			var current = article.getValue().replace(/##/g, '"').evalJSON();
			// ID ausbeuteln
			article.shaID = idPrefix.split('-')[1];
			// Klick Event auf den Submit Button
			article.submit.observe('click',this.addToCart.bind(article));
			// Change Funktion für den Artikel definieren
			if (!Object.isUndefined(current.ID)) {
				article.observe('change', this.onSizeChange.bind(article));
				// Dies zum Starten auch gleich ausführen, da der
				// Preis durch die Vorauswahl bereits anders sein kann
				this.onSizeChange(null,article);
			}
		}, this);
	},

	/**
	 * Behandelt die Grössenänderung eiens Artikels. Obacht, this
	 * ist hier der Kontext des Artikel-Selects, nicht der Klasse
	 */
	onSizeChange: function(event,article) {
		// Abfangen ob Event oder direkter Aufruf
		if (Object.isUndefined(article)) article = this;
		// Preis hinzurechnen mit aktueller Preishöhe
		var pricevalues = article.price.strip().split(' ');
		var origprice = parseFloat(pricevalues[0]);
		var selected = article.getValue().replace(/##/g, '"').evalJSON();
		var price = parseFloat(origprice) + parseFloat(selected.Price);
		article.priceContainer.update(
			getFormatted(price) + ' ' + pricevalues[1]
		);
		article.currentPrice = getFormatted(price);
		article.sizeChoosen.value = selected.ID;
	},

	addToCart: function(event) {
		// Versuchen die Grösse zu holen
		try {
			nSazID = this.sizeChoosen.getValue();
		} catch (exception) {
			nSazID = 0; // Keine Grösse einstellbar
		}
		// Artikelzahl validieren
		var nCount = parseInt(this.articleCount.getValue());
		if (!Object.isNumber(nCount)) nCount = 0;
		// Versuchen Warenkorb oder lokal als Fallback
		try {
			gTeaserCart.add(this.shaID,nSazID,nCount);
		} catch (exception) {
			var cart = new ArticleSizeController();
			cart.add(this.shaID,nSazID,nCount);
		}
		// Feedback in Button-Nähe
		var div = new Element('div',{
			'class' : 'cCartOkMessage'
		});
		div.update(this.addText);
		// Bisherige Subelemente löschen
		$$('.cCartOkMessage').each(function(element) {
			element.remove();
		})
		$(this.parentNode).insert(div);
		new Effect.Highlight(div, {'startcolor' : '#aa0000'});
		div.blindUp.bind(div).delay(3.0);
	},

	/**
	 * Einen Artikel in den Warenkorb werfen
	 */
	add : function(nShaID,nSazID,nCount) {
		new Ajax.Request('/modules/shop/service/cart/add/', {
			method :'post',
			parameters : {
				shaID : nShaID,
				sazID : nSazID,
				articles : nCount
			}
		});
	}
});

