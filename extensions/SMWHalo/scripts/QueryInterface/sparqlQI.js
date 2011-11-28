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

    json : {

      variableIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif',
      instanceIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/instance_icon.gif',
      categoryIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif',
      propertyIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif',

      TreeQuery : {}
    }
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
    var shortName = getShortName(string);
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
          category_restriction.push(thisCategoryRestriction);
        }

        //if this is property then add a triple
        if(child.attr.gui === 'property'){
          var thisTriple = {
            subject: {
              value: subject.attr.type === 'IRI' ? getFullName(SPARQL.fixName(subject.attr.name), 'instance') : SPARQL.fixName(subject.attr.name),
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
    queryJsonObject.projection_var = projection_var;
    queryJsonObject.category_restriction = category_restriction;
    queryJsonObject.triple = triple;

    
    //add order and format settings to query

    SPARQL.json.TreeQuery = queryJsonObject;
    SPARQL.json.treeJsonObject = treeJsonObject;

  };

  SPARQL.activateUpdateSourceBtn = function(){
    $('#qiUpdateSourceBtn').live('click', function(){
      //parse the parserfunc string
      SPARQL.parseParserFuncString($('#sparqlQueryText').val());
      
      //update tree from source
      SPARQL.sparqlToTree();

      SPARQL.getQueryResult();
      SPARQL.updateSortOptions();
    });
  };



  SPARQL.parseParserFuncString = function(parserFuncString){
    var regex = /\{\{#sparql:\s*(([\s\S]+)\s*\|(\w+)\s*=\s*(\w+))\s*\}\}/;
    var result = regex.exec(parserFuncString);
    for(var i = 0; i < result.length; i++){
      if(i === 1){
        SPARQL.queryWithParamsString = result[1];
      }
      if(i === 2){
        SPARQL.queryString = result[2];
      }
      if(i > 2){
        break;
      //@TODO implement query parameters initialization
      }
    }    
  };

  SPARQL.activateDiscardChangesLink = function(){
    $('#discardChangesLink').live('click', function(){
      $('#sparqlQueryText').val($('#sparqlQueryText').data('initialQuery'));
    });
  };

  SPARQL.activateQueryViewTabToggle = function(){
    //switch to tree view
    $('#sparqlQI #qiDefTab1').live('click', function(){
      $('#sparqlQI #treeview').css('display', 'inline');
      $('#sparqlQI #qiDefTab1').addClass('qiDefTabActive');
      $('#sparqlQI #qiDefTab1').removeClass('qiDefTabInactive');
      $('#sparqlQI #qisource').css('display', 'none');
      $('#sparqlQI #qiDefTab3').addClass('qiDefTabInactive');
      $('#sparqlQI #qiDefTab3').removeClass('qiDefTabActive');

      SPARQL.sparqlToTree(SPARQL.queryString);
    });


    $('#sparqlQI #qiDefTab3').live('click', function(){
      $('#sparqlQI #qisource').css('display', 'inline');
      $('#sparqlQI #qiDefTab3').addClass('qiDefTabActive');
      $('#sparqlQI #qiDefTab3').removeClass('qiDefTabInactive');
      $('#sparqlQI #treeview').css('display', 'none');
      $('#sparqlQI #qiDefTab1').addClass('qiDefTabInactive');
      $('#sparqlQI #qiDefTab1').removeClass('qiDefTabActive');

      SPARQL.updateAllFromTree();
    });
  };


  SPARQL.sparqlToTree = function(sparqlQuery){
    if(!(sparqlQuery && sparqlQuery.length))
      return;

    mw.log('sparql query send:\n' + (sparqlQuery ? sparqlQuery : ''));
    //send ajax post request to localhost:8080/sparql/sparqlToTree
    $.ajax({
      type: 'POST',
      url: 'http://localhost:8080/sparql/sparqlToTree',
      data: {
        sparql: sparqlQuery
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText)
        SPARQL.json.TreeQuery = data;
        SPARQL.renderTree(SPARQL.toTree());
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        SPARQL.showMessageDialog(errorJson.error);
      }
    });
  };

  SPARQL.showMessageDialog = function(message, anchorElement){
    if(!(anchorElement && anchorElement.length)){
      anchorElement = $('#sparqlQI');
    }

    var html = '<div id="dialogDiv">' + message + '</div>';
    anchorElement.append(html);
    anchorElement = anchorElement.children('#dialogDiv').eq(0);
    anchorElement.dialog({
      modal: true,
      width: 'auto',
      height: 'auto',
      resizable: false,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
          $( this ).remove('#dialogDiv');
        }
      }
    });
  };

  SPARQL.buildParserFuncString = function(queryString){
    queryString = queryString || SPARQL.queryString;
    var format = $('#qiQueryFormatContent #layout_format').children().filter(':selected').attr('value')
    format = format ? '|format=' + format : '';
    SPARQL.queryWithParamsString = queryString + '\n' + format;
    SPARQL.parserFuncString = '{{#sparql: \n' + SPARQL.queryWithParamsString + '\n' + '}}';
    return SPARQL.parserFuncString;
  };



  SPARQL.treeToSparql = function(treeJsonConfig, callbackFn){
    if(!treeJsonConfig || $.isEmptyObject(treeJsonConfig))
      return;

    var jsonString = SPARQL.stringifyJson(treeJsonConfig);
    mw.log('tree json:\n' + jsonString);
    //send ajax post request to localhost:8080/sparql/treeToSPARQL
    $.ajax({
      type: 'POST',
      url: 'http://localhost:8080/sparql/treeToSPARQL',
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
          $('#sparqlQueryText').val(parserFuncString);
          $('#sparqlQueryText').data('initialQuery', parserFuncString);
          if(callbackFn && typeof callbackFn === 'function'){
            callbackFn();
          }
        }
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        SPARQL.showMessageDialog(errorJson.error);
      }
    });
  };

  SPARQL.stringifyJson = function(jsonObject){
    var arrayToJsonFunc = Array.prototype.toJSON;
    if(arrayToJsonFunc && typeof arrayToJsonFunc === 'function'){
      delete Array.prototype.toJSON;
    }
    var result = JSON.stringify(jsonObject);
    Array.prototype.toJSON = arrayToJsonFunc;
    return result;
  };

  SPARQL.activateSwitchToSparqBtn = function(){
    var switchToSparqlBtn = $('#switchToSparqlBtn');
    switchToSparqlBtn.live('click', function(){
      $('#askQI').hide();
      $('#sparqlQI').show();
      switchToSparqlBtn.remove();

    //@TODO if ASK query is not empty send it to tsc for convertion to SPARQL
    });
  };

  SPARQL.activateAddCategoryBtn = function(){
    $('#qiAddCategoryBtn').live('click', function(){
      var sparqlTree;
      if(!($.jstree._focused() && $.jstree._focused().get_selected() && $.jstree._focused().get_selected().length)){
        //create new subject and add new category to it
        sparqlTree = $.jstree._reference('qiTreeDiv');
        SPARQL.createSubjectNode(sparqlTree, 'newsubject', 'newsubject', 'VAR');
      }
      else{
        sparqlTree = $.jstree._focused();
        if(sparqlTree.get_selected().attr('gui') !== 'subject'){
          sparqlTree.select_node(sparqlTree.get_selected().parents('li'));
        }
      }
      
      //add category child node to selected subject node
      var categoryNodeData = {
        data:{
          title: '',
          icon : SPARQL.json.categoryIcon
        },
        attr: {
          id: 'category-' + SPARQL.getNextUid(),
          gui: 'category',
          temporary: true,
          name: '',
          iri: SPARQL.category_iri
        }
      };
      
      sparqlTree.create (null , 'first' , categoryNodeData, function(){}, true );
      sparqlTree.deselect_all();
      sparqlTree.select_node('#' + categoryNodeData.attr.id);
    });

    
  };

  SPARQL.openCategoryDialog = function(selectedNode){
    var categoryName = selectedNode.attr('name');
    
    //Unbind the events. Cause inputbox kept changing all nodes ever passed to this function
    $('#qiCategoryNameInput').unbind();

    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();
    $('#qiCategoryDialog #qiCategoryNameInput').val(categoryName);


    SPARQL.openCategoryDialog.changeName = function(element){
      var value = element.val() || ' ';
      selectedNode.children('a').contents().filter(function(){
        return this.nodeType === 3;
      }).replaceWith(value || ' ');
      
      selectedNode.attr('name', element.val());
    }
    //bind keyup event to category name inputbox
    $('#qiCategoryNameInput').keyup(function(event){
      SPARQL.openCategoryDialog.changeName($(this));
    });
    $('#qiCategoryNameInput').change(function(event){
      SPARQL.openCategoryDialog.changeName($(this));
    });
  };

  SPARQL.createSubjectNode = function(tree, nodeName, nodeTitle, nodeType){
    //create new subject node and select it
    var subjectNodeData = {
      data:{
        title: '',
        icon : SPARQL.json.variableIcon
      },
      attr: {
        id: 'subject-' + SPARQL.getNextUid(),
        name: nodeName,
        gui: 'subject',
        temporary: true,
        type: nodeType
      },
      children: []
    };

    var isVar = (nodeType === 'VAR');
    if(isVar){
      if(nodeTitle){
        subjectNodeData.data.title = '?' + nodeTitle;
      }
    }
    else{
      subjectNodeData.data.icon = SPARQL.json.instanceIcon;
      subjectNodeData.data.iri = SPARQL.instance_iri;
    }

    tree.deselect_all();
    tree.create ( null , 'first' , subjectNodeData, function(){}, true );
    tree.select_node('#' + subjectNodeData.attr.id);
    var selectedNode = tree.get_selected();
    return selectedNode;
  };
  
  SPARQL.activateAddSubjectBtn = function(){
    $('#qiAddSubjectBtn').live('click', function(){
      var sparqlTree = $.jstree._reference('#qiTreeDiv');
      SPARQL.createSubjectNode(sparqlTree, '', '', 'VAR');
    });
  }

  SPARQL.openSubjectDialog = function(selectedNode){
    mw.log('openSubjectDialog. selected node: ' + selectedNode.attr('id') + ', size: ' + selectedNode.size());

    //unbind the events so they stop
    $('#qiSubjectNameInput').unbind();
    $('#qiSubjectShowInResultsChkBox').unbind();
    $('#qiSubjectColumnLabel').unbind();
    
    var subjectName = selectedNode.attr('name');
    var columnLabel = selectedNode.attr('columnlabel');
    var showInResults = (selectedNode.attr('showinresults') === 'true');
    var isVar = (selectedNode.attr('type') === 'VAR');
    
    if(isVar){
      subjectName = '?' + subjectName;
    //      $('#qiSubjectTypeLabel').html('Type: variable');
    }
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').show();
    $('#qiPropertyDialog').hide();
    $('#qiSubjectDialog #qiSubjectNameInput').val(subjectName || '');
    $('#qiSubjectDialog #qiSubjectColumnLabel').val(columnLabel || '');
    
    if(showInResults){
      $('#qiSubjectDialog #qiSubjectShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiSubjectDialog #qiSubjectShowInResultsChkBox').removeAttr('checked');
    }

    SPARQL.openSubjectDialog.changeName = function(element){
      mw.log('changeName. selected node: ' + selectedNode.attr('id') + ', element: ' + element.attr('id'));
      selectedNode.children('a').contents().filter(function(){
        return this.nodeType === 3;
      }).replaceWith(element.val() || ' ');
      
      var style = selectedNode.children('a').children('ins').attr('style');
      if(!isVariable(element.val())){
        selectedNode.attr('name', element.val());
        selectedNode.attr('type', 'IRI');
        selectedNode.attr('iri', SPARQL.instance_iri);
        //        $('#qiSubjectTypeLabel').html('Type: ' + SPARQL.instance_iri);
        style = style.replace('variable_icon', 'instance_icon');
        selectedNode.children('a').children('ins').attr('style', style);
      }
      else{
        selectedNode.attr('name', SPARQL.fixName(element.val()));
        selectedNode.attr('type', 'VAR');
        //        $('#qiSubjectTypeLabel').html('Type: variable');
        style = selectedNode.children('a').children('ins').attr('style');
        style = style.replace('instance_icon', 'variable_icon');
        selectedNode.children('a').children('ins').attr('style', style);
      }
    }
  
    $('#qiSubjectNameInput').keyup(function(event){
      mw.log($(this).attr('id') + '. keyup');
      SPARQL.openSubjectDialog.changeName($(this));
    });
    $('#qiSubjectNameInput').change(function(event){
      mw.log($(this).attr('id') + '. change');
      SPARQL.openSubjectDialog.changeName($(this));
    });

    $('#qiSubjectColumnLabel').change(function(event){
      selectedNode.attr('columnlabel', $(this).val());
    });

    $('#qiSubjectShowInResultsChkBox').change(function(event){
      if($(this).attr('checked')){
        selectedNode.attr('showinresults', 'true');
      }
      else{
        selectedNode.removeAttr('showinresults');
      }
    });

  }

  SPARQL.fixName = function(string){
    string = string.replace(/\?/, '');
    string = string.replace(/\s/, '_');
    return string;
  };

  SPARQL.openPropertyDialog = function(selectedNode){
    //unbind event handlers so all other nodes won't be changed
    $('#qiPropertyNameInput').unbind();
    $('#qiPropertyValueNameInput').unbind();
    $('#qiPropertyValueShowInResultsChkBox').unbind();
    $('#qiPropertyValueMustBeSetChkBox').unbind();  

    
    SPARQL.openPropertyDialog.changeName = function(nameElement, valueElement){
      selectedNode.children('a').contents().filter(function(){
        return this.nodeType === 3;
      }).replaceWith(nameElement.val() + ' ' + valueElement.val());
      
      selectedNode.attr('name', SPARQL.fixName(nameElement.val()));
      selectedNode.attr('valuename', SPARQL.fixName(valueElement.val()));
      if(isVariable(nameElement.val())){
        selectedNode.attr('type', 'VAR');
      }
      else{
        selectedNode.attr('type', 'IRI');
      }
      if(isVariable(valueElement.val())){
        selectedNode.attr('valuetype', 'VAR');
      }
      else{
        selectedNode.attr('valuetype', 'IRI');
      }
    };


    //bind keyup event to property name inputbox
    $('#qiPropertyNameInput').keyup(function(event){
      SPARQL.openPropertyDialog.changeName($(this), $('#qiPropertyValueNameInput'));
    });
    $('#qiPropertyNameInput').change(function(event){
      SPARQL.openPropertyDialog.changeName($(this), $('#qiPropertyValueNameInput'));
    });

    $('#qiPropertyValueNameInput').keyup(function(event){
      SPARQL.openPropertyDialog.changeName($('#qiPropertyNameInput'), $(this));
    });
    $('#qiPropertyValueNameInput').change(function(event){
      SPARQL.openPropertyDialog.changeName($('#qiPropertyNameInput'), $(this));
    });

    $('#qiPropertyValueMustBeSetChkBox').change(function(event){
      if($(this).attr('checked'))
        selectedNode.attr('valuemustbeset', 'true');
      else
        selectedNode.removeAttr('valuemustbeset');
    });

    $('#qiPropertyValueShowInResultsChkBox').change(function(event){
      if($(this).attr('checked'))
        selectedNode.attr('showinresults', 'true');
      else
        selectedNode.removeAttr('showinresults');
    });
    $('#qiSubjectColumnLabel').change(function(event){
      selectedNode.attr('columnlabel', $(this).val());
    });

    var propertyName = selectedNode.attr('name');
    var valueName = selectedNode.attr('valuename');
    var valueType = selectedNode.attr('valuetype');
    var columnLabel = selectedNode.attr('columnlabel');
    var showInResults = (selectedNode.attr('showinresults') === 'true');
    var valueMustBeSet = (selectedNode.attr('valuemustbeset') === 'true');

    valueName = valueType === 'VAR' ? '?' + valueName : valueName;

    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').show();
    $('#qiPropertyDialog #qiPropertyNameInput').val(propertyName || '');
    $('#qiPropertyDialog #qiPropertyValueNameInput').val(valueName || '?property');
    $('#qiPropertyDialog #qiSubjectColumnLabel').val(columnLabel || '');
    //    $('#qiPropertyTypeLabel').html('Type: ' + SPARQL.property_iri);
    //    var propValueType = isVariable(propertyName) ? '' : 'Type: ' + SPARQL.instance_iri;
    //    $('#qiPropertyValueTypeLabel').html(propValueType);
    if(valueMustBeSet){
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').attr('checked', true);
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').removeAttr('checked');
    }
    if(showInResults){
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').attr('checked', true);
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('checked');
    }
  };

  SPARQL.activateAddPropertyBtn = function(){
    $('#qiAddPropertyBtn').live('click', function(){
      var sparqlTree;
      if(!($.jstree._focused() && $.jstree._focused().get_selected() && $.jstree._focused().get_selected().length)){
        //create new subject and add new category to it
        sparqlTree = $.jstree._reference('qiTreeDiv');
        SPARQL.createSubjectNode(sparqlTree, 'newsubject', 'newsubject', 'VAR');
      }
      else{
        sparqlTree = $.jstree._focused();
        if(sparqlTree.get_selected().attr('gui') !== 'subject'){
          sparqlTree.select_node(sparqlTree.get_selected().parents('li'));
        }
      }
      
      //add property child node to selected subject node
      var propertyNodeData = {
        data:{
          title: '',
          icon : SPARQL.json.propertyIcon
        },
        attr: {
          id: 'property-' + SPARQL.getNextUid(),
          gui: 'property',
          temporary: true,
          iri: SPARQL.property_iri
        }
      };
      sparqlTree = $.jstree._focused();
      sparqlTree.select_node(sparqlTree.get_selected().parents('li'));
      sparqlTree.create ( null , 'first' , propertyNodeData, function(){}, true );
      sparqlTree.deselect_all();
      sparqlTree.select_node('#' + propertyNodeData.attr.id);      
    });
  };

  SPARQL.activateAddAndFilterLink = function(){
    $('#qiAddAndFilterLink').live('click', function(event){
      $(this).closest('tr').before('<tr><td>' + createFilterTable() + '</td></tr>');
      event.preventDefault();
    });
  };

  SPARQL.activateAddOrFilterLink = function(){
    $('#qiAddOrFilterLink').live('click', function(event){
      $(this).closest('tr').before(createFilterPanel());
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

  SPARQL.createFilterPanel = function(){
    return '<tr><td></td>'
    + '<td><select>'
    + '<option value="LT">' + gLanguage.getMessage('QI_LT') + ' (<)</option>'
    + '<option value="LTEQ">' + gLanguage.getMessage('QI_LT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (<=)</option>'
    + '<option value="GT">' + gLanguage.getMessage('QI_GT') + ' (>)</option>'
    + '<option value="GTEQ">' + gLanguage.getMessage('QI_GT') + ' ' + gLanguage.getMessage('QI_OR') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (>=)</option>'
    + '<option value="EQ">' + gLanguage.getMessage('QI_EQUAL') + ' (==)</option>'
    + '<option value="NEQ">' + gLanguage.getMessage('QI_NOT') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (!=)</option>'
    + '<option value="REGEX">' + gLanguage.getMessage('QI_LIKE') + ' (regexp)</option>'
    + '</select>'
    + '</td><td>'
    + '<input type="text">'
    + '</td><td>'
    + '<img id="qiDeleteFilterImg" title="Delete filter" src="' + mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/>'
    + '</td></tr>';
  };

  SPARQL.createFilterTable = function(){
    return '<table>' + SPARQL.createFilterPanel() + SPARQL.createAddOrFilterLink() + '</table>';
  };

  SPARQL.createAddOrFilterLink = function(){
    return '<tr><td colspan="4" style="text-align:center;"><a href="" id="qiAddOrFilterLink">' + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a></td><tr>';
  };

  SPARQL.createAdditionalCategoryPanel = function(){
    return '<tr><td></td><td>'
    + '<input id="qiCategoryNameInput" class="wickEnabled" type="text" autocomplete="OFF" constraints="namespace: 14">'
    + '</td><td><img id="qiDeleteCategoryImg" title="Delete category" src="'
    + mw.config.get('wgServer')
    + mw.config.get('wgScriptPath')
    + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/></td></tr>'
    + '<tr><td></td><td id="qiSubjectTypeLabel"></td><td></td></tr>';
  };

  SPARQL.activateAddOrCategoryLink = function(){
    $('#qiAddOrCategoryLink').live('click', function(event){
      $(this).closest('tr').before(createAdditionalCategoryPanel());
      event.preventDefault();
    });
  };

  SPARQL.activateDeleteCategoryImg = function(){
    $('#qiDeleteCategoryImg').live('click', function(){
      $(this).closest('tr').next().remove();
      $(this).closest('tr').remove();
    });
  };

  SPARQL.activateUpdateBtn = function(){
    $('#qiUpdateButton').live('click', function(){
      SPARQL.updateAllFromTree();
    });
  };

  SPARQL.updateAllFromTree = function(){
    var treeJsonObject = $.jstree._reference('qiTreeDiv').get_json(
      -1,
      ['name', 'id', 'gui', 'type', 'showinresults', 'valuemustbeset', 'iri', 'valuename', 'columnlabel', 'valuetype'],
      null
      );
    SPARQL.jstreeToQueryTree(treeJsonObject);
    SPARQL.showQueryResult();
    SPARQL.updateSortOptions();
  };


  SPARQL.escapeCssSelector = function(selector){
    return selector ? selector.replace(/([\_\:\/])/g, '\\$1') : selector;
  };

  SPARQL.isVariable = function(varName){
    if(!varName)
      return false;
    return varName.indexOf('?') === 0;
  };

 

  SPARQL.renderTree = function(treeJsonConfig, selectedNodeId){

    //    var treeJsonConfig = {
    //      "json_data" : {
    //        "data" : [
    //        {
    //          "data" : "A node",
    //          "state" : "open",
    //          "children" : [ "Child 1", "A Child 2" ],
    //          "icon" : mw.config.get('wgServer') + '/' + mw.config.get('wgScriptPath') + '/extensions/ScriptManager/scripts/jstree/themes/default/d.gif'
    //        },
    //        {
    //          "data" : {
    //            "title" : "Long format demo",
    //            "attr" : {
    //              "href" : "#"
    //            }
    //          },
    //          "attr" : {
    //            "id" : "li.node.id3"
    //          },
    //          "children" : [ "Child 3", "A Child 4" ]
    //        }
    //        ]
    //      },
    //      "plugins" : [ "themes", "json_data" ]
    //    }
    treeJsonConfig.plugins = [ "themes", "json_data", "ui", "crrm" ];
    treeJsonConfig.themes = {
      "theme" : "apple",
      "dots" : true,
      "icons" : true
    //      "url" : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/qi_tree.css'
    };
    treeJsonConfig.ui = {
      "select_limit" : 1,
      "initially_select" : selectedNodeId
    };

    mw.log('============== initially_select : ' + treeJsonConfig.ui.initially_select);
   
    var tree = $("#qiTreeDiv");

    tree.bind("loaded.jstree",
      function(event, data) {
        //do stuff when tree is loaded
        SPARQL.activateUpdateBtn();
        SPARQL.activateDeleteLink();
        SPARQL.activateCancelLink();
        SPARQL.activateAddSubjectBtn();
        SPARQL.activateAddCategoryBtn();
        SPARQL.activateAddPropertyBtn();
      });

    tree.jstree(treeJsonConfig);

    tree.bind("select_node.jstree",
      function(NODE, REF_NODE) {
        switch($(REF_NODE.rslt.obj).attr('gui')){
          case 'subject':
            SPARQL.openSubjectDialog($(REF_NODE.rslt.obj));
            break;

          case 'category':
            SPARQL.openCategoryDialog($(REF_NODE.rslt.obj));
            break;

          case 'property':
            SPARQL.openPropertyDialog($(REF_NODE.rslt.obj));
            break;


          default:
            break;
        }
      });

    $.jstree._themes = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/themes/';
 
    return tree;
  };

  //delete selected tree node
  SPARQL.activateDeleteLink = function(){
    $('#qiDeleteLink').live('click', function(event){
      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
        return;

      var sparqlTree = $.jstree._focused();
      var selectedNode = sparqlTree.get_selected();
      //delete from the query datastructure
      SPARQL.deleteFromQueryObject(selectedNode);
      //delete from the tree
      sparqlTree.delete_node(selectedNode);
      //don't open the link address
      event.preventDefault();
    });
  };



  SPARQL.deleteFromQueryObject = function(selectedNode){
    var type = selectedNode.attr('gui');
    switch(type){
      case 'subject':
        SPARQL.deleteSubject(selectedNode);
        break;

      case 'category':
        SPARQL.deleteCategory(selectedNode);
        break;

      case 'property':
        SPARQL.deleteProperty(selectedNode);
        break;

      default:
        break;
    }
  };

  SPARQL.deleteProperty = function(selectedNode){
    //remove this property from triple
    var property = selectedNode.attr('iri') + SPARQL.iri_delim + selectedNode.attr('name');
    SPARQL.json.TreeQuery.triple = SPARQL.json.TreeQuery.triple || [];
    var triple = SPARQL.json.TreeQuery.triple;
    for(i = 0; i < triple.length; i++){
      if(property === triple[i].predicate.value){
        triple.splice(i, 1);
        break;
      }
    }
  };

  SPARQL.deleteCategory = function(selectedNode){
    //remove this category from category_restriction
    var category = selectedNode.attr('iri') + SPARQL.iri_delim + selectedNode.attr('name');
    SPARQL.json.TreeQuery.category_restriction = SPARQL.json.TreeQuery.category_restriction || [];
    var category_restriction = SPARQL.json.TreeQuery.category_restriction;
    for(i = 0; i < category_restriction.length; i++){
      var categori_iri = category_restriction[i].category_iri;
      var categoryIndex = $.inArray(category, categori_iri);
      if(categoryIndex > -1){
        categori_iri.splice(categoryIndex, 1);
        break;
      }
    }
  };

  SPARQL.deleteSubject = function(selectedNode){
    var subjectId = selectedNode.attr('name');
    var subjectType = selectedNode.attr('type');
    var showInResults = selectedNode.attr('showinresults');

    
    //if this is var remove it from order
    if(subjectType === 'VAR'){
      SPARQL.json.TreeQuery.order = SPARQL.json.TreeQuery.order || [];
      var order = SPARQL.json.TreeQuery.order
      for(var i = 0; i < order.length; i++){
        if(subjectId === order[i].by_var){
          order.splice(i, 1);
          break;
        }
      }
      //if this is var and showInResults is true then remove it from projection_var
      if(showInResults){
        SPARQL.json.TreeQuery.projection_var = SPARQL.json.TreeQuery.projection_var || [];
        var projection_var = SPARQL.json.TreeQuery.projection_var;
        for(i = 0; i < projection_var.length; i++){
          if(subjectId === projection_var[i]){
            projection_var.splice(i, 1);
            break;
          }
        }
      }
    }
    //if it has category children then remove it from category_restriction
    if(selectedNode.children('li[gui="category"]').length){
      SPARQL.json.TreeQuery.category_restriction = SPARQL.json.TreeQuery.category_restriction || [];
      var category_restriction = SPARQL.json.TreeQuery.category_restriction;
      for(i = 0; i < category_restriction.length; i++){
        if(subjectId === category_restriction[i].subject.value){
          category_restriction.splice(i, 1);
          break;
        }
      }
    }
    //if it has property children then remove it from triple
    if(selectedNode.children('li[gui="property"]').length){
      SPARQL.json.TreeQuery.triple = SPARQL.json.TreeQuery.triple || [];
      var triple = SPARQL.json.TreeQuery.triple;
      for(i = 0; i < triple.length; i++){
        if(subjectId === triple[i].subject.value){
          triple.splice(i, 1);
          break;
        }
      }
    }
  };

  //unselect tree nodes and close the dialogs
  SPARQL.activateCancelLink = function(){
    $('#qiCancelLink').live('click', function(event){
      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
        return;
      var sparqlTree = $.jstree._focused();
      sparqlTree.deselect_all();
      sparqlTree.delete_node('[temporary]');
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').hide();
      event.preventDefault();
    });
  };

  SPARQL.activateFormatQueryLink = function() {
    var queryFormatContent = $('#qiQueryFormatContent');
    var queryFormatLink = $('#qiQueryFormatTitle #layouttitle-link');
    $('#qiQueryFormatTitle span').toggle(
      function(){
        queryFormatContent.show();
        queryFormatLink.removeClass("plusminus");
        queryFormatLink.addClass("minusplus");
      },
      function(){
        queryFormatContent.hide();
        queryFormatLink.removeClass("minusplus");
        queryFormatLink.addClass("plusminus");
      }
      );
  };

  SPARQL.getQueryResult = function(queryString){
    queryString = queryString || SPARQL.queryWithParamsString;
     
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
        rsargs: [ 'getQueryResult', queryString, currentPage]
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        $('#previewcontent').html(data);
      },
      error: function(xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        $('#previewcontent').empty();
        SPARQL.showMessageDialog(errorThrown);

      }
    });
  //display the results in preview pane
  };

  SPARQL.showQueryResult = function(){
    //get sparql query string
    SPARQL.treeToSparql(SPARQL.json.TreeQuery, SPARQL.getQueryResult);

  };

  SPARQL.updateSortOptions = function(){
    var projection_var = SPARQL.json.TreeQuery.projection_var || [];
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
        SPARQL.json.TreeQuery.order = orderObject;
      }
      else{
        delete SPARQL.json.TreeQuery.order;
      }
      SPARQL.showQueryResult();
    });
  };

  SPARQL.activateFormatSelectBox = function(){
    $('#qiQueryFormatContent #layout_format').change(function(){
      showQueryResult();
    });
  };

  SPARQL.initTripleStoreGraph = function(){
    SPARQL.tripleStoreGraph = window.smwghTripleStoreGraph + SPARQL.iri_delim;
    SPARQL.category_iri = SPARQL.tripleStoreGraph + 'category';
    SPARQL.property_iri = SPARQL.tripleStoreGraph + 'property';
    SPARQL.instance_iri = SPARQL.tripleStoreGraph + 'instance';
  };


  SPARQL.addSubject = function(treeJsonObject, queryJsonObject, triple){
    var subjectName = triple.subject.value;
    var subjectType = triple.subject.type;
    var subjectShortName = SPARQL.getShortName(subjectName);
    var subjectIRI = SPARQL.getIRI(subjectName);
    var subjectShowInResults = SPARQL.isInProjectionVars(queryJsonObject, subjectShortName);
    var iconFile = SPARQL.json.instanceIcon;

    if(SPARQL.getSubjectIndex(treeJsonObject, subjectShortName) === -1){
          
      var subjectAttributes = {
        id: 'subject-' + SPARQL.getNextUid(),
        name: subjectShortName,
        gui: 'subject',
        columnlabel: subjectShortName,
        iri: subjectIRI,
        title: SPARQL.getShortName(subjectName),
        type: subjectType,
        showinresults: subjectShowInResults
      };

      if(subjectType === 'VAR'){
        iconFile = SPARQL.json.variableIcon;
        subjectAttributes.title = '?' + subjectName;
        delete subjectAttributes.iri;
      }
      treeJsonObject.json_data.data.push(
      {
        data : {
          title : subjectAttributes.title,
          icon : iconFile
        },
        attr : subjectAttributes,
        children : [],
        state : 'open'
      });
    }
  };
     

  SPARQL.isInProjectionVars = function(queryJsonObject, subjectName){
    if(!(subjectName && queryJsonObject.projection_var && queryJsonObject.projection_var.length))
      return false;

    var index = $.inArray(subjectName, queryJsonObject.projection_var);
    return (index > -1);
  };

  SPARQL.getValidName = function(varName){
    return varName.substring(varName.lastIndexOf(':') + 1, varName.length);
  };

  SPARQL.isVariable = function(tripleObject){
    return (tripleObject.type === 'VAR');
  };

  SPARQL.getSubject = function(treeJsonObject, subjectName){
    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectName);
    return index > -1 ? treeJsonObject.json_data.data[index] : null;
  };

  //find subjectName in treeJsonObject
  SPARQL.getSubjectIndex = function(treeJsonObject, subjectName){
    var result = -1;
    if(treeJsonObject.json_data.data){
      for(var i = 0; i < treeJsonObject.json_data.data.length; i++){
        var nodeData = treeJsonObject.json_data.data[i];
        if(nodeData.attr && nodeData.attr.name){
          if(nodeData.attr.name === subjectName){
            return i;
          }
        }
        else{
          if(nodeData.data === subjectName)
            return i;
        }
      }
    }
    return result;
  };


  SPARQL.addCategoryToSubject = function(treeJsonObject, subjectName, categoryName){
    var categoryShortName = getShortName(categoryName);
    var categoryIRI = getIRI(categoryName);
    var subjectShortName = getShortName(subjectName);

    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectShortName);
    if(index > -1){
      treeJsonObject.json_data.data[index].children.push(
      {
        data: {
          title: categoryShortName,
          icon: SPARQL.json.categoryIcon
        },
        attr: {
          id: 'category-' + SPARQL.getNextUid(),
          name: categoryShortName,
          gui: 'category',
          iri: categoryIRI,
          title: categoryName
        }
      });
    }
  };

  SPARQL.addPropertyToSubject = function(treeJsonObject, queryJsonObject, triple){
    var subjectName = triple.subject.value;
    var propertyName = triple.predicate.value;
    var propertyType = triple.predicate.type;
    var propertyValueName = triple.object.value;
    var propertyValueType = triple.object.type;
    var valueMustBeSet = !triple.optional;
    var showInResutlts = (triple.object.type === 'VAR' && SPARQL.isInProjectionVars(queryJsonObject, propertyValueName));

    var propertyIRI = SPARQL.getIRI(propertyName);
    var propertyShortName = SPARQL.getShortName(propertyName);
    var propertyValueShortName = SPARQL.etShortName(propertyValueName);
    var subjectShortName = SPARQL.getShortName(subjectName);

    var index = SPARQL.getSubjectIndex(treeJsonObject, subjectShortName);
    var nodeTitle = '';
    if(index > -1){
      if(propertyType === 'VAR')
        nodeTitle += '?';
      nodeTitle += propertyShortName + ' ';
      if(propertyValueType === 'VAR')
        nodeTitle += '?';
      nodeTitle += propertyValueShortName;

      treeJsonObject.json_data.data[index].children.push(
      {
        data: {
          title: nodeTitle,
          icon: SPARQL.json.propertyIcon
        },
        attr: {
          id: 'property-' + SPARQL.getNextUid(),
          name: propertyShortName,
          valuename: propertyValueShortName,
          columnlabel: propertyValueShortName,
          valuemustbeset: valueMustBeSet,
          showinresults: showInResutlts,
          gui: 'property',
          iri: propertyIRI,
          valuetype: propertyValueType,
          type: propertyType
        }
      });
    }
  };


  /**
       * Build a jstree json object out of sparql query json object
       */
  SPARQL.toTree = function(queryJsonObject){
    //use internal data structure if none specified
    if(!queryJsonObject){
      queryJsonObject = SPARQL.json.TreeQuery;
    }

    //init tree object
    var treeJsonObject = {
      json_data: {
        data: []
      }
    };
    queryJsonObject.triple = queryJsonObject.triple || [];
    queryJsonObject.category_restriction = queryJsonObject.category_restriction || [];
    queryJsonObject.projection_var = queryJsonObject.projection_var || [];
    var triples = queryJsonObject.triple;
    var category_restrictions = queryJsonObject.category_restriction;

    //iterate over triples
    for(var tripleIndex = 0; tripleIndex < triples.length; tripleIndex++){
      var triple = triples[tripleIndex];
      if(triple.subject.type === 'VAR'){
        SPARQL.addSubject(treeJsonObject, queryJsonObject, triple);
        SPARQL.addPropertyToSubject(treeJsonObject, queryJsonObject, triple);
      }
      else if(triple.subject.type === 'IRI'){
        SPARQL.addSubject(treeJsonObject, queryJsonObject, triple);
        SPARQL.addPropertyToSubject(treeJsonObject, queryJsonObject, triple);
      }
    }
    //iterate over category_restriction
    for (var i = 0; i < category_restrictions.length; i++){
      var category_restriction = category_restrictions[i];
      SPARQL.addSubject(treeJsonObject, queryJsonObject, category_restriction);
      //@TODO add support for multiple categories (OR) in one category_restriction object
      SPARQL.addCategoryToSubject(treeJsonObject, category_restriction.subject.value, category_restriction.category_iri[0]);
    }

    SPARQL.json.treeJsonObject = treeJsonObject;
    return treeJsonObject;
  };

  $(document).ready(function(){
    SPARQL.activateSwitchToSparqBtn();
    SPARQL.activateAddAndFilterLink();
    SPARQL.activateDeleteFilterImg();
    SPARQL.activateAddOrFilterLink();
    SPARQL.activateAddOrCategoryLink();
    SPARQL.activateDeleteCategoryImg();
    SPARQL.activateQueryViewTabToggle();
    SPARQL.renderTree(SPARQL.toTree(SPARQL.json.TreeQuery));
    SPARQL.activateFormatQueryLink();
    SPARQL.activateSortSelectBox();
    SPARQL.activateFormatSelectBox();
    SPARQL.activateUpdateSourceBtn();
    SPARQL.activateDiscardChangesLink();

    SPARQL.initTripleStoreGraph();

  });


})(jQuery);



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



