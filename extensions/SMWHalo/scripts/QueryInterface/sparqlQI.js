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

(function($){
  if(!mw.config.get('smwgHaloWebserviceEndpoint')){
    mw.log('Error: smwgHaloWebserviceEndpoint is not defined. Failed to load SPARQL module.');
    return;
  }
  
  SPARQL = {
    smwgHaloWebserviceEndpoint: 'http://' + mw.config.get('smwgHaloWebserviceEndpoint'),
    uid: 0,
    iri_delim: '/',
    parserFuncString: '',
    namespaceString: '',
    queryString: null,
    queryParameters: {},
    srfInitMethods: [],
    srfInitScripts: [],
    sources: ['tsc', 'http://dbpedia.org/sparql'],
    graphs: ['default', 'any'],
    variables: [],

    json : {

      variableIcon : mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif',
      instanceIcon : mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/instance_icon.gif',
      categoryIcon : mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif',
      propertyIcon : mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif',

      TreeQuery : {}
    },
    
    //View component. Takes care of sparql treeview manipulation
    View: {
      jstreeId: 'qiTreeDiv'
//      map: {
//        treeToData: []
//      }
    }
  };

  
//  SPARQL.View.map.put = function(nodeId, nodeData){
//    if(!nodeId || !nodeData){
//      return;
//    }
//    SPARQL.View.map.treeToData[nodeId] = nodeData;
//  };


//  SPARQL.getXHR = function(){
//    if ($.browser.msie && window.XDomainRequest) {
//      // Use Microsoft XDR
//      return function(){
//        return new XDomainRequest();
//      }
//    }
//    else if(window.XMLHttpRequest && (window.location.protocol !== "file:" || !window.ActiveXObject)){
//      return function(){
//        return new window.XMLHttpRequest();
//      }
//    }
//    else{
//      return function(){
//        try {
//          return new window.ActiveXObject("Microsoft.XMLHTTP");
//        } catch(e) {}
//      }
//    }
//  };

  SPARQL.View.getTree = function(){
    return $.jstree._reference(SPARQL.View.jstreeId);
  };

  SPARQL.View.isTreeEmpty = function(){
    return SPARQL.View.getTree()._get_children(-1).length === 0;
  };

  SPARQL.View.getDataEntity = function(nodeId){
    return SPARQL.View.map.treeToData[nodeId];
  };

  SPARQL.View.getSelectedNode = function(){
    return SPARQL.View.getTree().get_selected();
  };

  SPARQL.View.getSelectedNodeAttr = function(attr){
    return SPARQL.View.getSelectedNode().attr(attr);
  };

  SPARQL.View.getSelectedNodeText = function(){
    return SPARQL.View.getTree().get_text(SPARQL.View.getSelectedNode());
  };

  SPARQL.View.getNodeText = function(node){
    return SPARQL.View.getTree().get_text(node);
  };

  SPARQL.View.getSelectedSubjectNode = function(){
    return SPARQL.View.getParentNode(SPARQL.View.getSelectedNode(), ['variable', 'instance']);
  };

  SPARQL.View.getSelectedCategoryNode = function(){
    return SPARQL.View.getParentNode(SPARQL.View.getSelectedNode(), ['category']);
  };

  SPARQL.View.getSelectedPropertyNode = function(){
    return SPARQL.View.getParentNode(SPARQL.View.getSelectedNode(), ['property']);
  };

  SPARQL.View.getNodeById = function(id){
    return SPARQL.View.getTree()._get_node('#' + id);
  };

  SPARQL.getQuery = function(){
    return SPARQL.buildParserFuncString(SPARQL.getNamespaceString() + SPARQL.queryString);
  };

  SPARQL.View.createCategory = function(categoryRestriction){
    var categoryNodeData = {
      data:{
        title: categoryRestriction.getString()
      },
      attr: {
        rel: 'category',
        id: categoryRestriction.getId(),
        title: categoryRestriction.getString()
      }
    };
  
    SPARQL.View.getTree().create(SPARQL.View.getNodeById(categoryRestriction.subject.getId()), 'first' , categoryNodeData, function(){}, true );
  };

  SPARQL.View.createSubject = function(subject){

    var subjectName = subject.getShortName('a');
    var rel = 'instance';
    if(subject.type === TYPE.VAR){
      subjectName = '?' + subjectName;
      rel = 'variable';
    }

    var subjectNodeData = {
      data:{
        title: subjectName
      },
      attr: {
        id: subject.getId(),
        rel: rel,
        title: subjectName
      },
      children: []
    };

    var jstree = SPARQL.View.getTree();
    
    jstree.create(jstree, 'first' , subjectNodeData, function(){}, true);
  };
  


  /**
   *  Compare two objects by comparing only their fields (not methods)
   *  @param obj1 first object
   *  @param obj2 second object
   *  return true if objects are equal, false otherwise
   */
  SPARQL.objectsEqual = function(obj1, obj2){
    if(typeof obj1 !== 'object' || typeof obj2 !== 'object'){
      return false;
    }
    for(var prop in obj1){
      if(prop && obj1.hasOwnProperty(prop) && typeof obj1[prop] !== 'function'){
        if(typeof obj1[prop] === 'object'){
          if(!SPARQL.objectsEqual(obj1[prop], obj2[prop])){
            return false;
          }
        }
        else if(obj1[prop] !== obj2[prop]){
          return false;
        }
      }
    }
    //second loop over obj2 properties to make sure obj2 does not have more properties than obj1
    for(prop in obj2){
      if(prop && obj2.hasOwnProperty(prop) && typeof obj2[prop] !== 'function'){
        if(typeof obj2[prop] === 'object'){
          return SPARQL.objectsEqual(obj2[prop], obj1[prop]);
        }
        else if(obj2[prop] !== obj1[prop]){
          return false;
        }
      }
    }

    return true;
  };

  /**
   * Check if given array contains the given object
   * @param object javascript object
   * @param array array of objects
   */
  SPARQL.isObjectInArray = function(object, array){
    if(!object || !array || !array.length){
      return false;
    }
    for(var i = 0; i < array.length; i++){
      if(SPARQL.objectsEqual(object, array[i])){
        return true;
      }
    }
    return false;
  };


  SPARQL.View.buildCategoryNodeLabel = function(categoryArray){
    var result = '';
    for(var i = 0; categoryArray && i < categoryArray.length; i++){
      result += categoryArray[i].replace(SPARQL.category_iri, '').replace(SPARQL.iri_delim, '');
      if(i < categoryArray.length - 1){
        result += ' or ';
      }
    }
    return result;
  };

 

  SPARQL.View.buildCategoryIRIArray = function(categoryArray){
    var result = [];
    var prefix = SPARQL.category_iri + SPARQL.iri_delim;
    for(var i = 0; i < categoryArray.length; i++){
      result.push(prefix + categoryArray[i].replace(/\s+/, '_'));
    }

    return result;
  };
  
//  SPARQL.View.deleteCategory = function(categoryObj){
//    //get node id
//    var nodeId = SPARQL.View.getNodeId(categoryObj);
//
//    //remove tree node
//    $.jstree._focused().delete_node('#' + nodeId);
//
//    //remove the nodeid from the map
//    SPARQL.View.map.remove(nodeId);
//  };

//  SPARQL.View.map.remove = function(nodeId){
//    delete SPARQL.View.map.treeToData[nodeId];
//  };

  
  SPARQL.arraysEqual = function(arr1, arr2){
    return ($(arr1).not(arr2).get().length == 0 && $(arr2).not(arr1).get().length == 0);
  };

  SPARQL.getNextUid = function(){
    return SPARQL.uid++;
  };


  SPARQL.activateUpdateSourceBtn = function(){
    $('#qiUpdateSourceBtn').live('click', function(){
//      SPARQL.sparqlToTree($('#sparqlQueryText').val());
      SPARQL.getQueryResult(SPARQL.getNamespaceString() + $('#sparqlQueryText').val());
      $('#sparqlQueryText').data('initialQuery', $('#sparqlQueryText').val());
    });
  };


  SPARQL.activateDiscardChangesLink = function(){
    $('#discardChangesLink').live('click', function(event){
      var initialQuery = $('#sparqlQueryText').data('initialQuery');
      if(initialQuery && initialQuery.length > 1){
        $('#sparqlQueryText').val($('#sparqlQueryText').data('initialQuery'));
        $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(SPARQL.getNamespaceString() + $('#sparqlQueryText').val()));
      }
      event.preventDefault();
    });
  };

  SPARQL.validateQueryString = function(sparqlQuery){
    if(!(sparqlQuery && sparqlQuery.length) || sparqlQuery.match(/SELECT[\s\*\?\w_]+WHERE\s*{[\s\r\n]*}\s*/gi))
      return false;
    return true;
  };


  SPARQL.sparqlToTree = function(sparqlQuery){
    sparqlQuery = sparqlQuery || SPARQL.getNamespaceString() + SPARQL.queryString;
    if(!SPARQL.validateQueryString(sparqlQuery)){
      return;
    }

    mw.log('sparql query send:\n' + sparqlQuery);
    //send ajax post request to localhost:8080/sparql/sparqlToTree
    $.ajax({
      type: 'POST',
      dataType: 'json',
      cache: false,
//      jsonp: 'jsonp_callback',
      url: SPARQL.smwgHaloWebserviceEndpoint + '/sparql/sparqlToTree',
      data: {
        sparql: sparqlQuery
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        SPARQL.queryString = sparqlQuery;        
        if(data && typeof data === 'object'){
          SPARQL.Model.init(data);
//          $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(SPARQL.getNamespaceString() + sparqlQuery));
          SPARQL.updateSortOptions();
          SPARQL.toTree();
        }
        else{
          //tsc is not reachable
          SPARQL.showMessageDialog('TSC not accessible. Check server: ' + SPARQL.smwgHaloWebserviceEndpoint, 'Empty response from server', 'sparqlToTreeMsgDialog');
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);        
        var errorJson = $.parseJSON(xhr.responseText);
        var msg = errorJson.error || 'The result is empty'
        SPARQL.showMessageDialog(errorJson.error, gLanguage.getMessage('QI_INVALID_QUERY'), 'sparqlToTreeMsgDialog');

      }
    });
  };

  SPARQL.showMessageDialog = function(message, title, id, onCloseCallback, modal, toggle, autoOpen){
    
    //if id is not specified then generate one
    id = id || 'dialog-' + SPARQL.getNextUid();

    //if this dialog is already open then close it first
    if($('#' + id).length && $('#' + id).dialog('isOpen')){
      $('#' + id).dialog('close');
      if(toggle){
        return null;
      }
    }

    var dialogDiv = $('<div/>').attr('id', id);
    $('body').append(dialogDiv);      

    var buttons = {};
    var closeMsg = gLanguage.getMessage('QI_CLOSE');
    buttons[closeMsg] = function() {
      dialogDiv.dialog("close");
    };

    dialogDiv.dialog({
      autoOpen: false,
      modal: (modal !== false),
      width: 'auto',
      height: 'auto',
      resizable: true,
      title: title || '',
      buttons: buttons,
      close: function(){
        if(onCloseCallback && typeof onCloseCallback === 'function'){
          onCloseCallback();
        }
        dialogDiv.remove();
      }
    });
    dialogDiv.html(message);
    if(autoOpen !== false){
      dialogDiv.dialog('open');
    }
    if(dialogDiv.height() > 600){
      dialogDiv.dialog('option', 'height', 600);
    }
    if(dialogDiv.width() > 1000){
      dialogDiv.dialog('option', 'width', 1000);
    }

    return dialogDiv;
  };

  SPARQL.showConfirmationDialog = function(message, title, onOkCallback, onOkCallbackArgs, onCancelCallback, onCancelCallbackArgs){
    var dialogDiv = $('<div/>').attr('id', 'qiConfirmationMsgDialog');
    $('body').append(dialogDiv);

    var buttons = {};
    buttons[gLanguage.getMessage('QI_OK')] = function() {
      if(onOkCallback && typeof onOkCallback === 'function'){
        onOkCallback(onOkCallbackArgs);
      }
      dialogDiv.dialog("close");
    };

    buttons[gLanguage.getMessage('QI_CANCEL')] = function() {      
      dialogDiv.dialog("close");
    };

    dialogDiv.dialog({
      autoOpen: false,
      modal: true,
      width: 'auto',
      height: 'auto',
      resizable: false,
      title: title || '',
      buttons: buttons,
      close: function(){        
        dialogDiv.remove();
        if(onCancelCallback && typeof onCancelCallback === 'function'){
          onCancelCallback(onCancelCallbackArgs);
        }
      }
    });
    dialogDiv.html(message);
    dialogDiv.dialog('open');
  };

  SPARQL.buildParserFuncString = function(queryString){
    queryString = queryString || SPARQL.queryString;
    if(!(queryString && queryString.length)){
      return null;
    }
    
    SPARQL.queryWithParamsString = queryString + '\n' + SPARQL.getParameterString();
    SPARQL.parserFuncString = '{{#sparql: \n' + SPARQL.queryWithParamsString + '\n}}';
    
    return SPARQL.parserFuncString;
  };

  SPARQL.getParameterString = function(){
    var parameterString = '';
    $.each(SPARQL.queryParameters, function(key, value){
      parameterString += '\n|' + key + '=' + value;
    });
    
    return parameterString;
  };


  SPARQL.treeToSparql = function(treeJsonConfig, getQueryResult){
    //else proceed with the translation
    treeJsonConfig = treeJsonConfig || SPARQL.Model.data;
      
    var jsonString = SPARQL.stringifyJSON(treeJsonConfig);
    mw.log('tree json:\n' + jsonString);
    //send ajax post request to localhost:8080/sparql/treeToSPARQL
    $.ajax({
      type: 'POST',
//      dataType: 'jsonp',
//      jsonp: 'jsonp_callback',
      url: SPARQL.smwgHaloWebserviceEndpoint + '/sparql/treeToSPARQL_noNamespaces',
      data: {
        tree: jsonString
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        if(data && data.query){
          SPARQL.queryString = data.query;
          SPARQL.Model.data.namespace = SPARQL.Model.removeNamespaceDuplicates(data.namespace);
          $('#sparqlQueryText').val(SPARQL.queryString);
          $('#sparqlQueryText').data('initialQuery', SPARQL.queryString);
          var parserFuncString = SPARQL.buildParserFuncString(SPARQL.getNamespaceString() + SPARQL.queryString);
          $('#qiSparqlParserFunction').val(parserFuncString);
          if(getQueryResult){
            if(SPARQL.validateQueryTree(treeJsonConfig)){
              SPARQL.getQueryResult(SPARQL.queryString);
            }
          }         
        }
        else{
          //tsc is not reachable
          SPARQL.showMessageDialog('TSC not accessible. Check server: ' + SPARQL.smwgHaloWebserviceEndpoint, 'Empty response from server', 'treeToSparqlMsgDialog');
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        var msg = errorJson.error || 'The result is empty'
        SPARQL.showMessageDialog(msg, 'SPARQL tree to string convertion error', 'treeToSparqlMsgDialog');
      }

    });
  };

//  SPARQL.parseSparqlQuery = function(query, callback){
//    query = query || SPARQL.queryString;
//    SPARQL.queryString = query;
//
//    mw.log('parse query:\n' + query);
//
//    $.ajax({
//      type: 'POST',
//      url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
//      data: {
//        rs: 'smwf_qi_parseSparqlQuery',
//        rsargs: [query]
//      },
//      success: function(data, textStatus, jqXHR) {
//        mw.log('data: ' + data);
//        mw.log('textStatus: ' + textStatus);
//        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
//        SPARQL.arc2ParsedQuery = $.parseJSON(data);
//        SPARQL.initNamespaceFromArray(SPARQL.arc2ParsedQuery.prefixes);
//        if(callback){
//          callback();
//        }
//      },
//      error: function(xhr, textStatus, errorThrown) {
//        mw.log(textStatus);
//        mw.log('response: ' + xhr.responseText)
//        mw.log('errorThrown: ' + errorThrown);
//        var errorJson = $.parseJSON(xhr.responseText);
//        SPARQL.showMessageDialog(errorJson.error, 'SPARQL parsing error', 'parseSparqlQueryMsgDialog');
//      }
//
//    });
//  };

  SPARQL.stringifyJSON = function(jsonObject){
    var arrayToJsonFunc = Array.prototype.toJSON;
    if(arrayToJsonFunc && typeof arrayToJsonFunc === 'function'){
      delete Array.prototype.toJSON;
    }
    var result = JSON.stringify(jsonObject);
    Array.prototype.toJSON = arrayToJsonFunc;
    return result;
  };

  SPARQL.switchToSparqlView = function(){
    $('#askQI').hide();
    $('#sparqlQI').show();
    $('#switchToSparqlBtn').hide();
  };

  SPARQL.activateSwitchToSparqBtn = function(){
    $('#askQI #qimenubar').append('<button id="switchToSparqlBtn">' + gLanguage.getMessage('QI_SWITCH_TO_SPARQL') + '</button>');
    var switchToSparqlBtn = $('#switchToSparqlBtn');
    var onOK = function(argMap){
      $('#askQI').hide();
      $('#sparqlQI').show();
      switchToSparqlBtn.hide();
      
      if(argMap && argMap.askQuery){
        var askQuery = argMap.askQuery;
        //split main query and parameters
        var regex = /\[\[.*?\]\]/gmi;
        var match;
        askQuery = askQuery.replace('{{#ask:', '').replace('}}', '');
        var paramString = askQuery;
        var mainQuery = '';
        while(match = regex.exec(askQuery)){
          mainQuery += match[0];
          paramString = paramString.replace(match[0], '');
        }

        SPARQL.askToSparql(mainQuery, paramString);
      }

      
    };

    switchToSparqlBtn.live('click', function(){
      SPARQL.init();
      //get ask query
      var askQuery = window.parent.qihelper.getAskQueryFromGui();
      if(askQuery && askQuery.length > 3){
        //warn user bout the risks of loosing his query
        SPARQL.showConfirmationDialog(gLanguage.getMessage('QI_SWITCH_TO_SPARQL_WARNING'), gLanguage.getMessage('QI_CONFIRMATION'), onOK, {'askQuery': askQuery});
      }
      else{
        onOK({'askQuery': askQuery});
      }
    });
  };

  SPARQL.askToSparql = function(query, parameters, baseURI){
    var askToSparqlUrl = SPARQL.smwgHaloWebserviceEndpoint + '/sparql/translateASK';
    $.ajax({
      type: 'GET',
      url: askToSparqlUrl,
      data: {
        query: query,
        parameters: parameters,
        baseuri: baseURI,
        namespaces: SPARQL.getNamespaceString()
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        if(data && data.length){
          //build parser function string
          SPARQL.queryString = data;
//          $('#sparqlQueryText').val(SPARQL.queryString);
//          $('#sparqlQueryText').data('initialQuery', SPARQL.queryString);
          var sparqlString = SPARQL.queryString;
//          $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(sparqlString));
          //build the tree
          SPARQL.sparqlToTree(sparqlString);
        }
        else{
          //tsc is not reachable
          SPARQL.showMessageDialog('TSC not accessible. Check server: ' + SPARQL.smwgHaloWebserviceEndpoint, 'Empty response from server', 'saskToSparqlMsgDialog');
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        SPARQL.showMessageDialog(errorThrown || xhr.responseText, 'ASK to SPARQL translation error', 'askToSparqlMsgDialog');
      }
    });
  };

  SPARQL.View.activateAddCategoryBtn = function(){
    $('#qiAddCategoryBtn').live('click', function(){
      //get selected node
      var selectedSubjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());      
      if(selectedSubjectNodeText){
        var subject = new SPARQL.Model.SubjectTerm(selectedSubjectNodeText);
        SPARQL.Model.createCategory(subject);
      }
      else{
        SPARQL.Model.createCategory();
      }

    });
  };

  SPARQL.View.openCategoryDialog = function(categoryRestriction){
    SPARQL.View.activateCategoryUpdateBtn();
    SPARQL.View.activateCategoryDeleteLink();

    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();

    //populate the dilaog
    var category_iri = categoryRestriction ? categoryRestriction.getShortNameArray() : [];

    //remove all previously added input rows
    $('#qiCategoryDialogTable tr').not('#categoryInputRow').not('#categoryTypeRow').not('#categoryOrLinkRow').remove();

    $('#qiCategoryDialogTable').find('input').first().focus();
    
    for(var i = 0; i < category_iri.length; i++){
      var categoryName = category_iri[i].replace(/_/g, ' ');
      if(i == 0){
        $('#qiCategoryDialogTable input').val(categoryName);
      }
      else{
        //add OR relation input
        $('#qiAddOrCategoryLink').closest('tr').before(SPARQL.createAdditionalCategoryPanel(categoryName));
      }
    }

    $('#qiCategoryDialogTable input').each(function(){
      $(this).attr('validator', 'iri');
    });

   if(categoryRestriction){
      $('#entityDetailsTd').empty().append('<img src="' + SPARQL.json.categoryIcon + '"><span>' + SPARQL.shortString(categoryRestriction.getString().replace(/_/g, ' ')) + '</span>');
   }

    $('#qiCategoryDialog input[type=text]').live('focus', function(event){
      $(this).attr('oldvalue', $(this).val());
    });
  };

  SPARQL.shortString = function(string){
    var limit = 30;
    if(string && string.length > limit){
      string = string.substr(0, limit - 3) + '...';
    }

    return SPARQL.escapeHtmlEntities(string);
  };

  SPARQL.View.openCategoryDialog.changeName = function(categoryArray){
    var categoryNodeText = SPARQL.View.getSelectedNodeText();
    var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
    var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);
    //change categoty in the model
    SPARQL.Model.updateCategory(new SPARQL.Model.CategoryRestriction(subject, categoryNodeText), categoryArray);
  };

  SPARQL.View.getCategories = function(){
    var categories = [];
    $('#qiCategoryDialogTable input').each(function(){
      categories.push($(this).val());
    });

    return categories;
  };

  
  SPARQL.View.activateAddSubjectBtn = function(){
    $('#qiAddSubjectBtn').live('click', function(){
//      SPARQL.View.openSubjectDialog();
      SPARQL.Model.createSubject();
    });
  };

  
  SPARQL.View.activateCategoryUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      if(SPARQL.Validator.validateAll()){
        SPARQL.View.openCategoryDialog.changeName(SPARQL.View.getCategories());
      }
    });
  };

  SPARQL.View.activatePropertyUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      //blur property value input to trigger change event
      $('#qiPropertyValueNameInput').siblings('input').eq(0).blur();
      if(SPARQL.Validator.validateAll()){
        SPARQL.View.openPropertyDialog.changeName();
      }
    });
  };

  SPARQL.View.activateCategoryDeleteLink = function(){
    $('#qiDeleteLink').unbind();
    $('#qiDeleteLink').click(function(event){
      var categoryNodeText = SPARQL.View.getSelectedNodeText();
      var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
      var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);
      //delete Category corresponding to the selected node
      SPARQL.Model.deleteCategory(new SPARQL.Model.CategoryRestriction(subject, categoryNodeText));
      event.preventDefault();
    });
  };

  SPARQL.View.activatePropertyDeleteLink = function(){
    $('#qiDeleteLink').unbind();
    $('#qiDeleteLink').click(function(event){
      event.preventDefault();
      if(SPARQL.View.getSelectedNodeAttr('rel') === 'filter'){
        return;
      }
      var propertyNodeText = SPARQL.View.getSelectedNodeText();
      var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
      var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);
      //delete Triple corresponding to the selected node
      SPARQL.Model.deleteProperty(new SPARQL.Model.Triple(subject, propertyNodeText));      
    });
  };

  SPARQL.View.activateSubjectUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      if(SPARQL.Validator.validateAll()){
        SPARQL.View.openSubjectDialog.changeName($('#qiSubjectNameInput'), $('#qiSubjectShowInResultsChkBox'));
      }
    });
  };

  SPARQL.View.activateSubjectDeleteLink = function(){
    $('#qiDeleteLink').unbind();
    $('#qiDeleteLink').click(function(event){
      //delete subject corresponding to the selected node
      SPARQL.Model.deleteSubject(new SPARQL.Model.SubjectTerm(SPARQL.View.getSelectedNodeText()));
      if(SPARQL.View.isTreeEmpty()){
        SPARQL.View.cancel();
      }
      event.preventDefault();
    });
  };

  SPARQL.View.openSubjectDialog = function(subject){    
    SPARQL.View.activateSubjectUpdateBtn();
    SPARQL.View.activateSubjectDeleteLink();

    if(subject){
      var subjectName = subject.getShortName('a');
      var isVar = (subject.type === TYPE.VAR);
      var showInResults = !subjectName.length || (isVar && SPARQL.Model.isVarInResults(subject));

      if(isVar){
        $('#entityDetailsTd').empty().append('<img src="' + SPARQL.json.variableIcon + '"><span>' + SPARQL.shortString(subjectName) + '</span>');
        subjectName = '?' + subjectName;
      }
      else{
        $('#entityDetailsTd').empty().append('<img src="' + SPARQL.json.instanceIcon + '"><span>' + SPARQL.shortString(subjectName).replace(/_/g, ' ') + '</span>');
      }
    }
    else{
      subjectName = '';
      isVar = true;
      showInResults = true;

      $('#entityDetailsTd').empty();
    }  
    
    
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').show();
    $('#qiPropertyDialog').hide();

    SPARQL.View.showFilters(subject, $('#qiSubjectFiltersTable'), true);
    
    if(showInResults){
      $('#qiSubjectShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiSubjectShowInResultsChkBox').removeAttr('checked');
    }

    //focus on the input box
    $('#qiSubjectNameInput').focus();

    $('#qiSubjectNameInput').val(subjectName);
    //set 1st time the oldValue mannualy cause we have to set the value after the focus
    $('#qiSubjectNameInput').attr('oldValue', subjectName);


    $('#qiSubjectNameInput').keyup(function(){
      if($.trim($(this).val()).indexOf('?') === 0){
        $('#qiSubjectNameInput').attr('validator', 'variable');
      }
      else{
        $('#qiSubjectNameInput').attr('validator', 'iri');
      }
    });
  }

  SPARQL.View.openSubjectDialog.changeName = function(input, checkBox){
    var subjectNewName = $.trim(input.val());
    var subjectOldName = $.trim(input.attr('oldValue') || '');

    //    var subjectOldType = (subjectOldName.indexOf('?') === 0 ? 'VAR' : 'IRI');
    //    var subjectNewType = (subjectNewName.indexOf('?') === 0 ? 'VAR' : 'IRI');

    if($.trim(input.val()).indexOf('?') === 0){
      checkBox.removeAttr('disabled');
    }
    else{
      checkBox.removeAttr('checked');
      checkBox.attr('disabled', 'disabled');
    }

    if(checkBox){
      var inResults = !!checkBox.attr('checked');
    }

    SPARQL.Model.updateSubject(new SPARQL.Model.SubjectTerm(subjectOldName), new SPARQL.Model.SubjectTerm(subjectNewName), inResults);
  };



  SPARQL.initVariableArray = function(){
    SPARQL.variables = [];
      
    //get variables from triples
    var triples = SPARQL.Model.data.triple;
    for(var i = 0; triples && i < triples.length; i++){
      var triple = triples[i];
      if(triple.subject.type === 'VAR' && $.inArray(triple.subject.value, SPARQL.variables) === -1){
        SPARQL.variables.push(triple.subject.value);
      }
      if(triple.object.value && triple.object.value.length && triple.object.type === 'VAR' && $.inArray(triple.object.value, SPARQL.variables) === -1){
        SPARQL.variables.push(triple.object.value);
      }
    }
    //get variables from category_restrictions
    var categories = SPARQL.Model.data.category_restriction;
    for(i = 0; categories && i < categories.length; i++){
      var category = categories[i];
      if(category.subject.type === 'VAR' && $.inArray(category.subject.value, SPARQL.variables) === -1){
        SPARQL.variables.push(category.subject.value);
      }
    }
    //get variables from projection_var
    var projection_var = SPARQL.Model.data.projection_var;
    for(i = 0; projection_var && i < projection_var.length; i++){
      var variable = projection_var[i];
      if($.inArray(variable, SPARQL.variables) === -1){
        SPARQL.variables.push(variable);
      }
    }
  };
  

  SPARQL.View.openPropertyDialog = function(triple){
    SPARQL.View.activatePropertyUpdateBtn();
    SPARQL.View.activatePropertyDeleteLink();
    var propertyName = triple.predicate.getShortName('property').replace(/_/g, ' ');
    var propertyType = triple.predicate.type;
    var valueName = triple.object.getShortName('a');
    var valueType = triple.object.type;

    var showInResults = SPARQL.Model.isVarInResults(triple.object);
    var valueMustBeSet = !triple.optional;

    $('#entityDetailsTd').empty().append('<img src="' + SPARQL.json.propertyIcon + '"><span>' + SPARQL.shortString(propertyName) + '</span>');    
    
    if(valueType === TYPE.VAR){
      valueName = '?' + valueName;
      $('#qiPropertyDialog #qiPropertyValueNameInput').attr('validator', 'variable');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueNameInput').attr('validator', 'iri');
    }
    SPARQL.View.initPropertyValueCombobox(valueName);

    if(propertyType === TYPE.VAR){
      propertyName = '?' + propertyName;
      $('#qiPropertyNameInput').attr('validator', 'variable');
    }
    else{
      $('#qiPropertyNameInput').attr('validator', 'iri');
    }

    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').show();
    //focus on the input box
    $('#qiPropertyNameInput').focus();
    
    $('#qiPropertyDialog #qiPropertyNameInput').val(propertyName);

    SPARQL.View.showFilters(new SPARQL.Model.Term($('#qiPropertyValueNameInput').val()), $('#qiPropertyFiltersTable'));
    SPARQL.getPropertyInfo(SPARQL.Model.assureFullyQualifiedIRI($('#qiPropertyDialog #qiPropertyNameInput').val()));

    if(valueMustBeSet){
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').removeAttr('checked');
    }
    if(valueType === TYPE.VAR){
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('disabled');
      if(showInResults){
        $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').attr('checked', 'checked');
      }
      else{
        $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('checked');
      }
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').attr('disabled', 'disabled');
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('checked');
    }

    $('#qiPropertyNameInput').attr('constraints', SPARQL.getPropertyAutocompleteConstrains(triple));

    $('#qiPropertyDialog #qiPropertyNameInput').keyup(function(){
      SPARQL.getPropertyInfo(SPARQL.Model.assureFullyQualifiedIRI($(this).val(), 'property'));
      SPARQL.View.setValidator($(this));

      $('#qiPropertyDialog #qiPropertyNameInput').focusout(function(){
        SPARQL.getPropertyInfo(SPARQL.Model.assureFullyQualifiedIRI($(this).val(), 'property'));
        SPARQL.View.setValidator($(this));
      });
    });
    
  };

  SPARQL.getPropertyAutocompleteConstrains = function(triple){
    var categories = [];
    var maxOrCategories = 0;
    var constraints = '';
    $.each(SPARQL.Model.data.category_restriction, function(index, category){
      if(category.subject.isEqual(triple.subject)){
        categories.push(category.getShortNameArray());
        var category_iri = category.category_iri;
        if(category_iri.length > maxOrCategories){
          maxOrCategories = category_iri.length;          
        }
      }
    });

    if(categories.length){
      if(categories.length === 1){
        constraints = 'schema-property-domain: Category:' + categories[0].join(',Category:');
      }
      else if(maxOrCategories === 1){
        constraints = 'schema-property-withsame-domain: Category:' + categories.join(',Category:');
      }
      else{
        constraints = 'schema-property-domain: Category:';
        var delimiter = ',Category:';
        $.each(categories, function(index, value){
          constraints += value.join(delimiter);
          if(index < categories.length - 1){
            constraints += delimiter;
          }
        });         
      }
    }
    if(constraints.length){
      constraints += '|';
    }
    constraints += 'namespace:102';
    return constraints;
  };

  SPARQL.View.initPropertyValueCombobox = function(defaultValue){
    $('#qiPropertyValueNameInput').empty();
    var selected = '';
    var valueExists;
    $.each(SPARQL.variables, function(index, value){
      value = '?' + value;
      if(value === defaultValue){
        valueExists = true;
//        selected = 'selected="selected"';
      }
//      else{
//        selected = '';
//      }
      $('#qiPropertyValueNameInput').append('<option value="' + value + '"' + selected + '>' + value + '</option>');
    });

    if(!valueExists){
//      $('#qiPropertyValueNameInput').prepend('<option value="' + defaultValue + '" selected="selected">' + defaultValue + '</option>');
      $('#qiPropertyValueNameInput').prepend('<option value="' + defaultValue + '">' + defaultValue + '</option>');
    }

    $('#qiPropertyValueNameInput').val(defaultValue);
    
    $('#qiPropertyValueNameInput').combobox('destroy').combobox({
      selected: function(event, ui){
        var selectedValue = ui.item ? ui.item.value : ui.value;
        SPARQL.View.showFilters(new SPARQL.Model.Term(selectedValue), $('#qiPropertyFiltersTable'));
      }
    });    
  };

  SPARQL.View.setValidator = function(element){
    if($.trim(element.val()).indexOf('?') === 0){
      element.attr('validator', 'variable');
    }
    else{
      element.attr('validator', 'iri');
    }
  };

  SPARQL.View.deleteAllFilters = function(filterTable){
    filterTable.find('tr').not(':first').remove();
  };

  SPARQL.getShortName = function(iri, defaultPrefix){
    if(!iri){
      return null;
    }
    var result;
      $.each(SPARQL.Model.data.namespace, function(index, namespace){
        if(iri.substr(0, namespace.namespace_iri.length) === namespace.namespace_iri){
          if(defaultPrefix === namespace.prefix){
            result = iri.replace(namespace.namespace_iri, '');
          }
          else{
            result = iri.replace(namespace.namespace_iri, namespace.prefix + ':');
          }
          return false;
        }
      });

      if(!result){
        result = iri;
      }

      return result;
    };

  /**
   * Show filter inputs in the dialog.
   * @param term Term representing the variable
   * @param dialog dom element of the dialog
   * @param readOnly boolean indicating whether the inputs should be read only or not
   */
  SPARQL.View.showFilters = function(term, dialog, readOnly){
    SPARQL.View.deleteAllFilters(dialog);
    $(dialog).append(SPARQL.createAddAndFilterLink());
    
    if(!dialog){
      return;
    }

    if(term){
      var filters = SPARQL.Model.data.filter;
      for(var i = 0; filters && i < filters.length; i++){
        for(var j = 0; j < filters[i].expression.length; j++){
//          for(var k = 0; k < filters[i].expression[j].argument.length; k++){
            if(filters[i].expression[j].argument[0].isEqual(term)){
              var operator = filters[i].expression[j].operator;
              var value = filters[i].expression[j].argument[1].getShortName('a');
              var datatype = filters[i].expression[j].argument[1].datatype_iri;
              var type = filters[i].expression[j].argument[1].type;
              value = (type ===  TYPE.VAR ? '?' + value : value);
              if(j == 0){
                //add AND filter panel
                var filterTable = $('<tr/>').append($('<td/>').append(SPARQL.createFilterTable(operator, value, SPARQL.getShortName(datatype))));
                $(dialog).find('#qiAddAndFilterLink').closest('tr').before(filterTable);
              }
              else{
                $(dialog).find('#qiAddOrFilterLink').last().closest('tr').before(SPARQL.createFilterPanel(operator, value, SPARQL.getShortName(datatype)));
              }
            }
//          }
        }
      }
    }
    
    if(readOnly){
      //Hide 'add' and 'delete' links and disabled inputs
      if($(dialog).find('.filterOR').length){
        $(dialog).find('.tableSectionTitle').show();
        var orFilterLink = $(dialog).find('#qiAddOrFilterLink');
        orFilterLink.last().remove();
        orFilterLink.text('OR').click(function(){
          return false
        });
      }
      else{
        $(dialog).find('.tableSectionTitle').hide();
        $(dialog).find('#qiAddOrFilterLink').hide();
      }
      
      $(dialog).find('#qiAddAndFilterLink').hide();
      $(dialog).find('#qiDeleteFilterImg').hide();
      $(dialog).find('.filterAND select').filter(':visible').attr('disabled', 'disabled');
      $(dialog).find('.filterAND input').attr('disabled', 'disabled');
    }

  };

  SPARQL.View.openPropertyDialog.changeName = function(){  
    
    var propertyNodeText = SPARQL.View.getSelectedNodeText();
    var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
    var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);

    var oldTriple = new SPARQL.Model.Triple(subject, propertyNodeText);
    var predicate = new SPARQL.Model.PredicateTerm($('#qiPropertyNameInput').val());
    var object = new SPARQL.Model.ObjectTerm($('#qiPropertyValueNameInput').siblings('input').eq(0).val());
