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
  
  SPARQL = {
    uid: 0,
    iri_delim: '/',
    parserFuncString: '',
    queryString: null,
    queryParameters: {
      source: 'tsc',
      format: 'table'
    },
    srfInitMethods: [],
    srfInitScripts: [],
    sources: ['tsc', 'http://dbpedia.org/sparql'],
    graphs: ['default', 'none', 'null', 'any'],
    variables: [],

    json : {

      variableIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif',
      instanceIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/instance_icon.gif',
      categoryIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif',
      propertyIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif',

      TreeQuery : {}
    },
    
    //View component. Takes care of sparql treeview manipulation
    View: {
      jstreeId: 'qiTreeDiv',
      map: {
        treeToData: []
      }
    }

  };

  
  SPARQL.View.map.put = function(nodeId, nodeData){
    if(!nodeId || !nodeData){
      return;
    }
    SPARQL.View.map.treeToData[nodeId] = nodeData;
  };

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
      SPARQL.sparqlToTree($('#sparqlQueryText').val());
      SPARQL.getQueryResult($('#sparqlQueryText').val());
      $('#sparqlQueryText').data('initialQuery', $('#sparqlQueryText').val());
    });
  };


  SPARQL.activateDiscardChangesLink = function(){
    $('#discardChangesLink').live('click', function(event){
      var initialQuery = $('#sparqlQueryText').data('initialQuery');
      if(initialQuery && initialQuery.length > 1){
        $('#sparqlQueryText').val($('#sparqlQueryText').data('initialQuery'));
      }
      event.preventDefault();
    });
  };


  SPARQL.sparqlToTree = function(sparqlQuery){
    sparqlQuery = sparqlQuery || SPARQL.queryString;
    if(!(sparqlQuery && sparqlQuery.length) || sparqlQuery.match(/SELECT\s*\*\s*WHERE\s*{[\s\r\n]*}\s*/gi))
      return;

    mw.log('sparql query send:\n' + sparqlQuery);
    //send ajax post request to localhost:8080/sparql/sparqlToTree
    $.ajax({
      type: 'POST',
      url: SPARQL.smwgHaloWebserviceEndpoint + '/sparql/sparqlToTree',
      data: {
        sparql: sparqlQuery
      },
      beforeSend: function(jqXHR, settings){
        mw.log('data: ' + $(this).data.sparql);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        SPARQL.queryString = sparqlQuery;
        $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(sparqlQuery));
        if(data && typeof data === 'object'){
          SPARQL.Model.init(data);
          SPARQL.updateSortOptions();
          SPARQL.toTree();
//          if(SPARQL.validateQueryTree(SPARQL.Model.data)){
//            SPARQL.getQueryResult(SPARQL.queryString);
//          }
        }
        else{
          //tsc is not reachable
          SPARQL.showMessageDialog('TSC not accessible. Check server: ' + SPARQL.smwgHaloWebserviceEndpoint, 'Empty response from server');
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        SPARQL.showMessageDialog(errorJson.error, gLanguage.getMessage('QI_INVALID_QUERY'));
      }
    });
  };

  SPARQL.showMessageDialog = function(message, title, anchorElement, callback, modal){
    if(!(anchorElement && anchorElement.length)){
      anchorElement = $('#sparqlQI');
    }
    if($('#dialogDiv').length === 0){
      var dialogDiv = $('<div id="dialogDiv"/>');
      anchorElement.append(dialogDiv);

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
          if(callback && typeof callback === 'function'){
            callback();
          }
          dialogDiv.remove();
        }
      });
      dialogDiv.html(message);
      dialogDiv.dialog('open');
      if(dialogDiv.height() > 600){
        dialogDiv.dialog('option', 'height', 600);
      }
      if(dialogDiv.width() > 1000){
        dialogDiv.dialog('option', 'height', 1000);
      }
    }
    return dialogDiv;
  };

  SPARQL.buildParserFuncString = function(queryString){
    queryString = queryString || SPARQL.queryString;
    if(!(queryString && queryString.length)){
      return '';
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
      url: SPARQL.smwgHaloWebserviceEndpoint + '/sparql/treeToSPARQL',
      data: {
        tree: jsonString
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        if(data && data.query){
          SPARQL.queryString = data.query;
          var parserFuncString = SPARQL.buildParserFuncString(data.query);
          $('#sparqlQueryText').val(SPARQL.queryString);
          $('#sparqlQueryText').data('initialQuery', SPARQL.queryString);
          $('#qiSparqlParserFunction').val(parserFuncString);
          if(getQueryResult){
            if(SPARQL.validateQueryTree(treeJsonConfig)){
              SPARQL.getQueryResult(SPARQL.queryString);
            }
          }
        }
        else{
          //tsc is not reachable
          SPARQL.showMessageDialog('TSC not accessible. Check server: ' + SPARQL.smwgHaloWebserviceEndpoint, 'Empty response from server');
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        SPARQL.showMessageDialog(errorJson.error, 'SPARQL tree to string convertion error');
      }

    });
  };

  SPARQL.stringifyJSON = function(jsonObject){
    var arrayToJsonFunc = Array.prototype.toJSON;
    if(arrayToJsonFunc && typeof arrayToJsonFunc === 'function'){
      delete Array.prototype.toJSON;
    }
    var result = JSON.stringify(jsonObject);
    Array.prototype.toJSON = arrayToJsonFunc;
    return result;
  };

  SPARQL.activateSwitchToSparqBtn = function(){
    $('#askQI #qimenubar').append('<button id="switchToSparqlBtn">' + gLanguage.getMessage('QI_SWITCH_TO_SPARQL') + '</button>');
    var switchToSparqlBtn = $('#switchToSparqlBtn');
    switchToSparqlBtn.live('click', function(){
      $('#askQI').hide();
      $('#sparqlQI').show();
      switchToSparqlBtn.remove();

      //get ask query
      var askQuery = window.parent.qihelper.getAskQueryFromGui();
      if(askQuery && askQuery.length > 3){
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
        baseuri: baseURI
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        //build parser function string
        SPARQL.queryString = data;
        $('#sparqlQueryText').val(SPARQL.queryString);
        $('#sparqlQueryText').data('initialQuery', SPARQL.queryString);
        $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(SPARQL.queryString));
        //build the tree
        SPARQL.sparqlToTree(SPARQL.queryString);
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        SPARQL.showMessageDialog(errorThrown || xhr.responseText, 'ASK to SPARQL translation error');
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
      $('#qiPropertyValueNameInput').siblings('input').eq(0).change();
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
    SPARQL.getPropertyInfo($('#qiPropertyDialog #qiPropertyNameInput').val());    

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

    $('#qiPropertyDialog #qiPropertyNameInput').keyup(function(){
      SPARQL.getPropertyInfo($(this).val());
      SPARQL.View.setValidator($(this));

      $('#qiPropertyDialog #qiPropertyNameInput').focusout(function(){
        SPARQL.getPropertyInfo($(this).val());
        SPARQL.View.setValidator($(this));
      });
    });
    
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
      $('#qiPropertyValueNameInput').prepend('<option value="' + defaultValue + '" selected="selected">' + defaultValue + '</option>');
      
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
          for(var k = 0; k < filters[i].expression[j].argument.length; k++){
            if(filters[i].expression[j].argument[k].isEqual(term)){
              var operator = filters[i].expression[j].operator;
              var value = filters[i].expression[j].argument[k^1].value;
              var type = filters[i].expression[j].argument[k^1].datatype_iri;
              if(j == 0){
                //add AND filter panel
                var filterTable = $('<tr/>').append($('<td/>').append(SPARQL.createFilterTable(operator, value, SPARQL.getShortName(type))));
                $(dialog).find('#qiAddAndFilterLink').closest('tr').before(filterTable);
              }
              else{
                $(dialog).find('#qiAddOrFilterLink').last().closest('tr').before(SPARQL.createFilterPanel(operator, value, SPARQL.getShortName(type)));
              }
            }
          }
        }
      }
    }
    
    if(readOnly){
      //Hide 'add' and 'delete' links and disabled inputs
      if($(dialog).find('.filterOR').length){
        $(dialog).find('#qiAddOrFilterLink').text('OR').click(function(){
          return false
        });
      }
      else{
        $(dialog).find('#qiAddOrFilterLink').hide();
      }
      if(!(filters && filters.length)){
        $(dialog).find('.tableSectionTitle').hide();
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
    var object = new SPARQL.Model.ObjectTerm($('#qiPropertyValueNameInput').val());
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
  
  
  SPARQL.getDataTypeIRI = function(type){
    var result;
    var prefix = /^(\w+):(\w+)$/.exec(type);
    if(prefix && prefix.length){
      var iri;
      var namespace = SPARQL.Model.data.namespace;
      for(var i = 0; i < namespace.length; i++){
        if(namespace[i].prefix === prefix[1]){
          iri = namespace[i].namespace_iri;
          break;
        }
      }
      result = iri + prefix[2];
    }
    else{
      result = type;
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
        var type = $(this).find('select.filterType').val();
        var dataType = SPARQL.getDataTypeIRI($('#qiPropertyDialog').find('.typeLabelTd').next().text());        
        dataType = dataType || SPARQL.getDataTypeIRI(type);
        dataType = (operator === 'regex') ? null : dataType;
        if(value.length){
          var argument2 = new SPARQL.Model.FilterArgumentTerm(value, TYPE.LITERAL, dataType);
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
            filterLabel += SPARQL.View.translateOperator(expression.operator) + ' ' + expression.argument[k^1].value;
          } 
        }
        if(j < filters[i].expression.length - 1){
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
                          .attr('src', mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png');
                          
    var filterPanel = tr
            .append($('<td/>').append(input))
              .append($('<td/>').append(typeSelect))
                .append($('<td/>').append(deleteImg));

    if(value && value.length){
      input.attr('value', value);
    }
    if(!$('#qiPropertyTypeLabel').next().html().length){
      typeSelect.closest('td').show();
      input.attr('validator', typeSelect.val());
    }
    else{
      typeSelect.closest('td').hide();
      input.attr('validator', validatorType);
    }

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
    $.jstree._themes = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/themes/';
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

  //delete selected tree node
  //  SPARQL.View.activateDeleteLink = function(){
  //    $('#qiDeleteLink').live('click', function(event){
  //      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
  //        return;
  //
  //      var selectedNodeId = $.jstree._focused().get_selected().attr('id');
  //      var dataEntity = SPARQL.View.getDataEntity(selectedNodeId);
  //
  //      if(dataEntity){
  //        switch(dataEntity.type){
  //          case 'subject':
  //            SPARQL.Model.deleteSubject(dataEntity);
  //            break;
  //
  //          case 'category':
  //            SPARQL.Model.deleteCategory(dataEntity);
  //            break;
  //
  //          case 'property':
  //            SPARQL.Model.deleteProperty(dataEntity);
  //            break;
  //
  //          default:
  //            break;
  //        }
  //      }
  //
  //      //don't open the link address
  //      event.preventDefault();
  //    });
  //  };



  //  SPARQL.deleteFromQueryObject = function(selectedNode){
  //    var type = selectedNode.attr('gui');
  //    switch(type){
  //      case 'subject':
  //        SPARQL.Model.deleteSubject(selectedNode);
  //        break;
  //
  //      case 'category':
  //        SPARQL.deleteCategory(selectedNode);
  //        break;
  //
  //      case 'property':
  //        SPARQL.deleteProperty(selectedNode);
  //        break;
  //
  //      default:
  //        break;
  //    }
  //  };

 

  SPARQL.View.deleteProperty = function(dataEntity){
    var nodeId = SPARQL.View.getNodeId(dataEntity);

    var jstree = $.jstree._reference('qiTreeDiv');
    var selectedNode = jstree.get_selected();
    if(selectedNode.attr('id') === nodeId){
      jstree.delete_node(selectedNode);
      SPARQL.View.map.remove(nodeId, dataEntity);
    }
  };

  //  SPARQL.deleteCategory = function(selectedNode){
  //    //remove this category from category_restriction
  //    var category = selectedNode.attr('iri') + SPARQL.iri_delim + selectedNode.attr('name');
  //    SPARQL.Model.data.category_restriction = SPARQL.Model.data.category_restriction || [];
  //    var category_restriction = SPARQL.Model.data.category_restriction;
  //    for(i = 0; i < category_restriction.length; i++){
  //      var categori_iri = category_restriction[i].category_iri;
  //      var categoryIndex = $.inArray(category, categori_iri);
  //      if(categoryIndex > -1){
  //        categori_iri.splice(categoryIndex, 1);
  //        break;
  //      }
  //    }
  //  };

  

  //  SPARQL.View.deleteSubject = function(subject){
  //    //get node id
  //    var nodeId = SPARQL.View.getNodeId(dataEntity);
  //
  //    //if selected node id = this subject node id then remove the node
  //    if(SPARQL.View.getSelectedNodeAttr('id') === nodeId){
  //      SPARQL.View.map.remove(nodeId);
  //    }
  //
  //    SPARQL.toTree();
  //
  //    if($.jstree._reference('qiTreeDiv')._get_children(-1).length === 0){
  //      SPARQL.View.cancel();
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
      SPARQL.showMessageDialog(gLanguage.getMessage('QI_SHOW_IN_RESULTS_MUST_BE_SET'), gLanguage.getMessage('QI_INVALID_QUERY'));
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
    queryString = queryString || SPARQL.queryString;
    if(!(queryString && queryString.length)){
      $('#sparqlQI #previewcontent').html('');
      return;
    }
    
    $('#previewcontent').empty().append('<img src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/>');

    $.ajax({
      type: 'POST',
      url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
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
        SPARQL.showMessageDialog(errorThrown || xhr.responseText);

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
      SPARQL.treeToSparql();
    });
  };

  SPARQL.View.reset = function(){
    $('#sparqlQI #qiTreeDiv').empty();
    $('#sparqlQI #sparqlQueryText').val('');
    $('#sparqlQI #sparqlQueryText').removeData();
    $('#sparqlQI #qiSparqlParserFunction').val('');
    $('#sparqlQI #previewcontent').html('');
  };

 

  //  SPARQL.addSubject = function(treeJsonObject, queryJsonObject, triple){
  //    var subjectName = triple.subject.value;
  //    var subjectType = triple.subject.type;
  //    var subjectShortName = SPARQL.getShortName(subjectName);
  //    var subjectIRI = SPARQL.getIRI(subjectName);
  //    var subjectShowInResults = SPARQL.isInProjectionVars(queryJsonObject, subjectShortName);
  //    var iconFile = SPARQL.json.instanceIcon;
  //
  //    if(SPARQL.getSubjectIndex(treeJsonObject, subjectShortName) === -1){
  //
  //      var subjectAttributes = {
  //        id: 'subject-' + SPARQL.getNextUid(),
  //        name: subjectShortName,
  //        gui: 'subject',
  //        columnlabel: subjectShortName,
  //        iri: subjectIRI,
  //        title: SPARQL.getShortName(subjectName),
  //        type: subjectType,
  //        showinresults: subjectShowInResults
  //      };
  //
  //      if(subjectType === 'VAR'){
  //        iconFile = SPARQL.json.variableIcon;
  //        subjectAttributes.title = '?' + subjectName;
  //        delete subjectAttributes.iri;
  //      }
  //      treeJsonObject.json_data.data.push(
  //      {
  //        data : {
  //          title : subjectAttributes.title,
  //          icon : iconFile
  //        },
  //        attr : subjectAttributes,
  //        children : [],
  //        state : 'open'
  //      });
  //    }
  //  };
     

  //  SPARQL.isInProjectionVars = function(queryJsonObject, subjectName){
  //    if(!(subjectName && queryJsonObject.projection_var && queryJsonObject.projection_var.length))
  //      return false;
  //
  //    var index = $.inArray(subjectName, queryJsonObject.projection_var);
  //    return (index > -1);
  //  };

  //  SPARQL.getValidName = function(varName){
  //    return varName.substring(varName.lastIndexOf(':') + 1, varName.length);
  //  };

  //  SPARQL.isVariable = function(argument){
  //    var result = false;
  //    if(typeof argument === 'object'){
  //      result = (argument.type === 'VAR');
  //    }
  //    else if(typeof argument === 'string'){
  //      result = (argument.indexOf('?') === 0);
  //    }
  //    return result;
  //  };

  //  SPARQL.getSubject = function(treeJsonObject, subjectName){
  //    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectName);
  //    return index > -1 ? treeJsonObject.json_data.data[index] : null;
  //  };

  //find subjectName in treeJsonObject
  //  SPARQL.getSubjectIndex = function(treeJsonObject, subjectName){
  //    var result = -1;
  //    if(treeJsonObject.json_data.data){
  //      for(var i = 0; i < treeJsonObject.json_data.data.length; i++){
  //        var nodeData = treeJsonObject.json_data.data[i];
  //        if(nodeData.attr && nodeData.attr.name){
  //          if(nodeData.attr.name === subjectName){
  //            return i;
  //          }
  //        }
  //        else{
  //          if(nodeData.data === subjectName)
  //            return i;
  //        }
  //      }
  //    }
  //    return result;
  //  };


  //  SPARQL.addCategoryToSubject = function(treeJsonObject, subjectName, categoryName){
  //    //create a node for the first category in the array
  //
  //    //add the rest of categories as children to the first one
  //
  //    var categoryShortName = SPARQL.getShortName(categoryName);
  //    var categoryIRI = SPARQL.getIRI(categoryName);
  //    var subjectShortName = SPARQL.getShortName(subjectName);
  //
  //    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectShortName);
  //    if(index > -1){
  //      treeJsonObject.json_data.data[index].children.push(
  //      {
  //        data: {
  //          title: categoryShortName,
  //          icon: SPARQL.json.categoryIcon
  //        },
  //        attr: {
  //          id: 'category-' + SPARQL.getNextUid(),
  //          name: categoryShortName,
  //          gui: 'category',
  //          iri: categoryIRI,
  //          title: categoryName
  //        }
  //      });
  //    }
  //  };

  //  SPARQL.addPropertyToSubject = function(treeJsonObject, queryJsonObject, triple){
  //    var subjectName = triple.subject.value;
  //    var propertyName = triple.predicate.value;
  //    var propertyType = triple.predicate.type;
  //    var propertyValueName = triple.object.value;
  //    var propertyValueType = triple.object.type;
  //    var valueMustBeSet = !triple.optional;
  //    var showInResutlts = (triple.object.type === 'VAR' && SPARQL.isInProjectionVars(queryJsonObject, propertyValueName));
  //
  //    var propertyIRI = SPARQL.getIRI(propertyName);
  //    var propertyShortName = SPARQL.getShortName(propertyName);
  //    var propertyValueShortName = SPARQL.getShortName(propertyValueName);
  //    var subjectShortName = SPARQL.getShortName(subjectName);
  //
  //    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectShortName);
  //    var nodeTitle = '';
  //    if(index > -1){
  //      if(propertyType === 'VAR')
  //        nodeTitle += '?';
  //      nodeTitle += propertyShortName + ' ';
  //      if(propertyValueType === 'VAR')
  //        nodeTitle += '?';
  //      nodeTitle += propertyValueShortName;
  //
  //      treeJsonObject.json_data.data[index].children.push(
  //      {
  //        data: {
  //          title: nodeTitle,
  //          icon: SPARQL.json.propertyIcon
  //        },
  //        attr: {
  //          id: 'property-' + SPARQL.getNextUid(),
  //          name: propertyShortName,
  //          valuename: propertyValueShortName,
  //          columnlabel: propertyValueShortName,
  //          valuemustbeset: valueMustBeSet,
  //          showinresults: showInResutlts,
  //          gui: 'property',
  //          iri: propertyIRI,
  //          valuetype: propertyValueType,
  //          type: propertyType
  //        }
  //      });
  //    }
  //  };


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
            SPARQL.sparqlToTree($('#sparqlQueryText').val());
            break;

          case 1:
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
    });
  };



  SPARQL.activateFullPreviewLink = function(){
    $('#sparqlQI #qiFullPreviewLink').live('click', function(event){
      var html = $('#sparqlQI #previewcontent').html() || gLanguage.getMessage('QI_EMPTY_QUERY');
      $('#sparqlQI #previewcontent').html('');
      SPARQL.processResultHtml(html, SPARQL.showMessageDialog(html, gLanguage.getMessage('QI_QUERY_RESULT'), null, function(){
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
        url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
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
          SPARQL.showMessageDialog(errorThrown || xhr.responseText);
        }
      });
    }, 500);
  };

  SPARQL.getQueryParameters = function(selectedResultPrinter){
    //show loading image
    $('#sparqlQI #qiQueryParameterTable').remove();
    $('#sparqlQI #qiQueryFormatContent').append('<img src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/>');

    $.ajax({
      type: 'POST',
      url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
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
        SPARQL.showMessageDialog(errorThrown || xhr.responseText);

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
      SPARQL.treeToSparql();
    });
    inputTd.append(checkBox);
    return labelTd.add(inputTd);
  };

  SPARQL.buildQueryParameterTable.createListbox = function(name, valuesArray, defaultValue, description){
    var labelTd = $('<td/>');
    labelTd.append(name);
    var inputTd = $('<td/>');
    var select = $('<select/>');
    select.attr('param', name);
    var selected = '';
    if(description){
      select.attr('title', description);
    }
    
    for(var index = 0; index < valuesArray.length; index++){
      var value = valuesArray[index];
      if(value === defaultValue){
        selected = ' selected="selected"';
      }
      else{
        selected = '';
      }
      select.append('<option value="' + value + '"' + selected + '>' + value + '</option>');
    }

    select.change(function(){
      var selectedVal = $(this).children(':selected').val();
      if(selectedVal !== defaultValue){
        SPARQL.queryParameters[name] = selectedVal;
      }
      else{
        delete SPARQL.queryParameters[name];
      }
      SPARQL.treeToSparql();
    });
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
     
      SPARQL.treeToSparql();
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

  SPARQL.createSelectBox = function(values, attributes, defaultValue, changeHandler){
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
      select.bind('change', {defaultValue: defaultValue}, changeHandler);
    }

    return select;
  };


//  SPARQL.createSelectBox_ = function(name, values, defaultValue, changeHandler, id){
//    var select = $('<select/>');
//    if(id){
//      select.attr('id', id);
//    }
//    var selected = null;
//    for(var i = 0; i < values.length; i++){
//      var value = values[i];
//      if(value === defaultValue){
//        selected = ' selected="selected"';
//      }
//      else{
//        selected = '';
//      }
//
//      select.append('<option value="' + value + '"' + selected + '>' + value + '</option>');
//    }
//
//    select.change(changeHandler);
//
//    return select;
//  };

  SPARQL.setQueryParameter = function(paramName, value, defaultValue){
      if(value !== defaultValue){
        SPARQL.queryParameters[paramName] = value;
      }
      else{
        delete SPARQL.queryParameters[paramName];
      }
      SPARQL.treeToSparql();
  };

  SPARQL.initSourceSelectBox = function(){
    $('#qiSourceSelect').replaceWith(SPARQL.createSelectBox(SPARQL.sources, {id: 'qiQuerySourceSelect'}, 'tsc', function(event){
      SPARQL.setQueryParameter('source', $(this).val(), event.data.defaultValue);
      SPARQL.getPropertyInfo($('#qiPropertyNameInput').val());
    }));
  };

  SPARQL.initGraphSelectBox = function(){
    $('#qiGraphSelect').replaceWith(SPARQL.createSelectBox(SPARQL.graphs, null, 'default', function(event){
      SPARQL.setQueryParameter('graph', event.data.value, event.data.defaultValue);
    }));
  };

  SPARQL.getWikiPrefixes = function(){
    $.ajax({
      type: 'POST',
      url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
      data: {
        rs: 'smwf_ts_getWikiPrefixes'
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('WikiNamespaces......textStatus: ' + textStatus);
        mw.log('WikiNamespaces......jqXHR.responseText: ' + jqXHR.responseText);
        //init namespaces array
        SPARQL.namespaceString = data;
        SPARQL.initNamespace(data);
        SPARQL.initNamespaceInfoImg();
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        SPARQL.showMessageDialog(xhr.responseText || errorThrown, 'Failed to get wiki namespace prefixes');
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
      SPARQL.Model.data.namespace = namespace;
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

  SPARQL.initNamespaceInfoImg = function(){
    SPARQL.initResultFormatLoading();
    mw.loader.using('ext.smw.sorttable', function(){
      var src = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/info.png';
      var img = $('<img/>').attr('src', src);
      img.click(function(){
        if($('#dialogDiv').length && $('#dialogDiv').dialog('isOpen')){
          $('#dialogDiv').dialog('close');
        }
        else{
          SPARQL.showMessageDialog(SPARQL.buildNamespaceHtmlTable(), 'Namespaces', null, null, false);
          SPARQL.executeInitMethods();
        }
      });

      $('#qiDefTab').children('ul').append($('<li/>').append(img));
    })
  };


  $(document).ready(function(){
    SPARQL.smwgHaloWebserviceEndpoint = mw.config.get('smwgHaloWebserviceEndpoint');
    if(SPARQL.smwgHaloWebserviceEndpoint){
      SPARQL.getWikiPrefixes();
      SPARQL.smwgHaloWebserviceEndpoint = 'http://' + SPARQL.smwgHaloWebserviceEndpoint;
      SPARQL.Model.reset();
      SPARQL.activateSwitchToSparqBtn();
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
    }
  });


})(jQuery);
//          namespace: [
//            {
//              prefix: "tsctype",
//              namespace_iri: "http://www.ontoprise.de/smwplus/tsc/unittype#"
//            },
//            {
//              prefix: "xsd",
//              namespace_iri: "http://www.w3.org/2001/XMLSchema#"
//            },
//            {
//              prefix: "rdf",
//              namespace_iri: "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
//            }
//          ],
//        projection_var: ["a","y"],
//        triple: [
//        {
//          subject: {
//            type: "IRI",
//            value: "girlfriend"
//          },
//          predicate: {
//            type: "IRI",
//            value: "does"
//          },
//          object: {
//            type: "VAR",
//            value: "a"
//          },
//          optional: true
//        },
//        {
//          subject: {
//            type: "VAR",
//            value: "y"
//          },
//          predicate: {
//            type: "IRI",
//            value: "likes"
//          },
//          object: {
//            type: "VAR",
//            value: "a"
//          },
//          optional: false
//        }
//        ],
//        filter: [
//        {
//          expression: [
//          {
//            operator: "LT",
//            argument: [
//            {
//              type: "VAR",
//              value: "a"
//            },
//            {
//              type: "LITERAL",
//              value: "7",
//              datatype_iri: "http://www.w3.org/2001/XMLSchema#int"
//            }
//            ]
//          }
//          ]
//        }
//        ],
//        order: [{
//          ascending: false,
//          by_var: ["y"]
//        }],
//        offset: 10,
//        limit: 100,
//
//        category_restriction : [
//        {
//          subject: {
//            type: "VAR",
//            value: "a"
//          },
//          category_iri : [
//          "http://localhost/mediawiki/category:boyfriend"
//          ]
//        }
//        ]
//      }



