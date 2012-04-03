/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileSave plugin.
 */

(function()
{
	var saveCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,

		exec : function( editor )
		{
			var $form = editor.element.$.form;

			if ( $form )
			{
				try
				{
					$form.submit();
				}
				catch( e )
				{
					// If there's a button named "submit" then the form.submit
					// function is masked and can't be called in IE/FF, so we
					// call the click() method of that button.
					if ( $form.submit.click )
						$form.submit.click();
				}
			}
		}
	};

	var pluginName = 'saveAndExit';

	// Register a plugin named "save".
	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
      var saveMsgs = {
        en: {
          saveAndExit: 'Save and Exit'
        },
        de: {
          saveAndExit: 'Save and Exit'
        }
      };

      CKEDITOR.tools.extend(editor.lang, saveMsgs[editor.langCode] || saveMsgs['en']);
      
			var command = editor.addCommand( pluginName, saveCmd );
			command.modes = { wysiwyg : !!( editor.element.$.form ) };

			editor.ui.addButton( 'SaveAndExit',
				{
					label : editor.lang.saveAndExit,
					command : pluginName,
          icon: this.path + 'images/icon_saveexit.gif'
				});
		}
	});
})();
