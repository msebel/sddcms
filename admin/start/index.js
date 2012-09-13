function selectUser(oForm) {
	var oValues = oForm.userselect.value.split('$$');
	oForm.username.value = oValues[0];
	oForm.password.value = oValues[1];
}