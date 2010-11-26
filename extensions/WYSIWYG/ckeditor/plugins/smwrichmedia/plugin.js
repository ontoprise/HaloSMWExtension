CKEDITOR.plugins.add('smw_richmedia', {

    requires : [ 'mediawiki', 'dialog' ],

	init : function( editor )
	{
		editor.addCommand( 'SMWrichmedia', new CKEDITOR.dialogCommand( 'SMWrichmedia' ) );
        CKEDITOR.dialog.add( 'SMWrichmedia', this.path + 'dialogs/smwRmUploadDlg.js');

        if ( editor.ui.addButton ) {
            editor.ui.addButton( 'SMWrichmedia',
                {
                    label : 'Upload media',
                    command : 'SMWrichmedia',
                    icon: this.path + 'images/icon_mediaupload.gif'
                });
        }
	}
});