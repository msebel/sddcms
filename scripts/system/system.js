// Hilfedialog aus oder einblenden
function showHelp() {
	Effect.toggle('helpDialog','blind');
}

// Popup öffnen
function openWindow(sPage, sWindowName, nWidth, nHeight) {
	var randomNumber = Math.random();
	randomNumber = parseInt(100000 * randomNumber);
	var sURL = "'toolbar=yes,location=no,directories=no,status=no,menubar=no,"+
	"scrollbars=yes,resizable=yes,width=" + nWidth + ",height=" + nHeight + "'";
	window.open(sPage,sWindowName + "_" + randomNumber,sURL);
}

// Pointer auf eine ID setzen
function SetPointer(id,cursor) {
	document.getElementById(id).style.cursor = cursor;
}

// Löschen bestätigen
function deleteConfirm(url,what,lang) {
	var sMsg = '';
	sMsg = getResource(807);
	sMsg = sMsg.replace('{0}', what);
	var nConf = confirm(sMsg);
	if (nConf == 1) window.location = url;
}

// Resource holen (Synchroner Ajax Request
function getResource(number) {
	myRequest = new Ajax.Request(
		'/library/class/ajaxRequest/call.php?ResJavascript', {
		method: 'post',
		asynchronous: false,
		parameters: { resource : number }
	});
	return(myRequest.transport.responseText);
}

// Neues Event hinzufügen
function addEvent(elm, evType, fn, useCapture) {
	if (elm.addEventListener) {
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent) {
		var r = elm.attachEvent('on' + evType, fn);
		EventCache.add(elm, evType, fn);
		return r;
	} else {
		elm['on' + evType] = fn;
	}
}

// Bilder vorladen
function preloadImages() {
	if (document.images) { 
		if (!document.preloadArray) {
			document.preloadArray = new Array();
		}
		var i = 0;
		var j = document.preloadArray.length;
		var a = preloadImages.arguments;
		for (i = 0;i < a.length;i++) {
			if (a[i].indexOf("#") != 0) {
				document.preloadArray[j] = new Image;
				document.preloadArray[j++].src = a[i];
			}
		}
	}
}

// Ajax API call
var ajaxApiResult = null;
function ajaxApiCall(call,params,answerID) {
	// Resultat Objekt setzen, wenn vorhanden
	if (answerID.length > 0) {
		ajaxApiResult = $(answerID);
	}
	// Funktion beim erfolreichen Ende des Requests
	ApiFunction = function(transport) {	
		if (ajaxApiResult != null) {
			ajaxApiResult.innerHTML = transport.responseText;
		}
		// Zurücksetzen des Resultatbehälters
		ajaxApiResult = null;
	}
	// Request absetzen
	new Ajax.Request('/library/class/ajaxRequest/call.php?' + call, {
		method: 'post',
		parameters: params,
		onSuccess: ApiFunction
	});
}

// Div ein/ausblenden
function toggleDiv(id) {
	var ele = document.getElementById(id);
	if (ele.style.display == 'none') {
		ele.blindDown();
	} else {
		ele.blindUp();
	}
}

// Mouseover zurücksetzen
function imageRestore() {
	var i, x;
	var a = document.restoreArray; 
	for (i=0;a && i < a.length && (x=a[i]) && x.oSrc;i++) {
		x.src = x.oSrc;
	}
}

// Capcha neu laden
function captchaReload() {
	var ele = document.getElementById("captchaImage");
	var now = new Date();
	ele.src = "/scripts/captcha/code.php?reload=" + now.getTime();
} 

// Bestimmtes Bildobjekt finden
function findObject(n,doc) {
  	var p,i,x;
	if (!doc) doc = document;
  	if (!(x = doc[n]) && doc.all) {
		x = doc.all[n]; 
		for (i=0;!x && i < doc.forms.length;i++) {
			x = doc.forms[i][n];
		}
  		for (i=0;!x && doc.layers && i < doc.layers.length;i++) {
			x = findObject(n,doc.layers[i].document);
		}
  		if (!x && doc.getElementById) {
			x = doc.getElementById(n);
		}
	}
	return(x);
}

// Mouseover Bild
function imageOver() {
	var i,j=0,x,a=imageOver.arguments; 
	document.restoreArray=new Array; 
	for(i=0;i<(a.length-2);i+=3)
   		if ((x=findObject(a[i]))!=null){
			document.restoreArray[j++]=x; 
			if(!x.oSrc) 
				x.oSrc=x.src; 
				x.src=a[i+2];
		}
}

// Event holen, bei IE
function getEvent(e) {
	if (!e) e = event;
	return(e);
}

// Indexe der Felder aktualisieren
function updateSort() {
	var els = document.getElementsByName("sort[]");
	for (i = 0;i < els.length;i++) {
		els[i].value = (i + 1);
	}
}

// Formatiert den Preis nach dem Komma
function getFormatted(price) {
	var sPrice = (' ' + price).strip();
	var nPos = sPrice.indexOf('.');
    if ((nPos+3) < sPrice.length && nPos != -1) {
        sPrice = sPrice.substring(0,nPos+3);
    }
	// Wenn Punkt vorhanden
	if (nPos >= 0) {
		if (nPos+2 == sPrice.length) {
			sPrice += '0';
		}
	} else {
		sPrice += '.00';
	}
	return(sPrice);
}

// Flackern in IE Browsern verhindern
try{document.execCommand("BackgroundImageCache",false,true);}catch(err){}
// EventCache erstellen
if(Array.prototype.push == null){
	Array.prototype.push = function(){
		for(var i = 0; i < arguments.length; i++){
			this[this.length] = arguments[i];
		};
		return this.length;
	};
};

var EventCache = function(){
	var listEvents = [];
	
	return {
		listEvents : listEvents,
	
		add : function(node, sEventName, fHandler, bCapture){
			listEvents.push(arguments);
		},
	
		flush : function(){
			var i, item;
			for(i = listEvents.length - 1; i >= 0; i = i - 1){
				item = listEvents[i];
				
				if(item[0].removeEventListener){
					item[0].removeEventListener(item[1], item[2], item[3]);
				};
				
				/* From this point on we need the event names to be prefixed with 'on" */
				if(item[1].substring(0, 2) != "on"){
					item[1] = "on" + item[1];
				};
				
				if(item[0].detachEvent){
					item[0].detachEvent(item[1], item[2]);
				};
				
				item[0][item[1]] = null;
			};
		}
	};
}();

addEvent(window, 'unload', EventCache.flush, false);