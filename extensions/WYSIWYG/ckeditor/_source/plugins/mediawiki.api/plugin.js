
(function($)
{
  //if mw write api is disabled then return
  if(!mw.config.get('wgEnableWriteAPI')){
    return;
  }

  var editor;

  var pluginName = 'mediawiki.api';

  var editToken = '';

  var saveAndContinueCommand = 'saveAndContinue';
  
  var moveWikiPageCommand = 'moveWikiPage';

  var msgDivId = 'wysiwyg-status-msg';

  var lastSave;

  var util = {
    getEditor: function(){
      if(!editor){
        editor = mw.config.get('wgCKeditorInstance');
      }
      return editor;
    },    

    getWikieditor: function(){
      if(!(util.wikieditor && util.wikieditor.length)){
        util.wikieditor = $('#toolbar');
        if(!util.wikieditor.length){
          util.wikieditor = $('#wpTextbox1');
          if(!util.wikieditor.length){
            util.wikieditor = $('#free_text');
          }
        }
      }

      return util.wikieditor;
    },
    
    showMsg: function(msg, error){
      var msgDivCss = {
        'color' : '#269FB2'
      };

      var msgDivErrorCss = {
        'color' : 'red'
      };

      if(util.showMsgTimeout){
        window.clearTimeout(util.showMsgTimeout);
      }
      var msgDiv = $('#' + msgDivId);

      msgDiv.fadeOut(500, function(){
        msgDiv.text(msg);
        msgDiv.css(error ? msgDivErrorCss : msgDivCss);
        msgDiv.fadeIn(500);

        util.showMsgTimeout = window.setTimeout(function(){
          msgDiv.fadeOut(500, function(){
            msgDiv.text(mw.msg('wysiwyg-last-save') + ': ' + lastSave);
            msgDiv.css(msgDivCss);
            msgDiv.fadeIn(500);
          });
        }, 3000);
      });
    },
    setupMsgElement: function(editor){
      if(!$('#' + msgDivId).length){
        editor = editor || util.getEditor();
      
        var msgDiv = $('<div/>').attr('id', msgDivId);
        var css = {
          'font-family': 'tahoma'
        };
        msgDiv.css(css);
        util.getWikieditor().before(msgDiv);
        util.showMsg(mw.msg('wysiwyg-last-save') + ': ' + lastSave);
      }
    },
    removeTitleElement: function(){
      var span = $('#rename-title-span');
      if(span.length){
        span.remove();
        $('#rename-title-input').remove();
        $('#rename-title-save-btn').remove();
        $('#rename-title-cancel-btn').remove();
        $('#ca-nstab-main').show();
      }
    },
    setupTitleElement: function(){
      if(!CKEDITOR.mw.isMoveAllowed()){
        return;
      }
      if(!$('#rename-title-span').length){
        //rename of Category, Property or Template is not supported
        var namespaces = mw.config.get('wgFormattedNamespaces');
        var namespaceId = mw.config.get('wgNamespaceNumber');

        if(namespaces && namespaces[namespaceId] && $.inArray(mw.config.get('wgNamespace'), ['Category', 'Property', 'Template']) !== -1){
          return;
        }
      
        var titleDiv = $('#ca-nstab-main');
        var css = {
          'float' : 'left'
        };
        var linkCss = {
          'background': 'url("data:image/gif;base64,R0lGODlhDwAQANUlAOWLIOSIG+2JEq5kcu+LFOujT+yoWO2sYe2uZOuGD+umVZOtu70xQZaWlu6LFMeqlPPIoeypW+6udemdRPCzho+quuWxt+WJHae+y+mcQdCYUdSwgOaRLdmRmaxkdO3Lz+qiTJqzw+SGFu6KE+uHEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACUALAAAAAAPABAAAAZFwJJwSBx+ikihh2FJEiuhQccpDGgWGGopQEhstAEHCQEekQ7aizmiBZgNbbMiTiq0BSSQloOfaEsSIhl/DxQQfyUNiEJBADs=") no-repeat scroll left center transparent',
          'padding': '2px 0 2px 18px'
        };
        var spanCSS = {
          'float': 'left',
          'font-weight': 'normal',
          'margin-left': '1em',
          'margin' : '5px 20px 5px -22px'
        };

        var editSpan = $('<span/>').attr('id', 'rename-title-span').css(spanCSS);
        var editLink = $('<a/>').attr('href', '#').text('rename').css(linkCss);
        var input = $('<input/>').attr('id', 'rename-title-input').css(css).hide();
        var saveButton = $('<button/>').text('Save Changes').attr('id', 'rename-title-save-btn').css(css).hide();
        var cancelButton = $('<button/>').text('Cancel').attr('id', 'rename-title-cancel-btn').css(css).hide();

        titleDiv.after(editSpan.append(editLink));
        titleDiv.after(cancelButton).after(saveButton).after(input);
      
        editLink.click(function(event){
          event.preventDefault();
          editLink.hide();
          titleDiv.hide();
          input.show();
          saveButton.show();
          cancelButton.show();
          input.val(mw.config.get('wgTitle'));
        });

        saveButton.click(function(){
          var editor = util.getEditor();
          editor.execCommand(moveWikiPageCommand, {
            toTitle: input.val()
          });
        });

        cancelButton.click(function(){
          input.hide();
          saveButton.hide();
          cancelButton.hide();
          editLink.show();
          titleDiv.show();
        });
      }
    },
    getEditToken: function(callbackFunction, calbackArguments) {
      if(editToken){
        callbackFunction(editToken, calbackArguments);
      }
      else{
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
              editToken = data.query.pages[pageid].edittoken;
              callbackFunction(editToken, calbackArguments);
            }
          }
          )
      }
    },
    saveWikiPage: function(editToken, args, callbackFunction){
      if(!util.getEditor().checkDirty()){
        util.showMsg(mw.msg('wysiwyg-no-changes'));
        util.getEditor().getCommand(saveAndContinueCommand).setState( CKEDITOR.TRISTATE_OFF );
        return;
      }
      var title = args && args.title || mw.config.get('wgPageName');
      var text = args && args.text || util.getEditor().getData();
      
      $.ajax({
        url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php',
        data: {
          format: 'json',
          action: 'edit',
          title: title,
          text: text,
          token: editToken,
          recreate: true
        //          starttimestamp: +new Date
        },
        dataType: 'json',
        type: 'POST',
        success: function(data, textStatus, jqXHR) {
          editor = util.getEditor();
          if ( data && data.edit && data.edit.result === 'Success' ) {
            lastSave = new Date().toLocaleString();
            $.cookie('wysiwyg-last-save-' + mw.user.name() + '-' + mw.config.get('wgPageName'), lastSave);
            util.showMsg(mw.msg('wysiwyg-save-successful'));
            util.getEditor().resetDirty();
            callbackFunction && typeof callbackFunction === 'function' && callbackFunction(editToken, args);
          }
          else if ( data && data.error ) {
            util.showMsg(mw.msg('wysiwyg-save-error') + ':\n' + data.error.info, true);
          }
          else {
            util.showMsg(mw.msg('wysiwyg-failed-unknown-error'), true);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          util.showMsg(util.getEditor().lang.pageSaveRequestFailed, true);
        },
        complete: function(){
          util.getEditor().getCommand(saveAndContinueCommand).setState( CKEDITOR.TRISTATE_OFF );
        }
      });
    },
    moveWikiPage: function(moveToken, args){
      var newTitle = args.toTitle;
      if(util.getEditor().checkDirty() && confirm(mw.msg('wysiwyg-save-before-rename'))){
        util.saveWikiPage(moveToken, args, function(){
          util.moveWikiPage(moveToken, args);
        });
        return;
      }
      $.ajax({
        url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php',
        data: {
          format: 'json',
          action: 'move',
          from: mw.config.get('wgPageName'),
          to: newTitle,
          reason: 'wysiwyg move ' + mw.user.name() + ' ' + new Date().toUTCString(),
          movetalk: true,
          movesubpages: true,
          token: moveToken
        },
        type: 'POST',
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
          var editor = util.getEditor();
          if ( data && data.move && textStatus === 'success' ) {
            util.showMsg(mw.msg('wysiwyg-move-successful'));
            var url = mw.util.wikiGetlink( newTitle );
            url += url.indexOf('?') > 0 ? '&' : '?';
            url += $.param({
              action: 'edit',
              mode: 'wysiwyg'
            });
            location.replace(url);
          }
          else if ( data && data.error ) {
            util.showMsg(mw.msg('wysiwyg-move-error') + ':\n' + data.error.info, true);
          }         
          else {
            util.showMsg(mw.msg('wysiwyg-move-failed-unknown-error'), true);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          util.showMsg(mw.msg('wysiwyg-move-failed') + '.\n\n' + errorThrown || textStatus, true);
        },
        complete: function(){
          util.getEditor().getCommand(moveWikiPageCommand).setState( CKEDITOR.TRISTATE_OFF );
        }
      });
    }    
  };  

  var saveCmd =
  {
    modes : {
      wysiwyg:1,
      source:1
    },
    readOnly : 1,   
    
    exec : function( editorInstance )
    {
      this.setState( CKEDITOR.TRISTATE_ON );

      util.getEditToken(util.saveWikiPage, {
        title: mw.config.get( 'wgPageName' ),
        text: editorInstance.getData()
      });
    }
  };

  var moveCmd =
  {
    modes : {
      wysiwyg:1,
      source:1
    },
    readOnly : 1,

    exec : function( editorInstance, data)
    {
      this.setState( CKEDITOR.TRISTATE_ON );

      util.getEditToken(util.moveWikiPage, data);
    }
  };
  

  // Register a plugin
  CKEDITOR.plugins.add( pluginName,
  {
    
    init : function( editor )
    {
      editor.addCommand( saveAndContinueCommand, saveCmd );      

      editor.ui.addButton( 'SaveAndContinue',
      {
        label : mw.msg('wysiwyg-save-and-continue'),
        command : saveAndContinueCommand,
        className: 'cke_button_save'
      });

      editor.addCommand( moveWikiPageCommand, moveCmd );

      editor.on('instanceReady', function(){
        if(editor.mode === 'wysiwyg'){
          lastSave = $.cookie('wysiwyg-last-save-' + mw.user.name() + '-' + mw.config.get('wgPageName')) || mw.msg('wysiwyg-never');
          util.setupTitleElement(editor);
          util.setupMsgElement(editor);
        }
      });

      editor.on('destroy', function(){
        util.removeTitleElement();
      });
      
    //      editor.ui.addButton( 'MoveWikiPage',
    //      {
    //        label : editor.lang.moveWikiPage,
    //        command : moveWikiPageCommand
    //      });
    }
  });
})(jQuery);
