
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
        var saveBtnCss = {
          'background': 'transparent',
          'background-repeat': 'no-repeat',
          'background-position': 'center center',
          'background-image': 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAANkE3LLaAgAAAjpJREFUeJy90k1IkwEcx/Hvs2evMt1KJ741Wy0zexMy0US6WHSKgii8pAUZKUEFubSgkjKRQgT1kL0hZNChMpJCi/RQGQll0sBE09wWw5c2c5rbs+fptIgutQ59758//8MP/nNCcsWSm5ajS+8C6qh1con5So+3W3ni6lTiS81XAe1f45QDsXV3JloVT2BC8c57lGZng6LZJVz8+Ub8fpVD0Mri1DVqf8dpZYYLZ6pOOjJi1jDqHyIoS7xwdyMbla1qANNO7fHDx0rrZPV3WufbpOl26iM4/YjuXEXlwdNWvZ3xuY9IssKDT23c6+0l3McjUVfEoe2Vm5vyEwuJ1yVgyRO3jflHfIFBXtvK1dUljt016ZpM/MFJZiUfTyfbed7/Ct9t6hmiRkzeR2Moddo6G5xBJYZJjEkiMUcoIvtrzo7iLeUpOhu+oJcpycPA3DPefXiP6zoN0gAOQBYRyLRslAqmtS7coSF8iguNQVFZs0yrtYIGb2iE0eBb3OFBvMMzOBuk2oV+qgAZQFz8zMvwPGkrc3XZQlyIb4KfsNqPUYhFL6pRqWQMOjULEwJ9l3yXZ/uojmAAEQgFhukKLsq2rLyE9XqTiiTtMuwxWaQb7Cw3ZjDjCtBx1tk41SNX/oojBwBCfiddQUlalVtgX5tqsmHVrWCdKZfxL2M0nXrY4nksnQDCf9pL3IZy/f1m917ljXxD6fCeV+zF2ugWB5gLHcbOFtceZVOZ4RagjwZHSrLkUwHE/guOqh90ld9+870vDgAAAABJRU5ErkJggg==)',
          'backround-color': 'transparent',
          'border': 'medium none',
          'height': '16px',
          'width': '16px',
          'float': 'left',
          'margin': '5px 5px 5px -40px',
          'cursor': 'pointer'
        };
        var cancelBtnCss = {
          'background': 'transparent',
          'background-repeat': 'no-repeat',
          'background-position': 'center center',
          'background-image': 'url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHMSURBVHjapFO/S0JRFP4UIUJIqMWgLQzalAyKIN4TxNXJoZaGIPwHXNMt/A+C1pZabKgQQd9kQ4pS0KBUi4MNNgT+ev54nXPeVTRoqQvfu+ee7zvnnnPvfQ7LsvCf4ZLvSZi/ScIpQScYv+g1QoGQEv15zk4wHo0k2BmJYJzNskB3XuTnkoyPQxKsNLwRnJTEycZwOJRgDAbgmdYF82hfmwSzzb4fGkni4DPoHu5K9sVw2I5wu9HNZKDagXDRKNBuy6Kbywm3ePlgSAUD0zQI+tftLdDrAa0WOIB8BYYEk4851rCWY1Qb1IJpYum6bNCsf97f0xZdoNHAUiwmYJt9zLFGaTFNMOj3ZbF882yQrX9ks0CnA9RqNshmH3OsmY1xqRampz21PR6g2bRtr3dOM6ubq+B9b1Uju7AWjwNvb3YVDLLZxxxrZmPkFurbK9NH4kskgHxeyHqpJLMvGLS3DYVQT6cnt2P4HluY3ILGpy3Bd3dy2i/F4uS0dbbldohjjbod+51wBU+bC5Z1dWZZBzsCXhM05hSviUbxrJU1cdJCZcMlTzng96NSrUqJZM89ZfJLizOaVKA2TEqC8rrjTz/T1quq4D/jW4ABAF7lQOO4C9PnAAAAAElFTkSuQmCC)',
          'backround-color': 'transparent',
          'border': 'medium none',
          'height': '16px',
          'width': '16px',
          'float': 'left',
          'margin': '5px 30px 5px -20px',
          'cursor': 'pointer'
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
        var inputCss = {
          'float' : 'left',
          'padding': '2px 46px 2px 2px',
          'border': '1px solid #116988',
          'border-radius': '5px',
          'height': '20px'
        };

        var editSpan = $('<span/>').attr('id', 'rename-title-span').css(spanCSS);
        var editLink = $('<a/>').attr('href', '#').text(mw.msg('wysiwyg-rename')).css(linkCss);
        var input = $('<input/>').attr('id', 'rename-title-input').css(inputCss).hide();
        var saveButton = $('<button/>').attr('id', 'rename-title-save-btn').css(saveBtnCss).hide();
        var cancelButton = $('<button/>').attr('id', 'rename-title-cancel-btn').css(cancelBtnCss).hide();

        titleDiv.after(editSpan.append(editLink));
        titleDiv.after(cancelButton).after(saveButton).after(input);
      
        editLink.click(function(event){
          event.preventDefault();
          editLink.hide();
          titleDiv.hide();
          input.show();
          cancelButton.show();
          input.val(mw.config.get('wgTitle'));
          input.css('background-color', 'white');
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

        input.change(function(){
          util.checkTitleExists($(this).val());
        });

        input.bind('paste', function(){
          util.checkTitleExists($(this).val());
        });

        input.bind('keyup', function(){
          var thisElement = $(this);
          util.renameTimeout && window.clearTimeout(util.renameTimeout);
          util.renameTimeout = window.setTimeout(function(){
            util.checkTitleExists(thisElement.val());
          }, 500);
        });
      }
    },
    titleInvalid: function(){
      $('#rename-title-input').css('background-color', '#FF6666');
      $('#rename-title-save-btn').hide();

    },
    titleValid: function(){
      var input = $('#rename-title-input');
      if(input.length && input.is(':visible')){
        input.css('background-color', 'lightgreen');
        $('#rename-title-save-btn').show();
      }
    },
    validateTitle: function(title){
      if(!(title && title.length)){
        util.titleInvalid();
        util.showMsg(mw.msg('wysiwyg-title-empty'), true);
        return false;
      }
      if(title.indexOf('|') > -1){
        util.titleInvalid();
        util.showMsg(mw.msg('wysiwyg-title-invalid'), true);
        return false;
      }

      return true;
    },
    checkTitleExists: function(title){
      if(util.validateTitle(title)){
        $.ajax({
          url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php',
          data: {
            format: 'json',
            action: 'query',
            titles: title
          },
          dataType: 'json',
          type: 'POST',
          success: function(data, textStatus, jqXHR) {
            if(data && data.query && data.query.pages){
              $.each(data.query.pages, function(key, page){
                if(page.invalid === ''){
                  util.titleInvalid();
                  util.showMsg(mw.msg('wysiwyg-title-invalid'), true);
                }
                else if(page.missing === '' && !page.id){
                  util.titleValid();
                }
                else{
                  util.titleInvalid();
                  util.showMsg(mw.msg('wysiwyg-title-exists'), true);
                }

                return false;
              });
            }
          },
          error: function(xhr, textStatus, errorThrown) {
            util.titleInvalid();
            util.showMsg(mw.msg('wysiwyg-title-check-failed') + ': ' + errorThrown || textStatus, true);
          }
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
            util.showMsg(mw.msg('wysiwyg-save-error') + ':' + data.error.info, true);
          }
          else {
            util.showMsg(mw.msg('wysiwyg-failed-unknown-error'), true);
          }
        },
        error: function(xhr, textStatus, errorThrown) {
          util.showMsg(mw.msg('wysiwyg-save-failed') + ': ' + errorThrown || textStatus, true);
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
