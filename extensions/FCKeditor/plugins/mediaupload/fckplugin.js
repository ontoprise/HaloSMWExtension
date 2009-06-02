// Register our toolbar buttons.
var tbButton = new FCKToolbarButton( 'MW_MediaUpload', 'UploadMedia', 'Upload media' ) ;
tbButton.IconPath = FCKConfig.PluginsPath + 'mediaupload/images/icon_mediaupload.gif' ;
FCKToolbarItems.RegisterItem( 'MW_MediaUpload', tbButton );

//FCKCommands.RegisterCommand( 'MW_MediaUpload', new FCKDialogCommand( 'MW_MediaUpload', 'UploadMedia', FCKConfig.PluginsPath + 'mediaupload/dialogs/uploadmedia.html', 400, 330 ) ) ;

var OpenUploadWindowCommand=function(){};
OpenUploadWindowCommand.prototype.Execute=function(){ }
OpenUploadWindowCommand.GetState=function() {
        return FCK_TRISTATE_OFF; //we dont want the button to be toggled
}
OpenUploadWindowCommand.Execute=function() {
	var article = window.parent.wgTitle;
	top.fb.loadAnchor('index.php?title=Special:UploadWindow&sfInputID=myWpDestFile&RMUpload[RelatedArticles]='+article+'&wpIgnoreWarning=true', 'width:600 height:660', 'Uploading files');
	// do stuff here if you want
}
FCKCommands.RegisterCommand( 'MW_MediaUpload', OpenUploadWindowCommand) ;