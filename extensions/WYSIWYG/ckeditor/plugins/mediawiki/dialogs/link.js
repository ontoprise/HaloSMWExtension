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

CKEDITOR.dialog.add( 'MWLink', function( editor ) {
{
  // need this to use the getSelectedLink function from the plugin
  var plugin = CKEDITOR.plugins.link;
  var searchTimer;
  var urlProtocolRegex = new RegExp('^' + mw.config.get('wgUrlProtocols'), 'i');
  var dialogDefinition = {
    title : editor.lang.mwplugin.linkTitle,
    minWidth : 350,
    minHeight : 140,
    resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
    contents : [
    {
      id : 'mwLinkTab1',
      label : 'Link label',
      title : 'Link title',
      elements :
      [
      {
        id: 'linkLabel',
        type: 'text',
        label: editor.lang.mwplugin.defineLabel,
        title: 'Link label',
        style: 'border: 1px;'
      },
      {
        id: 'linkTarget',
        type: 'text',
        label: editor.lang.mwplugin.defineTarget,
        title: 'Link target',
        style: 'border: 1px;',
        onKeyUp: function(){
          this.getDialog().definition.onUrlChange(this.getDialog());
        },
        validate: function(){
          if ( !this.getValue() )
          {
            alert( 'Link url cannot be empty.' );
            return false;
          }
        }
      },
      {
        id: 'searchMsg',
        type: 'html',
        style: 'font-size: smaller; font-style: italic;',
        html: editor.lang.mwplugin.startTyping
      },
      {
        id: 'linkList',
        type: 'select',
        size: 5,
        label: editor.lang.mwplugin.chooseTarget,
        title: 'Page list',
        required: false,
        style: 'border: 1px; width:100%;',
        onChange: function(){
          this.getDialog().definition.wikiPageSelected(this.getDialog());
        },
        items: [  ]
      }
      ]
    }
    ],

    onOk : function() {
      var dialog = this;      
      var linkInput = dialog.getContentElement( 'mwLinkTab1', 'linkTarget');
      var link = linkInput.getValue().Trim();

      var labelInput = dialog.getContentElement( 'mwLinkTab1', 'linkLabel');
      var label = labelInput.getValue().Trim();
      var attributes = {};

      if (!label){
        attributes._fcknotitle = true;
        label = urlProtocolRegex.test(link) ? '[n]' : link;
      }
      
      attributes.href = attributes._cke_saved_href = link.replace(/\s/g, '_');
      
      var editor = dialog.getParentEditor();

      if ( !dialog._.selectedElement || !dialog._.selectedElement.is('a')) {
        //create new element if none is selected
        var linkElement = new CKEDITOR.dom.element('a');
        linkElement.setAttributes(attributes);
        linkElement.setHtml(label);
        editor.insertElement(linkElement);
      }
      else {
        // We're only editing an existing link, so just overwrite the attributes.
        linkElement = dialog._.selectedElement;
        linkElement.setAttributes( attributes );
        linkElement.setHtml(label);

        if ( dialog.fakeObj )
          editor.createFakeElement( linkElement, 'cke_anchor', 'anchor' ).replace( dialog.fakeObj );

        delete dialog._.selectedElement;
      }
    },

    onShow : function()
    {
      var dialogDefinition = this.definition;      
      var editor = this.getParentEditor();

      this.fakeObj = false;
      var element = plugin.getSelectedLink( editor ) || editor.getSelection().getSelectedElement();
      if(element){
        var href = element.getAttribute( '_cke_saved_href' ) || element.getAttribute( 'href' );        
        var label = element.getHtml().replace('<br>', '');        
      }
      else{
        var selection = editor.getSelection();
        label = selection.getNative();
        if(CKEDITOR.env.ie){
          selection.unlock();
          label = selection.getNative().createRange().text;
        }
      }
      if(href) {
        href = decodeURIComponent(href).replace(/_/g, ' ');
        this.getContentElement( 'mwLinkTab1', 'linkTarget').setValue(href);        
      }
      if(label){
        this.getContentElement( 'mwLinkTab1', 'linkLabel').setValue(label);
      }
      dialogDefinition.onUrlChange(this);
      this._.selectedElement = element;
				
    },
    startSearch: function(dialog) {
      var dialogDefinition = dialog.definition;
      var link = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' ).getValue().Trim();

      //get page name part before #
      var hashIndex = link && link.indexOf('#');
      if(hashIndex > -1){ //search for anchors
        if(hashIndex > 0){
          var pageName = link.substring(0, hashIndex);
        }
        else{
          pageName = mw.config.get('wgPageName');
        }

        var linkAnchor = encodeURIComponent(link.substring(hashIndex + 1).replace(/\s/g, '_')).toUpperCase();
        CKEDITOR.ajax.load( mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php?action=parse&prop=sections&format=json&page=' + pageName, function( data )
        {
          data = $.parseJSON(data);
          if(data.error){
            dialogDefinition.setSearchMessage(data.error.info, dialog);
          }
          else{
            var select = dialog.getContentElement( 'mwLinkTab1', 'linkList' );
            var count = 0;
            $.each(data.parse.sections, function(index, value){
              var valueAnchor = value.anchor.toUpperCase();
              if(linkAnchor && valueAnchor.indexOf(linkAnchor) !== 0){
                return true; //continue
              }
              else{
                select.add('#' + value.anchor);
                count++;
              }
            });
            dialogDefinition.setSearchMessage(count + editor.lang.mwplugin.sectionsFound, dialog);
          }
        } );
      }
      else{
        dialogDefinition.setSearchMessage( editor.lang.mwplugin.searching, dialog ) ;

        // Make an Ajax search for the pages.
        window.parent.sajax_request_type = 'GET' ;
        window.parent.sajax_do_call( 'wfSajaxSearchArticleCKeditor', [link], function(response){
          dialogDefinition.loadSearchResults(response, dialog)
          }) ;
      }
    },
    clearSearch: function(dialog) {
      dialog.getContentElement( 'mwLinkTab1', 'linkList' ).clear();
    },
    setSearchMessage: function ( message, dialog ) {
      dialog.getContentElement( 'mwLinkTab1', 'searchMsg' ).getInputElement().setHtml(message);
    },
    loadSearchResults: function ( result, dialog ) {
      var dialogDefinition = dialog.definition;
      var results = result.responseText.split( '\n' );
      var select = dialog.getContentElement( 'mwLinkTab1', 'linkList' );

      dialogDefinition.clearSearch(dialog) ;

      var invalidTitle = false;
      if ( results.length == 0 || ( results.length == 1 && results[0].length == 0 ) ) {
        dialogDefinition.setSearchMessage( editor.lang.mwplugin.noPagesFound, dialog ) ;
      }
      else {
        if (results.length == 1) {
          if (results[0] === '***Title has an invalid format***') {
            dialogDefinition.setSearchMessage(editor.lang.mwplugin.invalidTitleFormat, dialog);
            // hide the OK button
            dialog.getButton('ok').getElement().hide();
            invalidTitle = true;
          }
          else {
            dialogDefinition.setSearchMessage(editor.lang.mwplugin.onePageFound, dialog);
          }
        }
        else {
          dialogDefinition.setSearchMessage(results.length + editor.lang.mwplugin.manyPagesFound, dialog);
        }
        if (!invalidTitle) {
          for (var i = 0; i < results.length; i++) {
            select.add(results[i].replace(/_/g, ' '), results[i]);
          }
        }
      }
      if (!invalidTitle) {
        // show the OK button
        dialog.getButton('ok').getElement().show();
      }
    },
    wikiPageSelected: function(dialog) {
      var target = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' );
      var select = dialog.getContentElement( 'mwLinkTab1', 'linkList' );
      var link = target.getValue();
      var selectedValue = select.getValue().replace(/_/g, ' ');
      var hashIndex = link.indexOf('#');
      if(hashIndex > -1){
        target.setValue(link.replace(link.substr(hashIndex), selectedValue));
      }
      else{
        target.setValue(selectedValue);
      }
    },
    onUrlChange: function(dialog) {
      
      var dialogDefinition = dialog.definition;
      var link = dialog.getContentElement( 'mwLinkTab1', 'linkTarget' ).getValue().Trim();
      dialogDefinition.clearSearch(dialog) ;

      if ( searchTimer ){
        window.clearTimeout( searchTimer ) ;
      }

      if ( !link ) {
        dialogDefinition.setSearchMessage( editor.lang.mwplugin.startTyping, dialog ) ;
        return ;
      }

      //      if ( link.StartsWith( '#' ) ) {
      //        dialogDefinition.setSearchMessage( editor.lang.mwplugin.anchorLink, dialog ) ;
      //        return ;
      //      }

      if( urlProtocolRegex.test( link ) ) {
        dialogDefinition.setSearchMessage( editor.lang.mwplugin.externalLink, dialog ) ;
        return ;
      }      

      dialogDefinition.setSearchMessage( editor.lang.mwplugin.stopTyping, dialog ) ;
      searchTimer = window.setTimeout( function(){
        dialogDefinition.startSearch(dialog);
      }, 500 ) ;
    }

  }


};

return dialogDefinition;
});
