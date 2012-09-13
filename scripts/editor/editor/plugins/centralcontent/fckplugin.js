// Register the related command.
FCKCommands.RegisterCommand( 
	'centralcontent', 
	new FCKDialogCommand( 
		'centralcontent', 
		'Zentrale Inhalte / Central content', 
		FCKPlugins.Items['centralcontent'].Path + 'fck_centralcontent.php', 
		400, 
		300 
	) 
);

// Create the "Mediamanager" toolbar button.
var oCentralContentItem = new FCKToolbarButton( 'centralcontent', 'Zentrale Inhalte / Central content' ) ;
oCentralContentItem.IconPath = FCKPlugins.Items['centralcontent'].Path + 'centralcontent.gif' ;

FCKToolbarItems.RegisterItem( 'centralcontent', oCentralContentItem ) ;