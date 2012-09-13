// Register the related command.
FCKCommands.RegisterCommand( 
	'mediamanager', 
	new FCKDialogCommand( 
		'mediamanager', 
		'Medienmanager', 
		FCKPlugins.Items['mediamanager'].Path + 'fck_mediamanager.php', 
		400, 
		300 
	) 
);

// Create the "Mediamanager" toolbar button.
var oMediamanagerItem = new FCKToolbarButton( 'mediamanager', 'Medienmanager' ) ;
oMediamanagerItem.IconPath = FCKPlugins.Items['mediamanager'].Path + 'mediamanager.gif' ;

FCKToolbarItems.RegisterItem( 'mediamanager', oMediamanagerItem ) ;