/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Scripts related to the Image dialog window (see fck_image.html).
 */

var dialog	= window.parent ;
var oEditor = dialog.InnerDialogLoaded() ;
var FCKBrowserInfo = oEditor.FCKBrowserInfo ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKDebug	= oEditor.FCKDebug ;

// Link selektion für Popups
var oLink = dialog.Selection.GetSelection().MoveToAncestorNode('A') ;
if (oLink) FCK.Selection.SelectNode(oLink) ;

// Function called when a dialog tag is selected.
function OnDialogTabChange(tabCode) {
	
}

window.onload = function() {
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document);

	window.parent.SetAutoSize( true ) ;

	// Activate the "OK" button.
	window.parent.SetOkButton( true ) ;
}

//#### The OK button was hit.
function Ok() {
	sTyp = 'popup';
	if (document.getElementById('viewtypePaste').checked) {
		sTyp = 'paste';
	}
	switch(sTyp) {
		case 'popup':
			linkPopup();
			break;
		case 'paste':
			pasteText();
			break;
	}
}

// Weitere Optionen je nach Anzeigemodus
function updateViewtype(type) {
	switch(type) {
		case 'paste':
			document.getElementById('divPopupOptions').style.display = 'none';
			document.getElementById('divPasteOptions').style.display = 'block';
			break;
		case 'popup':
			document.getElementById('divPopupOptions').style.display = 'block';
			document.getElementById('divPasteOptions').style.display = 'none';
			break;
	}
	document.getElementById('centralcontent').blur();
}

// Sections des gewählten Menupunktes anzeigen
function updateSections() {
	// Funktion beim erfolreichen Ende des Requests
	SuccessFunction = function(transport) {	
		updateSectionOptions(transport.responseText);
	};
	// Parameter herausfinden
	menuid = document.getElementById('centralcontent').value;
	// Request absetzen
	new Ajax.Request('/library/class/ajaxRequest/call.php?SectionsByMenu', {
		method: 'post',
		parameters: 'nomail=1&menu=' + menuid,
		onSuccess: SuccessFunction
	});
}

function updateSectionOptions(xml) {
	selector = document.getElementById('contentsection');
	if (FCKBrowserInfo.IsIE) {
		// Alle Options aus dem Request einlesen
		xmlDoc = new ActiveXObject("Msxml2.DomDocument");
		xmlDoc.async = false;
		xmlDoc.loadXML(xml);
	} else {
		// Alle anderen Browser als IE
		parser = new DOMParser();
		xmlDoc = parser.parseFromString(xml,'text/xml');
	}
	// Selektor leeren
	for (i = selector.length - 1; i >= 0; i--) {
		selector.remove(i);
	}
	// Alle Kinder holen (alles options)
	reqOptions = xmlDoc.documentElement.childNodes;
	for (i = 0;i < reqOptions.length;i++) {
		// Option Node in das Select Element einbinden
		addedOption = document.createElement('option');
		addedOption.text = reqOptions[i].childNodes[0].nodeValue;
		addedOption.value = reqOptions[i].getAttribute('value');
		// Wiedermal IE
		if (FCKBrowserInfo.IsIE) {
			selector.add(addedOption);
		} else {
			selector.add(addedOption,null);
		}
	}
}

// Popup Link erstellen
function linkPopup() {
	// Link erstellen zum Content
	nSection = parseInt(document.getElementById('contentsection').value);
	if (isNaN(nSection)) nSection = 0;
	// TODO Hier den neuen controller Link verwenden, sobald soweit
	sUrl = '/modules/central/view.php?section=' + nSection;
	
	// If no link is selected, create a new one (it may result in more than one link creation - #220).
	aLinks = oLink ? [ oLink ] : oEditor.FCK.CreateLink( sUrl, true ) ;

	// If no selection, no links are created, so use the uri as the link text (by dom, 2006-05-26)
	var aHasSelection = (aLinks.length > 0) ;
	if (!aHasSelection) {
		nIndex = document.getElementById('contentsection').selectedIndex;
		sInnerHtml = document.getElementById('contentsection')[nIndex].text;
		// Create a new (empty) anchor.
		aLinks = [ oEditor.FCK.InsertElement('a') ] ;
	}

	for ( var i = 0 ; i < aLinks.length ; i++ )
	{
		oLink = aLinks[i] ;

		if (aHasSelection) sInnerHtml = oLink.innerHTML;

		oLink.href = '#' ;
		SetAttribute( oLink, '_fcksavedurl', sUrl ) ;

		onclick = BuildOnClickPopup() ;
		// Encode the attribute
		onclick = encodeURIComponent( " onclick=\"" + onclick + "\"" )  ;
		SetAttribute( oLink, 'onclick_fckprotectedatt', onclick ) ;
		oLink.innerHTML = sInnerHtml ;
		SetAttribute( oLink, 'target', null ) ;
	}

	// Select the (first) link.
	oEditor.FCKSelection.SelectNode( aLinks[0] );
	window.parent.CloseDialog();
}

// Onclick Popup erstellen
function BuildOnClickPopup()
{
	var nRand = generateRandom();
	var sWindowName = "'CentralContentPopup_" + nRand + "'";

	var sFeatures = '';
	var width = 800; 
	var height = 600;
	try {
		width = parseInt(GetE('popupwidth').value);
		height = parseInt(GetE('popupwidth').value);
	} catch (exception) { }
	
	sFeatures += 'width=' + width;
	sFeatures += ',height=' + height;

	if (sFeatures != '')
		sFeatures = sFeatures + ",status" ;

	return ("window.open(this.href," + sWindowName + ",'" + sFeatures + "'); return false") ;
}

// Text / Bild etc. einfügen
function pasteText() {
	// Code anhand der contentsection holen
	PasteFunction = function(transport) {
		sHtml = transport.responseText;
		if (document.getElementById('newparagraph').checked) {
			sHtml = '<p>' + sHtml + '</p>';
		}
		FCK.InsertHtml(sHtml);
		try {
			FCK.SwitchEditMode();
			FCK.SwitchEditMode();
		} catch (exception) { }
		window.parent.CloseDialog();
	};
	// Parameter herausfinden
	section = document.getElementById('contentsection').value;
	// Request absetzen
	new Ajax.Request('/library/class/ajaxRequest/call.php?GetSection', {
		method: 'post',
		parameters: 'section=' + section,
		onSuccess: PasteFunction
	});
}

// Zufallszahl generieren
function generateRandom() {
	str = '' + (Math.random() * 1000).toString();
	str = str.replace('.','');
	return(str);
}


