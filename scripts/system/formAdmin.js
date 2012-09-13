var addNewFormShown = false;
var editDialog = false;
var newFormTypeIndex = -1;
var DROPDOWN_FIELD_DELIMITER = '$$';
var DROPDOWN_VALUE_DELIMITER = '##';
// Eventhandler, Klick auf "neues Formular"
function addNewForm(event) {
	var x = Event.pointerX(event) + 5;
	var y = Event.pointerY(event) + 5;
	// Element Positionieren
	var divPopup = $('newElementPopup');
	// Ein / Ausschalten
	if (!addNewFormShown) {
		divPopup.style.top = y+'px';
		divPopup.style.left = x+'px';
		Effect.Appear('newElementPopup');
	} else {
		Effect.Fade('newElementPopup');
	}
	// Umkehren fürs nächstemal
	addNewFormShown = !addNewFormShown;
}

// Effekt mouseover
function formTypeOver(tableRow,id) {
	if (id != newFormTypeIndex) {
		tableRow.style.backgroundColor = '#FFCF3F';
		tableRow.style.cursor = 'pointer';
	}
}

// Effekt mouseout
function formTypeOut(tableRow,id) {
	if (id != newFormTypeIndex) {
		tableRow.style.backgroundColor = 'transparent';
		tableRow.style.cursor = 'default';
	}
}

function selectFormType(tableRow,id) {
	var type = '';
	// Formulartyp herausfinden
	switch (id) {
		case 0: type = 'text'; 		break;
		case 1: type = 'textarea'; 	break;
		case 2: type = 'radio'; 	break;
		case 3: type = 'checkbox'; 	break;
		case 4: type = 'submit'; 	break;
		case 5: type = 'hidden'; 	break;
		case 6: type = 'dropdown';	break;
		case 7: type = 'captcha';	break;
	}
	// Entsprechende ID / Namen setzen
	document.formNew.newFormType.value = type;
	newFormTypeIndex = id;
	// Alle Table Rows resetten
	for (i = 0; i <= 5;i++) {
		document.getElementById('newTableRow'+i).style.backgroundColor = 'transparent';
	}
	tableRow.style.backgroundColor = '#879DFF';
}

// Formularfeld updaten
function updateFormfield(index) {
	var oField = getFormColletion(index);
	showFormfield(oField,index);
}

// View eines Feldes anzeigen
function showFormfield(oField,index) {
	var divDesc = document.getElementById('tabCellDesc_'+index);
	var divForm = document.getElementById('tabCellForm_'+index);
	// divDesc setzen
	switch (oField.Type.value) {
		case 'text':
		case 'textarea':
		case 'dropdown':
			if (oField.Required == 1) {
				divDesc.innerHTML = oField.Desc.value + ' <span style="color:#d00">*</span>';
			} else {
				divDesc.innerHTML = oField.Desc.value;
			}
			break;
		case 'radio':
		case 'checkbox':
		case 'hidden':
		case 'submit':
			divDesc.innerHTML = '&nbsp;';
			break;
		case 'captcha':
			divDesc.innerHTML = oField.Desc.value + ' <span style="color:#d00">*</span>';
			break;
	}
	// Formularfeld erstellen
	switch (oField.Type.value) {
		case 'text':
			divForm.innerHTML = '<input '+
			'type="text" name="'+oField.Name.value+'" value="'+oField.Value.value+'"'+
			''+getCssClass(oField)+getStyle(oField)+'>';
			break;
		case 'textarea':
			divForm.innerHTML = '<textarea '+
			'name="'+oField.Name.value+'"'+getCssClass(oField)+getStyle(oField)+'>'+
			''+oField.Value.value+'</textarea>';
			break;
		case 'checkbox':
		case 'radio':
			divForm.innerHTML = '<input '+
			'type="'+oField.Type.value+'" name="'+oField.Name.value+'" value="'+oField.Value.value+'"'+
			''+getCssClass(oField)+'> '+oField.Desc.value;
			break;
		case 'submit':
			divForm.innerHTML = '<input '+
			'type="button" name="'+oField.Name.value+'" value="'+oField.Desc.value+'"'+
			''+getCssClass(oField)+getStyle(oField)+'>';
			break;
		case 'captcha':
			divForm.innerHTML = '<img src="/scripts/captcha/code.php"/>' +
			'<p><input type="text" name="captchaCode"'+getCssClass(oField)+getStyle(oField)+'/> ' +
			'<img onclick="captchaReload()" src="/images/icons/arrow_refresh_small.png"/></p>';
			break;
		case 'hidden':
			divForm.innerHTML = '<span style="color:#999;">Unsichtbares Feld / Invisible Field:'+oField.Desc.value+'</span>';
			break;
		case 'dropdown':
			var HTML = '<select name="'+oField.Name.value+'"'+getCssClass(oField)+getStyle(oField)+'>';
			var Options = oField.Options.value.split(DROPDOWN_FIELD_DELIMITER);
			for (var optIdx = 0;optIdx < Options.length;optIdx++) {
				NameValuePair = Options[optIdx].split(DROPDOWN_VALUE_DELIMITER);
				HTML+= '<option value="'+NameValuePair[0]+'">'+NameValuePair[1]+'</option>';
			}
			HTML+= '</select>';	
			divForm.innerHTML = HTML;
	}
}

