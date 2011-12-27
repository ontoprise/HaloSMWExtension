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
    //    value = SPARQL.stringifyJSON(value);
    SPARQL.View.map.treeToData[nodeId] = nodeData;
  //    SPARQL.View.map.dataToTree[value] = key;
  };

  SPARQL.View.getNodeId = function(nodeData){
    var treeToDataArray = SPARQL.View.map.treeToData;
    var result;
    for(var nodeId in treeToDataArray){
      if(nodeId && SPARQL.objectsEqual(treeToDataArray[nodeId], nodeData)){
        result = nodeId;
        break;
      }
    }

    return result; 
  };

  SPARQL.View.getTree = function(){
    return $.jstree._reference(SPARQL.View.jstreeId);
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

  SPARQL.View.createCategory = function(categoryRestriction){
    var categoryNodeData = {
      data:{
        title: categoryRestriction.getString()
      },
      attr: {
        rel: 'category'
      }
    };
  
    SPARQL.View.getTree().create(SPARQL.View.getSelectedSubjectNode(), 'first' , categoryNodeData, function(){}, true );
  };

  SPARQL.View.createSubject = function(subject){

    var dataEntity = {
      type: 'subject',
      value: subject
    };
    //if this subject already exists then return
    if(SPARQL.View.getNodeId(dataEntity)){
      return;
    }

    var subjectName = subject.value;
    var rel;
    if(subject.type === 'VAR'){
      subjectName = '?' + subjectName;
      rel = 'variable';
    }
    else{
      subjectName = subjectName.replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, '');
      rel = 'instance';
    }

    var subjectNodeData = {
      data:{
        title: subjectName
      },
      attr: {
        id: subjectName,
        rel: rel
      },
      children: []
    };

    var jstree = SPARQL.View.getTree();
    
    jstree.create (jstree, 'first' , subjectNodeData, function(){}, true);
  };
  

  
  SPARQL.View.updateSubject = function(subjectOld, subjectNew){
    var jstree = $.jstree._reference('qiTreeDiv');
    var selectedNode = jstree.get_selected();
    var subjectOldObj = {
      type: 'subject',
      value: subjectOld
    };
    var nodeId = SPARQL.View.getNodeId(subjectOldObj);
    if(nodeId === selectedNode.attr('id')){
      var subjectType = subjectNew.type;
      if(subjectType === 'VAR'){
        jstree.set_text(selectedNode, '?' + subjectNew.value);
        jstree.set_type('variable', selectedNode);
      }
      else{
        jstree.set_text(selectedNode, subjectNew.value.replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, ''));
        jstree.set_type('instance', selectedNode);
      }
      
      SPARQL.View.map.put(nodeId, {
        type: 'subject',
        value: subjectNew
      });
    }    
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
    for(var i = 0; i < array.length; i++){
      if(SPARQL.objectsEqual(object, array[i])){
        return true;
      }
    }
    return false;
  };

  SPARQL.View.updateCategory = function(nodeData, newCategories){
   
    var nodeId = SPARQL.View.getNodeId(nodeData);
    nodeData.category_iri = SPARQL.View.buildCategoryIRIArray(newCategories);
    SPARQL.View.map.put(nodeId, nodeData);
    
    //set text for this node
    var tree = $.jstree._reference('qiTreeDiv');
    var categoryNode = tree._get_node('#' + nodeId);
    tree.set_text(categoryNode, SPARQL.View.buildCategoryNodeLabel(newCategories));
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
  
  SPARQL.View.deleteCategory = function(categoryObj){
    //get node id
    var nodeId = SPARQL.View.getNodeId(categoryObj);

    //remove tree node
    $.jstree._focused().delete_node('#' + nodeId);

    //remove the nodeid from the map
    SPARQL.View.map.remove(nodeId);
  };

  SPARQL.View.map.remove = function(nodeId){
    delete SPARQL.View.map.treeToData[nodeId];
  };

  
  SPARQL.arraysEqual = function(arr1, arr2){
    return ($(arr1).not(arr2).get().length == 0 && $(arr2).not(arr1).get().length == 0);
  };

  SPARQL.getNextUid = function(){
    return SPARQL.uid++;
  };

  //  SPARQL.getFullName = function(name, type){
  //    var fullName = null;
  //    switch(type){
  //      case 'category':
  //        fullName = SPARQL.category_iri + SPARQL.iri_delim + name;
  //        break;
  //
  //      case 'property':
  //        fullName = SPARQL.property_iri + SPARQL.iri_delim + name;
  //        break;
  //
  //      case 'instance':
  //        fullName = SPARQL.instance_iri + SPARQL.iri_delim + name;
  //        break;
  //
  //      default:
  //        fullName = name;
  //        break;
  //    }
  //
  //    return fullName;
  //  };

  //  SPARQL.getShortName = function(iri){
  //    var iriTokens = String.split(iri, SPARQL.iri_delim);
  //    if(iriTokens && iriTokens.length){
  //      return iriTokens[iriTokens.length - 1];
  //    }
  //
  //    return iri;
  //  };

  //  SPARQL.getIRI = function(string){
  //    var shortName = SPARQL.getShortName(string);
  //    if(shortName !== string)
  //      return string.substring(0, string.indexOf(SPARQL.iri_delim + shortName));
  //    return null;
  //  };

  //  SPARQL.jstreeToQueryTree = function(treeJsonObject){
  //    treeJsonObject = treeJsonObject || SPARQL.json.treeJsonObject;
  //    var queryJsonObject = {};
  //    var projection_var = [];
  //    var category_restriction = [];
  //    var triple = [];
  //    //for each subject
  //    //    var data = treeJsonObject.json_data.data;
  //    for(var i = 0; i < treeJsonObject.length; i++){
  //
  //      //if this is variable and showInResults then add it to projection_var
  //      var subject = treeJsonObject[i];
  //      if(subject.attr.type === 'VAR' && subject.attr.showinresults === 'true'){
  //        projection_var.push(SPARQL.fixName(subject.attr.name));
  //      }
  //
  //      //for each child
  //      if(subject.children){
  //        for(var j = 0; j < subject.children.length; j++){
  //          //if this is category then add a category_restriction
  //          var child = subject.children[j];
  //          if(child.attr.gui === 'category'){
  //            var thisCategoryRestriction = {
  //              subject: {
  //                value: SPARQL.fixName(subject.attr.name),
  //                type: subject.attr.type
  //              },
  //              category_iri:[child.attr.iri + SPARQL.iri_delim + SPARQL.fixName(child.attr.name)]
  //            }
  //            //for each category child (OR relation)
  //            var categoryChildren = child.children;
  //            if(categoryChildren){
  //              for(var catIndex = 0; catIndex < categoryChildren.length; catIndex++){
  //                //add it to category_iri array
  //                var categoryChild = categoryChildren[catIndex];
  //                thisCategoryRestriction.category_iri.push(categoryChild.attr.iri + SPARQL.iri_delim + SPARQL.fixName(categoryChild.attr.name));
  //              }
  //            }
  //
  //
  //            category_restriction.push(thisCategoryRestriction);
  //          }
  //
  //          //if this is property then add a triple
  //          if(child.attr.gui === 'property'){
  //            var thisTriple = {
  //              subject: {
  //                value: subject.attr.type === 'IRI' ? SPARQL.getFullName(SPARQL.fixName(subject.attr.name), 'instance') : SPARQL.fixName(subject.attr.name),
  //                type: subject.attr.type
  //              },
  //              predicate:{},
  //              object:{}
  //            }
  //
  //            thisTriple.predicate.value = child.attr.iri + SPARQL.iri_delim + SPARQL.fixName(child.attr.name);
  //            thisTriple.predicate.type = child.attr.type;
  //            thisTriple.object.value = SPARQL.fixName(child.attr.valuename);
  //            thisTriple.object.type = child.attr.valuetype;
  //            thisTriple.optional = !child.attr.valuemustbeset;
  //            if(child.attr.showinresults){
  //              projection_var.push(SPARQL.fixName(child.attr.valuename));
  //            }
  //            triple.push(thisTriple);
  //
  //          }
  //        }
  //      }
  //    }
  //    queryJsonObject.projection_var = projection_var;
  //    queryJsonObject.category_restriction = category_restriction;
  //    queryJsonObject.triple = triple;
  //
  //    //add order and format settings to query
  //
  //    SPARQL.Model.data = queryJsonObject;
  //    SPARQL.json.treeJsonObject = treeJsonObject;
  //
  //  };

  SPARQL.activateUpdateSourceBtn = function(){
    $('#qiUpdateSourceBtn').live('click', function(){
      SPARQL.getQueryResult($('#sparqlQueryText').val());
    });
  };



  //  SPARQL.parseParserFuncString = function(parserFuncString){
  //    var regex = /\{\{#sparql:\s*(([\s\S]+)\s*\|(\w+)\s*=\s*(\w+))\s*\}\}/;
  //    var result = regex.exec(parserFuncString);
  //    if(result){
  //      for(var i = 0; i < result.length; i++){
  //        if(i === 1){
  //          SPARQL.queryWithParamsString = result[i];
  //        }
  //        if(i === 2){
  //          SPARQL.queryString = result[i];
  //        }
  //        if(i > 2){
  //          break;
  //        //@TODO implement query parameters initialization
  //        }
  //      }
  //    }
  //  };

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
    if(!(sparqlQuery && sparqlQuery.length))
      return;

    mw.log('sparql query send:\n' + sparqlQuery);
    //send ajax post request to localhost:8080/sparql/sparqlToTree
    $.ajax({
      type: 'POST',
      url: SPARQL.smwgHaloWebserviceEndpoint + '/sparql/sparqlToTree',
      data: {
        sparql: sparqlQuery
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        SPARQL.queryString = sparqlQuery;
        $('#qiSparqlParserFunction').val(SPARQL.buildParserFuncString(sparqlQuery));
        if(data && typeof data === 'object'){
          SPARQL.Model.data = data;
          SPARQL.updateSortOptions();
          SPARQL.toTree(data);
          if(SPARQL.validateQueryTree(SPARQL.Model.data)){
            SPARQL.getQueryResult(SPARQL.queryString);
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
        SPARQL.showMessageDialog(errorJson.error, gLanguage.getMessage('QI_INVALID_QUERY'));
      }
    });
  };

  SPARQL.showMessageDialog = function(message, title, anchorElement){
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
        modal: true,
        width: 'auto',
        height: 'auto',
        resizable: true,
        title: title || '',
        buttons: buttons,
        close: function(){
          dialogDiv.remove();
        }
      });
      dialogDiv.html(message);
      dialogDiv.dialog('open');
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


  SPARQL.treeToSparql = function(treeJsonConfig){
    treeJsonConfig = treeJsonConfig || SPARQL.Model.data;
    if(SPARQL.validateQueryTree(treeJsonConfig)){
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
            SPARQL.getQueryResult(SPARQL.queryString);
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
    }
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
        //        var regex = /\|\s*\??\w+\s*\#?=?\s*\w*\s*/gmi;
        var regex = /\[\[.*?\]\]/gmi;
        var match;
        askQuery = askQuery.replace('{{#ask:', '').replace('}}', '');
        var paramString = askQuery;
        var mainQuery = '';
        while(match = regex.exec(askQuery)){
          mainQuery += match[0];
          paramString = paramString.replace(match[0], '');
        }
        //        mainQuery = $.trim(mainQuery.replace(/\|$/, ''));

        //send it to server for conversion to sparql
        //(http://<tsc-server>:<tsc-port>/sparql/translateASK?query=<query>&parameters=<parameters>&baseuri=<baseuri>)
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
      var subject = null;
      if(selectedSubjectNodeText){
        subject = new SPARQL.Model.SubjectTerm(selectedSubjectNodeText);
      }
      else{
        subject = SPARQL.Model.createSubject('?newsubject');
      }
      SPARQL.Model.createCategory(subject, ['newcategory']);
    });
  };

  SPARQL.View.openCategoryDialog = function(categoryRestriction){
    SPARQL.View.activateCategoryUpdateBtn();
    SPARQL.View.activateCategoryDeleteLink();

    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();

    //populate the dilaog
    var category_iri = categoryRestriction.getShortNameArray();

    //remove all previously added input rows
    $('#qiCategoryDialogTable tr').not('#categoryInputRow').not('#categoryTypeRow').not('#categoryOrLinkRow').remove();

    $('#qiCategoryDialogTable').find('input').first().focus();
    
    for(var i = 0; i < category_iri.length; i++){
      var categoryName = category_iri[i];
      if(i == 0){
        $('#qiCategoryDialogTable input').val(categoryName);
      }
      else{
        //add OR relation input
        $('#qiAddOrCategoryLink').closest('tr').before(SPARQL.createAdditionalCategoryPanel(categoryName));
      }
    }

    

    //    //bind keyup event to every category name inputbox
    //    $('#qiCategoryDialog input[type=text]').live('keyup', function(event){
    //      //bind focusout event for use with autocompletion
    //      $(this).live('focusout', function(event){
    //        $(this).unbind('focusout');
    //        SPARQL.View.openCategoryDialog.changeName($(this));
    //      });
    //
    //      SPARQL.View.openCategoryDialog.changeName($(this));
    //    });
    //    $('#qiCategoryDialog input[type=text]').live('focusout', function(event){
    //      SPARQL.View.openCategoryDialog.changeName($(this));
    //    });

    $('#qiCategoryDialog input[type=text]').live('focus', function(event){
      $(this).attr('oldvalue', $(this).val());
    });
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

  

  //  SPARQL.createSubjectNode = function(tree, nodeName, nodeTitle, nodeType){
  //    //create new subject node and select it
  //    var subjectNodeData = {
  //      data:{
  //        title: '',
  //        icon : SPARQL.json.variableIcon
  //      },
  //      attr: {
  //        id: 'subject-' + SPARQL.getNextUid(),
  //        name: nodeName,
  //        gui: 'subject',
  //        temporary: true,
  //        type: nodeType,
  //        showinresults: true
  //      },
  //      children: []
  //    };
  //
  //    var isVar = (nodeType === 'VAR');
  //    if(isVar){
  //      if(nodeTitle){
  //        subjectNodeData.data.title = '?' + nodeTitle;
  //      }
  //    }
  //    else{
  //      subjectNodeData.data.icon = SPARQL.json.instanceIcon;
  //      subjectNodeData.data.iri = SPARQL.instance_iri;
  //    }
  //
  //    tree.deselect_all();
  //    tree.create ( null , 'first' , subjectNodeData, function(){}, true );
  //    tree.select_node('#' + subjectNodeData.attr.id);
  //    var selectedNode = tree.get_selected();
  //    return selectedNode;
  //  };
  
  SPARQL.View.activateAddSubjectBtn = function(){
    $('#qiAddSubjectBtn').live('click', function(){
      SPARQL.Model.createSubject('newsubject', 'VAR');
    });
  };

  

  SPARQL.View.getTripleNodeData = function(nodeData){
    var result;
    $.each(SPARQL.View.map.treeToData, function(key, value){
      if(value.type === 'property'
        && value.value.object.value === nodeData.value.value
        && value.value.object.type === nodeData.value.type)
        {
        result = value;
        return false;
      }
    });

    return result ? result.value : result;

  };
  SPARQL.View.activateCategoryUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      SPARQL.View.openCategoryDialog.changeName(SPARQL.View.getCategories());
    });
  };

  SPARQL.View.activatePropertyUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      SPARQL.View.openPropertyDialog.changeName();
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
      var propertyNodeText = SPARQL.View.getSelectedNodeText();
      var subjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
      var subject = new SPARQL.Model.SubjectTerm(subjectNodeText);
      //delete Triple corresponding to the selected node
      SPARQL.Model.deleteProperty(new SPARQL.Model.Triple(subject, propertyNodeText));
      event.preventDefault();
    });
  };

  SPARQL.View.activateSubjectUpdateBtn = function(){
    $('#qiUpdateButton').unbind();
    $('#qiUpdateButton').click(function(){
      SPARQL.View.openSubjectDialog.changeName($('#qiSubjectNameInput'), $('#qiSubjectShowInResultsChkBox'));
    });
  };

  SPARQL.View.activateSubjectDeleteLink = function(){
    $('#qiDeleteLink').unbind();
    $('#qiDeleteLink').click(function(event){
      //delete subject corresponding to the selected node
      SPARQL.Model.deleteSubject(new SPARQL.Model.SubjectTerm(SPARQL.View.getSelectedNodeText()));
      event.preventDefault();
    });
  };

  SPARQL.View.openSubjectDialog = function(nodeData){
    SPARQL.View.activateSubjectUpdateBtn();
    SPARQL.View.activateSubjectDeleteLink();
    
    var subjectName = nodeData.value;
    var isVar = (nodeData.type === 'VAR');
    var showInResults = !subjectName.length || (isVar && SPARQL.Model.isVarInResults(nodeData));
    
    if(isVar){
      subjectName = '?' + subjectName;
    }
    
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').show();
    $('#qiPropertyDialog').hide();

    SPARQL.View.showFilters(SPARQL.View.getTripleNodeData(nodeData), $('#qiSubjectDialog'), true);
    
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



    //    inResults = subjectNewType === 'VAR' ? inResults : undefined;
    //    if(subjectOldType === TYPE.VAR){
    //      subjectOldName = subjectOldName.replace(/^\?/, '');
    //    }
    //    else{
    //      subjectOldName = SPARQL.instance_iri + SPARQL.iri_delim + subjectOldName
    //    }
    //    if(subjectNewType === TYPE.VAR){
    //      subjectNewName = subjectNewName.replace(/^\?/, '');
    //    }
    //    else{
    //      subjectNewName = SPARQL.instance_iri + SPARQL.iri_delim + subjectNewName;
    //    }

    SPARQL.Model.updateSubject(new SPARQL.Model.SubjectTerm(subjectOldName), new SPARQL.Model.SubjectTerm(subjectNewName), inResults);
  };

  SPARQL.fixName = function(string){
    if(string){
      string = string.replace(/\?/, '');
      string = string.replace(/\s/, '_');
    }
    return string;
  };

  SPARQL.View.replaceTextInputWithListBox = function(textInputBoxId){
    if(textInputBoxId){
      var listBox = $('<select/>');
      listBox.attr('id', textInputBoxId);
      var triples = SPARQL.Model.data.triple;
      var varArray = [];
      //get subjects from triples
      for(var i = 0; i < triples.length; i++){
        var triple = triples[i];
        if(triple.subject.type === 'VAR' && $.inArray(triple.subject.value, varArray) === -1){
          varArray.push(triple.subject.value);
          listBox.append('<option value="?' + triple.subject.value + '">?' + triple.subject.value + '</option>');
        }
        if(triple.object.value && triple.object.value.length && triple.object.type === 'VAR' && $.inArray(triple.object.value, varArray) === -1){
          varArray.push(triple.object.value);
          listBox.append('<option value="?' + triple.object.value + '">?' + triple.object.value + '</option>');
        }
      }
      //get subjects from category_restrictions
      var categories = SPARQL.Model.data.category_restriction;
      for(i = 0; i < categories && categories.length; i++){
        var category = categories[i];
        if(category.subject.type === 'VAR' && $.inArray(category.subject.value, varArray) === -1){
          varArray.push(category.subject.value);
          listBox.append('<option value="?' + category.subject.value + '">?' + category.subject.value + '</option>');
        }
      }
      //sparql tree is not initilized yet. get parent subject name
      if(varArray.length == 0){
        var jstree = $.jstree._reference('qiTreeDiv');
        var subjectNode = jstree._get_parent(jstree.get_selected());
        var subjectName = jstree.get_text(subjectNode);
        subjectName = $.trim(subjectName);
        listBox.append('<option value="?' + subjectName + '">' + subjectName + '</option>');
      }

      $('#' + textInputBoxId).replaceWith(listBox);
      $('#' + textInputBoxId).jec({
        triggerChangeEvent: true,
        focusOnNewOption: true
      });
    }
  },
  

  SPARQL.View.openPropertyDialog = function(triple){
    SPARQL.View.activatePropertyUpdateBtn();
    SPARQL.View.activatePropertyDeleteLink();
    var propertyName = triple.predicate.getShortName();
    var propertyType = triple.predicate.type;
    var valueName = triple.object.getShortName();
    var valueType = triple.object.type;
    //    var columnLabel = selectedNode.attr('columnlabel');
    var showInResults = SPARQL.Model.isVarInResults(triple.object);
    var valueMustBeSet = !triple.optional;

    SPARQL.View.replaceTextInputWithListBox('qiPropertyValueNameInput');
    
    if(valueType === TYPE.VAR){
      valueName = '?' + valueName;
      $('#qiPropertyDialog #qiPropertyValueNameInput').val(valueName);
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueNameInput').jecValue(valueName, true);
    }

    if(propertyType === TYPE.VAR){
      propertyName = '?' + propertyName;
    }   
    
    SPARQL.View.showFilters(triple, $('#qiPropertyDialog'));

    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').show();
    //focus on the input box
    $('#qiPropertyNameInput').focus();
    
    $('#qiPropertyDialog #qiPropertyNameInput').val(propertyName);    
    
    //    $('#qiPropertyDialog #qiSubjectColumnLabel').val(columnLabel || '');

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
  };

  SPARQL.View.deleteAllFilters = function(dialog){
    $(dialog).find('#qiAddAndFilterLink').closest('tr').siblings().remove();
  };

  SPARQL.View.showFilters = function(dataEntity, dialog, readOnly){
    SPARQL.View.deleteAllFilters(dialog);
    if(!dataEntity || !dialog){
      return;
    }
    
    var filters = SPARQL.Model.data.filter;
    for(var i = 0; filters && i < filters.length; i++){
      for(var j = 0; j < filters[i].expression.length; j++){
        if(SPARQL.objectsEqual(filters[i].expression[j].argument[0], dataEntity.object)){
          var operator = filters[i].expression[j].operator;
          var value = filters[i].expression[j].argument[1].value;
          if(j == 0){
            //add AND filter panel
            $(dialog).find('#qiAddAndFilterLink').closest('tr').before('<tr><td>' + SPARQL.createFilterTable(operator, value) + '</td></tr>');
          }
          else{
            $(dialog).find('#qiAddOrFilterLink').first().closest('tr').before(SPARQL.createFilterPanel(operator, value));
          }
        }
        else if(SPARQL.objectsEqual(filters[i].expression[j].argument[0], dataEntity.object)){
          operator = filters[i].expression[j].operator;
          value = filters[i].expression[j].argument[1].value;
          if(j == 0){
            //add AND filter panel
            $(dialog).find('#qiAddAndFilterLink').closest('tr').before('<tr><td>' + SPARQL.createFilterTable(operator, value) + '</td></tr>');
          }
          else{
            $(dialog).find('#qiAddOrFilterLink').first().closest('tr').before(SPARQL.createFilterPanel(operator, value));
          }
        }
      }
    }
    if(readOnly){
      //Hide add and delete links and disabled inputs
      $('#qiAddOrFilterLink').hide();
      $('#qiAddAndFilterLink').hide();
      $('#qiDeleteFilterImg').hide();
      $('.filterAND select').attr('disabled', 'disabled');
      $('.filterAND input').attr('disabled', 'disabled');
    }
  };

