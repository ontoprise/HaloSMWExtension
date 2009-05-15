// Register our toolbar buttons.
var tbButton = new FCKToolbarButton( 'MW_MediaUpload', 'UploadMedia', 'Upload media' ) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'mediaupload/images/icon_mediaupload.gif' ;
FCKToolbarItems.RegisterItem( 'MW_MediaUpload', tbButton );

FCKCommands.RegisterCommand( 'MW_MediaUpload', new FCKDialogCommand( 'MW_MediaUpload', 'UploadMedia', FCKConfig.PluginsPath + 'mediaupload/dialogs/uploadmedia.html', 400, 330 ) ) ;