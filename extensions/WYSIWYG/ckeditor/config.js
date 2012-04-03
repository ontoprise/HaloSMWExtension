/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

//fix: when this script is minified quotes are stripped from 'SMW_HALO_VERSION' string
SMW_HALO_VERSION = 'SMW_HALO_VERSION';

if (!String.prototype.InArray) {
  String.prototype.InArray = function(arr) {
    for(var i=0;i<arr.length;i++) {
      if (arr[i] == this)
        return true;
    }
    return false;
  };
}

CKEDITOR.editorConfig = function( config )
{
  // Define changes to default configuration here. For example:
//   config.language = 'fr';
  // config.uiColor = '#AADC6E';
  var showTbButton = (typeof window.parent.wgCKEditorHideDisabledTbutton == 'undefined');
    
  var extraPlugins = "mediawiki,mwtemplate";

  config.toolbar = 'Wiki';
  // var origToolbar = CKEDITOR.config.toolbar_Full

  // SMWHalo extension
  var qiButton;
//  var stbToolbarButtons = [];
  if ( ('SMW_HALO_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions) || showTbButton) {
    CKEDITOR.plugins.addExternal( 'smw_qi', CKEDITOR.basePath + 'plugins/smwqueryinterface/' );
    //        CKEDITOR.plugins.addExternal( 'smw_toolbar', CKEDITOR.basePath + 'plugins/smwtoolbar/' );
    extraPlugins += ",smw_qi,smwtoolbar";
    qiButton = 'SMWqi';
//    stbToolbarButtons = ['SMWtoolbar','SMWAddProperty', 'SMWAddCategory'];
  }
  // DataImport extension
  var wsButton;
  if ( ('SMW_DI_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions) || showTbButton) {
    CKEDITOR.plugins.addExternal( 'smw_webservice', CKEDITOR.basePath + 'plugins/smwwebservice/' );
    extraPlugins += ",smw_webservice";
    wsButton = 'SMWwebservice';
  }
  // SemanticRule extension
  if (('SEMANTIC_RULES_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
    CKEDITOR.plugins.addExternal( 'smw_rule', CKEDITOR.basePath + 'plugins/smwrule/' );
    extraPlugins += ",smw_rule";
  }
  // Richmedia extension
  var rmButton;
  if ( ('SMW_RM_VERSION').InArray(window.parent.wgCKeditorUseBuildin4Extensions) || showTbButton) {
    CKEDITOR.plugins.addExternal( 'smw_richmedia', CKEDITOR.basePath + 'plugins/smwrichmedia/' );
    extraPlugins += ",smw_richmedia";
    rmButton = 'SMWrichmedia';
  }

  config.toolbar_Wiki = [
    ['SaveAndContinue', 'SaveAndExit'],
  ['Format','Font','FontSize'],
  ['Bold','Italic','Underline'],
  ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
  ['NumberedList','BulletedList'],
  ['Link','Unlink'],
  ['TextColor','BGColor'],
  ['Maximize'],
  '/',
  ['Source'],
  ['PasteText','PasteFromWord', '-','Find','Replace'],
  ['Strike', 'Subscript','Superscript', '-', 'Blockquote', 'RemoveFormat'],  
  ['Undo','Redo'],
  ['Image', rmButton, 'Table', 'HorizontalRule', 'SpecialChar'],
  ['MWSpecialTags', 'MWTemplate', 'MWSignature'],
  [qiButton, wsButton, '-', 'SMWtoolbar','SMWAddProperty', 'SMWAddCategory'],
  ['About']
  ];

  //    config.toolbar_Wiki = [
  //        ['Source'], ['Print','SpellChecker','Scayt'],
  //        ['PasteText','PasteFromWord', '-','Find','Replace'],
  //        ['SelectAll','RemoveFormat'],
  //        ['Subscript','Superscript'],
  //        ['Link','Unlink'],
  //        ['Undo','Redo'],
  //        ['Image', 'Table', 'HorizontalRule', 'SpecialChar'],
  //        ['MWSpecialTags', 'MWTemplate', 'MWSignature', qiButton, wsButton, rmButton ],
  //        stbToolbarButtons,
  //        '/',
  //        ['Styles','Format','Font','FontSize'],
  //        ['Bold','Italic','Underline','Strike'],
  //        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
  //        ['NumberedList','BulletedList', '-', 'Outdent','Indent', 'Blockquote'],
  //        ['TextColor','BGColor'],
  //        ['Maximize', 'ShowBlocks'],
  //        ['About']
  //    ];
  config.extraPlugins = extraPlugins + ',autogrow,saveAndExit,saveAndContinue';
  config.height = config.autoGrow_minHeight = '300';
  config.autoGrow_onStartup = true;
  config.language = mw.user.options.get('language') || window.parent.wgUserLanguage || 'en';

  config.WikiSignature = '--~~~~';

  // remove format: address
  config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;div';
  // use fontsizes only that do not harm the skin
  config.fontSize_sizes = 'smaller;larger;xx-small;x-small;small;medium;large;x-large;xx-large';

  //the forms plugin requires image plugin, so you need to remove it as well in order to turn the image plugin off
  //elementspath is removed to disable the bottom bar displaying html path
  config.removePlugins = 'forms, image, elementspath, resize' ;
    
  //don't remove empty format elements when loading HTML
  CKEDITOR.dtd.$removeEmpty['span'] = 0;

  config.resize_enabled = false;

  config.autoGrow_maxHeight = 0;

  config.toolbarLocation = 'top';



  /**
* Override the default 'toolbarCollapse' command to hide
* only toolbars in the row two and onwards.
*/
  CKEDITOR.on('instanceReady', function(e) {

    function switchVisibilityAfter1stRow(toolbox, show)
    {
      var inFirstRow = true;
      var elements = toolbox.getChildren();
      var elementsCount = elements.count();
      var elementIndex = 0;
      var element = elements.getItem(elementIndex);
      for (; elementIndex < elementsCount; element = elements.getItem(++elementIndex))
      {
        inFirstRow = inFirstRow && !(element.is('div') && element.hasClass('cke_break'));

        if (!inFirstRow)
        {
          if (show) element.show(); else element.hide();
        }
      }
    }

    var editor = e.editor;
    var collapser = (function()
    {
      try
      {
        // We've HTML: td.cke_top {
        // div.cke_toolbox {span.cke_toolbar, ... }
        // , a.cke_toolbox_collapser }
        var firstToolbarId = editor.toolbox.toolbars[0].id;
        var firstToolbar = CKEDITOR.document.getById(firstToolbarId);
        var toolbox = firstToolbar.getParent();
        var collapser = toolbox.getNext();
        return collapser;
      }
      catch (e) {}
    })();

    // Copied from editor/_source/plugins/toolbar/plugin.js & modified
    //this is actually toolbarToggle command. Collapses the toolbar if it's expanded and expands it if it's collapsed.
    editor.addCommand( 'toolbarCollapse',
    {

      exec : function( editor )
      {
        if (collapser == null) return;

        var toolbox = collapser.getPrevious(),
        contents = editor.getThemeSpace( 'contents' ),
        toolboxContainer = toolbox.getParent(),
        contentHeight = parseInt( contents.$.style.height, 10 ),
        previousHeight = toolboxContainer.$.offsetHeight;

        var collapsed = toolbox.hasClass('iterate_tbx_hidden');//!toolbox.isVisible();

        if ( !collapsed )
        {
          switchVisibilityAfter1stRow(toolbox, false); // toolbox.hide();
          toolbox.addClass('iterate_tbx_hidden');
          if (!toolbox.isVisible()) toolbox.show(); // necessary 1st time if initially collapsed

          collapser.addClass( 'cke_toolbox_collapser_min' );
          collapser.setAttribute( 'title', editor.lang.toolbarExpand );
        }
        else
        {
          switchVisibilityAfter1stRow(toolbox, true); // toolbox.show();
          toolbox.removeClass('iterate_tbx_hidden');

          collapser.removeClass( 'cke_toolbox_collapser_min' );
          collapser.setAttribute( 'title', editor.lang.toolbarCollapse );
        }

        // Update collapser symbol.
        collapser.getFirst().setText( collapsed ?
          '\u25B2' : // BLACK UP-POINTING TRIANGLE
          '\u25C0' ); // BLACK LEFT-POINTING TRIANGLE

        var dy = toolboxContainer.$.offsetHeight - previousHeight;
        contents.setStyle( 'height', ( contentHeight - dy ) + 'px' );

        editor.fire( 'resize' );
      },

      modes : {
        wysiwyg : 1,
        source : 1
      }
    } );

    //real toolbar collapse command. Collapses the toolbar if it's expanded, otherwise does nothing
    editor.addCommand( '_toolbarCollapse',
    {

      exec : function( editor )
      {
        if (collapser == null){
          return;
        }

        var toolbox = collapser.getPrevious(),
        contents = editor.getThemeSpace( 'contents' ),
        toolboxContainer = toolbox.getParent(),
        contentHeight = parseInt( contents.$.style.height, 10 ),
        previousHeight = toolboxContainer.$.offsetHeight;

        var collapsed = toolbox.hasClass('iterate_tbx_hidden');//!toolbox.isVisible();

        if ( !collapsed )
        {
          switchVisibilityAfter1stRow(toolbox, false); // toolbox.hide();
          toolbox.addClass('iterate_tbx_hidden');
          if (!toolbox.isVisible()) {
            toolbox.show(); // necessary 1st time if initially collapsed
          }

          collapser.addClass( 'cke_toolbox_collapser_min' );
          collapser.setAttribute( 'title', editor.lang.toolbarExpand );

          // Update collapser symbol.
          collapser.getFirst().setText( '\u25C0' /*BLACK LEFT-POINTING TRIANGLE */);

          var dy = toolboxContainer.$.offsetHeight - previousHeight;
          contents.setStyle( 'height', ( contentHeight - dy ) + 'px' );

          editor.fire( 'resize' );
        }
        else
        {
          return;
        }


      },

      modes : {
        wysiwyg : 1,
        source : 1
      }
    } );

    // Make sure advanced toolbars initially collapsed
    editor.execCommand( '_toolbarCollapse' );
  });

};
