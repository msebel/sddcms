// Register the related command.
FCKCommands.RegisterCommand( 
	'sddcode', 
	new FCKDialogCommand( 
		'sddcode', 
		'BB-Code', 
		FCKPlugins.Items['sddcode'].Path + 'fck_sddcode.php', 
		400, 
		300 
	) 
);

// Create the "Mediamanager" toolbar button.
var oSddCodeItem = new FCKToolbarButton( 'sddcode', 'BB-Code' ) ;
oSddCodeItem.IconPath = FCKPlugins.Items['sddcode'].Path + 'sddcode.gif' ;

FCKToolbarItems.RegisterItem( 'sddcode', oSddCodeItem ) ;