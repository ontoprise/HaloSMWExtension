// Register our toolbar buttons.
if (typeof window.parent.RichMediaPage != 'undefined') {
    var tbButton = new FCKToolbarButton( 'MW_MediaUpload', 'UploadMedia', 'Upload media' ) ;
    tbButton.IconPath = FCKConfig.PluginsPath + 'mediaupload/images/icon_mediaupload.gif' ;
    FCKToolbarItems.RegisterItem( 'MW_MediaUpload', tbButton );
}
else {
    var tbButton = new FCKToolbarButton( 'MW_MediaUpload', ' ', ' ' ) ;
    tbButton.IconPath = FCKConfig.PluginsPath + 'mediawiki/images/tb_icon_blank.gif' ;
    FCKToolbarItems.RegisterItem( 'MW_MediaUpload', tbButton );
}

var OpenUploadWindowCommand=function(){};
OpenUploadWindowCommand.Execute=function(){ }
OpenUploadWindowCommand.GetState=function() {
        return FCK_TRISTATE_OFF; //we dont want the button to be toggled
}
if (typeof window.parent.RichMediaPage != 'undefined') {
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
	top.fb.loadAnchor(uri+'&RMUpload[RelatedArticles]='+article+'&wpIgnoreWarning=true', 'width:600 height:660', 'Uploading files');
	// do stuff here if you want
    }
}
FCKCommands.RegisterCommand( 'MW_MediaUpload', OpenUploadWindowCommand) ;