//  SPARQL.View.updateProperty = function(nameInput, valueMustBeSetChkbox, valueInput, showInResultsChkbox){
//    var newName = $.trim(nameInput.val() || '');
//    var newType = (newName.indexOf('?') === 0 ? 'VAR' : 'IRI');
//    var oldName = $.trim(nameInput.attr('oldValue') || '');
//    var oldType = (oldName.indexOf('?') === 0 ? 'VAR' : 'IRI');
//    var newValueName = $.trim(valueInput.val() || '');
//    var newValueType = (newName.indexOf('?') === 0 ? 'VAR' : 'IRI');
//    var oldValueName = $.trim(valueInput.attr('oldValue') || '');
//    var oldValueType = (oldName.indexOf('?') === 0 ? 'VAR' : 'IRI');
//
//    var oldObject = new SPARQL.Model.Term(oldValueType === 'VAR' ? oldValueName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + oldValueName, oldValueType);
//    var newObject = new SPARQL.Model.Term(newValueType === 'VAR' ? newValueName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + newValueName, newValueType);
//    var oldPredicate = new SPARQL.Model.Term(oldType === 'VAR' ? oldName : SPARQL.property_iri + SPARQL.iri_delim + oldName, oldType);
//    var newPredicate = new SPARQL.Model.Term(newType === 'VAR' ? newName : SPARQL.property_iri + SPARQL.iri_delim + newName, newType);
//    var subject;
//    var subjectNode = SPARQL.View.getParentNode($.jstree._reference('qiTreeDiv').get_selected(), ['variable', 'instance']);
//    if(subjectNode.length){
//      var subjectNodeId = subjectNode.attr('id');
//      var subjectNodeData = SPARQL.View.getDataEntity(subjectNodeId);
//      subject = subjectNodeData.value;
//    }
//
//    var oldTriple = {
//      subject: subject,
//      predicate: oldPredicate,
//      object: oldObject,
//      optional: !valueMustBeSetChkbox.attr('oldValue')
//    };
//
//    var newTriple = {
//      subject: subject,
//      predicate: newPredicate,
//      object: newObject,
//      optional: !valueMustBeSetChkbox.attr('checked')
//    };
//
//    SPARQL.Model.updateProperty(oldTriple, newTriple, !!showInResultsChkbox.attr('checked'));
//    SPARQL.Model.updateFilters(newTriple.object, SPARQL.View.getFilters(newValueName, newValueType, $('#qiPropertyDialog')));
//  };

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

  //  SPARQL.View.openPropertyDialog.changePropertyValue = function(dataEntity){
  //    var newName = $.trim($('#qiPropertyValueNameInput').val() || '');
  //    var newType = (newName.indexOf('?') === 0 ? 'VAR' : 'IRI');
  //    var oldName = $.trim($('#qiPropertyValueNameInput').attr('oldValue') || '');
  //    var oldType = (oldName.indexOf('?') === 0 ? 'VAR' : 'IRI');
  //
  //    dataEntity.object = {
  //      type: oldType,
  //      value: oldType === 'VAR' ? oldName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + oldName
  //    };
  //
  //    var newTriple = {
  //      subject: dataEntity.subject,
  //      predicate: dataEntity.predicate,
  //      object: {
  //        type: newType,
  //        value: newType === 'VAR' ? newName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + newName
  //      },
  //      optional: dataEntity.optional
  //    };
  //
  //    SPARQL.Model.updateProperty(dataEntity, newTriple, !dataEntity.optional);
  //  };

  //  SPARQL.View.openPropertyDialog.changeValueMustBeSet = function(dataEntity){
  //
  //    var valueMustBeSet = !!$('#qiPropertyValueMustBeSetChkBox').attr('checked');
  //
  //    var newTriple = {
  //      subject: dataEntity.subject,
  //      predicate: dataEntity.predicate,
  //      object: dataEntity.object,
  //      optional: !valueMustBeSet
  //    };
  //
  //    SPARQL.Model.updateProperty(dataEntity, newTriple);
  //
  //    dataEntity.optional = !valueMustBeSet;
  //  };

  //  SPARQL.View.openPropertyDialog.changeShowInResults = function(dataEntity){
  //
  //    var showInResults = !!$('#qiPropertyValueShowInResultsChkBox').attr('checked');
  //
  //    var newTriple = {
  //      subject: dataEntity.subject,
  //      predicate: dataEntity.predicate,
  //      object: dataEntity.object,
  //      optional: dataEntity.optional
  //    };
  //
  //    SPARQL.Model.updateProperty(dataEntity, newTriple, showInResults);
  //  };

  
  SPARQL.View.updateProperty = function(oldTriple, newTriple){
    var oldTripleObj = {
      type: 'property',
      value: oldTriple
    };
    var nodeId = SPARQL.View.getNodeId(oldTripleObj);
    SPARQL.View.map.remove(nodeId);
    SPARQL.View.map.put(nodeId, {
      type: 'property',
      value: newTriple
    });

    var jstree = $.jstree._reference('qiTreeDiv');
    var propValue = newTriple.object.value.replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, '');
    if(newTriple.object.type === 'VAR'){
      if(newTriple.object.value && newTriple.object.value.length){
        propValue = '?' + newTriple.object.value;
      }
    }
    var nodeText = newTriple.predicate.value.replace(SPARQL.property_iri, '').replace(SPARQL.iri_delim, '') + ' ' + propValue;
    jstree.set_text(SPARQL.View.getParentNode(jstree.get_selected(), 'property'), nodeText);
  };

  
  SPARQL.View.createProperty = function(triple){
    var propertyName = triple.predicate.getShortName();
    var valueName = triple.object.getShortName();
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
        rel: 'property'
      }
    };

    SPARQL.View.getTree().create (SPARQL.View.getSelectedSubjectNode(), 'first' , propertyNodeData, function(){}, true);
  };

  //  SPARQL.View.getSubjectNode = function(subject){
  //    //get node id by data entity
  //    var nodeId = SPARQL.View.getNodeId({
  //      type: 'subject',
  //      value: subject
  //    });
  //    //get tree node by id
  //    return $.jstree._reference('qiTreeDiv')._get_node('#' + nodeId);
  //  };

  //  SPARQL.View.getParentSubjectNode = function(thisNode){
  //    return SPARQL.View.getParentNode(thisNode, ['variable', 'instance']);
  //  };

  SPARQL.View.activateAddPropertyBtn = function(){
    $('#qiAddPropertyBtn').live('click', function(){
      var selectedSubjectNodeText = SPARQL.View.getNodeText(SPARQL.View.getSelectedSubjectNode());
      var subject = null;
      if(selectedSubjectNodeText){
        subject = new SPARQL.Model.SubjectTerm(selectedSubjectNodeText);
      }
      else{
        subject = SPARQL.Model.createSubject('?newsubject-' + SPARQL.getNextUid());
      }
      SPARQL.Model.createProperty(subject);
    });
  };

  SPARQL.activateAddAndFilterLink = function(){
    $('#qiAddAndFilterLink').live('click', function(event){
      //display filter gui
      $(this).closest('tr').before('<tr><td>' + SPARQL.createFilterTable() + '</td></tr>');   
      event.preventDefault();
    });
  };
  

  SPARQL.View.addFilterAND =  function(label){
    //get parent property node of the selected node
    var parentPropertyNode = SPARQL.View.getParentNode(SPARQL.View.getSelectedNode(), ['variable', 'property']);

    //create a new filter node
    var filterNode = {
      data:{
        title: label
      },
      attr: {
        rel: 'filter'
      }
    };

    //append it to property node as child
    SPARQL.View.getTree().create (parentPropertyNode, 'first' , filterNode, function(){}, true );
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


  //  SPARQL.Model.addFilterOR = function(operator, operandType1, operandValue1, operandType2, operandValue2, dataTypeIRI){
  //    //build expression object using the arguments
  //    var expression = [{
  //      operator: operator,
  //      argument: [{
  //        type: operandType1,
  //        value: operandValue1
  //      },
  //      {
  //        type: operandType2,
  //        value: operandValue2,
  //        datatype_iri: dataTypeIRI
  //      }]
  //    }];
  //
  //    var newFilter = {
  //      expression: []
  //    };
  //
  //    //find a filter for operandValue1
  //    var filters = SPARQL.Model.data.filter;
  //
  //    var found = false;
  //    for(var i = 0; !found && i < filters.length; i++){
  //      var arguments = filters[i].expression[0].argument;
  //      for(var j = 0; j < arguments.length; j++){
  //        if(arguments[j].value === operandValue1 && arguments[j].type === operandType1){
  //          newFilter = filters[i];
  //          found = true;
  //          break;
  //        }
  //      }
  //    }
  //
  //    //add this expression object to the filter
  //    newFilter.expression.push(expression);
  //
  //    if(!found){
  //      filters.push(newFilter);
  //    }
  //
  //    SPARQL.View.updateFilters(operandType1, operandValue1);
  //  };

  //  SPARQL.View.addFilterOR = function(operator, operandType1, operandValue1, operandType2, operandValue2, dataTypeIRI){
  //  //get AND filter node
  //
  //  //add filter description to the node label
  //  };

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
  
  //  SPARQL.getNumber = function(value){
  //    var result = value;
  //    try{
  //      result = parseInt(value);
  //    }
  //    catch(x){
  //      return value;
  //    }
  //
  //    result = (result === NaN ? value : result);
  //    return result;
  //  };
  
  SPARQL.getDataTypeIRI = function(type){
    var result;
    var prefix = /^(\w+):(\w+)/.exec(type);
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
        var operator = $(this).find('select').val();        
        var argument2 = new SPARQL.Model.Term($(this).find('input').val(), TYPE.LITERAL, SPARQL.getDataTypeIRI($('#qiPropertyDialog').find('.typeLabelTd').next().text()));
        var expression = new SPARQL.Model.FilterExpression(operator, term, argument2);

        //add expression object to the array
        filter.expression.push(expression);
      });
      //add filter object to array
      filters.push(filter);
    });

    return filters;
  }

  //  SPARQL.Model.deleteFilter = function(operator, operandType1, operandValue1, operandType2, operandValue2){
  //    var filters = SPARQL.Model.data.filter;
  //    for(var i = 0; i < filters.length; i++){
  //      for(var j = 0; j < filters[i].expression.length; j++){
  //        var expression = filters[i].expression[j];
  //        if((expression.argument[0].value === operandValue1
  //          && expression.argument[0].type === operandType1
  //          && expression.argument[1].value === operandValue2
  //          && expression.argument[1].type === operandType2)
  //        || (expression.argument[0].value === operandValue2
  //          && expression.argument[0].type === operandType2
  //          && expression.argument[1].value === operandValue1
  //          && expression.argument[1].type === operandType1))
  //          {
  //          if(filters[i].expression.length > 1){
  //            filters[i].expression.splice(j, 1);
  //          }
  //          else{
  //            filters.splice(i, 1);
  //            break;
  //          }
  //        }
  //      }
  //    }
  //    SPARQL.View.deleteFilter(operator, operandType1, operandValue1, operandType2, operandValue2);
  //  };

  //  SPARQL.View.deleteFilter = function(operator, operandType1, operandValue1, operandType2, operandValue2){
  //    SPARQL.View.updateFilters(operandType1, operandValue1);
  //
  //  };

