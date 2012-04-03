/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileSave plugin.
 */

(function($)
{  
  var editor = mw.config.get('wgCKeditorInstance');

  var pluginName = 'saveAndContinue';

  var saveCmd =
  {
    modes : {
      wysiwyg:1,
      source:1
    },
    readOnly : 1,

    getEditToken: function(callbackFunction, calbackArguments) {
      $.getJSON(
        mw.config.get('wgScriptPath') + '/api.php?',
        {
          action: 'query',
          prop: 'info',
          intoken: 'edit',
          titles: 'Main Page',
          indexpageids: '',
          format: 'json'
        },
        function( data ) {
          if ( data.query.pages && data.query.pageids ) {
            var pageid = data.query.pageids[0];
            var wgEditToken = data.query.pages[pageid].edittoken;
            callbackFunction(calbackArguments.title, calbackArguments.text, wgEditToken);
          }
        }
        )
    },
    saveWikiPage: function(title, text, editToken){
      $.ajax({
        url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php',
        data: {
          format: 'json',
          action: 'edit',
          title: title,
          text: text,
          token: editToken,
          starttimestamp: +new Date
        },
        dataType: 'json',
        type: 'POST',
        success: function(data, textStatus, jqXHR) {
          if ( data && data.edit && data.edit.result === 'Success' ) {
            alert(editor.lang.pageSaveSuccess);
          }
          else if ( data && data.error ) {
            alert(editor.lang.pageSaveError + data.error.code + ': ' + data.error.info);
          }
          else {
            alert(editor.lang.pageSaveUnknownError);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          alert(editor.lang.pageSaveRequestFailed);
        },
        complete: function(){
          editor.getCommand(pluginName).setState( CKEDITOR.TRISTATE_OFF );
        }
      });
    },
    exec : function( editor )
    {
      this.setState( CKEDITOR.TRISTATE_ON );

      this.getEditToken(this.saveWikiPage, {
        title: mw.config.get( 'wgPageName' ),
        text: editor.getData()
      });
    }
  };
  

  // Register a plugin named "save".
  CKEDITOR.plugins.add( pluginName,
  {
    
    init : function( editor )
    {
      var saveAndContinueMsgs = {
        en: {
          saveAndContinue: 'Save and Continue',
          pageSaveRequestFailed: 'Save request failed',
          pageSaveUnknownError: 'Save request failed with unknown error',
          pageSaveError: 'Save error: ',
          pageSaveSuccess: 'Page is saved successfully'
        },
        de: {
          saveAndContinue: 'Save and Continue',
          pageSaveRequestFailed: 'Save request failed',
          pageSaveUnknownError: 'Save request failed with unknown error',
          pageSaveError: 'Save error: ',
          pageSaveSuccess: 'Page is saved successfully'
        }
      };

      CKEDITOR.tools.extend(editor.lang, saveAndContinueMsgs[editor.langCode] || saveAndContinueMsgs['en']);

      var command = editor.addCommand( pluginName, saveCmd );
      command.modes = {
        wysiwyg : !!( editor.element.$.form )
      };

      editor.ui.addButton( 'SaveAndContinue',
      {
        label : editor.lang.saveAndContinue,
        command : pluginName,
        className: 'cke_button_save'
      });
    }
  });
})(jQuery);
