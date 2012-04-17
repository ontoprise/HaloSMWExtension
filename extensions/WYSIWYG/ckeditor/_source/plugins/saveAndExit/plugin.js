/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileSave plugin.
 */

(function($)
{
  var saveCmd =
  {
    modes : {
      wysiwyg:1,
      source:1
    },
    readOnly : 1,

    exec : function( editor )
    {
      var form = editor.element.$.form;

      if ( form )
      {
        editor.resetDirty();
        editor.getCommand(pluginName).setState(CKEDITOR.TRISTATE_ON);
        //submit by clicking mediawiki "Save" button
        var submitButton = $('#wpSave');
        if ( submitButton.length ){
          submitButton.click();
        }
        else{ //submit by clicking the first submit button
          submitButton = $('[type="submit"]', $(form));
          if ( submitButton.length ){
            submitButton = submitButton.eq(0);
            submitButton.click();
          }
          else{
            try
            { //submit by invocking jquery form submit method
              $(form).submit();
            }
            catch( e ){
              try
              { //submit by invocking native form submit method
                form.submit();
              }
              catch( e ){}
            }
          }
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
      
      editor.addCommand( pluginName, saveCmd );

      editor.ui.addButton( 'SaveAndExit',
      {
        label : editor.lang.saveAndExit,
        command : pluginName,
        icon: this.path + 'images/icon_saveexit.gif'
      });
    }
  });
})(jQuery);
