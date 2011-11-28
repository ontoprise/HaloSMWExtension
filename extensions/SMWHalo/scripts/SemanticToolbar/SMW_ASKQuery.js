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
    && typeof stb_control != 'undefined'
    && stb_control.isToolbarAvailable()
      && //inline query section should not be visible in wysiwyg mode
      !(typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.wpTextbox1 && (showFCKEditor & RTE_VISIBLE)))
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

    //if wysiwyg is installed and in rich text mode
    //then open wysiwyg QI dialog
    //  if(CKEDITOR && CKEDITOR.instances.wpTextbox1 && document.getElementById('cke_wpTextbox1').style.display !== 'none' ){
    //    CKEDITOR.instances.wpTextbox1.openDialog('SMWqi');
    //  }
    //  //else open QI fancybox
    //  else{
    this.openQueryInterfaceDialog(mw.config.get('wgScript') + '?action=ajax&rs=smwf_qi_getAskPage&rsargs[]=CKE', smwhgASKQuery.setNewAskQuery);
  //  }
  //	alert(selection,"STB-Queries","create_clicked");
  },

  openQueryInterfaceDialog: function(href, onCleanup){
    jQuery.fancybox({
      'href' : href,
      'width' : 977,
      'height' : 600,
      'padding': 10,
      'margin' : 0,
      'autoScale' : false,
      'transitionIn' : 'none',
      'transitionOut' : 'none',
      'type' : 'iframe',
      'overlayColor' : '#222',
      'overlayOpacity' : '0.8',
      'hideOnContentClick' : false,
      'scrolling' : 'auto',
      'onCleanup' : onCleanup      
    });
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
	
    //  var editorInstance = CKEDITOR.instances.wpTextbox1;
    //  if(CKEDITOR && editorInstance && document.getElementById('cke_wpTextbox1').style.display !== 'none' ){
    //    //get all images in the editor
    //    var queryImages = editorInstance.document.getElementsByTag('img');
    //
    //    //find the query we want to edit
    //    for(var i = 0; i < queryImages.count(); i++){
    //      var queryImg = queryImages.getItem(i);
    //      if(queryImg.getAttribute('class') === 'FCK__SMWquery'){
    //        var realElement = editorInstance.restoreRealElement(queryImg);
    //        var realQuery = realElement.getChild(0).getText().replace(/fckLR/g, '').replace(/^{{#ask:\s*/, '').replace(/\s*}}$/, '');
    //        if(realQuery === jQuery.trim(query)){
    //          //workaround for null selection object in IE8 if editor is not focused
    //          if(CKEDITOR.env.ie8){
    //            editorInstance.focus();
    //          }
    //          editorInstance.getSelection().selectElement(queryImg);
    //          break;
    //        }
    //      }
    //    }
    //    //then open the dialog
    //    editorInstance.openDialog('SMWqi');
    //  }
    //  else{
    var uri = mw.config.get('wgScript') + '?action=ajax&rs=smwf_qi_getAskPage&rsargs[]=CKE' + encodeURIComponent('&query=' + query);
    this.openQueryInterfaceDialog(uri, smwhgASKQuery.setUpdatedAskQuery);
  //  }
  },

  getQIHelper: function(){
    // some extensions use the YUI lib that adds an additional iframe
    if(!smwhgASKQuery.qihelper){
      for (i=0; i<window.top.frames.length; i++) {
        if (window.top.frames[i].qihelper) {
          smwhgASKQuery.qihelper = window.top.frames[i].qihelper;
          break;
        }
      }
    }

    return smwhgASKQuery.qihelper;
  },

  saveQuery: function(){
    mw.log('OK clicked');
    smwhgASKQuery.getQIHelper().querySaved = true;
    jQuery.fancybox.close();
    delete smwhgASKQuery.qihelper;

  },

  cancelQuery: function(){
    mw.log('Cancel clicked');
    smwhgASKQuery.getQIHelper().querySaved = false;
    jQuery.fancybox.close();
    delete smwhgASKQuery.qihelper;
  },


  /**
 * replaces existing query annotations
 */
  setUpdatedAskQuery: function() {
    //	alert('Query Interface is going to be closed! Saving Query now...');
    var qiHelperObj = smwhgASKQuery.getQIHelper();
    
   
    var newQuery = qiHelperObj.getAskQueryFromGui();
    if( typeof( qiHelperObj.querySaved) == 'undefined' ||
      qiHelperObj.querySaved !== true ) {
      return;
    }
    if( typeof( qiHelperObj.queryFormated ) === 'undefined' ) {
      // format query if not already done
      newQuery = newQuery.replace(/\]\]\[\[/g, "]]\n[[");
      newQuery = newQuery.replace(/>\[\[/g, ">\n[[");
      newQuery = newQuery.replace(/\]\]</g, "]]\n<");
      newQuery = newQuery.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
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
    delete qiHelperObj;
  },

  /**
 * set new query annotations
 */
  setNewAskQuery:function() {
    var qiHelperObj = smwhgASKQuery.getQIHelper();
    
    var newQuery = qiHelperObj.getAskQueryFromGui();
    if( typeof( qiHelperObj.querySaved) == 'undefined' ||
      qiHelperObj.querySaved !== true ) {
      return;
    }
    newQuery = newQuery.replace(/\]\]\[\[/g, "]]\n[[");
    newQuery = newQuery.replace(/>\[\[/g, ">\n[[");
    newQuery = newQuery.replace(/\]\]</g, "]]\n<");
    newQuery = newQuery.replace(/([^\|]{1})\|{1}(?!\|)/g, "$1\n|");
	
    smwhgASKQuery.wtp.addAnnotation(newQuery);
    refreshSTB.refreshToolBar();
    delete qiHelperObj;
  }


};// End of Class

window.smwhgASKQuery = new ASKQuery();
stb_control.registerToolbox(smwhgASKQuery);