//  SPARQL.View.getPropertyNode = function(value, type){
//    //get all triples having object with such value and type
//    var dataEntities = SPARQL.View.map.treeToData;
//    var result = [];
//    for(var nodeId in dataEntities){
//      var dataEntity = dataEntities[nodeId];
//      if(dataEntity.type === 'property' && dataEntity.value.object.value === value && dataEntity.value.object.type === type){
//        result.push($.jstree._reference('qiTreeDiv')._get_node('#' + nodeId));
//      }
//    }
//
//    return $(result);
//  };

  SPARQL.View.updateFilters = function(operandTerm){  
    //iterate over filters array
    var filters = SPARQL.Model.data.filter || [];
    for(var i = 0; filters && i < filters.length; i++){
      var filterLabel = '';
      for(var j = 0; j < filters[i].expression.length; j++){
        var expression = filters[i].expression[j];
        //add filter node for each filter whose expression argument type and value equal to the arguments
        if(expression.argument[0].isEqual(operandTerm)){
          filterLabel += SPARQL.View.translateOperator(expression.operator) + ' ' + expression.argument[1].value;
        }
        else if(expression.argument[1].isEqual(operandTerm)){
          filterLabel += SPARQL.View.translateOperator(expression.operator) + ' ' + expression.argument[0].value;
        }
        else{//this is filter for different variable
          break;
        }

        if(j < filters[i].expression.length - 1){
          filterLabel += ' or ';
        }
      }
      //create filter node
      SPARQL.View.addFilterAND(filterLabel);
    }
  };
  

  SPARQL.createFilterPanel = function(operator, value){
    value = value || '';
    return '<tr class="filterOR"><td></td>'
    + '<td><select>'
    + '<option value="LT"' + SPARQL.createFilterPanel.selected(operator, 'LT') + '>' + gLanguage.getMessage('QI_LT') + ' (<)</option>'
    + '<option value="LE"' + SPARQL.createFilterPanel.selected(operator, 'LE') + '>' + gLanguage.getMessage('QI_LT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (<=)</option>'
    + '<option value="GT"' + SPARQL.createFilterPanel.selected(operator, 'GT') + '>' + gLanguage.getMessage('QI_GT') + ' (>)</option>'
    + '<option value="GE"' + SPARQL.createFilterPanel.selected(operator, 'GE') + '>' + gLanguage.getMessage('QI_GT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (>=)</option>'
    + '<option value="EQ"' + SPARQL.createFilterPanel.selected(operator, 'EQ') + '>' + gLanguage.getMessage('QI_EQUAL') + ' (==)</option>'
    + '<option value="NE"' + SPARQL.createFilterPanel.selected(operator, 'NE') + '>' + gLanguage.getMessage('QI_NOT') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (!=)</option>'
    + '<option value="regex"' + SPARQL.createFilterPanel.selected(operator, 'regex') + '>' + gLanguage.getMessage('QI_LIKE') + ' (regexp)</option>'
    + '</select>'
    + '</td><td>'
    + '<input type="text" value="' + value + '">'
    + '</td><td>'
    + '<img id="qiDeleteFilterImg" title="Delete filter" src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/>'
    + '</td></tr>';
  };

  SPARQL.createFilterPanel.selected = function(operator, value){
    return operator === value ? 'selected="selected"' : '';
  };

  SPARQL.createFilterTable = function(operator, value){
    return '<table class="filterAND">' + SPARQL.createFilterPanel(operator, value) + SPARQL.createAddOrFilterLink() + '</table>';
  };

  SPARQL.createAddOrFilterLink = function(){
    return '<tr><td colspan="4" style="text-align:center;"><a href="" id="qiAddOrFilterLink">' + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a></td><tr>';
  };

  SPARQL.createAdditionalCategoryPanel = function(categoryName){
    categoryName = categoryName || '';
    return '<tr><td></td><td>'
    + '<input id="qiCategoryNameInput-' + SPARQL.getNextUid() + '" class="qiCategoryNameInput wickEnabled" '
    + 'type="text" autocomplete="OFF" constraints="namespace: 14" value="' + categoryName + '"/>'
    + '</td><td><img id="qiDeleteCategoryImg" title="Delete category" src="'
    + mw.config.get('wgServer')
    + mw.config.get('wgScriptPath')
    + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/></td></tr>'
    + '<tr><td></td><td id="qiSubjectTypeLabel"></td><td></td></tr>';
  };

  //  SPARQL.Model.addCategoryOR = function(categoryName, subjectName, subjectType){
  //    var category_restrictions = SPARQL.Model.data.category_restriction;
  //    for(var i = 0; i < category_restrictions.length; i++){
  //      var category_restriction = category_restrictions[i];
  //      if(category_restriction.subject.type === subjectType
  //        && category_restriction.subject.value.replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, '') === subjectName){
  //        category_restriction.category_iri.push(SPARQL.category_iri + SPARQL.iri_delim + categoryName);
  //        break;
  //      }
  //    }
  //
  //    SPARQL.View.addCategoryOR(categoryName, subjectName, subjectType);
  //    SPARQL.updateAllFromTree();
  //  };

  //  SPARQL.View.addCategoryOR = function(categoryName, subjectName, subjectType){
  //    //display additional category input
  //    $('#qiAddOrCategoryLink').closest('tr').before(SPARQL.createAdditionalCategoryPanel(''));
  //    //get category node
  //    subjectName = (subjectType === 'VAR' ? subjectName : SPARQL.instance_iri + SPARQL.iri_delim + subjectName);
  //    var nodeId = SPARQL.View.getNodeId({
  //      type: 'subject',
  //      value: {
  //        type: subjectType,
  //        value: subjectName
  //      }
  //    });
  //    var jstree = $.jstree._reference('qiTreeDiv');
  //    var subjectNode = jstree._get_node('#' + nodeId);
  //    var categoryNode = null;
  //    jstree._get_children(subjectNode).each(function(){
  //      if($(this).attr('rel') === 'category'){
  //        categoryNode = $(this);
  //        return false; //break the loop
  //      }
  //    });
  //    var nodeData = SPARQL.View.getDataEntity(categoryNode.attr('id'));
  //    nodeData.category_iri.push(SPARQL.category_iri + SPARQL.iri_delim + categoryName);
  //    SPARQL.View.map.put(categoryNode.attr('id'), nodeData);
  //
  //    if(categoryName && categoryName.length){
  //      //change it's label to contain additional category name
  //      jstree.set_text(categoryNode, jstree.get_text(categoryNode) + ' or ' + categoryName);
  //    }
  //
  //    $('#qiCategoryDialogTable').find('input').last().focus();
  //
  //  };

  SPARQL.activateAddOrCategoryLink = function(){
    $('#qiAddOrCategoryLink').live('click', function(event){
      event.preventDefault();
      var tr = $(this).closest('tr');
      tr.before(SPARQL.createAdditionalCategoryPanel(''));
      tr.find('input').focus();
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

  //  SPARQL.activateUpdateBtn = function(){
  //    $('#qiUpdateButton').bind('click', function(event){
  //      switch(event.data.dialogType){
  //        case 'subject':
  //          SPARQL.View.openSubjectDialog.changeName($('#qiSubjectNameInput'), $('#qiSubjectShowInResultsChkBox'));
  //          break;
  //
  //        case 'category':
  //          SPARQL.View.openCategoryDialog.changeName($('#qiCategoryNameInput'));
  //          break;
  //
  //        case 'property':
  //          SPARQL.View.updateProperty($('#qiPropertyNameInput'), $('#qiPropertyValueMustBeSetChkBox'), $('#qiPropertyValueNameInput'), $('#qiPropertyValueShowInResultsChkBox'));
  //          break;
  //
  //        default:
  //          break;
  //      }
  //
  //      SPARQL.toTree();
  //      SPARQL.updateAllFromTree();
  //    });
  //
  //  };


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
        var newId = SPARQL.View.getDomPath(REF_NODE.rslt.obj);
        $(REF_NODE.rslt.obj).attr('id', newId);
        REF_NODE.inst.select_node('#' + newId, true);
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
        tagName += "-" + $(this).prevAll(tagName).length;
      }
      return tagName;
    }).get().join("-").toLowerCase();
  };

  SPARQL.updateAllFromTree = function(){
    //if tree structure is empty then reset the model and return
    if(!SPARQL.Model.data.triple.length && !SPARQL.Model.data.category_restriction.length){
      return;
    }
    window.clearTimeout(window.updateTimeoutId);
    window.updateTimeoutId = window.setTimeout(function(){
      SPARQL.treeToSparql();
      SPARQL.updateSortOptions();
    }, 500)
    
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
    if($.jstree._focused() && $.jstree._focused().get_selected()){
      $.jstree._focused().deselect_all();
    }
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();
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
    //if none of the vars has 'show in results' set display a message
    if(queryTree.projection_var.length == 0){
      SPARQL.showMessageDialog(gLanguage.getMessage('QI_SHOW_IN_RESULTS_MUST_BE_SET'), gLanguage.getMessage('QI_INVALID_QUERY'));
      return false;
    }
    return true;
  };

  SPARQL.getQueryResult = function(queryString){
    queryString = queryString || SPARQL.queryString;
    
    $('#previewcontent').empty().append('<img src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/ajax-loader.gif"/>');
  
    var currentPage = null;
    if (window.parent.wgPageName) {
      currentPage = window.parent.wgPageName.wgCanonicalNamespace || '';
      currentPage += ':' + window.parent.wgPageName;
    }

    $.ajax({
      type: 'POST',
      url: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php?action=ajax',
      data: {
        rs: 'smwf_qi_QIAccess',
        rsargs: [ 'getQueryResult', queryString + ',' + SPARQL.getParameterString(), currentPage]
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
    SPARQL.srfInitScripts = SPARQL.getInitScripts(html);
    html = SPARQL.srfInitScripts.pop();

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
    SPARQL.View.map.treeToData = {};
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
  SPARQL.toTree = function(selectedNodeId, queryJsonObject){
    var jstree = SPARQL.View.getTree();
    
    //clear the tree nodes
    
    jstree.delete_node(jstree._get_children(-1));
    SPARQL.View.map.treeToData = {};

    //use internal data structure if none specified
    queryJsonObject = queryJsonObject || SPARQL.Model.data;

    
    var triples = queryJsonObject.triple;
    var category_restrictions = queryJsonObject.category_restriction;
    var subjectArray = [];
    //iterate over triples
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
    for (i = 0; i < category_restrictions.length; i++){
      var category_restriction = category_restrictions[i];
      if(!SPARQL.isObjectInArray(category_restriction.subject, subjectArray)){
        subjectArray.push(category_restriction.subject);
        SPARQL.View.createSubject(category_restriction.subject);
      }
     
      SPARQL.View.createCategory(category_restriction);
    }

    //if the tree is still empty then show the vars in projection_var if any
    if(jstree._get_children(-1).length === 0){
      SPARQL.View.cancel();
      var projection_var = SPARQL.Model.data.projection_var;
      for(i = 0; i < projection_var.length; i++){
        SPARQL.View.createSubject(new SPARQL.Model.SubjectTerm(projection_var[i], TYPE.VAR));
      }
    }

    //select the nodes that were selected before rebuild
    if(jstree._get_children(-1).length && jstree._get_node('#' + selectedNodeId)){
      jstree.select_node('#' + selectedNodeId, true);
    }
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
        if(ui.index === 0){
          SPARQL.sparqlToTree($('#sparqlQueryText').val());
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
        var method = initMethods[i];
        if((method.name == 'smw_sortables_init' || method.toString().indexOf('function smw_sortables_init') > -1)
          && $('.sortheader').length > 0)
          {
          continue;
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
    var result = [];
    var noscript = text;
    var match;
    while(match = scriptRegexp.exec(text)){
      result.push(match[0]);
      noscript = noscript.replace(match[0], '');
    }

    result.push(noscript);
    return result;
  };

  SPARQL.appendScripts = function(domElement, scriptArray){
    for(var i = 0; i < scriptArray.length; i++){
      $(domElement).append(scriptArray[i]);
    }
  };

  SPARQL.activateResetQueryBtn = function(){
    $('#sparqlQI #qiResetQueryButton').live('click', function(){
      SPARQL.Model.reset();
      SPARQL.View.cancel();
    });
  };



  SPARQL.activateFullPreviewLink = function(){
    $('#sparqlQI #qiFullPreviewLink').live('click', function(event){
      var html = $('#sparqlQI #previewcontent').html() || gLanguage.getMessage('QI_EMPTY_QUERY');
      SPARQL.processResultHtml(html, SPARQL.showMessageDialog(html, gLanguage.getMessage('QI_QUERY_RESULT')));
      event.preventDefault();
    });
  };

  SPARQL.getPropertyInfo = function(propertyName){
    window.clearTimeout(window.getPropertyTypeTimeout);
    window.getPropertyTypeTimeout = window.setTimeout(function(){
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

        if(i > 0 && i % columnLimit === 0){
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
      xsdType = $(xmlDoc).find('param').attr('xsdType');
      type = type || '';
      xsdType = xsdType || '';
    }
    
    //diplay type label under property name input
    $('#qiPropertyTypeLabel').html('Type: ' + type);
    $('#qiPropertyTypeLabel').next().html(xsdType).hide();
  };


  $(document).ready(function(){
    SPARQL.smwgHaloWebserviceEndpoint = mw.config.get('smwgHaloWebserviceEndpoint');
    if(SPARQL.smwgHaloWebserviceEndpoint){
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