function loadUpdate() {
	var CountTabRows = parseInt(document.getElementById('CountTabRows').value);
	for (i = 0;i < CountTabRows;i++) {
		updateFormfield(i);
	}
}

// Style bekommen
function getStyle(oField) {
	var sHtml = '';
	if (oField.Width.length > 0) {
		sHtml = ' style="width:'+oField.Width+'px;"';
	}
	return(sHtml);
}

// Class bekommen
function getCssClass(oField) {
	var sHtml = '';
	if (oField.Class.value.length > 0) {
		sHtml = ' class="'+oField.Class.value;
	}
	return(sHtml);
}

// Objekt für ein Formularfeld erstellen und zurückgeben
function getFormColletion(index) {
	var Collection = new FormCollectionObject(
		document.getElementsByName('ffi_ID[]')[index],
		document.getElementsByName('ffi_Width[]')[index],
		document.getElementsByName('ffi_Required[]')[index],
		document.getElementsByName('ffi_Name[]')[index],
		document.getElementsByName('ffi_Desc[]')[index],
		document.getElementsByName('ffi_Type[]')[index],
		document.getElementsByName('ffi_Class[]')[index],
		document.getElementsByName('ffi_Value[]')[index],
		document.getElementsByName('ffi_Sortorder[]')[index],
		document.getElementsByName('ffi_Deleted[]')[index],
		document.getElementsByName('ffi_Changed[]')[index],
		document.getElementsByName('ffi_Options[]')[index]
	);
	return(Collection);
}

// Objekt für ein Formularfeld
function FormCollectionObject(ID,Width,Required,Name,Desc,Type,Class,Value,Sortorder,Deleted,Changed,Options) {
	this.ID = parseInt(ID);
	try { this.Width = Width.value; } catch (exeption) { this.Width = ''; }
	try { this.Required = Required.value; } catch (exeption) { this.Required = 0; }
	this.Name = Name;
	this.Desc = Desc;
	this.Type = Type;
	this.Class = Class;
	this.Value = Value;
	this.Options = Options;
	try { this.Sortorder = Sortorder.value; } catch (exeption) { this.Sortorder = 0; }
	this.Deleted = parseInt(Deleted.value);
	this.Changed = parseInt(Changed.value);
}

// Edit Dialog anzeigen
function showEditDialog(index) {
	var divPopup = $('editDialog');
	if (!editDialog) {
		Effect.Appear('editDialog');
		new Effect.Opacity('formArea', { from: 1.0, to: 0.4, duration: 0.5 });
		// Umkehren fürs nächstemal
		editDialog = !editDialog;
	} 
	// Daten aber so oder so einfüllen, wenn was anderes gewählt ist
	document.formEdit.fieldDesc.value = document.getElementsByName('ffi_Desc[]')[index].value;
	document.formEdit.fieldValue.value = document.getElementsByName('ffi_Value[]')[index].value;
	document.formEdit.fieldName.value = document.getElementsByName('ffi_Name[]')[index].value;
	document.formEdit.fieldRequired[parseInt(document.getElementsByName('ffi_Required[]')[index].value)].checked = true;
	document.formEdit.fieldClass.value = document.getElementsByName('ffi_Class[]')[index].value;
	document.formEdit.fieldWidth.value = document.getElementsByName('ffi_Width[]')[index].value;
	// Je nach Typ spezielles Edit Fenster zeigen
	var divSpecial = document.getElementById('editDialogSelectbox');
	switch (document.getElementsByName('ffi_Type[]')[index].value) {
		case 'text':
		case 'textarea':
		case 'checkbox':
		case 'radio':
		case 'submit':
		case 'hidden':
			divSpecial.style.display = 'none';
			break;
		case 'dropdown':
			divSpecial.style.display = 'block';
	}
	// Index speichern
	document.formEdit.editingIndex.value = index;
}

