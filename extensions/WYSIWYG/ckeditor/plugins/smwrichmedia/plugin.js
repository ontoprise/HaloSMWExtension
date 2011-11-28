/*
 * Copyright (C) ontoprise GmbH
 *
 * Vulcan Inc. (Seattle, WA) and ontoprise GmbH (Karlsruhe, Germany)
 * expressly waive any right to enforce any Intellectual Property
 * Rights in or to any enhancements made to this program.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

if (('SMW_RM_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
// Richmedia extension is installed

var commandDefinition =
{
	preserveState : true,
	editorFocus : false,
	canUndo : false,
	modes : { wysiwyg : 1, source : 1 },

	exec: function( editor ) {
		//get URL first
		var src = window.parent.wgServer + window.parent.wgScript + '?title=Special:UploadWindow';
		var article = window.parent.wgTitle;
		if (window.parent.wgNamespaceNumber == -1 &&
            window.parent.wgCanonicalSpecialPageName == 'FormEdit')  {
			//obviously we are in Special:FormEdit and wgTitle is not containing what we're loooking for...
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
		var url = src + '&RMUpload[RelatedArticles]='+article+'&wpIgnoreWarning=true';
		jQuery.fancybox({
			'href' : url,
			'width' : '75%',
			'height' : '75%',
			'autoScale' : false,
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'type' : 'iframe',
			'overlayColor'  : '#222',
			'overlayOpacity' : '0.8',
			'hideOnContentClick' : true
		});
	}
};

CKEDITOR.plugins.add('smw_richmedia', {
	requires : [ 'mediawiki'],
	init : function( editor ) {
		editor.addCommand( 'SMWrichmedia', commandDefinition);
		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'SMWrichmedia', {
				label : 'Upload media',
				command : 'SMWrichmedia',
				icon: this.path + 'images/icon_mediaupload.gif'
			});
		}
	}
});

} else {
// Richmedia extension is not installed
CKEDITOR.plugins.add( 'smw_richmedia',
{
	requires : [ 'dialog' ],
	init : function( editor )
	{
		var command = editor.addCommand( 'SMWrichmedia', new CKEDITOR.dialogCommand( 'SMWrichmedia' ) );
		command.canUndo = false;

		editor.ui.addButton( 'SMWrichmedia',
			{
				label : 'Upload media',
				command : 'SMWrichmedia',
                icon: this.path + 'images/icon_mediaupload.gif'
			});

		CKEDITOR.dialog.add( 'SMWrichmedia', this.path + 'dialogs/teaser.js' );
	}
});

}
