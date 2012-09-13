/**
 * Klasse welche den Warenkorb im Teaser handelt
 */
var teaserCartClass = Class.create({

	Odd : true,

	/**
	 * JS Konstruktor, erstellt einen dom:loaded Event für den
	 * richtigen Konstruktor (Da erst möglich wenn DOM writable)
	 */
	initialize : function(sContainerID) {
		this.ContainerID = sContainerID;
		document.observe('dom:loaded',this.__construct.bind(this));
	},

	/**
	 * Effektiver Konstruktor
	 */
	__construct : function() {
		// Initialisieren der Controls
		this.Cart = $('shopCartContainer');
		this.Articles = $('shopCartArticles');
		this.Total = $('shopCartTotal');
		this.Error = $('shopCartError');
		// Order ID laden (Immer initialisieren)
		this.getOrderID();
		// Daten laden
		this.update();
	},

	/**
	 * Fehlermeldung im Warenkorb ausgeben
	 */
	throwError: function(message) {
		this.Error.update(message);
	},

	/**
	 * Callback, um Daten im Warenkorb anzuzeigen
	 */
	updateCallback : function(data) {
		// Leeren der Artikelliste
		this.Articles.update();
		// Alle Artikel durchgehen
		data.articles.each(function(article) {
			// Div erstellen (Klasse dank IE separat)
			var articleDiv = new Element('div', {});
			articleDiv.addClassName(this.getArticleClass());
			// Darin ein Div mit dem Artikelnamen / Grösse
			var articleContent = new Element('div', {});
			articleContent.addClassName('cartArticleName');
			// Wie viele Artikel?
			var sArticleTimes = '';
			if (article.soa_Times > 0) {
				sArticleTimes = article.soa_Times + 'x '
			}
			articleContent.update(
				sArticleTimes +
				article.soa_Title + ' ' +
				article.soa_Size
			);
			// Und ein Div mit dem Preis
			var articlePrice = new Element('div', {'class' : 'cartArticlePrice'});
			if (article.soa_Price > 0) {
				articlePrice.update(getFormatted(article.soa_Price));
			}
			// Diese beiden dem Content anheften
			articleDiv.insert(articleContent);
			articleDiv.insert(articlePrice);
			// Und dies dem globalen Container anhängen
			this.Articles.insert(articleDiv);
		}, this);
		// Preis Total darstelle
		this.Total.update(
			data.meta.total +
			data.meta.totalPrice + ' ' +
			data.meta.currency
		);
	},

	/**
	 * Gibt eine abwechselnde Klasse zurück
	 */
	getArticleClass : function() {
		this.Odd = !this.Odd;
		if (this.Odd) {
			return('cartArticleEntryOdd');
		} else {
			return('cartArticleEntryEven');
		}
	},

	/**
	 * ID des aktuellen Order holen
	 */
	getOrderID : function() {
		new Ajax.Request('/modules/shop/service/cart/order/', {
			onSuccess : (function (tp) {
				this.OrderID = parseInt(tp.responseText);
			}).bind(this)
		})
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
			},
			// Funktion im Erfolgsfall
			onSuccess : (function(tp) {
				this.update();
			}).bind(this),
			// Funktion im Fehlerfall
			onFailure : (function(tp) {
				var data = tp.responseText.evalJSON();
				this.throwError(data.message);
			}).bind(this)
		});
	},

	/**
	 * Den Warenkorb anhand aktueller Daten befüllen
	 */
	update : function() {
		new Ajax.Request('/modules/shop/service/cart/update/', {
			method : 'post',
			parameters : {
				OrderID : this.OrderID
			},
			onSuccess : (function(tp) {
				var data = tp.responseText.evalJSON();
				this.updateCallback(data);
			}).bind(this)
		})
	}
});