// Aktuell bearbeitetes Dropdown zum bearbeiten öffnen
function OpenSelectedDropdown(url) {
	var index = document.formEdit.editingIndex.value;
	var nId = document.getElementsByName('ffi_ID[]')[index].value;
	document.location.href = url + nId;
}

// Edit Dialog verschwinden lassen (nur abbrechen)
function editDialogHide() {
	var divPopup = $('editDialog');
	if (editDialog) {
		Effect.Fade('editDialog');
		new Effect.Opacity('formArea', { from: 0.4, to: 1.0, duration: 0.5 });
	} 
	// Umkehren fürs nächstemal
	editDialog = !editDialog;
}

// Edit Dialog verschwinden lassen (daten zwischenspeichern)
function editDialogSave() {
	var index = parseInt(document.formEdit.editingIndex.value);
	var required = 0;
	if (document.formEdit.fieldRequired[1].checked == true) required = 1
	// Daten speichern
	document.getElementsByName('ffi_Desc[]')[index].value = document.formEdit.fieldDesc.value;
	document.getElementsByName('ffi_Name[]')[index].value = document.formEdit.fieldName.value;
	document.getElementsByName('ffi_Value[]')[index].value = document.formEdit.fieldValue.value;
	document.getElementsByName('ffi_Required[]')[index].value = required;
	document.getElementsByName('ffi_Class[]')[index].value = document.formEdit.fieldClass.value;
	document.getElementsByName('ffi_Width[]')[index].value = document.formEdit.fieldWidth.value;
	document.getElementsByName('ffi_Changed[]')[index].value = 1;
	// Dialog ausblenden
	editDialogHide();
	// Speichern der Angaben
	updateFormfield(index);
	document.formEdit.submit();
}

// Formfeld löschen, hinterlegen usw.
function deleteFormfield(index) {
	// Deleted Objekt holen
	var del = document.getElementsByName('ffi_Deleted[]')[index];
	var table = document.getElementById('formTable');
	// Index aufgrund 7 divs zuviel herausfinden
	if (index > 0) index = (index * 7);
	var tabRow = table.getElementsByTagName('div')[index];
	var now = parseInt(del.value);
	// Status Deleted, wenn 0, undeleted wenn 1
	switch (now) {
		case 0:
			// Als gelöscht markieren
			del.value = 1;
			// Entsprechende Tablerow färben
			tabRow.style.backgroundColor = '#ff9999';
			break;
		case 1:
			// Löschmarkierung aufheben
			del.value = 0;
			tabRow.style.backgroundColor = 'transparent';
			break;
	}
}

// Indexe der Felder aktualisieren
function updateIndizes() {
	var delLinks = document.getElementsByName('deleteLink');
	var updLinks = document.getElementsByName('editDialogLink');
	for (i = 0;i < delLinks.length;i++) {
		delLinks[i].setAttribute('onclick','deleteFormfield('+i+')');
		updLinks[i].setAttribute('onclick','showEditDialog('+i+')');
	}
}

// Updaten der Content Sortierung
function updateContentIndizes() {
	updateSort();
	// Aktiv Checkboxen anpassen
	var els = document.getElementsByName("divActive");
	for (i = 0;i < els.length;i++) {
		var checkbox = els[i].getElementsByTagName("input")[0];
		checkbox.name = 'active_'+i;
	}
}

Event.observe(window,'load',function() {
	var popup = $('newElementPopup');
	$$('body')[0].insert({
		bottom: popup
	});
})