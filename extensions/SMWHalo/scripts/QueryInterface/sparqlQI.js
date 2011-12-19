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
    //Model component. Takes care of sparql data structure manipulation
    Model: {
      data: {
        category_restriction: [],
        triple: [],
        filter: [],
        projection_var: [],
        namespace: [],
        order: []
      }
    },
    //View component. Takes care of sparql treeview manipulation
    View: {
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

  SPARQL.View.getDataEntity = function(nodeId){
    return SPARQL.View.map.treeToData[nodeId];
  };

  SPARQL.View.getSelectedNodeId = function(){
    var selectednodeId;
    try{
      selectednodeId = $.jstree._reference('qiTreeDiv').get_selected().attr('id');
    }
    catch(x){
      mw.log('EXCEPTION: ' + x);
    }

    return selectednodeId;
  };

  SPARQL.View.createCategory = function(subject, categoryName){
    var categoryNodeData = {
      data:{
        title: categoryName
      },
      attr: {
        id: 'category-' + SPARQL.getNextUid(),
        rel: 'category'
      }
    };

    SPARQL.View.map.put(categoryNodeData.attr.id, {
      type: 'category',
      category_iri: [SPARQL.category_iri + SPARQL.iri_delim + categoryName],
      subject: subject
    });

    var tree = $.jstree._reference('qiTreeDiv');
    var selectedNode = tree.get_selected();
    if(!selectedNode.length){
      selectedNode = SPARQL.View.getSubjectNode(subject);
    }
    tree.create (SPARQL.View.getParentSubjectNode(selectedNode), 'first' , categoryNodeData, function(){}, true );
    tree.deselect_all();
    tree.select_node('#' + categoryNodeData.attr.id);    
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
        id: 'subject-' + SPARQL.getNextUid(),
        rel: rel
      },
      children: []
    };

    SPARQL.View.map.put(subjectNodeData.attr.id, dataEntity);
    
    var tree = $.jstree._reference('qiTreeDiv');
    tree.deselect_all();
    tree.create ( tree , 'first' , subjectNodeData, function(){}, true );
    tree.select_node('#' + subjectNodeData.attr.id);    
  };
  

  SPARQL.Model.getSubjectFromDataEntity = function(dataEntity){
    var result = null;
    if(dataEntity && dataEntity.type){
      switch(dataEntity.type){
        case 'subject':
          result = dataEntity.value;
          break;

        case 'category':
          result = dataEntity.subject;
          break;

        case 'property':
          result = dataEntity.value.subject;
          break;
      }
    }

    return result;
  };

  SPARQL.Model.createSubject = function(subjectName, type){
    var subject = {
      type: type ? type : 'VAR',
      value: subjectName
    }
    if(subject.type === 'VAR'){
      SPARQL.Model.data.projection_var.push(subjectName);
    }
    SPARQL.View.createSubject(subject);
    
    return subject;
  };

  SPARQL.Model.updateSubject = function(subjectOld, subjectNew, inResults){
    //go over triples, find this subject and change it
    var triples = SPARQL.Model.data.triple;
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      if(triple.subject.value === subjectOld.value && triple.subject.type === subjectOld.type){
        triple.subject.value = subjectNew.value.replace(/\s+/, '_');
        triple.subject.type = subjectNew.type;
      }
      if(triple.object.value === subjectOld.value && triple.subject.type === subjectOld.type){
        triple.object.value = subjectNew.value;
        triple.object.type = subjectNew.type;
      }
    }  
    //do this only if inResults is defined
    if(typeof inResults !== 'undefined'){
      var projection_vars = SPARQL.Model.data.projection_var;
      var varInArray = $.inArray(subjectOld.value, projection_vars);
      //if new value should be in results
      if(inResults){
        //if old value is in results
        if(varInArray > -1){
          //change name
          for(var j = 0; j < projection_vars.length; j++){
            if(projection_vars[j] === subjectOld.value){
              projection_vars[j] = subjectNew.value;
            }
          }
        }
        //if old value is NOT in results
        else{
          //add to array
          projection_vars.push(subjectNew.value);
        }
      }
      //if new value should NOT be in results
      else{
        if(varInArray > -1){
          //remove from array
          for(j = 0; j < projection_vars.length; j++){
            if(projection_vars[j] === subjectOld.value){
              projection_vars.splice(j, 1);
            }
          }
        }
      }
    }
    SPARQL.View.updateSubject(subjectOld, subjectNew);
    SPARQL.updateAllFromTree();
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


  SPARQL.Model.createCategory = function(subject, categoryName){
    if(!subject){
      subject = SPARQL.Model.createSubject('newsubject');
    }

    var newCategoryRestriction = {
      subject: subject,
      category_iri: [SPARQL.category_iri + SPARQL.iri_delim + categoryName]
    }
    SPARQL.Model.data.category_restriction.push(newCategoryRestriction);
    SPARQL.View.createCategory(subject, categoryName);    
  };


  SPARQL.Model.updateCategory = function(nodeData, newCategories){
    //find the right category restriction obj
    var category_restriction = SPARQL.Model.data.category_restriction;
    var prefix = SPARQL.category_iri + SPARQL.iri_delim;
    var updated = false;
    for(var i = 0; i < category_restriction.length; i++){
      if(SPARQL.objectsEqual(category_restriction[i].subject, nodeData.subject)
        && SPARQL.arrayEqual(category_restriction[i].category_iri, nodeData.category_iri))
        {
        //create category_iri from newCategories and replace old category_iri with new one
        category_restriction[i].category_iri = SPARQL.View.buildCategoryIRIArray(newCategories);
        break;
      }
    }   

    SPARQL.View.updateCategory(nodeData, newCategories);
    SPARQL.updateAllFromTree();
  };

  SPARQL.objectsEqual = function(obj1, obj2){
    if(typeof obj1 === 'object' && typeof obj2 === 'object'){
      var result = true;
      $.each(obj1, function(prop, value){
        if(prop && obj1.hasOwnProperty(prop)){
          if(typeof value === 'object'){
            if(!SPARQL.objectsEqual(value, obj2[prop])){
              result = false;
              return result;
            }
          }
          else if(value !== obj2[prop]){
            result = false;
            return result;
          }
        }
      });
      return result;
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
  }

  
  SPARQL.Model.deleteCategory = function(dataEntity, categoryName){    
    var subject = dataEntity.subject;
    var category_restriction = SPARQL.Model.data.category_restriction;
    var removed = false;
    for(var i = 0; i < category_restriction.length; i++){
      if(removed){
        break;
      }
      if(SPARQL.objectsEqual(category_restriction[i].subject, subject)){
        if(categoryName){
          var category_iri_array = category_restriction[i].category_iri;
          var category_iri = SPARQL.category_iri + SPARQL.iri_delim + categoryName;
          for(var j = 0; j < category_iri_array.length; j++){
            if(category_iri === category_iri_array[j]){
              category_iri_array.splice(j, 1);
              //update category node
              SPARQL.View.updateCategory(dataEntity.subject.value, dataEntity.subject.type, dataEntity.category_iri, category_iri_array);
              removed = true;
              break;
            }
          }
        }
        else{
          category_restriction.splice(i, 1);
          //delete category node
          SPARQL.View.deleteCategory(dataEntity, categoryName);
          break;
        }
      }
    }    
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


  SPARQL.Model.getCategory = function(subjectName, subjectType, categoryArray){
    var result;
    var category_restrictions = SPARQL.Model.data.category_restriction;
    for(var i = 0; i < category_restrictions.length; i++){
      var category_restriction = category_restrictions[i];
      if(category_restriction.subject.value === subjectName && category_restriction.subject.type === subjectType){
        if(SPARQL.arrayEqual(categoryArray, category_restriction.category_iri)){
          result = category_restriction;
          break;
        }
      }
    }

    return result;
  };

  SPARQL.arrayEqual = function(arr1, arr2){
    return ($(arr1).not(arr2).get().length == 0 && $(arr2).not(arr1).get().length == 0);
  };

  SPARQL.getNextUid = function(){
    return SPARQL.uid++;
  };

  SPARQL.getFullName = function(name, type){
    var fullName = null;
    switch(type){
      case 'category':
        fullName = SPARQL.category_iri + SPARQL.iri_delim + name;
        break;

      case 'property':
        fullName = SPARQL.property_iri + SPARQL.iri_delim + name;
        break;

      case 'instance':
        fullName = SPARQL.instance_iri + SPARQL.iri_delim + name;
        break;

      default:
        fullName = name;
        break;
    }

    return fullName;
  };

  SPARQL.getShortName = function(iri){
    var iriTokens = String.split(iri, SPARQL.iri_delim);
    if(iriTokens && iriTokens.length){
      return iriTokens[iriTokens.length - 1];
    }

    return iri;
  };

  SPARQL.getIRI = function(string){
    var shortName = SPARQL.getShortName(string);
    if(shortName !== string)
      return string.substring(0, string.indexOf(SPARQL.iri_delim + shortName));
    return null;
  };

  SPARQL.jstreeToQueryTree = function(treeJsonObject){
    treeJsonObject = treeJsonObject || SPARQL.json.treeJsonObject;
    var queryJsonObject = {};   
    var projection_var = [];
    var category_restriction = [];
    var triple = [];
    //for each subject
    //    var data = treeJsonObject.json_data.data;
    for(var i = 0; i < treeJsonObject.length; i++){
      
      //if this is variable and showInResults then add it to projection_var
      var subject = treeJsonObject[i];
      if(subject.attr.type === 'VAR' && subject.attr.showinresults === 'true'){
        projection_var.push(SPARQL.fixName(subject.attr.name));
      }
      
      //for each child
      if(subject.children){
        for(var j = 0; j < subject.children.length; j++){
          //if this is category then add a category_restriction
          var child = subject.children[j];
          if(child.attr.gui === 'category'){
            var thisCategoryRestriction = {
              subject: {
                value: SPARQL.fixName(subject.attr.name),
                type: subject.attr.type
              },
              category_iri:[child.attr.iri + SPARQL.iri_delim + SPARQL.fixName(child.attr.name)]
            }
            //for each category child (OR relation)
            var categoryChildren = child.children;
            if(categoryChildren){
              for(var catIndex = 0; catIndex < categoryChildren.length; catIndex++){
                //add it to category_iri array
                var categoryChild = categoryChildren[catIndex];
                thisCategoryRestriction.category_iri.push(categoryChild.attr.iri + SPARQL.iri_delim + SPARQL.fixName(categoryChild.attr.name));
              }
            }
            
            
            category_restriction.push(thisCategoryRestriction);
          }

          //if this is property then add a triple
          if(child.attr.gui === 'property'){
            var thisTriple = {
              subject: {
                value: subject.attr.type === 'IRI' ? SPARQL.getFullName(SPARQL.fixName(subject.attr.name), 'instance') : SPARQL.fixName(subject.attr.name),
                type: subject.attr.type
              },
              predicate:{},
              object:{}
            }

            thisTriple.predicate.value = child.attr.iri + SPARQL.iri_delim + SPARQL.fixName(child.attr.name);
            thisTriple.predicate.type = child.attr.type;
            thisTriple.object.value = SPARQL.fixName(child.attr.valuename);
            thisTriple.object.type = child.attr.valuetype;
            thisTriple.optional = !child.attr.valuemustbeset;
            if(child.attr.showinresults){
              projection_var.push(SPARQL.fixName(child.attr.valuename));
            }
            triple.push(thisTriple);

          }
        }
      }      
    }
    queryJsonObject.projection_var = projection_var;
    queryJsonObject.category_restriction = category_restriction;
    queryJsonObject.triple = triple;
    queryJsonObject.namespace = [
    {
      prefix: "tsctype",
      namespace_iri: "http://www.ontoprise.de/smwplus/tsc/unittype#"
    },
    {
      prefix: "xsd",
      namespace_iri: "http://www.w3.org/2001/XMLSchema#"
    },
    {
      prefix: "rdf",
      namespace_iri: "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    }
    ,
    {
      prefix: "category",
      namespace_iri: SPARQL.category_iri + SPARQL.iri_delim
    }
    ,
    {
      prefix: "property",
      namespace_iri: SPARQL.property_iri + SPARQL.iri_delim
    }
    ,
    {
      prefix: "instance",
      namespace_iri: SPARQL.instance_iri + SPARQL.iri_delim
    }
    ];
    
    //add order and format settings to query

    SPARQL.Model.data = queryJsonObject;
    SPARQL.json.treeJsonObject = treeJsonObject;

  };

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

  SPARQL.activateAddCategoryBtn = function(){
    $('#qiAddCategoryBtn').live('click', function(){
      //get selected node
      var selectedNodeId = SPARQL.View.getSelectedNodeId();

      //get selected subject
      var dataEntity = SPARQL.View.getDataEntity(selectedNodeId);
      var subject = SPARQL.Model.getSubjectFromDataEntity(dataEntity);
      SPARQL.Model.createCategory(subject, '');
    });
  };  

  SPARQL.View.openCategoryDialog = function(categoryData){
    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();

    //get category_restriction obj cantaining this category
    var category_restriction = SPARQL.Model.getCategory(categoryData.subject.value, categoryData.subject.type, categoryData.category_iri);

    //populate the dilaog
    var category_iri = category_restriction.category_iri;

    //remove all previously added input rows
    $('#qiCategoryDialogTable tr').not('#categoryInputRow').not('#categoryTypeRow').not('#categoryOrLinkRow').remove();
    
    for(var i = 0; i < category_iri.length; i++){
      var categoryName = category_iri[i].replace(SPARQL.category_iri + SPARQL.iri_delim, '');
      if(i == 0){
        $('#qiCategoryDialogTable input').val(categoryName);
      }
      else{
        //add OR relation input
        $('#qiAddOrCategoryLink').closest('tr').before(SPARQL.createAdditionalCategoryPanel(categoryName));
      }      
    }

    $('#qiCategoryDialogTable').find('input').first().focus();

    //bind keyup event to every category name inputbox
    $('#qiCategoryDialog input[type=text]').live('keyup', function(event){
      //bind focusout event for use with autocompletion
      $(this).live('focusout', function(event){
        $(this).unbind('focusout');
        SPARQL.View.openCategoryDialog.changeName($(this));
      });
      
      SPARQL.View.openCategoryDialog.changeName($(this));
    });   
    $('#qiCategoryDialog input[type=text]').live('focusout', function(event){
      SPARQL.View.openCategoryDialog.changeName($(this));
    });

    $('#qiCategoryDialog input[type=text]').live('focus', function(event){
      $(this).attr('oldvalue', $(this).val());     
    });
  };

  SPARQL.View.openCategoryDialog.changeName = function(element){
    //get selected subject node
    var jstree = $.jstree._reference('qiTreeDiv');
    var nodeData = SPARQL.View.getDataEntity(jstree.get_selected().attr('id'));

    //change categoty in the model
    SPARQL.Model.updateCategory(nodeData, SPARQL.View.getCategories());

    //save the old value after each update
    element.attr('oldvalue', element.val());
  };

  SPARQL.View.getCategories = function(){
    var categories = [];
    $('#qiCategoryDialogTable input').each(function(){
      categories.push($(this).val());
    });

    return categories;
  };

  SPARQL.Model.buildSubjectObject = function(treeSubjectName){
    treeSubjectName = $.trim(treeSubjectName);
    var subjectType = (treeSubjectName.indexOf('?') === 0 ? 'VAR' : 'IRI');
    var subjectName = treeSubjectName.replace(/^\?/, '');
    subjectName = (subjectType === 'VAR' ? subjectName : SPARQL.instance_iri + SPARQL.iri_delim + subjectName);

    return {
      type: 'subject',
      value: {
        type: subjectType,
        value: subjectName
      }
    };
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
  
  SPARQL.activateAddSubjectBtn = function(){
    $('#qiAddSubjectBtn').live('click', function(){
      SPARQL.Model.createSubject('', 'VAR');
    });
  };

  SPARQL.Model.isVarInResults = function(subject){
    var result = true;
    var projection_var = SPARQL.Model.data.projection_var;
    if(subject){
      result = (subject.type === 'VAR' && $.inArray(subject.value, projection_var) > -1);
    }

    return result;
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

  SPARQL.View.openSubjectDialog = function(nodeData){
    
    //unbind the events
    $('#qiSubjectNameInput').unbind();
    $('#qiSubjectShowInResultsChkBox').unbind();
    $('#qiSubjectColumnLabel').unbind();
    
    var subjectName = nodeData.value.value;
    //    var columnLabel;
    var isVar = (nodeData.value.type === 'VAR');
    var showInResults = !subjectName.length || (isVar && SPARQL.Model.isVarInResults(nodeData.value));
    
    if(isVar){
      subjectName = '?' + subjectName;
    }
    
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').show();
    $('#qiPropertyDialog').hide();

    SPARQL.View.showFilters(SPARQL.View.getTripleNodeData(nodeData), $('#qiSubjectDialog'), true);
    
    //    $('#qiSubjectDialog #qiSubjectColumnLabel').val(columnLabel || '');
    
    if(showInResults){
      $('#qiSubjectShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiSubjectShowInResultsChkBox').removeAttr('checked');
    }    
  
    $('#qiSubjectNameInput').keyup(function(event){
      $(this).focusout(function(event){
        $(this).unbind('focusout');
        SPARQL.View.openSubjectDialog.changeName($(this), $('#qiSubjectShowInResultsChkBox'));
      });
      SPARQL.View.openSubjectDialog.changeName($(this), $('#qiSubjectShowInResultsChkBox'));
      $(this).attr('oldValue', $(this).val());
    });
    $('#qiSubjectNameInput').focus(function(event){
      $(this).attr('oldValue', $(this).val());     
    }); 

    $('#qiSubjectShowInResultsChkBox').change(function(event){
      SPARQL.View.openSubjectDialog.changeName($('#qiSubjectNameInput'), $(this));
    });

    //focus on the input box
    $('#qiSubjectNameInput').focus();

    $('#qiSubjectNameInput').val(subjectName);
    //set 1st time the oldValue mannualy cause we have to set the value after the focus
    $('#qiSubjectNameInput').attr('oldValue', subjectName);
  }

  SPARQL.View.openSubjectDialog.changeName = function(input, checkBox){
    var subjectNewName = $.trim(input.val());
    var subjectOldName = $.trim(input.attr('oldValue') || '');

    var subjectOldType = (subjectOldName.indexOf('?') === 0 ? 'VAR' : 'IRI');
    var subjectNewType = (subjectNewName.indexOf('?') === 0 ? 'VAR' : 'IRI');

    if(subjectNewType === 'IRI'){
      checkBox.removeAttr('checked');
      checkBox.attr('disabled', 'disabled');
    }
    else{
      checkBox.removeAttr('disabled');
    }

    if(checkBox){
      var inResults = !!checkBox.attr('checked');
    }

    inResults = subjectNewType === 'VAR' ? inResults : undefined;

    SPARQL.Model.updateSubject({
      type: subjectOldType,
      value: (subjectOldType === 'VAR' ? subjectOldName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + subjectOldName)
    },
    {
      type: subjectNewType,
      value: (subjectNewType === 'VAR' ? subjectNewName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + subjectNewName)
    },
    inResults);
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
  

  SPARQL.View.openPropertyDialog = function(dataEntity){
    dataEntity = dataEntity.value;
    var propertyName = dataEntity.predicate.value.replace(SPARQL.property_iri, '').replace(SPARQL.iri_delim, '');
    var valueName = dataEntity.object.value.replace(SPARQL.property_iri, '').replace(SPARQL.iri_delim, '');
    var valueType = dataEntity.object.type;
    //    var columnLabel = selectedNode.attr('columnlabel');
    var showInResults = SPARQL.Model.isVarInResults(dataEntity.subject);
    var valueMustBeSet = !dataEntity.optional;

    if(valueName && valueName.length){
      if(valueType === 'VAR'){
        valueName = '?' + valueName;
      }
    }
    
    SPARQL.View.replaceTextInputWithListBox('qiPropertyValueNameInput');
    SPARQL.View.showFilters(dataEntity, $('#qiPropertyDialog'));   
    

    $('#qiPropertyFiltersTable select').live('change', function(){
      SPARQL.Model.updateFilters(SPARQL.View.getFilters(valueName, valueType, $('#qiPropertyDialog')));
    });
    $('#qiPropertyFiltersTable input[type=text]').live('keyup', function(){
      SPARQL.Model.updateFilters(SPARQL.View.getFilters(valueName, valueType, $('#qiPropertyDialog')));
    });


    //bind keyup event to property name inputbox
    $('#qiPropertyNameInput').keyup(function(event){
      $(this).focusout(function(event){
        $(this).unbind('focusout');        
        SPARQL.View.openPropertyDialog.changePropertyName(dataEntity);
        SPARQL.getPropertyInfo($('#qiPropertyNameInput').val());
      });
      window.clearTimeout(window.changePropertyNameTimeoutId);
      window.changePropertyNameTimeoutId = window.setTimeout(function(){
        SPARQL.View.openPropertyDialog.changePropertyName(dataEntity);
        SPARQL.getPropertyInfo($(this).val());
        $(this).attr('oldValue', $.trim($(this).val()));
      }, 500);
    });

    $('#qiPropertyNameInput').focus(function(event){
      $(this).attr('oldValue', $.trim($(this).val()));      
    });

    $('#qiPropertyValueNameInput').focus(function(event){
      mw.log('.............focus. old value: ' + $.trim($(this).val()));
      $(this).attr('oldValue', $.trim($(this).val()));
    });
    $('#qiPropertyValueNameInput').change(function(event){
      mw.log('............change. old value: ' + $.trim($(this).attr('oldValue')));
      mw.log('............change. new value: ' + $.trim($(this).val()));
      window.clearTimeout(window.changePropertyValueTimeoutId);
      window.changePropertyValueTimeoutId = window.setTimeout(function(){
        SPARQL.View.openPropertyDialog.changePropertyValue(dataEntity);
        $(this).attr('oldValue', $.trim($(this).val()));
      }, 500);
    });

    $('#qiPropertyValueMustBeSetChkBox').change(function(event){    
      SPARQL.View.openPropertyDialog.changeValueMustBeSet(dataEntity);
    });

    $('#qiPropertyValueShowInResultsChkBox').change(function(event){
      SPARQL.View.openPropertyDialog.changeShowInResults(dataEntity);
    });
    //    $('#qiSubjectColumnLabel').change(function(event){
    //
    //      });  
    

    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').show();
    $('#qiPropertyDialog #qiPropertyNameInput').val(propertyName || '');
    $('#qiPropertyDialog #qiPropertyValueNameInput').val(valueName || '?value');
    //    $('#qiPropertyDialog #qiSubjectColumnLabel').val(columnLabel || '');

    if(valueMustBeSet){
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').removeAttr('checked');
    }
    if(showInResults){
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('checked');
    }

    //focus on the input box
    $('#qiPropertyNameInput').focus();
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

  SPARQL.View.openPropertyDialog.changePropertyName = function(dataEntity){
    var newName = $.trim($('#qiPropertyNameInput').val() || '').replace(/\s+/, '_');
    var newType = (newName.indexOf('?') === 0 ? 'VAR' : 'IRI');
    var oldName = $.trim($('#qiPropertyNameInput').attr('oldValue') || '');
    var oldType = (oldName.indexOf('?') === 0 ? 'VAR' : 'IRI');

    dataEntity.predicate = {
      type: oldType,
      value: oldType === 'VAR' ? oldName : SPARQL.property_iri + SPARQL.iri_delim + oldName
    };
   
    var newTriple = {
      subject: dataEntity.subject,
      predicate: {
        type: newType,
        value: newType === 'VAR' ? newName : SPARQL.property_iri + SPARQL.iri_delim + newName
      },
      object: dataEntity.object,
      optional: dataEntity.optional
    };

    SPARQL.Model.updateProperty(dataEntity, newTriple);

  };

  SPARQL.View.openPropertyDialog.changePropertyValue = function(dataEntity){    
    var newName = $.trim($('#qiPropertyValueNameInput').val() || '');
    var newType = (newName.indexOf('?') === 0 ? 'VAR' : 'IRI');
    var oldName = $.trim($('#qiPropertyValueNameInput').attr('oldValue') || '');
    var oldType = (oldName.indexOf('?') === 0 ? 'VAR' : 'IRI');

    dataEntity.object = {
      type: oldType,
      value: oldType = 'VAR' ? oldName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + oldName
    };

    var newTriple = {
      subject: dataEntity.subject,
      predicate: dataEntity.predicate,
      object: {
        type: newType,
        value: newType = 'VAR' ? newName.replace(/^\?/, '') : SPARQL.instance_iri + SPARQL.iri_delim + newName
      },      
      optional: dataEntity.optional
    };

    SPARQL.Model.updateProperty(dataEntity, newTriple, !dataEntity.optional);
  };

  SPARQL.View.openPropertyDialog.changeValueMustBeSet = function(dataEntity){
    
    var valueMustBeSet = !!$('#qiPropertyValueMustBeSetChkBox').attr('checked');

    var newTriple = {
      subject: dataEntity.subject,
      predicate: dataEntity.predicate,
      object: dataEntity.object,
      optional: !valueMustBeSet
    };

    SPARQL.Model.updateProperty(dataEntity, newTriple);

    dataEntity.optional = !valueMustBeSet;
  };

  SPARQL.View.openPropertyDialog.changeShowInResults = function(dataEntity){

    var showInResults = !!$('#qiPropertyValueShowInResultsChkBox').attr('checked');

    var newTriple = {
      subject: dataEntity.subject,
      predicate: dataEntity.predicate,
      object: dataEntity.object,
      optional: dataEntity.optional
    };
    
    SPARQL.Model.updateProperty(dataEntity, newTriple, showInResults);
  };

  

  SPARQL.Model.updateProperty = function(oldTriple, newTriple, valueInResults){    
    var triples = SPARQL.Model.data.triple;
    var category_restrictions = SPARQL.Model.data.category_restriction;
    var projection_var = SPARQL.Model.data.projection_var;
    //find old triple
    for(var i = 0; i < triples.length; i++){
      if(SPARQL.objectsEqual(oldTriple, triples[i])){
        //replace with the new triple
        triples.splice(i, 1);
        triples.push(newTriple);
        break;
      }
    }
    if(typeof valueInResults !== 'undefined'){
      //if old object value is not in other triples or categories then remove it from projection vars
      var varExists = false;
      $.each(triples, function(index, triple){
        if(triple.object.value === oldTriple.object.value && triple.object.type === oldTriple.object.type){
          varExists = true;
          return false;//break the loop
        }
      });
      if(!varExists){
        $.each(category_restrictions, function(index, category_restriction){
          if(category_restriction.subject.value === oldTriple.object.value && category_restriction.subject.type === oldTriple.object.type){
            varExists = true;
            return false;//break the loop
          }
        });
      }
      if(!varExists){
        $.each(projection_var, function(index, variable){
          if(variable === oldTriple.object.value){
            projection_var.splice(index, 1);
            return false;//break the loop
          }
        });
      }
      if(valueInResults && $.inArray(newTriple.object.value, projection_var) === -1){
        projection_var.push(newTriple.object.value);
      }
      if(!valueInResults && $.inArray(newTriple.object.value, projection_var) > -1){
        for(i = 0; i < projection_var.length; i++){
          if(projection_var[i] === newTriple.object.value){
            projection_var.splice(i, 1);
          }
        }
      }
    }

    SPARQL.View.updateProperty(oldTriple, newTriple);
    SPARQL.updateAllFromTree();
  };

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

  SPARQL.Model.createProperty = function(subject, propertyName, valueName){
    if(!subject){
      subject = SPARQL.Model.createSubject('newsubject', 'VAR');
    }
    var propertyType = ($.trim(propertyName).indexOf('?') === 0 ? 'VAR' : 'IRI');
    var propertyValue = (propertyType === 'VAR' ? propertyName : SPARQL.property_iri + SPARQL.iri_delim + propertyName);
    var valueType = ($.trim(valueName).indexOf('?') === 0 ? 'VAR' : 'IRI');
    var valueValue = (valueType === 'VAR' ? valueName : SPARQL.instance_iri + SPARQL.iri_delim + valueName);
    
    var triple  = {
      subject: subject,
      predicate: {
        type: propertyType,
        value: propertyValue
      },
      object: {
        type: valueType,
        value: valueValue
      },
      optional: false
    };
    
    SPARQL.Model.data.triple.push(triple);
    if(triple.object.type === 'VAR' && $.inArray(triple.object.value, SPARQL.Model.data.projection_var) === -1){
      SPARQL.Model.data.projection_var.push(triple.object.value);
    }
    SPARQL.View.createProperty(triple);
  };

  SPARQL.View.createProperty = function(triple){
    var propertyName = triple.predicate.value.replace(SPARQL.property_iri, '').replace(SPARQL.iri_delim, '');
    var valueName = triple.object.value.replace(SPARQL.instance_iri, '').replace(SPARQL.iri_delim, '');
    var nodeLabel = '';
    if(propertyName.length){
      if(triple.predicate.type === 'VAR'){
        nodeLabel += '?' + propertyName;
      }
      else{
        nodeLabel += propertyName;
      }
    }
    if(valueName.length){
      if(triple.object.type === 'VAR'){
        nodeLabel += ' ?' + valueName;
      }
      else{
        nodeLabel += ' ' + valueName;
      }
    }

    //add a child node
    var propertyNodeData = {
      data:{
        title: nodeLabel
      },
      attr: {
        id: 'property-' + SPARQL.getNextUid(),
        rel: 'property'
      }
    };

    SPARQL.View.map.put(propertyNodeData.attr.id, {
      type: 'property',
      value: triple
    });
    var jstree = $.jstree._reference('qiTreeDiv');
    var selectedNode = jstree.get_selected();
    if(!selectedNode.length){
      selectedNode = SPARQL.View.getSubjectNode(triple.subject);
    }
    jstree.create ( SPARQL.View.getParentSubjectNode(selectedNode), 'first' , propertyNodeData, function(){}, true );
    jstree.deselect_all();
    jstree.select_node('#' + propertyNodeData.attr.id);
  };

  SPARQL.View.getSubjectNode = function(subject){
    //get node id by data entity
    var nodeId = SPARQL.View.getNodeId({
      type: 'subject',
      value: subject
    });
    //get tree node by id
    return $.jstree._reference('qiTreeDiv')._get_node('#' + nodeId);
  };

  SPARQL.View.getParentSubjectNode = function(thisNode){    
    return SPARQL.View.getParentNode(thisNode, ['variable', 'instance']);
  };

  SPARQL.activateAddPropertyBtn = function(){
    $('#qiAddPropertyBtn').live('click', function(){
      var selectedNodeId = SPARQL.View.getSelectedNodeId();

      //get selected subject
      var dataEntity = SPARQL.View.getDataEntity(selectedNodeId);
      var subject = SPARQL.Model.getSubjectFromDataEntity(dataEntity);
      SPARQL.Model.createProperty(subject, '', '');
    });
  };

  SPARQL.activateAddAndFilterLink = function(){
    $('#qiAddAndFilterLink').live('click', function(event){        
      var jstree = $.jstree._reference('qiTreeDiv');
      var node = SPARQL.View.getParentNode(jstree.get_selected(), ['variable', 'property']);
      if(node && node != -1){
        $(this).closest('tr').before('<tr><td>' + SPARQL.createFilterTable() + '</td></tr>');
        var nodeId = node.attr('id');
        var nodeData = SPARQL.View.getDataEntity(nodeId);
        var varType, varValue;

        switch(nodeData.type){
          case 'subject':
            varType = nodeData.value.type;
            varValue = nodeData.value.value;
            break;

          case 'property':
            varType = nodeData.value.object.type;
            varValue = nodeData.value.object.value;
            break;

          default:
            break;
        }        
        SPARQL.Model.addFilterAND('LT', varType, varValue);

        $('.filterAND input').live('keyup', function(){
          SPARQL.Model.updateFilters(SPARQL.View.getFilters(varValue, varType, $('#qiPropertyDialog')));
        });

        $('.filterAND select').live('change', function(){
          SPARQL.Model.updateFilters(SPARQL.View.getFilters(varValue, varType, $('#qiPropertyDialog')));
        });
      }
      else{
        SPARQL.showMessageDialog('Filters can only be added to variables', 'Operation failed');
      }
      event.preventDefault();
    });
  };

  SPARQL.Model.updateFilters = function(newFilters){
    //replace old filters with new ones
    SPARQL.Model.data.filter = newFilters;

    var jstree = $.jstree._reference('qiTreeDiv');
    var propertyNode = SPARQL.View.getParentNode(jstree.get_selected(), 'property');
    var nodeData = SPARQL.View.getDataEntity(propertyNode.attr('id'));

    SPARQL.View.updateFilters(nodeData.value.object.value, nodeData.value.object.type);
    SPARQL.updateAllFromTree();
  };

  SPARQL.Model.addFilterAND = function(operator, operandType1, operandValue1, operandType2, operandValue2, dataTypeIRI){
    //    operator = operator || 'LT',
    //    operandType1 = operandType1 || '',
    //    operandValue1 = operandValue1 || '',
    //    operandType2 = operandType2 || '',
    //    operandValue2 = operandValue2 || '',
    //    dataTypeIRI = dataTypeIRI || ''
    //
    //    var filters = SPARQL.Model.data.filter;
    //    var newFilter = {
    //      expression: [{
    //        operator: operator,
    //        argument: [{
    //          type: operandType1,
    //          value: operandValue1
    //        },
    //        {
    //          type: operandType2,
    //          value: operandValue2,
    //          datatype_iri: dataTypeIRI
    //        }]
    //      }]
    //    };
    //    filters.push(newFilter);

    SPARQL.View.addFilterAND('');
  };

  SPARQL.View.addFilterAND =  function(label){
    //get parent property node of the selected node
    var jstree = $.jstree._reference('qiTreeDiv');
    var parentPropertyNode = SPARQL.View.getParentNode(jstree.get_selected(), ['variable', 'property']);

    //create a new filter node
    var filterNode = {
      data:{
        title: label
      },
      attr: {
        id: 'filter-' + SPARQL.getNextUid(),
        rel: 'filter'
      }
    };

    //append it to property node as child
    jstree.create (parentPropertyNode, 'first' , filterNode, function(){}, true );
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
      var jstree = $.jstree._reference('qiTreeDiv');
      var propertyNode = SPARQL.View.getParentNode(jstree.get_selected(), 'property');
      var nodeData = SPARQL.View.getDataEntity(propertyNode.attr('id'));

      if($(this).closest('table').find('#qiDeleteFilterImg').length == 1){
        $(this).closest('table').remove();
      }
      else{
        $(this).closest('tr').remove();
      }

      SPARQL.Model.updateFilters(SPARQL.View.getFilters(nodeData.value.object.value, nodeData.value.object.type), $('#qiPropertyDialog'));
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

  SPARQL.View.getFilters = function(varName, varType, dialog){
    dialog = dialog || $('#qiPropertyDialog');
    var filters = [];
    varName = $.trim(varName).replace(/^\?/, '');

    //iterate over AND filters
    var filtersAND = $(dialog).find('.filterAND');
    filtersAND.each(function(){
      //create new filter object
      var filter = {
        expression: []
      };
      //iterate over OR filters
      $(this).find('.filterOR').each(function(){
        //create new expression object
        var expression = {};
        //populate the expression object with data
        expression.operator = $(this).find('select').val();
        var argument1 = {
          type: varType,
          value: varName
        };
        var argument2 = {
          type: 'LITERAL',
          datatype_iri: SPARQL.getDataTypeIRI($(dialog).find('.typeLabelTd').next().text()),
          value: $(this).find('input').val()
        };
        expression.argument = [argument1, argument2];
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

  SPARQL.View.getPropertyNode = function(value, type){
    //get all triples having object with such value and type
    var dataEntities = SPARQL.View.map.treeToData;
    var result = [];
    for(var nodeId in dataEntities){
      var dataEntity = dataEntities[nodeId];
      if(dataEntity.type === 'property' && dataEntity.value.object.value === value && dataEntity.value.object.type === type){
        result.push($.jstree._reference('qiTreeDiv')._get_node('#' + nodeId));
      }
    }

    return $(result);
  };

  SPARQL.View.updateFilters = function(operandValue, operandType){
    //remove filter nodes
    var jstree = $.jstree._reference('qiTreeDiv');
    var selectedNode = jstree.get_selected();
    if(!selectedNode.length){
      selectedNode = SPARQL.View.getPropertyNode(operandValue, operandType);
    }
    var propertyNode = SPARQL.View.getParentNode(selectedNode, 'property');
    jstree._get_children(propertyNode).each(function(){
      jstree.delete_node($(this));
    });
    
    //iterate over filters array
    var filters = SPARQL.Model.data.filter || [];
    for(var i = 0; filters && i < filters.length; i++){
      var filterLabel = '';
      for(var j = 0; j < filters[i].expression.length; j++){
        var expression = filters[i].expression[j];
        //add filter node for each filter whose expression argument type and value equal to the arguments
        if(expression.argument[0].value === operandValue && expression.argument[0].type === operandType){
          filterLabel += SPARQL.View.translateOperator(expression.operator) + ' ' + expression.argument[1].value;
        }
        else if(expression.argument[1].value === operandValue && expression.argument[1].type === operandType){
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
      var tr = $('#qiAddOrCategoryLink').closest('tr');
      tr.before(SPARQL.createAdditionalCategoryPanel(''));
      tr.find('input').focus();      
    });
  };

  SPARQL.activateDeleteCategoryImg = function(){
    $('#qiDeleteCategoryImg').live('click', function(){      
      //remove the input controls
      $(this).closest('tr').next().remove();
      $(this).closest('tr').remove();

      var jstree = $.jstree._reference('qiTreeDiv');
      var nodeData = SPARQL.View.getDataEntity(jstree.get_selected().attr('id'));

      //change categoty in the model
      SPARQL.Model.updateCategory(nodeData, SPARQL.View.getCategories());
    });
  };

  SPARQL.activateUpdateBtn = function(){
    $('#qiUpdateButton').live('click', function(){
      SPARQL.updateAllFromTree();
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
        SPARQL.activateUpdateBtn();
        SPARQL.View.activateDeleteLink();
        SPARQL.activateCancelLink();
        SPARQL.activateAddSubjectBtn();
        SPARQL.activateAddCategoryBtn();
        SPARQL.activateAddPropertyBtn();
      });

    tree.jstree(treeJsonConfig);

    tree.bind("select_node.jstree",
      function(NODE, REF_NODE) {
        //get node data
        var theNode = SPARQL.View.getParentNode($(REF_NODE.rslt.obj), ['variable', 'instance', 'category', 'property']);
        var nodeData = SPARQL.View.getDataEntity(theNode.attr('id'));
        if(!nodeData){
          return;
        }

        switch(nodeData.type){
          case 'subject':
            SPARQL.View.openSubjectDialog(nodeData);
            break;

          case 'category':
            SPARQL.View.openCategoryDialog(nodeData);
            break;

          case 'property':
            SPARQL.View.openPropertyDialog(nodeData);
            break;

          default:
            break;
        }
      });

    //    var treeChangeimeout;
    //    tree.bind("set_text.jstree", function(event, data){
    //      if(data.rslt.name !== data.rslt.obj.wholeText){
    //        window.clearTimeout(treeChangeimeout);
    //        treeChangeimeout = window.setTimeout(function(){
    //          SPARQL.updateAllFromTree();
    //        }, 500);
    //      }
    //    });
    //    tree.bind("delete_node.jstree", function(event, data){
    //      if(data.rslt.name !== data.rslt.obj.wholeText){
    //        if(!SPARQL.Model.data.triple.length && !SPARQL.Model.data.category_restriction.length){
    //          SPARQL.View.reset();
    //        }
    //      }
    //    });

    $.jstree._themes = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/themes/';
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
  SPARQL.View.activateDeleteLink = function(){
    $('#qiDeleteLink').live('click', function(event){
      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
        return;

      var selectedNodeId = $.jstree._focused().get_selected().attr('id');
      var dataEntity = SPARQL.View.getDataEntity(selectedNodeId);

      switch(dataEntity.type){
        case 'subject':
          SPARQL.Model.deleteSubject(dataEntity);
          break;

        case 'category':
          SPARQL.Model.deleteCategory(dataEntity);
          break;

        case 'property':
          SPARQL.Model.deleteProperty(dataEntity);
          break;

        default:
          break;
      }

      //don't open the link address
      event.preventDefault();
    });
  };



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

  SPARQL.Model.deleteProperty = function(dataEntity){
    //remove this property from triple
    var triples = SPARQL.Model.data.triple;
    for(var i = 0; i < triples.length; i++){
      if(SPARQL.objectsEqual(dataEntity.value, triples[i])){
        triples.splice(i, 1);
        break;
      }
    }

    SPARQL.View.deleteProperty(dataEntity);
    SPARQL.updateAllFromTree();
  };

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

  SPARQL.Model.deleteSubject = function(dataEntity){
    var subjectId = dataEntity.value.value;
    var subjectType = dataEntity.value.type;
    var triples = SPARQL.Model.data.triple;
    //remove from triple
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      if(SPARQL.objectsEqual(triple.subject, dataEntity.value)){
        triples.splice(i, 1);
      }
    }

    //if this is var remove it from order
    if(subjectType === 'VAR'){
      var order = SPARQL.Model.data.order || [];
      for(i = 0; i < order.length; i++){
        if(subjectId === order[i].by_var){
          order.splice(i, 1);
          break;
        }
      }
      //remove it from projection_var
      var projection_var = SPARQL.Model.data.projection_var;
      for(i = 0; i < projection_var.length; i++){
        if(subjectId === projection_var[i]){
          projection_var.splice(i, 1);
          break;
        }
      }
    }
    SPARQL.View.deleteSubject(dataEntity);
    if(SPARQL.Model.data.triple.length == 0 && SPARQL.Model.data.category_restriction.length == 0){
      SPARQL.Model.reset();
    }
    else{
      SPARQL.updateAllFromTree();
    }
  };

  SPARQL.View.deleteSubject = function(dataEntity){
    //get node id
    var nodeId = SPARQL.View.getNodeId(dataEntity);

    //if selected node id = this subject node id then remove the node
    var jstree = $.jstree._reference('qiTreeDiv');
    var selectedNode = jstree.get_selected();
    if(selectedNode.attr('id') === nodeId){
      jstree.delete_node(selectedNode);
      SPARQL.View.map.remove(nodeId);
    }

    if($.jstree._reference('qiTreeDiv')._get_children(-1).length === 0){
      SPARQL.View.cancel();
    }
  };

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

  SPARQL.Model.reset = function(){
    SPARQL.tripleStoreGraph = window.parent.smwghTripleStoreGraph + SPARQL.iri_delim;
    SPARQL.category_iri = SPARQL.tripleStoreGraph + 'category';
    SPARQL.property_iri = SPARQL.tripleStoreGraph + 'property';
    SPARQL.instance_iri = SPARQL.tripleStoreGraph + 'instance';

    SPARQL.Model.data = {
      category_restriction: [],
      triple: [],
      filter: [],
      projection_var: [],
      namespace: [],
      order: []
    };

    SPARQL.queryString = null;
    SPARQL.queryParameters = {
      source: 'tsc',
      format: 'table'
    },

    SPARQL.Model.data.namespace = [
    {
      prefix: "tsctype",
      namespace_iri: "http://www.ontoprise.de/smwplus/tsc/unittype#"
    },
    {
      prefix: "xsd",
      namespace_iri: "http://www.w3.org/2001/XMLSchema#"
    },
    {
      prefix: "rdf",
      namespace_iri: "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    },
    {
      prefix: "category",
      namespace_iri: SPARQL.category_iri + SPARQL.iri_delim
    },
    {
      prefix: "property",
      namespace_iri: SPARQL.property_iri + SPARQL.iri_delim
    },
    {
      prefix: "instance",
      namespace_iri: SPARQL.instance_iri + SPARQL.iri_delim
    }];

    SPARQL.View.reset();
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
  SPARQL.toTree = function(queryJsonObject){
    //clear the tree nodes
    var jstree = $.jstree._reference('qiTreeDiv');
    jstree.delete_node(jstree._get_children(-1));
    SPARQL.View.map.treeToData = {};

    //use internal data structure if none specified
    if(!queryJsonObject){
      queryJsonObject = SPARQL.Model.data;
    }

    queryJsonObject.triple = queryJsonObject.triple || [];
    queryJsonObject.category_restriction = queryJsonObject.category_restriction || [];
    queryJsonObject.projection_var = queryJsonObject.projection_var || [];
    var triples = queryJsonObject.triple;
    var category_restrictions = queryJsonObject.category_restriction;

    //iterate over triples
    for(var i = 0; i < triples.length; i++){
      var triple = triples[i];
      SPARQL.View.createSubject(triple.subject);
      SPARQL.View.createProperty(triple);
      SPARQL.View.updateFilters(triple.object.value, triple.object.type);
    }
    //iterate over category_restriction
    for (i = 0; i < category_restrictions.length; i++){
      var category_restriction = category_restrictions[i];
      SPARQL.View.createSubject(category_restriction.subject);
      var category_iri = category_restriction.category_iri;
      SPARQL.View.createCategory(category_restriction.subject, category_iri[0].replace(SPARQL.category_iri, '').replace(SPARQL.iri_delim, ''));
      for(var j = 1; j < category_iri.length; j++){
        SPARQL.View.addCategoryOR(category_iri[j].replace(SPARQL.category_iri, '').replace(SPARQL.iri_delim, ''), category_restriction.subject.value, category_restriction.subject.type);
      }
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



