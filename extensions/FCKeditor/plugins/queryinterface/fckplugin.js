// Register our toolbar buttons.
var tbButton = new FCKToolbarButton( 'SMW_QueryInterface', 'QueryInterface', 'Query Interface' ) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'queryinterface/images/tb_icon_ask.gif' ;
FCKToolbarItems.RegisterItem( 'SMW_QueryInterface', tbButton );

FCKCommands.RegisterCommand( 'SMW_QueryInterface', new FCKDialogCommand( 'SMW_QueryInterface', 'QueryInterface', FCKConfig.PluginsPath + 'queryinterface/dialogs/queryinterface.php', 800, 600 ) ) ;