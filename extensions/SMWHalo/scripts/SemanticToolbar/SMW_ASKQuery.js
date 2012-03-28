/*
 * Copyright (C) Vulcan Inc.
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

/**
 * @file
 * @ingroup SMWHalo
 * 
 * @author Benjamin Langguth
 * 
 * @class ASKQuery
 * This class provides a container for query hints in semantic toolbar 
 * (in the Edit Mode).
 * 
 */
var ASKQuery = Class.create();

ASKQuery.prototype = {

  initialize: function() {
    this.genTB = new GenericToolBar();
    this.toolbarContainer = null;
    this.showList = true;
    this.currentAction = "";
  },

  showToolbar: function() {
    this.askQueryContainer.setHeadline(gLanguage.getMessage('QUERY_HINTS'));
    var container = this;
    if (wgAction == 'edit' || wgAction == 'formedit' || wgAction == 'submit' ||
      wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
      wgCanonicalSpecialPageName == 'FormEdit' ) {
      // Create a wiki text parser for the edit mode. In annotation mode,
      // the mode's own parser is used.
      this.wtp = new WikiTextParser();
    }
    this.om = new OntologyModifier();
    this.fillList(true);
  },

  initToolbox: function(event){
    if ((wgAction == "edit" || wgAction == "annotate" || wgAction == "formedit" || wgAction == "submit" ||
      wgCanonicalSpecialPageName == 'AddData' || wgCanonicalSpecialPageName == 'EditData' ||
      wgCanonicalSpecialPageName == 'FormEdit')
    && typeof stb_control != 'undefined' && stb_control.isToolbarAvailable()
      && !mw.config.get('wgCKeditorVisible')) //inline query section should not be visible in wysiwyg mode
      {
      this.askQueryContainer = stb_control.createDivContainer(ASKQUERYCONTAINER,0);
      this.showToolbar();
    }
  },

  fillList: function(forceShowList) {
    if (forceShowList == true) {
      this.showList = true;
    }
    if (!this.showList) {
      return;
    }
    if (this.wtp) {
      this.wtp.initialize();
      var askQueries = this.wtp.getAskQueries();

      var id = 'query';
      var html ='<div id="query-tools">';
      html += '<a href="javascript:smwhgASKQuery.newQuery()" class="menulink">'+gLanguage.getMessage('CREATE')+'</a>';
      html += '</div>';
      html += '<div id="query-itemlist"><table id="query-table">';
      var path = wgArticlePath;

      var len = askQueries == null ? 0 : askQueries.length;
      for (var i = 0; i < len; i++) {
        var fn = 'smwhgASKQuery.getSelectedItem(' + i + ')';
        var shortName = askQueries[i].getName().escapeHTML();
        var encodedQSforAsk = encodeURI(gLanguage.getMessage('NS_SPECIAL') + ":Ask" +
          '?q='+askQueries[i].getQueryText());
        var askSpecialPage = path.replace(/\$1/, encodedQSforAsk);
        var encodedQSforQI = encodeURI(gLanguage.getMessage('NS_SPECIAL') + ":QueryInterface" +
          '?q='+askQueries[i].getQueryText());
        var qiSpecialPage = path.replace(/\$1/, encodedQSforQI);
        var img = '<img src="' + wgScriptPath  + '/extensions/SMWHalo/skins/edit.gif"/>';
        html += '<tr>' +
        '<td class="query-col1" >' +
        shortName +
        '</td>' +
        '<td class="query-col2">' +
        '<a href="javascript:' + fn + '">' + img + '</a>' +
        '</td></tr>';
      }
      if( len == 0 ) {
        var tb = this.createToolbar("");
        html += tb.createText('query-status-msg', gLanguage.getMessage('NO_QUERIES_FOUND'), '', true);
      }
      html += '</table></div>';
      this.askQueryContainer.setContent(html);
      this.askQueryContainer.contentChanged();
		
    }
  },

  /**
 * Creates a new toolbar for the query hints container.
 * Further elements can be added to the toolbar. Call <finishCreation> after the
 * last element has been added.
 * 
 * @param string attributes
 * 		Attributes for the new container
 * @return 
 * 		A new toolbar container
 */
  createToolbar: function(attributes) {
    if (this.toolbarContainer) {
      this.toolbarContainer.release();
    }
    this.toolbarContainer = new ContainerToolBar('askquery-content',1500,this.askQueryContainer);
    var tb = this.toolbarContainer;
    tb.createContainerBody(attributes);
    return tb;
  },

  /**
 * TODO: doku
 */
  newQuery: function() {

    this.currentAction = "create";
    this.wtp.initialize();
    var selection = this.wtp.getSelection(true);

    /*STARTLOG*/
    smwhgLogger.log(selection,"STB-Queries","create_clicked");
    /*ENDLOG*/

    this.openQueryInterfaceDialog(null, smwhgASKQuery.setNewAskQuery);

  },

  openQueryInterfaceDialog: function(query, onOk, onCancel){
    query = query || '';
    if(query.length){
      query = '&query=' + encodeURIComponent(query);
    }

    var url = mw.config.get('wgScript') + '?title=Special:QueryInterface&rsargs[]=CKE' + query;

    var dialog = jQuery('<div/>')
                .html('<iframe id="qiDialogIframe" src="' + url + '"></iframe>')
                .dialog({
                    autoOpen: false,
                    modal: true,
                    height: 770,
                    width: 985,
                    title: 'Query Interface',                    
                    close: function(){
                      jQuery( this ).dialog('destroy');
                    },
                    buttons: {
                      Ok: function() {
                        if(typeof(onOk) === 'function'){
                          onOk();
                        }
                        jQuery( this ).dialog('close');
                      },
                      Calcel: function() {
                        if(typeof(onCancel) === 'function'){
                          onCancel();
                        }
                        jQuery( this ).dialog('close');
                      }
      }
                });
    dialog.dialog('open');

//    mw.loader.using('ext.smwhalo.queryInterface', function(){
//      jQuery('#qiSTBDialog').load(url);
//    });
  },

  /**
 * TODO:doku
 */
  getSelectedItem: function(selindex) {
    this.wtp.initialize();
    var queries = this.wtp.getAskQueries();
    if ( selindex == null
      || selindex < 0
      || selindex >= queries.length) {
      // Invalid index
      return;
    }

    this.currentAction = "edit_query";
    this.currentQueryIndex = selindex;

    /*STARTLOG*/
    smwhgLogger.log(queries[selindex].getName(),"STB-Queries",this.currentAction+"clicked");
    /*ENDLOG*/

  
    var query = queries[selindex].getQueryText().replace(/\n|\r/g, '');

    this.openQueryInterfaceDialog(query, smwhgASKQuery.setUpdatedAskQuery);
  },

  /**
 * replaces existing query annotations
 */
  setUpdatedAskQuery: function() {
    var newQuery;
    if(typeof(SPARQL) !== 'undefined' && SPARQL.getQuery){
      newQuery = SPARQL.getQuery();      
    }
    if(!newQuery){
      newQuery = QIHELPER.getAskQueryFromGui();
      if(!newQuery){
        return;
      }
      if( typeof( QIHELPER.queryFormated ) === 'undefined' ) {
        // format query if not already done
        newQuery = newQuery.replace(/\]\]\[\[/g, "]]\n[[");
        newQuery = newQuery.replace(/>\[\[/g, ">\n[[");
        newQuery = newQuery.replace(/\]\]</g, "]]\n<");
        newQuery = newQuery.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
      }
    }
    smwhgASKQuery.wtp.initialize();
    var queries = smwhgASKQuery.wtp.getAskQueries();
    var i = smwhgASKQuery.currentQueryIndex;
    if ( i == null
      || i < 0
      || i >= queries.length) {
      // Invalid index
      return;
    }

    /*STARTLOG*/
    smwhgLogger.log(queries[i].getName(),"STB-Queries","query-update");
    /*ENDLOG*/
    queries[i].replaceAnnotation(newQuery);
    smwhgASKQuery.fillList();
  },

  /**
 * set new query annotations
 */
  setNewAskQuery:function() {
    var newQuery;
    if(typeof(SPARQL) !== 'undefined' && SPARQL.getQuery){
      newQuery = SPARQL.getQuery();
    }
    if(!newQuery){
      newQuery = QIHELPER.getAskQueryFromGui();
      if(!newQuery){
        return;
      }
      newQuery = newQuery.replace(/\]\]\[\[/g, "]]\n[[");
      newQuery = newQuery.replace(/>\[\[/g, ">\n[[");
      newQuery = newQuery.replace(/\]\]</g, "]]\n<");
      newQuery = newQuery.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
    }
	
    smwhgASKQuery.wtp.addAnnotation(newQuery);
    refreshSTB.refreshToolBar();
  }


};// End of Class

window.smwhgASKQuery = new ASKQuery();
stb_control.registerToolbox(smwhgASKQuery);
