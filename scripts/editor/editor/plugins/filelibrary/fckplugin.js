// Register the related command.
FCKCommands.RegisterCommand( 
	'filelibrary', 
	new FCKDialogCommand( 
		'filelibrary', 
		'Datei Bibliothek / File Library', 
		FCKPlugins.Items['filelibrary'].Path + 'fck_filelibrary.php', 
		400, 
		300 
	) 
);

// Create the "Mediamanager" toolbar button.
var oFileLibrayItem = new FCKToolbarButton( 'filelibrary', 'Datei Bibliothek / File Library' ) ;
oFileLibrayItem.IconPath = FCKPlugins.Items['filelibrary'].Path + 'filelibrary.gif' ;

FCKToolbarItems.RegisterItem( 'filelibrary', oFileLibrayItem ) ;