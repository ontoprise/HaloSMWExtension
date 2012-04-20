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
var SMW_HALO_VERSION = 'SMW_HALO_VERSION';
if (!SMW_HALO_VERSION.InArray(window.parent.wgCKeditorUseBuildin4Extensions)) {
  // Halo Import extension is not installed, show a teaser only.
  CKEDITOR.plugins.add( 'smw_qi',
  {
    requires : [ 'dialog' ],
    init : function( editor )
    {
      var command = editor.addCommand( 'SMWqi', new CKEDITOR.dialogCommand( 'SMWqi' ) );
      command.canUndo = false;

      editor.ui.addButton( 'SMWqi',
      {
        label : mw.msg('wysiwyg-qi-insert-new-query'),
        command : 'SMWqi',
        icon: this.path + 'images/tb_icon_ask.gif'
      });

      CKEDITOR.dialog.add( 'SMWqi', this.path + 'dialogs/teaser.js' );
    }
  });

} else {
  // Halo extension is installed, use the Webservice
  CKEDITOR.plugins.add('smw_qi', {

    requires : [ 'mediawiki', 'dialog', 'iframe', 'iframedialog'],
    
    insertDataInTextarea : function(query) {
      var myArea = window.parent.getElementById('wpTextbox1');
      if (!myArea) myArea = window.parent.getElementById('free_text');

      if ( CKEDITOR.env.ie ) {
        if (document.selection) {
          // The current selection
          var range = document.selection.createRange();
          // Well use this as a "dummy"
          var stored_range = range.duplicate();
          // Select all text
          stored_range.moveToElementText( myArea );
          // Now move "dummy" end point to end point of original range
          stored_range.setEndPoint( 'EndToEnd', range );
          // Now we can calculate start and end points
          myArea.selectionStart = stored_range.text.length - range.text.length;
        }
      }
      if (myArea.selectionStart != undefined) {
        var before = myArea.value.substr(0, myArea.selectionStart);
        var after = myArea.value.substr(myArea.selectionStart);
        myArea.value = before + query + after;
      }
    },
    getIframeSrc: function(editor){
      var url = window.parent.wgScript + '?title=Special:QueryInterface&rsargs[]=CKE';
      var selection = editor.getSelection();
      if( selection && (element = selection.getSelectedElement()) && element.is( 'img' ) && element.getAttribute( 'class' ) === 'FCK__SMWquery' ){
        var element = editor.restoreRealElement( element );
        var querySource = element.getHtml().replace(/_$/, '');
        // decode HTML entities in the encoded query source
        querySource = jQuery("<div/>").html(querySource).text();
        querySource = querySource.replace(/fckLR/g, '\r\n');
        url += '&query=' + encodeURIComponent(querySource);
      }
      return url;
    },
    addIframeDialog: function(editor, iframeSrc){
      //work around CKEDITOR.dialog caching mechanism to allow new url to be loaded in iframe
          if(CKEDITOR.dialog._.dialogDefinitions && CKEDITOR.dialog._.dialogDefinitions['SMWqi']){
            delete CKEDITOR.dialog._.dialogDefinitions['SMWqi'];
          }
          if(editor._.storedDialogs && editor._.storedDialogs['SMWqi']){
            delete editor._.storedDialogs['SMWqi'];
          }

          CKEDITOR.dialog.addIframe( 'SMWqi', 'Query Interface', iframeSrc, 976, 632, function(){},
          {
            scrolling: 'false',
            frameborder: 'false',
            buttons: [
              CKEDITOR.dialog.okButton(editor, {
                label: mw.msg('wysiwyg-qi-insert-query')
              }),
              CKEDITOR.dialog.cancelButton
            ],
            onOk: function() {
              var query = SPARQL.getQuery();
              if(!query){
                query = window.parent.qihelper.getAskQueryFromGui();
                if(!query){
                  return true;
                }
                query = query.replace(/\]\]\[\[/g, "]]\n[[");
                query = query.replace(/>\[\[/g, ">\n[[");
                query = query.replace(/\]\]</g, "]]\n<");
                query = query.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
              }

              if ( editor.mode == 'wysiwyg') {
                query = query.replace(/\r?\n/g, 'fckLR');
                query = '<span class="fck_smw_query">' + CKEDITOR.tools.htmlEncode(query) + '</span>';

                ////////hack for changing query object title in wysiwyg////////
                var fakeSpanDescription = editor.lang.fakeobjects['span'];
                editor.lang.fakeobjects['span'] = mw.msg('wysiwyg-qi-edit-query');
                ///////////////////////////////////////////////////////////////

                var element = CKEDITOR.dom.element.createFromHtml(query, editor.document),
                newFakeObj = editor.createFakeElement( element, 'FCK__SMWquery', 'span', false);

                //////////////////////////////////////////////////////////////
                editor.lang.fakeobjects['span'] = fakeSpanDescription;
                /////////////////////////end of hack//////////////////////////

                if ( this.fakeObj ) {
                  newFakeObj.replace( this.fakeObj );
                  editor.getSelection().selectElement( newFakeObj );
                } else
                  editor.insertElement( newFakeObj );
              }
              else {
                this.insertDataInTextarea(query);
              }

              window.refreshSTB.refreshToolBar();
            }
          }
        );
    },
    init : function( editor )
    {
      var thisPlugin = this;
      
      editor.addCss(
      'img.FCK__SMWquery' +
        '{' +
        'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/tb_icon_ask.gif' ) + ');' +
        'background-position: center center;' +
        'background-repeat: no-repeat;' +
        'border: 1px solid #a9a9a9;' +
        'width: 18px !important;' +
        'height: 18px !important;' +				
        '}\n'
    );


      editor.addCommand( 'SMWqi',  new CKEDITOR.command( editor,
      {
          exec : function( editor )
          {
              thisPlugin.addIframeDialog(editor, thisPlugin.getIframeSrc(editor));
              editor.openDialog('SMWqi');
          }
      }));
      

      if (editor.addMenuItem) {
        // A group menu is required
        // order, as second parameter, is not required
        editor.addMenuGroup('mediawiki');
        // Create a menu item
        editor.addMenuItem('SMWqi', {
          label : mw.msg('wysiwyg-qi-insert-new-query'),
          command: 'SMWqi',
          group: 'mediawiki'
        });
      }

      if ( editor.ui.addButton ) {
        editor.ui.addButton( 'SMWqi',
        {
          label : mw.msg('wysiwyg-qi-insert-new-query'),
          command : 'SMWqi',
          icon: this.path + 'images/tb_icon_ask.gif'
        });
      }
      // context menu
      if (editor.contextMenu) {
        editor.contextMenu.addListener(function(element, selection) {
          var name = element.getName();
          // fake image for some <span> with special tag
          if ( name == 'img' && element.getAttribute( 'class' ) == 'FCK__SMWquery' )
            return {
              SMWqi: CKEDITOR.TRISTATE_ON
            };
        });
      }
		
      editor.on( 'doubleclick', function( evt )
      {
        var element = evt.data.element;

        if ( element.is( 'img' ) &&  element.getAttribute( 'class' ) === 'FCK__SMWquery' ){
          
          thisPlugin.addIframeDialog(editor, thisPlugin.getIframeSrc(editor));
          evt.data.dialog = 'SMWqi';
        }          
      });
    }
  });
}