//var object = new SPARQL.Model.ObjectTerm($('#qiPropertyValueNameInput').val());
    var newShowInResults = !!$('#qiPropertyValueShowInResultsChkBox').attr('checked');
    var newOptional = !$('#qiPropertyValueMustBeSetChkBox').attr('checked');
    var newTriple = new SPARQL.Model.Triple(subject, predicate, object, newOptional);

    //update filters
    SPARQL.Model.updateFilters(object, SPARQL.View.getFilters(object));

    //change property in the model
    SPARQL.Model.updateProperty(oldTriple, newTriple, newShowInResults);
  };

  
  SPARQL.View.createProperty = function(triple){
    var propertyName = triple.predicate.getShortName('property');
    var valueName = triple.object.getShortName('a');
    var nodeLabel = '';
    if(propertyName.length){
      if(triple.predicate.type === TYPE.VAR){
        nodeLabel += '?';
      }
      nodeLabel += propertyName;
    }
    if(valueName.length){
      nodeLabel += ' ';
      if(triple.object.type === TYPE.VAR){
        nodeLabel += '?';
      }
      nodeLabel += valueName;
    }

    var propertyNodeData = {
      data:{
        title: nodeLabel
      },
      attr: {
        rel: 'property',
        id: triple.getId(),
        title: nodeLabel
      }
    };

    SPARQL.View.getTree().create(SPARQL.View.getNodeById(triple.subject.getId()), 'first' , propertyNodeData, function(){}, true);
  };


  SPARQL.View.activateAddPropertyBtn = function(){
    $('#qiAddPropertyBtn').live('click', function(){
      var selectedSubjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
      if(selectedSubjectNodeText){
        var subject = new SPARQL.Model.SubjectTerm(selectedSubjectNodeText);
        SPARQL.Model.createProperty(subject);
      }
      else{
        SPARQL.Model.createProperty();
      }      
    });
  };

  SPARQL.activateAddAndFilterLink = function(){
    $('#qiAddAndFilterLink').live('click', function(event){
      //display filter gui
      var filterTable = $('<tr/>').append($('<td/>').append(SPARQL.createFilterTable()));
      $(this).closest('tr').before(filterTable);
      event.preventDefault();
    });
  };
  

  SPARQL.View.createFilter =  function(label){
    //get parent property node of the selected node
    var parentPropertyNode = SPARQL.View.getParentNode(SPARQL.View.getSelectedNode(), ['variable', 'property']);

    //create a new filter node
    var filterNode = {
      data:{
        title: label
      },
      attr: {
        rel: 'filter',
        id: parentPropertyNode.attr('id') + '-' + label.replace(/[\s\/:<>!=]/g, '-'),
        title: label
      }
    };

    //append it to property node as child
    SPARQL.View.getTree().create (parentPropertyNode, 'last' , filterNode, function(){}, true );
  };

  SPARQL.View.translateOperator = function(sparqlOperator){
    var operatorMap = {
      LT: '<',
      LE: '<=',
      GT: '>',
      GE: '>=',
      EQ: '=',
      NE: '!=',
      REGEX: 'regex'
    };

    return operatorMap[sparqlOperator] || sparqlOperator;
  };

  SPARQL.View.getParentNode = function(selectedNode, nodeRelAttribute){
    var jstree = $.jstree._reference('qiTreeDiv');
    if(typeof nodeRelAttribute === 'string'){
      nodeRelAttribute = [nodeRelAttribute];
    }
    else if(!$.isArray(nodeRelAttribute)){//invalid type
      return null;
    }
    while(selectedNode !== -1 && selectedNode.length && $.inArray(selectedNode.attr('rel'), nodeRelAttribute) === -1){
      selectedNode = jstree._get_parent(selectedNode);
    }

    return selectedNode;
  };


  SPARQL.activateAddOrFilterLink = function(){
    $('#qiAddOrFilterLink').live('click', function(event){
      $(this).closest('tr').before(SPARQL.createFilterPanel());
      event.preventDefault();
      
    });
  };

  SPARQL.activateDeleteFilterImg = function(){
    $('#qiDeleteFilterImg').live('click', function(){
      if($(this).closest('table').find('#qiDeleteFilterImg').length == 1){
        $(this).closest('table').remove();
      }
      else{
        $(this).closest('tr').remove();
      }      
    });
  };
  
  /**
   * Get full iri for given datatype.
   * If a given datatype is not known then use default iri: instance
   * @param type string datatype
   * @return string full iri
   */
  SPARQL.getDataTypeIRI = function(type){
    if(!(type && type.length)){
     return null;
    }

    var result = null;
//    var defaultPrefix = 'a';
//    var defaultIri = null;
    var prefix = /^(\w+):(\w+)$/.exec(type);
    if(prefix && prefix.length){
      var iri = null;
      var namespace = SPARQL.Model.data.namespace;
      for(var i = 0; i < namespace.length; i++){
        if(namespace[i].prefix === prefix[1]){
          iri = namespace[i].namespace_iri;
          break;
        }
//        else if(namespace[i].prefix === defaultPrefix){
//          defaultIri = namespace[i].namespace_iri;
//        }
      }
      if(iri){
        result = iri + prefix[2];
      }
      else{
//        result = defaultIri + prefix[2];
        result = type;
      }
    }

    return result;
  };

  SPARQL.View.getFilters = function(term){
    var filters = [];    

    //iterate over AND filters
    var filtersAND = $('#qiPropertyDialog').find('.filterAND');
    filtersAND.each(function(){
      //create new filter object
      var filter = {
        expression: []
      };
      //iterate over OR filters
      $(this).find('.filterOR').each(function(){
        //create new expression object
        var operator = $(this).find('select.filterOperator').val();
        var value = $.trim($(this).find('input.filterValue').val());
        var selectType = $(this).find('select.filterType').val();
        var propertyType = SPARQL.getDataTypeIRI($('#qiPropertyDialog').find('.typeLabelTd').next().text());
        var dataType = propertyType || SPARQL.getDataTypeIRI(selectType);
        var type = TYPE.LITERAL;
        if(operator === 'regex'){
          dataType = null;
        }
        else if(value.indexOf('?') === 0){
           type = TYPE.VAR;
           dataType = null;
        }
        else if(dataType === SPARQL.getDataTypeIRI('tsctype:page')){
           type = TYPE.IRI;
           dataType = null;
        }
        
        if(value.length){
          var argument2 = new SPARQL.Model.FilterArgumentTerm(value, type, dataType);
          var expression = new SPARQL.Model.FilterExpression(operator, term, argument2);

          //add expression object to the array
          filter.expression.push(expression);
        }
      });
      //add filter object to array
      filters.push(filter);
    });

    return filters;
  }

  SPARQL.View.updateFilters = function(operandTerm){  
    //iterate over filters array
    var filters = SPARQL.Model.data.filter || [];
    for(var i = 0; filters && i < filters.length; i++){
      var filterLabel = '';
      for(var j = 0; j < filters[i].expression.length; j++){
        var expression = filters[i].expression[j];
        for(var k = 0; k < expression.argument.length; k++){
          //add filter node for each filter whose expression argument type and value equal to the arguments
          if(expression.argument[k].isEqual(operandTerm)){
            filterLabel += SPARQL.View.translateOperator(expression.operator) + ' ' + expression.argument[k^1].getShortName('a');
          } 
        }
        if(filterLabel && filterLabel.length && j < filters[i].expression.length - 1){
          filterLabel += ' or ';
        }        
      }
      //create filter node
      if(filterLabel.length){
        SPARQL.View.createFilter(filterLabel);
      }
    }
  };

  SPARQL.getOperatorValues = function(type, values){
    var operators = $.extend({}, values);
    
    switch(type){
      case 'tsctype:page':
      case 'xsd:anyURI':
      case 'tsctype:record':
        delete operators.LT;
        delete operators.LE;
        delete operators.GT;
        delete operators.GE;
        break;

      case 'xsd:boolean':
        delete operators.LT;
        delete operators.LE;
        delete operators.GT;
        delete operators.GE;
        delete operators.regex;
        break;

      case 'xsd:dateTime':
      case 'xsd:date':
        delete operators.regex;
        break;

      default:
        break;
    }    

    return operators
  }
  

  SPARQL.createFilterPanel = function(operator, value, type){
    var validatorType = $('#qiPropertyTypeLabel').next().html() || type;

    var operatorValues = {
      LT: gLanguage.getMessage('QI_LT') + ' (<)',
      LE: gLanguage.getMessage('QI_LT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (<=)',
      GT: gLanguage.getMessage('QI_GT') + ' (>)',
      GE: gLanguage.getMessage('QI_GT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (>=)',
      EQ: gLanguage.getMessage('QI_EQUAL') + ' (=)',
      NE: gLanguage.getMessage('QI_NOT') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (!=)',
      regex: gLanguage.getMessage('QI_LIKE') + ' (regexp)'
    };

    var typeValues = ['xsd:string', 'xsd:double', 'xsd:decimal', 'xsd:integer', 'xsd:date', 'xsd:dateTime'];

    var operatorSelect = SPARQL.createSelectBox(SPARQL.getOperatorValues(validatorType, operatorValues), {'class': 'filterOperator'}, operator);
    var tr = $('<tr/>')
              .attr('class', 'filterOR')
                .append($('<td/>').append(operatorSelect));

    var input = $('<input type="text"/>').attr('class', 'filterValue');

    var typeSelect = SPARQL.createSelectBox(typeValues, {'class': 'filterType'}, type, function(event){
      $(this)
        .closest('tr')
          .find('select.filterOperator')
            .replaceWith(SPARQL.createSelectBox(SPARQL.getOperatorValues($(this).val(), operatorValues), {'class': 'filterOperator'}, operator));
    });
    var deleteImg = $('<img/>')
                      .attr('id', 'qiDeleteFilterImg')
                        .attr('title', 'Delete filter')
                          .attr('src', mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png');
                          
    var filterPanel = tr
            .append($('<td/>').append(input))
              .append($('<td/>').append(typeSelect))
                .append($('<td/>').append(deleteImg));

    if(value && value.length){
      input.attr('value', value);
    }
    if($('#qiQuerySourceSelect').val() !== 'tsc' && !$('#qiPropertyTypeLabel').next().html().length){
      validatorType = typeSelect.val();
      typeSelect.closest('td').show();
    }
    else{
      typeSelect.closest('td').hide();      
    }    
    
    input.keyup(function(){
      if($.trim($(this).val()).indexOf('?') === 0){
        input.attr('validator', 'variable');
      }
      else{
        input.attr('validator', validatorType);
      }
    });

    input.attr('validator', validatorType).keyup();

    return filterPanel;
  };
 

  SPARQL.createFilterTable = function(operator, value, type){
    return $('<table/>')
      .attr('class', 'filterAND')
        .append(SPARQL.createFilterPanel(operator, value, type))
          .append(SPARQL.createAddOrFilterLink());
  };

  SPARQL.createAddOrFilterLink = function(){
    return '<tr><td colspan="4" style="text-align:center;"><a href="" id="qiAddOrFilterLink">' + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a></td><tr>';
  };

  SPARQL.createAddAndFilterLink = function(){
    return '<tr><td><a href="" id="qiAddAndFilterLink">' + gLanguage.getMessage('QI_DC_ADD_NEW_FILTER') + '</a></td></tr>';
  };

  SPARQL.createAdditionalCategoryPanel = function(categoryName){
    categoryName = categoryName || '';
    return '<tr><td></td><td>'
    + '<input id="qiCategoryNameInput-' + SPARQL.getNextUid() + '" class="qiCategoryNameInput wickEnabled" '
    + 'type="text" autocomplete="OFF" constraints="namespace: 14" value="' + categoryName + '" validator="iri"/>'
    + '</td><td><img id="qiDeleteCategoryImg" title="Delete category" src="'
    + mw.config.get('wgServer')
    + mw.config.get('wgScriptPath')
    + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/></td></tr>'
    + '<tr><td></td><td id="qiSubjectTypeLabel"></td><td></td></tr>';
  };

  SPARQL.activateAddOrCategoryLink = function(){
    $('#qiAddOrCategoryLink').live('click', function(event){
      event.preventDefault();
      var tr = $(this).closest('tr');
      tr.before(SPARQL.createAdditionalCategoryPanel(''));
      tr.closest('table').find('input').last().focus();
    });
  };

  SPARQL.activateDeleteCategoryImg = function(){
    $('#qiDeleteCategoryImg').live('click', function(){
      //remove the input controls
      var tr = $(this).closest('tr');
      tr.next().remove();
      tr.remove();
      //set focus on the remaining category input
      $('#qiAddOrCategoryLink').closest('tr').find('input').focus();
    });
  };

  SPARQL.escapeCssSelector = function(selector){
    return selector ? selector.replace(/([\_\:\/])/g, '\\$1') : selector;
  };
 

  SPARQL.renderTree = function(){
    var treeJsonConfig = {
      json_data : {
        data : []
      }
    };
    

    treeJsonConfig.plugins = [ "themes", "json_data", "ui", "crrm", "types" ];
    treeJsonConfig.themes = {
      "theme" : "apple",
      "dots" : true,
      "icons" : true
    };
    treeJsonConfig.ui = {
      "select_limit" : 1
    };
    treeJsonConfig.types = {
      "types" : {
        "variable" : {
          "icon" : {
            "image" : SPARQL.json.variableIcon
          }
        },
        "instance" : {
          "icon" : {
            "image" : SPARQL.json.instanceIcon
          }
        },
        "category" : {
          "icon" : {
            "image" : SPARQL.json.categoryIcon
          }
        },
        "property" : {
          "icon" : {
            "image" : SPARQL.json.propertyIcon
          }
        },
        "filter" : {
          "icon" : {}
        }
      }
    };
   
    var tree = $("#qiTreeDiv");

    tree.bind("loaded.jstree",
      function(event, data) {
        //do stuff when tree is loaded
        SPARQL.activateCancelLink();
        SPARQL.View.activateAddSubjectBtn();
        SPARQL.View.activateAddCategoryBtn();
        SPARQL.View.activateAddPropertyBtn();
      });

    tree.jstree(treeJsonConfig);

    tree.bind('create.jstree',
      function(NODE, REF_NODE){
        //        var newId = SPARQL.View.getDomPath(REF_NODE.rslt.obj);
        //        $(REF_NODE.rslt.obj).attr('id', newId);
//        REF_NODE.inst.select_node(REF_NODE.rslt.obj, true, false);
        REF_NODE.inst.select_node(REF_NODE.rslt.obj, true);
      });
    tree.bind('delete_node.jstree',
      function(NODE, REF_NODE){
        var prevNode = REF_NODE.inst._get_prev(REF_NODE.rslt.obj);
        if(prevNode){
          var nodeId = prevNode.attr('id');
          REF_NODE.inst.select_node('#' + nodeId, true);
        }
      });

    tree.bind("select_node.jstree",
      function(NODE, REF_NODE) {
        if(REF_NODE.args[2] === false){
          return;
        }
        //get node data
        var theNode = SPARQL.View.getParentNode($(REF_NODE.rslt.obj), ['variable', 'instance', 'category', 'property']);
        var nodeText = SPARQL.View.getNodeText(theNode);
        var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
        var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);
        

        switch(theNode.attr('rel')){
          case 'variable':
          case 'instance':
            SPARQL.View.openSubjectDialog(new SPARQL.Model.SubjectTerm(SPARQL.View.getNodeText(theNode)));
            break;

          case 'category':
            SPARQL.View.openCategoryDialog(new SPARQL.Model.CategoryRestriction(subject, nodeText));
            break;

          case 'property':
            SPARQL.View.openPropertyDialog(new SPARQL.Model.Triple(subject, nodeText));
            break;

          default:
            break;
        }
      });

    //set jstree themes custom location
    $.jstree._themes = mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/themes/';
  };

  SPARQL.View.getDomPath = function(element){
    return $(element).parentsUntil('#qiTreeDiv').andSelf().map(function() {
      var tagName = this.nodeName;
      if ($(this).siblings(tagName).length > 0) {
        tagName += "-" + $(this).nextAll(tagName).length;
      }
      return tagName;
    }).get().join("-").toLowerCase();
  };

  SPARQL.updateAllFromTree = function(){
    if(SPARQL.validateQueryTree()){
      SPARQL.treeToSparql(null, true);
      SPARQL.updateSortOptions();
    }
  };
 

//  SPARQL.View.deleteProperty = function(dataEntity){
//    var nodeId = SPARQL.View.getNodeId(dataEntity);
//
//    var jstree = $.jstree._reference('qiTreeDiv');
//    var selectedNode = jstree.get_selected();
//    if(selectedNode.attr('id') === nodeId){
//      jstree.delete_node(selectedNode);
//      SPARQL.View.map.remove(nodeId, dataEntity);
//    }
//  };

 

  SPARQL.View.cancel = function(){
    $('#entityDetailsTd').empty();
    $('#previewcontent').empty();
    SPARQL.toTree(null, -1);
    
  //    SPARQL.View.getTree().deselect_all();
  //
  //    $('#qiCategoryDialog').hide();
  //    $('#qiSubjectDialog').hide();
  //    $('#qiPropertyDialog').hide();
  };

  //unselect tree nodes and close the dialogs
  SPARQL.activateCancelLink = function(){
    $('#qiCancelLink').live('click', function(event){
      SPARQL.View.cancel();
      event.preventDefault();
    });
  };
  
  SPARQL.activateToggleLinks = function() {
    SPARQL.activateFormatLink();
    SPARQL.activateResultPreviewLink();
  };

  SPARQL.activateFormatLink = function(){
    $('#sparqlQI #qiQueryFormatTitle span').toggle(
      function(){
        $('#sparqlQI #qiQueryFormatContent').show();
        $('#sparqlQI #layouttitle-link').removeClass("plusminus");
        $('#sparqlQI #layouttitle-link').addClass("minusplus");
        SPARQL.getQueryParameters($('#sparqlQI #layout_format').children(':selected').val());
      },
      function(){
        $('#sparqlQI #qiQueryFormatContent').hide();
        $('#sparqlQI #layouttitle-link').removeClass("minusplus");
        $('#sparqlQI #layouttitle-link').addClass("plusminus");
      }
      );
  };

  SPARQL.activateResultPreviewLink = function(){
    $('#sparqlQI #previewtitle span').toggle(
      function(){
        $('#sparqlQI #previewcontent').hide();
        $('#sparqlQI #previewtitle-link').removeClass("minusplus");
        $('#sparqlQI #previewtitle-link').addClass("plusminus");
      },
      function(){
        $('#sparqlQI #previewcontent').show();
        $('#sparqlQI #previewtitle-link').removeClass("plusminus");
        $('#sparqlQI #previewtitle-link').addClass("minusplus");
      }
      );
  };
  

  SPARQL.validateQueryTree = function(queryTree){
    queryTree = queryTree || SPARQL.Model.data;
    //if tree is empty fail silently
    if(!(queryTree.triple && queryTree.triple.length)
      && !(queryTree.category_restriction && queryTree.category_restriction.length))
      {
      return false;
    }
    //if tree is not empty but none of the vars has 'show in results' set display a message
    if(queryTree.projection_var.length == 0){
      SPARQL.showMessageDialog(gLanguage.getMessage('QI_SHOW_IN_RESULTS_MUST_BE_SET'), gLanguage.getMessage('QI_INVALID_QUERY'), 'validateQueryTreeMsgDialog');
      return false;
    }
    return true;
  };

  //  SPARQL.showLoadingIcon = function(element){
  //    $(element).content().hide();
  //    var loadingElement = $(element).find();
  //    $(element).prepend('<div class="loadingIconDiv"><img src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/></div>');
  //  };
  //
  //  SPARQL.hideLoadingIcon = function(element){
  //    $(element).find('div.loadingIcon').remove();
  //  };

  SPARQL.getQueryResult = function(queryString){
    queryString = queryString || SPARQL.getNamespaceString() + SPARQL.queryString;
    if(!SPARQL.validateQueryString(queryString)){
      $('#sparqlQI #previewcontent').html('Your query is empty');
      return;
    }
    
    $('#previewcontent').empty().append('<img src="' + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/>');

    $.ajax({
      type: 'POST',
      url: mw.config.get('wgScriptPath') + '/index.php?action=ajax',
      data: {
        rs: 'smwf_qi_getSparqlQueryResult',
        rsargs: [ queryString, SPARQL.getParameterString()]
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        if(data && data.length){
          SPARQL.processResultHtml(data, $('#sparqlQI #previewcontent'));
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        $('#sparqlQI #previewcontent').empty();
        SPARQL.showMessageDialog(errorThrown || xhr.responseText, null, 'getQueryResultMsgDialog');

      }
    });
  };

  SPARQL.processResultHtml = function(html, domElement){
    //if html is empty add error message
    if(!(html && html.length)){
      return '<span style="color:red">Check that TSC is up and running and connected to your wiki</span>';
    }
    //else
    //override document ready
    SPARQL.initResultFormatLoading();

    //get inline scripts
    html = SPARQL.getInitScripts(html);

    //set the result html
    domElement.html(html);

    //execute inline scripts
    SPARQL.appendScripts(domElement, SPARQL.srfInitScripts);
    SPARQL.executeInitMethods();
    
  };

  SPARQL.updateSortOptions = function(){
    var projection_var = SPARQL.Model.data.projection_var || [];
    var selectBoxElement = $('#qiQueryFormatContent #layout_sort');
    var selectedOption = selectBoxElement.children().filter(':selected');
    selectBoxElement.empty();
    selectBoxElement.append('<option></option>');
    for(var i = 0; i < projection_var.length; i++){
      var varName = projection_var[i];
      if(varName === selectedOption.text())
        selectBoxElement.append('<option selected="selected">' + varName + '</option>');
      else
        selectBoxElement.append('<option>' + varName + '</option>');
    }
  };

  SPARQL.activateSortSelectBox = function(){
    $('#qiQueryFormatContent #layout_sort').change(function(){
      //get selected value
      var selectedOption = $(this).children().filter(':selected');
      var orderObject = [];
      //update query object
      if(selectedOption.length && selectedOption.text()){
        orderObject =  [{
          by_var: selectedOption.text(),
          ascending: true
        }];
        SPARQL.Model.data.order = orderObject;
      }
      else{
        delete SPARQL.Model.data.order;
      }
      SPARQL.treeToSparql();
    });
  };

  SPARQL.activateFormatSelectBox = function(){
    $('#qiQueryFormatContent #layout_format').change(function(){
      var selectedValue = $(this).children(':selected').val();
      SPARQL.queryParameters['format'] = selectedValue;
      SPARQL.getQueryParameters(selectedValue);
      SPARQL.treeToSparql(null, true);
    });
  };

  SPARQL.View.reset = function(){
    $('#sparqlQI #qiTreeDiv').empty();
    $('#sparqlQI #sparqlQueryText').val('');
    $('#sparqlQI #sparqlQueryText').removeData();
    $('#sparqlQI #qiSparqlParserFunction').val('');
    $('#sparqlQI #previewcontent').html('');
  };


  SPARQL.hide = function(){
    $('#askQI').show();
    $('#sparqlQI').hide();
    $('#switchToSparqlBtn').show();
  };

 

  /**
   * Build a jstree json object out of sparql query json object
   */
  SPARQL.toTree = function(queryJsonObject, selectedNodeId){
    SPARQL.initVariableArray();
    SPARQL.Model.moveOptionalTriplesToEnd();
    
    var jstree = SPARQL.View.getTree();
    
    //clear the tree nodes    
    jstree.delete_node(jstree._get_children(-1));

    //use internal data structure if none specified
    queryJsonObject = queryJsonObject || SPARQL.Model.data;
    var subjectArray = [];
    
    //iterate over triples
    var triples = queryJsonObject.triple;
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      if(!SPARQL.isObjectInArray(triple.subject, subjectArray)){
        subjectArray.push(triple.subject);
        SPARQL.View.createSubject(triple.subject);
      }
      
      SPARQL.View.createProperty(triple);
      SPARQL.View.updateFilters(triple.object);
    }
    //iterate over category_restriction
    var category_restrictions = queryJsonObject.category_restriction;    
    for (i = 0; i < category_restrictions.length; i++){
      var category_restriction = category_restrictions[i];
      if(!SPARQL.isObjectInArray(category_restriction.subject, subjectArray)){
        subjectArray.push(category_restriction.subject);
        SPARQL.View.createSubject(category_restriction.subject);
      }
     
      SPARQL.View.createCategory(category_restriction);
    }

    //member of projection_var which doesnt appear anywhere else is an empty (newly created) subject    
    var projection_var = SPARQL.Model.data.projection_var;
    for(i = 0; i < projection_var.length; i++){
      var subjectTerm = new SPARQL.Model.SubjectTerm(projection_var[i], TYPE.VAR);
      if(!SPARQL.Model.isTermInModel(subjectTerm)){
        SPARQL.View.createSubject(subjectTerm);
      }
    }

    //if the tree is empty then close the dialogs
    if(!jstree._get_children(-1).length || selectedNodeId === -1){
      jstree.deselect_all();
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').hide();
    }
    //otherwise select the nodes that were selected before rebuild
    else if(jstree._get_node('#' + selectedNodeId)){
      jstree.select_node('#' + selectedNodeId, true);
    }
    initToolTips();
    SPARQL.updateAllFromTree();
  };
  

  SPARQL.View.getCategoryNodeLabel = function(category_iri){
    var result = '';
    for(var i = 0; i < category_iri.length; i++){
      result += category_iri[i].replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, '');
      if(i < category_iri.length - 1){
        result += ' or ';
      }
    }

    return result;
  };

  SPARQL.initTabs = function(){
    $('#sparqlQI').tabs();
    $('#sparqlQI #qiDefTab').tabs({
      select: function(event, ui) {
        switch(ui.index){
          case 0:
            SPARQL.sparqlToTree(SPARQL.getNamespaceString() + $('#sparqlQueryText').val());
            break;

          case 1:
            SPARQL.treeToSparql();
            break;

          case 2:
            SPARQL.treeToSparql();
            break;
        }
      }
    });
  };

  //add function to array only if it's not there yet
  SPARQL.addInitMethod = function(func){
    if(!SPARQL.isFunctionInArray(func, SPARQL.srfInitMethods)){
      SPARQL.srfInitMethods.push(func);
    }
  };

  SPARQL.isFunctionInArray = function(someFunction, arrayOfFunctions){
    var result = false;
    if(typeof someFunction === 'function' && arrayOfFunctions && arrayOfFunctions.length){
      $.each(arrayOfFunctions, function(key, value){
        if(value.toString() == someFunction.toString()){
          result = true;
          return false; //break the loop
        }
      });
    }
    return result;
  };

  //execute init methods of result format modules registered via overriden $(document).ready methosd
  SPARQL.executeInitMethods = function(){
    var initMethods = SPARQL.srfInitMethods || [];

    for(var i = 0; i < initMethods.length; i++){
      try{
        //method 'smw_sortables_init' when applied more than once causes multiple sort headers to appear
        //so if there are visible sort headers already then remove them
        var method = initMethods[i];
        if((method.name == 'smw_sortables_init' || method.toString().indexOf('function smw_sortables_init') > -1)
          && $('.sortheader').filter(':visible').length > 0)
        {
          $('th a.sortheader').each(function(){
            $(this).parent().html($(this).siblings('span').eq(0).text());
          });
        }
        method();

      }
      catch(x){
        //exceptions are expected so just continue
        mw.log('EXCEPTION: ' + x);
      }
    }
  };

  //override jquery.ready and addOnloadHook methods to save the functions passed to them as arguments
  SPARQL.initResultFormatLoading = function(){
    $.fn.ready = SPARQL.documentReady;
    addOnloadHook = SPARQL.documentReady;
  };

  SPARQL.documentReady = function(someFunction){
    SPARQL.addInitMethod(someFunction);
  };

  SPARQL.getInitScripts = function(text){
    var scriptRegexp = new RegExp(/\<script[^\>]*\>[\s\S]*?\<\/script\>/gmi);
    var noscript = text;
    var match;
    while(match = scriptRegexp.exec(text)){
      var script = match[0].replace(/\r\n/, '');
      if($.inArray(script, SPARQL.srfInitScripts) === -1){
        SPARQL.srfInitScripts.push(script);
      }
      noscript = noscript.replace(match[0], '');
    }

    return noscript;
  };

  SPARQL.appendScripts = function(domElement, scriptArray){
    for(var i = 0; i < scriptArray.length; i++){
      $(domElement).append(scriptArray[i]);
    }
  };

  SPARQL.activateResetQueryBtn = function(){
    $('#sparqlQI #qiResetQueryButton').live('click', function(){
      SPARQL.Model.reset();
      SPARQL.getWikiPrefixes();
      SPARQL.View.cancel();
      $('#qiQuerySourceSelect').next().val('tsc').change();
      $('#qiQueryGraphSelect').next().val('default').change();
      $('#sparqlQI #layout_format').val('table').change();
    });
  };



  SPARQL.activateFullPreviewLink = function(){
    $('#sparqlQI #qiFullPreviewLink').live('click', function(event){
      var html = $('#sparqlQI #previewcontent').html() || gLanguage.getMessage('QI_EMPTY_QUERY');
      $('#sparqlQI #previewcontent').html('');
      SPARQL.processResultHtml(html, SPARQL.showMessageDialog(html, gLanguage.getMessage('QI_QUERY_RESULT'), 'fullPreviewDialog', function(){
        SPARQL.processResultHtml(html, $('#sparqlQI #previewcontent'));
      }));
      event.preventDefault();
    });
  };

  SPARQL.View.updateFilterInput = function(){
    var select = $('#qiPropertyFiltersTable').find('select.filterType');
    var type = $('#qiPropertyTypeLabel').next().text();

    if(type.length){
      select.each(function(){
        $(this).parent().hide();
      });
    }
    else{
      select.each(function(){
        $(this).parent().show();
      });
    }

  };

  SPARQL.getPropertyInfo = function(propertyName){    
    window.clearTimeout(window.getPropertyTypeTimeout);
    window.getPropertyTypeTimeout = window.setTimeout(function(){
      if($('#qiQuerySourceSelect').val() !== 'tsc'){
        $('#qiPropertyTypeLabel').html('Type: unknown');
        $('#qiPropertyTypeLabel').next().html('');
        SPARQL.View.updateFilterInput();
        return;
      }
      if(!(propertyName && propertyName.length)){
        return;
      }
      $.ajax({
        type: 'POST',
        url: mw.config.get('wgScriptPath') + '/index.php?action=ajax',
        data: {
          rs: 'smwf_qi_QIAccess',
          rsargs: [ 'getPropertyInformation', propertyName ]
        },
        success: function(data, textStatus, jqXHR) {
          mw.log('data: ' + data);
          mw.log('textStatus: ' + textStatus);
          mw.log('jqXHR.responseText: ' + jqXHR.responseText);
          SPARQL.processPropertyInfo(data);          
        },
        error: function(xhr, textStatus, errorThrown) {
          mw.log(textStatus);
          mw.log('response: ' + xhr.responseText)
          mw.log('errorThrown: ' + errorThrown);
          $('#sparqlQI #previewcontent').empty();
          SPARQL.showMessageDialog(errorThrown || xhr.responseText, null, 'getPropertyInfoMsgDialog');
        }
      });
    }, 500);
  };

  SPARQL.getQueryParameters = function(selectedResultPrinter){
    //show loading image
    $('#sparqlQI #qiQueryParameterTable').remove();
    $('#sparqlQI #qiQueryFormatContent').append('<img src="' + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/>');

    $.ajax({
      type: 'POST',
      url: mw.config.get('wgScriptPath') + '/index.php?action=ajax',
      data: {
        rs: 'smwf_qi_QIAccess',
        rsargs: [ 'getSupportedParameters', selectedResultPrinter ]
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        SPARQL.buildQueryParameterTable(data);
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        $('#sparqlQI #previewcontent').empty();
        SPARQL.showMessageDialog(errorThrown || xhr.responseText, null, 'getQueryParametersMsgDialog');

      }
    });
  };

  SPARQL.buildQueryParameterTable = function(data){
    //remove old table
    $('#sparqlQI #qiQueryFormatContent img').remove();

    var table = $('<table id="qiQueryParameterTable"/>');
    var tr = $('<tr/>');

    //parse json string
    var paramsJsonObj = $.parseJSON(data);
    var columnLimit = 4;
    var columnCount = 0;
    //for each parameter
    for(var i = 0; i < paramsJsonObj.length; i++){
      var parameter = paramsJsonObj[i];
      if($.inArray(parameter.name, ['format', 'default']) === -1){
        var inputElement;
        //if there is 'values' array then create listbox
        if(parameter.values && parameter.values.length){
          inputElement = SPARQL.buildQueryParameterTable.createListbox(parameter.name, parameter.values, parameter.defaultValue, parameter.description);
        }
        //if type == boolean then create checkbox
        else if(parameter.type === 'boolean'){
          inputElement = SPARQL.buildQueryParameterTable.createCheckbox(parameter.name, parameter.defaultValue, parameter.description);
        }
        //else create inputbox
        else{
          inputElement = SPARQL.buildQueryParameterTable.createInputbox(parameter.name, parameter.defaultValue, parameter.description);
        }
        tr.append(inputElement);
        columnCount++;
        
        if(columnCount === columnLimit){
          columnCount = 0;
          
          table.append(tr);
          tr = $('<tr/>');
        }
      }
    }
    //less than 'columnLimit' columns in row
    if(tr.children().length){
      table.append(tr);
    }
    $('#sparqlQI #qiQueryFormatContent').append(table);
    initToolTips();
  };

  SPARQL.buildQueryParameterTable.createCheckbox = function(name, defaultValue, description){
    var labelTd = $('<td/>');
    labelTd.append(name);
    var inputTd = $('<td/>');
    var checkBox = $('<input type="checkbox"/>');
    checkBox.attr('checked', !!defaultValue);
    if(description){
      checkBox.attr('title', description);
    }
    
    checkBox.change(function(){
      var selectedVal = $(this).val();
      if(selectedVal !== defaultValue){
        SPARQL.queryParameters[name] = selectedVal;
      }
      else{
        delete SPARQL.queryParameters[name];
      }
      SPARQL.setQueryParameter(name, selectedVal, defaultValue);
      SPARQL.getQueryResult();
    });
    inputTd.append(checkBox);
    return labelTd.add(inputTd);
  };

  SPARQL.buildQueryParameterTable.createListbox = function(name, valuesArray, defaultValue, description){
    var labelTd = $('<td/>');
    labelTd.append(name);
    var inputTd = $('<td/>');
    if(description){
      var attributes = {'title': description};
    }

    var select = SPARQL.createSelectBox(valuesArray, attributes, defaultValue, function(event){
      var selectedVal = $(this).val();
      var name = event.data.name;
      if(selectedVal !== defaultValue){
        SPARQL.queryParameters[name] = selectedVal;
      }
      else{
        delete SPARQL.queryParameters[name];
      }

      SPARQL.setQueryParameter(name, selectedVal, defaultValue);
      SPARQL.getQueryResult();

    }, {'name' : name});

    inputTd.append(select);
    return labelTd.add(inputTd);
  };

  SPARQL.buildQueryParameterTable.createInputbox = function(name, defaultValue, description){
    var labelTd = $('<td/>');
    labelTd.append(name);
    var inputTd = $('<td/>');
    var input = $('<input type="text"/>');
    input.attr('param', name);
    if(defaultValue && defaultValue.length){
      input.attr('value', defaultValue);
    }
    if(description && description.length){
      input.attr('title', description);
    }

    input.bind('keyup change', function(){
      var selectedVal = $(this).val();
      if(selectedVal && selectedVal !== defaultValue){
        SPARQL.queryParameters[name] = selectedVal;
      }
      else{
        delete SPARQL.queryParameters[name];
      }
     
      SPARQL.setQueryParameter(name, selectedVal, defaultValue);
      SPARQL.getQueryResult();
    });

    inputTd.append(input);
    return labelTd.add(inputTd);
  };

  SPARQL.processPropertyInfo = function(data){
    var type = '';
    if(data){
      //parse xml data
      var xmlDoc = GeneralXMLTools.createDocumentFromString(data);
      type = $(xmlDoc).find('param').attr('name');
      var xsdType = $(xmlDoc).find('param').attr('xsdType');
      type = type || '';
      xsdType = xsdType || '';
    }
    
    //diplay type label under property name input
    $('#qiPropertyTypeLabel').html('Type: ' + type);
    $('#qiPropertyTypeLabel').next().html(xsdType).hide();

    SPARQL.View.updateFilterInput();
  };

  SPARQL.initOperatorSelectBox = function(xsdType, selectElement){
    switch(xsdType){
      case 'xsd:string':
      case 'tsctype:page':
      case 'xsd:anyURI':
      case 'tsctype:record':
        selectElement.find('option[value="LT"]').remove();
        selectElement.find('option[value="LE"]').remove();
        selectElement.find('option[value="GT"]').remove();
        selectElement.find('option[value="GE"]').remove();
        break;

      case 'xsd:boolean':
        selectElement.find('option[value="LT"]').remove();
        selectElement.find('option[value="LE"]').remove();
        selectElement.find('option[value="GT"]').remove();
        selectElement.find('option[value="GE"]').remove();
        selectElement.find('option[value="regex"]').remove();
        break;

      case 'xsd:dateTime':
      case 'xsd:date':
        selectElement.find('option[value="regex"]').remove();
        break;

      default:
        break;
    }
  };

  SPARQL.createSelectBox = function(values, attributes, defaultValue, changeHandler, changeHandlerData){
    var select = $('<select/>');
    var selected = null;
    
    if(attributes && typeof attributes === 'object'){
      $.each(attributes, function(key, value){
        select.attr(key, value);
      });
    }

    $.each(values, function(key, value){
      var selectValue = key;
      if($.isArray(values)){
        selectValue = value;
      }
      if(selectValue === defaultValue){
        selected = ' selected="selected"';
      }
      else{
        selected = '';
      }

      select.append('<option value="' + selectValue + '"' + selected + '>' + value + '</option>');
    });

    if(changeHandler){
      changeHandlerData = changeHandlerData || {};
      changeHandlerData.values = values;
      changeHandlerData.attributes = attributes;
      changeHandlerData.defaultValue = defaultValue;

      select.bind('change', changeHandlerData, changeHandler);
    }

    return select;
  };


  SPARQL.setQueryParameter = function(paramName, value, defaultValue){
      if(value !== defaultValue){
        SPARQL.queryParameters[paramName] = value;
      }
      else{
        delete SPARQL.queryParameters[paramName];
      }

      $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(SPARQL.getNamespaceString() + $('#sparqlQueryText').val()));
  };

  SPARQL.initSourceSelectBox = function(){
    $('#qiSourceSelect').replaceWith(SPARQL.createSelectBox(SPARQL.sources, {id: 'qiQuerySourceSelect'}, 'tsc', function(event){
//      SPARQL.setQueryParameter('source', $(this).val(), event.data.defaultValue);
//      SPARQL.getPropertyInfo($('#qiPropertyNameInput').val());
//      SPARQL.getQueryResult();
    }));

    $('#qiQuerySourceSelect').combobox({
      selected: function(){
        SPARQL.setQueryParameter('source', $(this).val(), 'tsc');
        SPARQL.getPropertyInfo($('#qiPropertyNameInput').val());
        SPARQL.getQueryResult();
      }
    });
  };

  SPARQL.initGraphSelectBox = function(){
    $('#qiGraphSelect').replaceWith(SPARQL.createSelectBox(SPARQL.graphs, {id: 'qiQueryGraphSelect'}, 'default', function(event){
//      SPARQL.setQueryParameter('graph', $(this).val(), event.data.defaultValue);
//      SPARQL.getQueryResult();
    }));

    $('#qiQueryGraphSelect').combobox({
      selected: function(){
        SPARQL.setQueryParameter('graph', $(this).val(), 'default');
        SPARQL.getQueryResult();
      }
    });
  };

  SPARQL.getWikiPrefixes = function(){
    $.ajax({
      type: 'POST',
      url: mw.config.get('wgScriptPath') + '/index.php?action=ajax',
      data: {
        rs: 'smwf_ts_getWikiPrefixes'
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('WikiNamespaces......textStatus: ' + textStatus);
        mw.log('WikiNamespaces......jqXHR.responseText: ' + jqXHR.responseText);
        //init namespaces array
        SPARQL.initNamespace(data);
        SPARQL.initNamespaceInfoImg();
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        SPARQL.showMessageDialog(xhr.responseText || errorThrown, 'Failed to get wiki namespace prefixes', 'getWikiPrefixesMsgDialog');
      }
    });
  };

  SPARQL.initNamespace = function(namespaceString){
    var splitByPrefix = namespaceString.split(/[\r\n]/);
    var namespace = [];
    var pattern = /PREFIX\s+([^:]+):<([^\>]+)>/;
    var match = null;
    $.each(splitByPrefix, function(index, value){
      if(match = pattern.exec(value)){
        var prefix = $.trim(match[1]);
        var namespace_iri = $.trim(match[2]);
          namespace.push({prefix: prefix, namespace_iri: namespace_iri});
      }
    });

    if(namespace.length){
      SPARQL.Model.data.namespace = SPARQL.Model.removeNamespaceDuplicates(namespace);
    }
  };

  SPARQL.escapeHtmlEntities = function(string){
    return string.replace(/&/g, '&amp')
                  .replace(/</g, '&lt')
                  .replace(/>/g, '&gt')
                  .replace(/\r*\n/g, '<br/>');
  };

  SPARQL.buildNamespaceHtmlTable = function(){
    var namespaces = SPARQL.Model.data.namespace;
    var html = '<table class="smwtable" id="namespaceTable"><tr><th>Prefix</th><th>Namespace</th></tr>';

    $.each(namespaces, function(index, namespace){
      html += '<tr><td>' + namespace.prefix + '</td><td>' + namespace.namespace_iri + '</td></tr>\n';
    });

    html += '</table>';
  
    return html;
  };

  SPARQL.getNamespaceString = function(){
    var namespaces = SPARQL.Model.data.namespace;
    var result = '';

    $.each(namespaces, function(index, namespace){
      result += 'PREFIX ' + namespace.prefix + ': <' + namespace.namespace_iri + '>\r\n';
    });

    return result.length ? result : null;

  };

  SPARQL.initNamespaceInfoImg = function(){
    if($('#qiShowNamespacesImg').length){
      return;
    }
    SPARQL.initResultFormatLoading();
    mw.loader.using('ext.smw.sorttable', function(){
      var src = mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/info.png';
      var img = $('<img id="qiShowNamespacesImg"/>').attr('src', src);
      img.click(function(){
        var dialog = SPARQL.showMessageDialog(SPARQL.buildNamespaceHtmlTable(), 'Namespaces', 'namespaceDialog', null, false, true, false);
        SPARQL.executeInitMethods();
        
        if(dialog){
          dialog.dialog('option', 'buttons', []);
          dialog.dialog('option', 'show', 'slide');
          dialog.dialog('open');
          
          dialog.parent().position({            
            my: 'left bottom',
            at: 'right top',
            of: $('#sparqlQI'),
            offset: '40 -30',
            collision: 'none'
          });
          dialog.css({
            'font-family': 'tahoma',
            'font-size': '11px'
          })
          
        }
        
      });

      $('#qiDefTab').children('ul').append($('<li/>').append(img));
    })
  };

  SPARQL.init = function(){        
      SPARQL.Model.reset();
      SPARQL.getWikiPrefixes();      
      SPARQL.activateAddAndFilterLink();
      SPARQL.activateDeleteFilterImg();
      SPARQL.activateAddOrFilterLink();
      SPARQL.activateAddOrCategoryLink();
      SPARQL.activateDeleteCategoryImg();
      SPARQL.renderTree();
      SPARQL.toTree(SPARQL.Model.data);
      SPARQL.activateToggleLinks();
      SPARQL.activateSortSelectBox();
      SPARQL.activateFormatSelectBox();
      SPARQL.activateUpdateSourceBtn();
      SPARQL.activateDiscardChangesLink();
      SPARQL.initTabs();
      SPARQL.activateResetQueryBtn();
      SPARQL.activateFullPreviewLink();
      SPARQL.initSourceSelectBox();
      SPARQL.initGraphSelectBox();
  };


  $(document).ready(function(){
//    $.ajaxSetup({
//      xhr: SPARQL.getXHR(),
//      accepts: {
//        xml: "application/xml, text/xml",
//        html: "text/html",
//        script: "text/javascript, application/javascript",
//        json: "application/json, text/javascript",
//        text: "text/plain",
//        _default: "*/*"
//      }
//    });
    
    SPARQL.activateSwitchToSparqBtn();
  }); 


})(jQuery);
