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

CKEDITOR.dialog.add( 'SMWqi', function( editor ) {
  var wgScript = window.parent.wgScript;
  //Workaround for issue 15679. smwghQiLoadUrl var is not initialized when action = formedit
  var qiUrl = window.parent.smwghQiLoadUrl || '?action=ajax&rs=smwf_qi_getPage&rsargs[]=CKE';
  var locationQi =  wgScript + qiUrl;
  var querySource, Tip;
  var height = window.outerHeight || window.screen.availHeight || 500;
  height = parseInt(height * 0.6);
  var getQIHelper = function(){
      return window.parent.qihelper;
    };

  
    
  return {
    title: 'Query Interface',
    lang: editor.lang,
    minWidth: 900,
    minHeight: (window.outerHeight == undefined) ? 400 : parseInt(window.outerHeight * 0.6),

    contents: [
    {
      id: 'tab1_smw_qi',
      label: 'Tab1',
      title: 'Tab1',
      elements : [
      {
        id: 'qiframe',
        type: 'html',
        label: "Text",
        style: 'width:100%; height:'+height+'px;',                                              
        html: '<iframe name="CKeditorQueryInterface" id="CKeditorQueryInterface" style="border:0; width:100%; height:'+height+'px;" scrolling="auto" src="'+locationQi+'"></iframe>'
      }
      ]
    }
    ],
		 
    buttons: [
    CKEDITOR.dialog.okButton(editor, {
      label: 'Insert Query'
    }),
    CKEDITOR.dialog.cancelButton
    ],

		 
    InsertDataInTextarea : function(ask) {
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
        myArea.value = before + ask + after;
      }
    },   

  
    onShow : function() {    
      var thisDialog = this;  

      thisDialog.fakeObj = false;

      var editor = thisDialog.getParentEditor(),
      selection = editor.getSelection(),
      element = null;
                
      // Fill in all the relevant fields if there's already one item selected.
      if( editor.mode == 'wysiwyg'
        && selection
        && (element = selection.getSelectedElement())
        && element.is( 'img' )
        && element.getAttribute( 'class' ) == 'FCK__SMWquery' )
        {
        thisDialog.fakeObj = element;
        element = editor.restoreRealElement( thisDialog.fakeObj );
        selection.selectElement( thisDialog.fakeObj );
        querySource = element.getHtml().replace(/_$/, '');
        // decode HTML entities in the encoded query source
        querySource = jQuery("<div/>").html(querySource).text();
        querySource = querySource.replace(/fckLR/g, '\r\n');

        if(window.parent.qihelper && window.parent.qihelper.initFromQueryString)
          window.parent.qihelper.initFromQueryString(querySource);
        else{
          var initFromQueryStringIntervalId = window.setInterval(function(){
            if(window.parent.qihelper && window.parent.qihelper.initFromQueryString){
              window.clearInterval(initFromQueryStringIntervalId);
              window.parent.qihelper.initFromQueryString(querySource);
            }
              
          }, 1000)
        }
      }
      else {      
        if(window.parent.qihelper && window.parent.qihelper.doReset)
          window.parent.qihelper.doReset();
        else
          var resetIntervalId = window.setInterval(function(){
            if(window.parent.qihelper && window.parent.qihelper.doReset){
              window.clearInterval(resetIntervalId);
              window.parent.qihelper.doReset();
            }

          }, 1000)
      }
    },

    onOk: function() {
      //			var qiDocument = window.frames['CKeditorQueryInterface'];
      var ask = window.parent.qihelper.getAskQueryFromGui();
      ask = ask.replace(/\]\]\[\[/g, "]]\n[[");
      ask = ask.replace(/>\[\[/g, ">\n[[");
      ask = ask.replace(/\]\]</g, "]]\n<");
      ask = ask.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");

      if ( editor.mode == 'wysiwyg') {
        ask = ask.replace(/\r?\n/g, 'fckLR');
        ask = '<span class="fck_smw_query">' + ask + '</span>';
                
        ////////hack for changing query object title in wysiwyg////////
        var fakeSpanDescription = editor.lang.fakeobjects['span'];
        editor.lang.fakeobjects['span'] = 'Edit Query (with Query Interface)';
        ///////////////////////////////////////////////////////////////
                
        var element = CKEDITOR.dom.element.createFromHtml(ask, editor.document),
        newFakeObj = editor.createFakeElement( element, 'FCK__SMWquery', 'span', false);
                    
        //////////////////////////////////////////////////////////////
        editor.lang.fakeobjects['span'] = fakeSpanDescription;
        ////////end of hack///////////////////////////////////////////
                
        if ( this.fakeObj ) {
          newFakeObj.replace( this.fakeObj );
          editor.getSelection().selectElement( newFakeObj );
        } else
          editor.insertElement( newFakeObj );
      }
      else {
        this.InsertDataInTextarea(ask);
      }

      window.refreshSTB.refreshToolBar();
    }

  };

} );

