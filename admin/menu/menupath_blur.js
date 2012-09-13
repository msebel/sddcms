document.observe("dom:loaded", function() {
	// Blur Handler für autovervollständigung des Pfads
	$('mnuname').observe('blur',function() {
		// Wieder raus, wenns deaktiviert ist
		if (!BlurLinkConfig.isBlurActive) return;
		// Wenn schon etwas drin steht, nix tun
		if ($('mnulink').getValue().length > 0) return;
		// Formularfeld 'Link' deaktivieren
		$('mnulink').setValue(BlurLinkConfig.waitMessage);
		$('mnulink').disable();
		// Ajax Request, zur Autovervollständigung absetzen
		new Ajax.Request('/admin/menu/generatelink.php', {
			method : 'post',
			parameters : {
				mnuname : $('mnuname').getValue()
			},
			onSuccess : function(response) {
				// Speichern und Handler deaktivieren
				$('mnulink').setValue(response.responseText);
				$('mnulink').enable();
				BlurLinkConfig.isBlurActive = false;
			}
		})
	});
});