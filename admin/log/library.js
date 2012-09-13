// User per Ajax updaten
function updateUser(nUser,answerID,nMenuID) {
	ajaxRequest(
		'ajax/user.php?id='+nMenuID+'&user='+nUser,
		'user_' + answerID
	);
}

// Menu per Ajax updaten
function updateMenu(nMenu,answerID,nMenuID) {
	ajaxRequest(
		'ajax/menu.php?id='+nMenuID+'&menu='+nMenu,
		'menu_' + answerID
	);
}