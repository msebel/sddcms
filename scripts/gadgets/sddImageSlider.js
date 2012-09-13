var sddImageSlider = Class.create({
	
	Config : {},
	
	// Konstruktor
	initialize : function(config) {
		// Konfiguration lokal speichern
		this.Config = config;
		this.Config.NextImage = 3;
		// Event bei DOM loaded
		Event.observe(window, "load", (function() {
			this.InitializeLoaded();
		}).bind(this));
	},
	
	// Initialisierung nach Window Load
	InitializeLoaded : function() {
		// BildID's direkt als Objekte laden
		this.Images = [
			$(this.Config.ImageIds[0]),
			$(this.Config.ImageIds[1])
		];
		// Bild Slider starten
		this.ChangeImage.bind(this).delay(
			this.Config.Timer
		);
	},
	
	// Nächstes Bild anzeigen
	ChangeImage : function() {
		var currIdx = this.Config.CurrentIndex;
		// Anderen Index definieren
		var newIdx = 0;
		(currIdx == 0) ? newIdx = 1 : newIdx = 0;
		// Aktuelles Bild ausfaden
		this.Images[currIdx].fade(
			{ duration : this.Config.EffectDuration }
		);
		// Neues Bild einfaden
		this.Images[newIdx].appear(
			{ duration : this.Config.EffectDuration }
		);
		// Index wechseln (Aktueller Index)
		this.Config.CurrentIndex = newIdx;
		// Neues Bild definieren (Nach Fadeout)
		this.SetNextImage.bind(this).delay(
			this.Config.EffectDuration + 0.5
		);
		// Rekursiv wieder aufrufen
		this.ChangeImage.bind(this).delay(
			this.Config.Timer
		);
	},
	
	// Nächstes bild in der Liste setzen
	SetNextImage : function() {
		var currIdx = this.Config.CurrentIndex;
		// Anderen Index definieren
		var newIdx = 0;
		(currIdx == 0) ? newIdx = 1 : newIdx = 0;
		// Neues Bild definieren
		var next = this.Config.NextImage;
		this.Images[newIdx].src = this.List[next];
		// Nächstes Bild definieren (Mit Check)
		next++;
		if (next > (this.List.length) - 1) next = 0;
		this.Config.NextImage = next;
	}
})