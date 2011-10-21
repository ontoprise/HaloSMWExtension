(function($){
  function getFullName(name, type){
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
  }

  function getShortName(iri){
    var iriTokens = String.split(iri, SPARQL.iri_delim);
    if(iriTokens && iriTokens.length){
      return iriTokens[iriTokens.length - 1];
    }

    return iri;
  }

  function getIRI(string){
    var shortName = getShortName(string);
    if(shortName !== string)
      return string.substring(0, string.indexOf(SPARQL.iri_delim + shortName));
    return null;
  }

  function activateQueryViewTabToggle(){
    //switch to tree view
    $('#sparqlQI #qiDefTab1').live('click', function(){
      $('#sparqlQI #treeview').css('display', 'inline');
      $('#sparqlQI #qiDefTab1').addClass('qiDefTabActive');
      $('#sparqlQI #qiDefTab1').removeClass('qiDefTabInactive');
      $('#sparqlQI #qisource').css('display', 'none');
      $('#sparqlQI #qiDefTab3').addClass('qiDefTabInactive');
      $('#sparqlQI #qiDefTab3').removeClass('qiDefTabActive');

//      sparqlToTree(SPARQL.parserFuncString.replace(/^\{\{#sparql:\s*([\s\S]+)\s*\}\}$/, '$1'));
      sparqlToTree(SPARQL.queryString);
    });


    $('#sparqlQI #qiDefTab3').live('click', function(){
      $('#sparqlQI #qisource').css('display', 'inline');
      $('#sparqlQI #qiDefTab3').addClass('qiDefTabActive');
      $('#sparqlQI #qiDefTab3').removeClass('qiDefTabInactive');
      $('#sparqlQI #treeview').css('display', 'none');
      $('#sparqlQI #qiDefTab1').addClass('qiDefTabInactive');
      $('#sparqlQI #qiDefTab1').removeClass('qiDefTabActive');

      treeToSparql(SPARQL.json.TreeQuery);

    });
  }


  function sparqlToTree(sparqlQuery){
    if(!(sparqlQuery && sparqlQuery.length))
      return;
  
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
        renderTree(SPARQL.json.toTree());
      },
      error: function (xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        showMessageDialog(errorJson.error);
      }
    });
  }

  function showMessageDialog(message, anchorElement){
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
  }

  function buildParserFuncString(queryString){
    queryString = queryString || SPARQL.queryString;
    var format = $('#qiQueryFormatContent #layout_format').children().filter(':selected').attr('value')
    format = format ? '|format=' + format : '';
    SPARQL.queryWithParamsString = queryString + '\n' + format;
    SPARQL.parserFuncString = '{{#sparql: \n' + SPARQL.queryWithParamsString + '\n' + '}}';
    return SPARQL.parserFuncString;
  }



  function treeToSparql(treeJsonConfig, callbackFn){
    if(!treeJsonConfig || $.isEmptyObject(treeJsonConfig))
      return;

    //send ajax post request to localhost:8080/sparql/treeToSPARQL
    $.ajax({
      type: 'POST',
      url: 'http://localhost:8080/sparql/treeToSPARQL',
      data: {
        tree: stringifyJson(treeJsonConfig)
      },
      success: function(data, textStatus, jqXHR) {
        mw.log('data: ' + data);
        mw.log('textStatus: ' + textStatus);
        mw.log('jqXHR.responseText: ' + jqXHR.responseText);
        if(data && data.query){
          SPARQL.queryString = data.query;
          $('#sparqlQueryText').val(buildParserFuncString(data.query));
          if(callbackFn && typeof callbackFn === 'function'){
            callbackFn();
          }
        }
      },
      error: function (xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        var errorJson = $.parseJSON(xhr.responseText);
        showMessageDialog(errorJson.error);
      }
    });
  }

  function stringifyJson(jsonObject){
    var arrayToJsonFunc = Array.prototype.toJSON;
    if(arrayToJsonFunc && typeof arrayToJsonFunc === 'function'){
      delete Array.prototype.toJSON;
    }
    var result = JSON.stringify(jsonObject);
    Array.prototype.toJSON = arrayToJsonFunc;
    return result;
  }

  function activateSwitchToSparqBtn(){
    var switchToSparqlBtn = $('#switchToSparqlBtn');
    switchToSparqlBtn.live('click', function(){
      $('#askQI').hide();
      $('#sparqlQI').show();
      switchToSparqlBtn.remove();

    //@TODO if ASK query is not empty send it to tsc for convertion to SPARQL
    });
  }

  function activateAddCategoryBtn(sparqlTree){
    $('#qiAddCategoryBtn').live('click', function(){
      if(!($.jstree._focused() && $.jstree._focused().get_selected() && $.jstree._focused().get_selected().length)){
        return;
      }
      
      //add category child node to selected subject node
      var categoryNodeData = {
        data:{
          title: '',
          icon : SPARQL.json.categoryIcon
        },
        attr: {
          id: 'newcategorynode',
          gui: 'category',
          temporary: true
        }
      };
      sparqlTree = $.jstree._focused();
      sparqlTree.select_node(sparqlTree.get_selected().parents('li'));
      sparqlTree.create (null , 'first' , categoryNodeData, function(){}, true );
      sparqlTree.deselect_all();
      sparqlTree.select_node('#' + categoryNodeData.attr.id);
      var selectedNode = sparqlTree.get_selected();
      var html = selectedNode.children('a').html();

      function changeName(element){
        selectedNode.children('a').html(html + element.val());
        if(element.val())
          selectedNode.attr('id', element.val());
      }
      //bind keyup event to category name inputbox
      $('#qiCategoryNameInput').keyup(function(event){
        changeName($(this));
      });
      $('#qiCategoryNameInput').change(function(event){
        changeName($(this));
      });

      openCategoryDialog();
    });

    
  }

  function openCategoryDialog(event, categoryName){
    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();
    $('#qiCategoryDialog #qiCategoryNameInput').val(categoryName || '');
    $('#qiCategoryTypeLabel').html('Type: ' + SPARQL.category_iri);
  }
  
  function activateAddSubjectBtn(sparqlTree){
    $('#qiAddSubjectBtn').live('click', function(){
      sparqlTree = $.jstree._reference('#qiTreeDiv');
      //create new subject node and select it
      var subjectNodeData = {
        data:{
          title: '',
          icon : SPARQL.json.variableIcon
        },
        attr: {
          id: 'newsubjectnode',
          gui: 'subject',
          temporary: true
        },
        children: []
      };

      sparqlTree.deselect_all();
      sparqlTree.create ( null , 'first' , subjectNodeData, function(){}, true );
      sparqlTree.select_node('#' + subjectNodeData.attr.id);
      var selectedNode = sparqlTree.get_selected();
      var html = selectedNode.children('a').html();

      function changeName(element){
        selectedNode.children('a').html(html + element.val());
        selectedNode.attr('id', element.val());
        selectedNode.attr('type', (isVariable(element.val()) ? 'VAR' : 'IRI'));
        if(!isVariable(element.val())){
          selectedNode.attr('iri', SPARQL.instance_iri);
          $('#qiSubjectTypeLabel').html('Type: ' + SPARQL.instance_iri);
        }
        else{
          $('#qiSubjectTypeLabel').html('Type: variable');
        }
      }
      $('#qiSubjectNameInput').keyup(function(event){
        changeName($(this));
      });
      $('#qiSubjectNameInput').change(function(event){
        changeName($(this));
      });

      $('#qiSubjectColumnLabel').change(function(event){
        selectedNode.attr('columnlabel', $(this).val());
      });

      $('#qiSubjectShowInResultsChkBox').change(function(event){
        if($(this).attr('checked'))
          selectedNode.attr('showinresults', 'true');
        else
          selectedNode.removeAttr('showinresults');
      });

      openSubjectDialog();
    });
  }

  function openSubjectDialog(event, subjectName, columnLabel, showInResults, isVar){
    if(isVar){
      if(!isVariable(subjectName)){
        subjectName = '?' + subjectName;
      }
      $('#qiSubjectTypeLabel').html('Type: variable');
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
  }

  function openPropertyDialog(event, propertyName, valueName, columnLabel, showInResults, valueMustBeSet){
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').show();
    $('#qiPropertyDialog #qiPropertyNameInput').val(propertyName || '');
    $('#qiPropertyDialog #qiPropertyValueNameInput').val(valueName ?  '?' + valueName : '');
    $('#qiPropertyDialog #qiSubjectColumnLabel').val(columnLabel || '');
    $('#qiPropertyTypeLabel').html('Type: ' + SPARQL.property_iri);
    var propValueType = isVariable(propertyName) ? '' : 'Type: ' + SPARQL.instance_iri;
    $('#qiPropertyValueTypeLabel').html(propValueType);
    if(valueMustBeSet === 'true'){
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueMustBeSetChkBox').removeAttr('checked');
    }
    if(showInResults === 'true'){
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiPropertyDialog #qiPropertyValueShowInResultsChkBox').removeAttr('checked');
    }
  }

  function activateAddPropertyBtn(sparqlTree){
    $('#qiAddPropertyBtn').live('click', function(){
      if(!($.jstree._focused() && $.jstree._focused().get_selected() && $.jstree._focused().get_selected().length)){
        return;
      }
      
      //add property child node to selected subject node
      var propertyNodeData = {
        data:{
          title: '',
          icon : SPARQL.json.propertyIcon
        },
        attr: {
          id: 'newpropertynode',
          gui: 'property',
          temporary: true
        }
      };
      sparqlTree = $.jstree._focused();
      sparqlTree.select_node(sparqlTree.get_selected().parents('li'));
      sparqlTree.create ( null , 'first' , propertyNodeData, function(){}, true );
      sparqlTree.deselect_all();
      sparqlTree.select_node('#' + propertyNodeData.attr.id);
      var selectedNode = sparqlTree.get_selected();
      var html = selectedNode.children('a').html();

      function changeName(element){
        selectedNode.children('a').html(html + element.val());
        if(element.val())
          selectedNode.attr('id', element.val());
      }

      function changeValue(element){
        selectedNode.attr('valuename', element.val());
        if(isVariable(element.val())){
          $('#qiPropertyValueTypeLabel').html('Type: variable');
        }
        else{
          $('#qiPropertyValueTypeLabel').html('Type: ' + SPARQL.instance_iri);
        }
      }

      //bind keyup event to property name inputbox
      $('#qiPropertyNameInput').keyup(function(event){
        changeName($(this));
      });
      $('#qiPropertyNameInput').change(function(event){
        changeName($(this));
      });

      $('#qiPropertyValueNameInput').keyup(function(event){
        changeValue($(this));
      });
      $('#qiPropertyValueNameInput').change(function(event){
        changeValue($(this));
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
      
      openPropertyDialog();
    });
  }

  function activateAddAndFilterLink(){
    $('#qiAddAndFilterLink').live('click', function(event){
      $(this).closest('tr').before('<tr><td>' + createFilterTable() + '</td></tr>');
      event.preventDefault();
    });
  }

  function activateAddOrFilterLink(){
    $('#qiAddOrFilterLink').live('click', function(event){
      $(this).closest('tr').before(createFilterPanel());
      event.preventDefault();
    });
  }

  function activateDeleteFilterImg(){
    $('#qiDeleteFilterImg').live('click', function(){
      if($(this).closest('table').find('#qiDeleteFilterImg').length == 1){
        $(this).closest('table').remove();
      }
      else{
        $(this).closest('tr').remove();
      }
    });
  }

  function createFilterPanel(){
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
  }

  function createFilterTable(){
    return '<table>' + createFilterPanel() + createAddOrFilterLink() + '</table>';
  }

  function createAddOrFilterLink(){
    return '<tr><td colspan="4" style="text-align:center;"><a href="" id="qiAddOrFilterLink">' + gLanguage.getMessage('QI_DC_ADD_OTHER_RESTRICT') + '</a></td><tr>';
  }

  function createAdditionalCategoryPanel(){
    return '<tr><td></td><td>'
    + '<input id="qiCategoryNameInput" class="wickEnabled" type="text" autocomplete="OFF" constraints="namespace: 14">'
    + '</td><td><img id="qiDeleteCategoryImg" title="Delete category" src="'
    + mw.config.get('wgServer')
    + mw.config.get('wgScriptPath')
    + '/extensions/SMWHalo/skins/QueryInterface/images/delete_icon.png"/></td></tr>'
    + '<tr><td></td><td id="qiSubjectTypeLabel"></td><td></td></tr>';
  }

  function activateAddOrCategoryLink(){
    $('#qiAddOrCategoryLink').live('click', function(event){
      $(this).closest('tr').before(createAdditionalCategoryPanel());
      event.preventDefault();
    });
  }

  function activateDeleteCategoryImg(){
    $('#qiDeleteCategoryImg').live('click', function(){
      $(this).closest('tr').next().remove();
      $(this).closest('tr').remove();
    });
  }

  //click.jstree delete_node.jstree events
  function activateUpdateBtn(sparqlTree){
    $('#qiUpdateButton').live('click', function(){
      if(!($.jstree._focused() && $.jstree._focused().get_selected() && $.jstree._focused().get_selected().length)){
        return;
      }

      //get selected tree node
      var selectedNode = $.jstree._focused().get_selected();
      var parentNode = selectedNode.parents('li').eq(0);
      var newNodeId;
      //update all its properties with the ones in the dialog
      switch($(selectedNode).attr('gui')){
        case 'subject':
          updateSubject($(selectedNode),
            $('#qiSubjectNameInput').val(),
            $('#qiSubjectColumnLabel').val(),
            $('#qiSubjectShowInResultsChkBox').attr('checked') === true);
          newNodeId = $('#qiSubjectNameInput').val().replace(/\?/, '');
          renderTree(SPARQL.json.toTree(), newNodeId);
          break;

        case 'category':
          updateCategory($(selectedNode), $(parentNode), $('#qiCategoryNameInput').val());
          newNodeId = $('#qiCategoryNameInput').val();
          //rebuild the tree
          renderTree(SPARQL.json.toTree(), newNodeId);
          break;

        case 'property':
          updateProperty($(selectedNode),
            $(parentNode),
            $('#qiPropertyNameInput').val(),
            $('#qiPropertyValueNameInput').val(),
            $('#qiPropertyColumnLabel').val(),
            $('#qiPropertyValueMustBeSetChkBox').attr('checked') === true,
            $('#qiPropertyValueShowInResultsChkBox').attr('checked') === true);
          newNodeId = $('#qiPropertyNameInput').val();
          //rebuild the tree
          renderTree(SPARQL.json.toTree(), newNodeId);
          break;

        default:
          break;
      }

      
    });
  }

  function addNewProperty(subjectNode, propertyName, propertyValueName, columnLabel, valueMustBeSet, showInResults){
    //if subject is new then add a subject
    if(subjectNode.attr('temporary')){
      addNewSubject(subjectNode.attr('id'), subjectNode.attr('columnlabel'), subjectNode.attr('showinresults'), subjectNode.attr('valuemustbeset'));
    }
    //now add new property
    var newTriple = {
      subject: {
        type: subjectNode.attr('type'),
        value: subjectNode.attr('type') === 'VAR' ? subjectNode.attr('id') : getFullName(subjectNode.attr('id'), 'instance')
      },
      predicate: {
        type: 'IRI',
        value: getFullName(propertyName, 'property')
      },
      object: {
        type: isVariable(propertyValueName) ? 'VAR' : 'IRI',
        value: isVariable(propertyValueName) ? propertyValueName.replace(/\?/, '') : getFullName(propertyValueName, 'instance')
      },
      optional : !valueMustBeSet
    }
    SPARQL.json.TreeQuery.triple.push(newTriple);
    if(showInResults)
      SPARQL.json.TreeQuery.projection_var.push(propertyValueName.replace(/\?/, ''));
  }


  function updateProperty(selectedNode, parentNode, propertyName, propertyValueName, columnLabel, valueMustBeSet, showInResults){
    if(selectedNode.attr('temporary')){
      addNewProperty(parentNode, propertyName, propertyValueName, columnLabel, valueMustBeSet, showInResults);
    }
    else{
      var originalPropertyName = selectedNode.attr('id');
      var originalPropertyValue = selectedNode.attr('valuename');
      var originalShowInResults = (selectedNode.attr('showinresults') === 'true');
      var subjectName = parentNode.attr('id');

      //check if propertyValueName is of type var (starting with ?) and strip it
      var isVar = isVariable(propertyValueName);
      propertyValueName = isVar ? propertyValueName.replace(/\?/, '') : getFullName(propertyValueName, 'instance');

      SPARQL.json.TreeQuery.triple = SPARQL.json.TreeQuery.triple || [];
      SPARQL.json.TreeQuery.projection_var = SPARQL.json.TreeQuery.projection_var || [];
      //update triples
      for(var i = 0; i < SPARQL.json.TreeQuery.triple.length; i++){
        var triple = SPARQL.json.TreeQuery.triple[i];
        if(getShortName(triple.subject.value) === subjectName){
          if(getShortName(triple.predicate.value) === originalPropertyName){
            triple.predicate.value = getFullName(propertyName, 'property');
          }
          if(getShortName(triple.object.value) === originalPropertyValue){
            triple.object.value = propertyValueName;
          }
          triple.optional = valueMustBeSet;
        }
      }
      //update projection_var, add or edit
      if(showInResults){
        if(originalShowInResults !== showInResults){
          if(isVar)
            SPARQL.json.TreeQuery.projection_var.push(propertyValueName);
        }
        else{
          if(isVar){
            var projection_var = SPARQL.json.TreeQuery.projection_var;
            for(i = 0; i < projection_var.length; i++){
              if(getShortName(projection_var[i]) === originalPropertyValue){
                projection_var[i] = propertyValueName;
                break;
              }
            }
          }
        }
      }
      //remove from projection_var
      else{
        if(originalShowInResults !== showInResults){
          projection_var = SPARQL.json.TreeQuery.projection_var;
          for(i = 0; i < projection_var.length; i++){
            if(getShortName(projection_var[i]) === originalPropertyValue){
              projection_var.splice(i, 1);
              break;
            }
          }
        }
      }
    }
  }

  function addNewCategory(subjectNode, categoryName){
    if(subjectNode.attr('type') === 'IRI'){
      showMessageDialog('Category can only be added to variable');
      return;
    }
    SPARQL.json.TreeQuery.category_restriction = SPARQL.json.TreeQuery.category_restriction || [];
    
    //if subject is new add subject and category to the datastructure
    if(subjectNode.attr('temporary')){
      addNewSubject(subjectNode.attr('id'), subjectNode.attr('columnlabel'), subjectNode.attr('showinresults'), subjectNode.attr('valuemustbeset'));
    }
    //else add the category to the existing subject
    if(SPARQL.json.TreeQuery.category_restriction.length){
      for(i = 0; i < SPARQL.json.TreeQuery.category_restriction.length; i++){
        if(getShortName(SPARQL.json.TreeQuery.category_restriction[i].subject.value) === subjectNode.attr('id')){
          SPARQL.json.TreeQuery.category_restriction[i].category_iri.push(getFullName(categoryName, 'category'));
        }
      }
    }
    else{
      var newcategory_restriction = {
        subject: {
          type: 'VAR',
          value: subjectNode.attr('id').replace(/\?/, '')
        },
        category_iri : [categoryName]
      }
      SPARQL.json.TreeQuery.category_restriction.push(newcategory_restriction);
    }
  }
  

  function updateCategory(selectedTreeNode, parentNode, categoryName){
    var originalCategoryName = selectedTreeNode.attr('id');
    var subjectId = parentNode.attr('id');
    if(selectedTreeNode.attr('temporary')){
      addNewCategory(parentNode, getFullName(categoryName, 'category'));
    }
    else{
      //if there is a change
      if(originalCategoryName !== categoryName){
        //update category_restriction
        for(i = 0; i < SPARQL.json.TreeQuery.category_restriction.length; i++){
          if(getShortName(SPARQL.json.TreeQuery.category_restriction[i].subject.value) === subjectId){
            for(var j = 0; j < SPARQL.json.TreeQuery.category_restriction[i].category_iri.length; j++){
              if(getShortName(SPARQL.json.TreeQuery.category_restriction[i].category_iri[j]) === originalCategoryName){
                SPARQL.json.TreeQuery.category_restriction[i].category_iri[j] = getFullName(categoryName, 'category');
              }
            }
          }
        }
      }
    }
  }


  function escapeCssSelector(selector){
    return selector ? selector.replace(/([\_\:\/])/g, '\\$1') : selector;
  }

  function isVariable(varName){
    if(!varName)
      return false;
    return varName.indexOf('?') === 0;
  }

  function addNewSubject(subjectName, columnLabel, showInResults, valueMustBeSet){
    var sparqlTree = SPARQL.json.TreeQuery;
    sparqlTree.projection_var = sparqlTree.projection_var || [];
    if(isVariable(subjectName) && showInResults){
      sparqlTree.projection_var.push(subjectName.replace(/\?/, ''));
    }
  }

  function updateSubject(selectedTreeNode, subjectName, columnLabel, showInResults){
    var originalSubjectName = selectedTreeNode.attr('id');
    var originalShowInResults = (selectedTreeNode.attr('showinresults') === 'true');
    
    var isVar = isVariable(subjectName);
    subjectName = subjectName.replace(/\?/, '');
    //update triples
    if(originalSubjectName !== subjectName){
      SPARQL.json.TreeQuery.triple = SPARQL.json.TreeQuery.triple || [];
      //rename each occurence of this var in triples as subject or object
      for(var i = 0; i < SPARQL.json.TreeQuery.triple.length; i++){
        var triple = SPARQL.json.TreeQuery.triple[i];
        if(getShortName(triple.subject.value) === originalSubjectName){
          triple.subject.type = isVar ? 'VAR' : 'IRI';
          subjectName = isVar ? subjectName : getFullName(subjectName, 'instance');
          triple.subject.value = subjectName;
        }
        else if(getShortName(triple.object.value) === originalSubjectName){
          triple.object.type = isVar ? 'VAR' : 'IRI';
          subjectName = isVar ? subjectName : getFullName(subjectName, 'instance');
          triple.object.value = subjectName;
        }
      }
    }
    //update projection_var array
    if(isVar){
      if(showInResults){
        //showInResults was changed to true
        if(originalShowInResults !== showInResults){
          SPARQL.json.TreeQuery.projection_var.push(subjectName);
        }
        //showInResults was true
        else if(originalSubjectName !== subjectName){
          for(i = 0; i < SPARQL.json.TreeQuery.projection_var.length; i++){
            if(SPARQL.json.TreeQuery.projection_var[i] === originalSubjectName){
              SPARQL.json.TreeQuery.projection_var[i] = subjectName;
            }
          }
        }
      }
      //showInResults is false
      else{
        //showInResults changed to false
        if(originalShowInResults !== showInResults){
          for(i = 0; i < SPARQL.json.TreeQuery.projection_var.length; i++){
            if(SPARQL.json.TreeQuery.projection_var[i] === originalSubjectName){
              SPARQL.json.TreeQuery.projection_var.splice(i, 1);
            }
          }
        }
      }
    }
  //    }
 

  //rename each occurence of this var in filters
  }

  function buildSparqlTree(){
    //parse query json string to query jason object
    var queryJson = $.parseJSON(TreeQuery);

    //build jstree json object out of it
    var treeJson = buildTreeJson(queryJson);

    //pass this new object to renderTree function
    renderTree(treeJson);
  }

  function renderTree(treeJsonConfig, selectedNodeId){

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
      "theme" : "qi",
      "dots" : true,
      "icons" : true,
      "url" : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/qi_tree.css'
    };
    treeJsonConfig.ui = {
      "select_limit" : 1,
      "initially_select" : selectedNodeId /*? [escapeCssSelector(selectedNodeId)] : []*/
    };

    mw.log('============== initially_select : ' + treeJsonConfig.ui.initially_select);
   
    var tree = $("#qiTreeDiv");

    tree.bind("loaded.jstree",
      function (event, data) {
        //do stuff when tree is loaded
        SPARQL.json.jstree = data.inst;
        activateUpdateBtn(data.inst);
        activateDeleteLink(data.inst);
        activateCancelLink(data.inst);
        activateAddSubjectBtn(data.inst);
        activateAddCategoryBtn(data.inst);
        activateAddPropertyBtn(data.inst);
      });

    tree.jstree(treeJsonConfig);

    tree.bind("select_node.jstree",
      function (NODE, REF_NODE) {
        mw.log('======= selected: ' + REF_NODE.rslt.obj[0]);
        switch($(REF_NODE.rslt.obj).attr('gui')){
          case 'subject':
            openSubjectDialog(null,
              $(REF_NODE.rslt.obj).attr('id'),
              $(REF_NODE.rslt.obj).attr('columnlabel'),
              $(REF_NODE.rslt.obj).attr('showinresults'),
              $(REF_NODE.rslt.obj).attr('type') === 'VAR');
            break;

          case 'category':
            openCategoryDialog(null, $(REF_NODE.rslt.obj).attr('id'));
            break;

          case 'property':
            openPropertyDialog(null,
              $(REF_NODE.rslt.obj).attr('id'),
              $(REF_NODE.rslt.obj).attr('valuename'),
              $(REF_NODE.rslt.obj).attr('columnlabel'),
              $(REF_NODE.rslt.obj).attr('showinresults'),
              $(REF_NODE.rslt.obj).attr('valuemustbeset'));
            break;


          default:
            break;
        }
      });
 
    return tree;
  }

  ///delete selected tree node
  function activateDeleteLink(sparqlTree){
    $('#qiDeleteLink').live('click', function(event){
      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
        return;
      sparqlTree = $.jstree._focused();
      sparqlTree.delete_node(sparqlTree.get_selected());

      //delete also from the query datastructure
      deleteFromQueryObject(sparqlTree.get_selected());
      event.preventDefault();
    });
  }


  function deleteFromQueryObject(selectedNode){
    var type = selectedNode.attr('gui');
    switch(type){
      case 'subject':
        deleteSubject(selectedNode);
        break;

      case 'category':
        deleteCategory(selectedNode);
        break;

      case 'property':
        deleteProperty(selectedNode);
        break;

      default:
        break;
    }
  }

  function deleteSubject(selectedNode){
    var subjectId = selectedNode.attr('id');

  }

  //unselect tree nodes and close the dialogs
  function activateCancelLink(sparqlTree){
    $('#qiCancelLink').live('click', function(event){
      if(!($.jstree._focused() && $.jstree._focused().get_selected()))
        return;
      sparqlTree = $.jstree._focused();
      sparqlTree.deselect_all();
      sparqlTree.delete_node('[temporary]');
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').hide();
      event.preventDefault();
    });
  }

  function activateFormatQueryLink() {
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

  function getQueryResult(queryString){
    queryString = queryString || SPARQL.queryWithParamsString;
    
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
      error: function (xhr, textStatus, errorThrown) {
        mw.log(textStatus);
        mw.log('response: ' + xhr.responseText)
        mw.log('errorThrown: ' + errorThrown);
        showMessageDialog(errorThrown);

      }
    });
  //display the results in preview pane
  }

  function showQueryResult(){
    //get sparql query string
    treeToSparql(SPARQL.json.TreeQuery, getQueryResult);
  }

  function updateSortOptions(){
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
  }

  function activateSortSelectBox(){
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
      }
      SPARQL.json.TreeQuery.order = orderObject;
      $(SPARQL.json.TreeQuery).trigger('change.sparql');
    });
  }

  function activateFormatSelectBox(){
    $('#qiQueryFormatContent #layout_format').change(function(){
       $('#sparqlQueryText').val(buildParserFuncString());
      getQueryResult();
     
    });
  }

  function initTripleStoreGraph(){
    SPARQL.tripleStoreGraph = window.smwghTripleStoreGraph + SPARQL.iri_delim;
    SPARQL.category_iri = SPARQL.tripleStoreGraph + 'category';
    SPARQL.property_iri = SPARQL.tripleStoreGraph + 'property';
    SPARQL.instance_iri = SPARQL.tripleStoreGraph + 'instance';
  }

  


  $(document).ready(function(){
    activateSwitchToSparqBtn();
    activateAddAndFilterLink();
    activateDeleteFilterImg();
    activateAddOrFilterLink();
    activateAddOrCategoryLink();
    activateDeleteCategoryImg();
    activateQueryViewTabToggle();
    renderTree(SPARQL.json.toTree(SPARQL.json.TreeQuery));
    activateFormatQueryLink();
    activateSortSelectBox();
    activateFormatSelectBox();

    //bind a change event to the query object
    $(SPARQL.json.TreeQuery).bind('change.sparql', function(){
      showQueryResult();
      updateSortOptions();
    });

    initTripleStoreGraph();

  });

  SPARQL = {
    iri_delim: '/',        
    parserFuncString: '',
    queryString: null,

    json : {

      variableIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif',
      instanceIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/instance_icon.gif',
      categoryIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif',
      propertyIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif',


      addSubject: function(treeJsonObject, subjectName, showInResults, isVariable){
        var subjectShortName = getShortName(subjectName);
        var subjectIRI = getIRI(subjectName);
        var iconFile = this.instanceIcon;
        var subjectAttributes = {
          id: subjectShortName,
          gui: 'subject',
          columnlabel: getShortName(subjectName),
          iri: subjectIRI,
          title: subjectName,
          type: isVariable ? 'VAR' : 'IRI'
        };
        if(this.getSubjectIndex(treeJsonObject, subjectShortName) === -1){
          if(showInResults){
            subjectAttributes.showinresults = 'true';
          }
          if(isVariable){
            iconFile = this.variableIcon;
            subjectAttributes.title = 'variable: ?' + subjectName,
            delete subjectAttributes.iri;
          }
          treeJsonObject.json_data.data.push(
          {
            data : {
              title : subjectShortName,
              icon : iconFile
            },
            attr : subjectAttributes,
            children : [],
            state : 'open'
          });
        }
      },
     

      isSubjectInProjectionVars: function(queryJsonObject, subjectName){
        var index = $.inArray(subjectName, queryJsonObject.projection_var);
        return (index > -1);
      },

      getValidName: function(varName){
        return varName.substring(varName.lastIndexOf(':') + 1, varName.length);
      },

      isVariable: function(tripleObject){
        return (tripleObject.type === 'VAR');
      },

      getSubject: function(treeJsonObject, subjectName){
        var index = this.getSubjectIndex(treeJsonObject, subjectName);
        return index > -1 ? treeJsonObject.json_data.data[index] : null;
      },

      getSubjectIndex: function(treeJsonObject, subjectName){
        var result = -1;
        if(treeJsonObject.json_data.data){
          for(var i = 0; i < treeJsonObject.json_data.data.length; i++){
            var nodeData = treeJsonObject.json_data.data[i];
            if(nodeData.data && nodeData.data.title){
              if(nodeData.data.title === subjectName){
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
      },
   

      addCategoryToSubject: function(treeJsonObject, subjectName, categoryName){
        var categoryShortName = getShortName(categoryName);
        var categoryIRI = getIRI(categoryName);
        var subjectShortName = getShortName(subjectName);

        var index = this.getSubjectIndex(treeJsonObject, subjectShortName);
        if(index > -1){
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: categoryShortName,
              icon: this.categoryIcon
            },
            attr: {
              id: categoryShortName,
              gui: 'category',
              iri: categoryIRI,
              title: categoryName
            }
          });
        }
      },

      addPropertyToSubject: function(treeJsonObject, subjectName, propertyName, propertyValueName, valueMustBeSet, showInResutlts, isPropertyValueVariable){
        var propertyShortName = getShortName(propertyName);
        var propertyIRI = getIRI(propertyName);
        var propertyValueShortName = getShortName(propertyValueName);
        var propertyValueIRI = getIRI(propertyValueName);
        var subjectShortName = getShortName(subjectName);

        var index = this.getSubjectIndex(treeJsonObject, subjectShortName);
        if(index > -1){
          var nodeTitle = isPropertyValueVariable ? (propertyShortName + ' ?' + propertyValueName) : (propertyShortName + ' ' + propertyValueShortName);
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: nodeTitle,
              icon: this.propertyIcon
            },
            attr: {
              id: propertyShortName,
              valuename: propertyValueShortName,
              columnlabel: propertyValueShortName,
              valuemustbeset: valueMustBeSet,
              showinresults: showInResutlts,
              gui: 'property',
              iri: propertyIRI,
              title: propertyName
            }
          });
        }
      },



      toTree: function(queryJsonObject){
        //use internal data structure if none specified
        if(!queryJsonObject){
          queryJsonObject = this.TreeQuery;
        }

        //init tree object
        var treeJsonObject = {
          json_data: {
            data: []
          }
        };
        queryJsonObject.triple = queryJsonObject.triple || [];
        queryJsonObject.category_restriction = queryJsonObject.category_restriction || [];
        var triples = queryJsonObject.triple;
        var category_restrictions = queryJsonObject.category_restriction;
   
        for(var tripleIndex = 0; tripleIndex < triples.length; tripleIndex++){
          var triple = triples[tripleIndex];
          if(this.isVariable(triple.subject)){
            var isVariable = (triple.subject.type === 'VAR');
            var showInResults = isVariable && this.isSubjectInProjectionVars(queryJsonObject, triple.subject.value);
            this.addSubject(treeJsonObject, triple.subject.value, showInResults, isVariable);
            this.addPropertyToSubject(treeJsonObject,
              triple.subject.value,
              triple.predicate.value,
              triple.object.value,
              triple.optional,
              this.isSubjectInProjectionVars(queryJsonObject, triple.object.value)
              );
          }
          else if(triple.subject.type === 'IRI'){
            this.addSubject(treeJsonObject, triple.subject.value, false);
            this.addPropertyToSubject(treeJsonObject,
              triple.subject.value,
              triple.predicate.value,
              triple.object.value,
              triple.optional,
              this.isSubjectInProjectionVars(queryJsonObject, triple.object.value)
              );
          }
        }

        for (var i = 0; i < category_restrictions.length; i++){
          var category_restriction = category_restrictions[i];
          isVariable = category_restriction.subject.type === 'VAR';
          showInResults = isVariable && this.isSubjectInProjectionVars(queryJsonObject, category_restrictions[i].subject.value);
          this.addSubject(treeJsonObject, category_restriction.subject.value, showInResults, isVariable);
          this.addCategoryToSubject(treeJsonObject, category_restriction.subject.value, category_restriction.category_iri[0]);
        }
        $(queryJsonObject).trigger('change');
        return treeJsonObject;
      },
    
      TreeQuery : {}
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
    }
  };

 
})(jQuery);


