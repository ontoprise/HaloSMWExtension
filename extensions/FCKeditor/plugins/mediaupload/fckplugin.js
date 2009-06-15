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
	var uri = window.parent.wgServer + window.parent.wgScriptPath + "/index.php?title=Special:UploadWindow";
	var article = window.parent.wgTitle;
	if (window.parent.wgPageName == 'Special:AddData') {
		//obviously we are in Special:AddData and wgTitle is not containing what we're loooking for...
		// try target= ...first
		var regexS = "[\\?&]target=([^&#]*)";
		var regex = new RegExp( regexS );
		var result = regex.exec( window.parent.location.href );
		if (result == null) {
			//target not found, it has to be the path now!
			article = window.parent.location.pathname.match( /[^\/]+\/?$/ )[0];
		}
		else {
			article = result[1];
		}
	}
	top.fb.loadAnchor(uri+'&sfInputID=myWpDestFile&RMUpload[RelatedArticles]='+article+'&wpIgnoreWarning=true', 'width:600 height:660', 'Uploading files');
	// do stuff here if you want
}
FCKCommands.RegisterCommand( 'MW_MediaUpload', OpenUploadWindowCommand) ;