(function($){
  function activateQueryViewTabToggle(){
    //switch to tree view
    $('#sparqlQI #qiDefTab1').live('click', function(){
      $('#sparqlQI #treeview').css('display', 'inline');
      $('#sparqlQI #qiDefTab1').addClass('qiDefTabActive');
      $('#sparqlQI #qiDefTab1').removeClass('qiDefTabInactive');
      $('#sparqlQI #qisource').css('display', 'none');
      $('#sparqlQI #qiDefTab3').addClass('qiDefTabInactive');
      $('#sparqlQI #qiDefTab3').removeClass('qiDefTabActive');

      sparqlToTree($('#sparqlQueryText').val());
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
    $.ajaxSetup(
    {
      //      scriptCharset: "utf-8",
      //      contentType: "application/json; charset=utf-8",
      //      dataType: "json"
      //      cache: false
      processData: false
    });
    
  //send ajax post request to localhost:8080/sparql/sparqlToTree
  //    $.ajax({
  //      url: 'http://ajax.googleapis.com/ajax/services/search/web',
  //      data: {q:"House", v:"1.0", hl:"en"},
  //      success: function(data, textStatus, jqXHR) {
  //        mw.log('data: ' + data);
  //        mw.log('textStatus: ' + textStatus);
  //        mw.log('jqXHR.responseText: ' + jqXHR.responseText)
  //      },
  //      error: function (xhr, textStatus, errorThrown) {
  //        mw.log(textStatus);
  //        mw.log('response: ' + xhr.responseText)
  //        mw.log('errorThrown: ' + errorThrown);
  //      }
  //    }); mn

  //      $.getJSON('localhost:8080/sparql/sparqlToTree' {sparql=' + encodeURIComponent(sparqlQuery), function(json, textStatus, xhr) {
  //        mw.log('data: ' + json);
  //        mw.log('textStatus: ' + textStatus);
  //        mw.log('xhr.responseText: ' + xhr.responseText);
  //      });

    
  //        $.post("http://localhost:8080/sparql/sparqlToTree", { sparql: encodeURIComponent(sparqlQuery) }, function(data) {
  //          if(data.notification) {
  //            mw.log(data.notification);
  //          } else {
  //            mw.log(data);
  //          }
  //        });


  //get the response

  //if the response contains tree then  store it in the local tree var

  //else display error message
  }

  function treeToSparql(treeJsonConfig){

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

  function activateAddCategoryBtn(){
    $('#qiAddCategoryBtn').live('click', openCategoryDialog);
  }

  function openCategoryDialog(event, categoryName){
    $('#qiCategoryDialog').show();
    $('#qiSubjectDialog').hide();
    $('#qiPropertyDialog').hide();
    $('#qiCategoryDialog #qiCategoryNameInput').val(categoryName || '');
  }
  
  function activateAddSubjectBtn(sparqlTree){
    $('#qiAddSubjectBtn').live('click', function(){
      //create new subject node and select it
      var nodeData = {
        data:{
          title: '',
          icon : SPARQL.json.variableIcon
        },
        attr: {
          id: 'newsubjectnode',
          gui: 'subject',
          temporary: true
        }
      };
      sparqlTree.create ( null , 'first' , nodeData, function(){}, true );
      sparqlTree.select_node('#' + nodeData.attr.id);
      openSubjectDialog();
    });
  }

  function openSubjectDialog(event, subjectName, columnLabel, showInResults){
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
    $('#qiPropertyDialog #qiPropertyValueNameInput').val(valueName || '');
    $('#qiPropertyDialog #qiSubjectColumnLabel').val(columnLabel || '');
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

  function activateAddPropertyBtn(){
    $('#qiAddPropertyBtn').live('click', openPropertyDialog);
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
      //get selected tree node
      var selectedNode = sparqlTree.get_selected();
      var parentNode = selectedNode.parents('li').eq(0);
      var newNodeId;
      //update all its properties with the ones in the dialog
      switch($(selectedNode).attr('gui')){
        case 'subject':
          updateSubject($(selectedNode),
            $('#qiSubjectNameInput').val(),
            $('#qiSubjectColumnLabel').val(),
            $('#qiSubjectShowInResultsChkBox').attr('checked') === true);
          newNodeId = $('#qiSubjectNameInput').val();
          break;

        case 'category':
          updateCategory($(selectedNode), $(parentNode), $('#qiCategoryNameInput').val());
          newNodeId = $('#qiCategoryNameInput').val();
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
          break;

        default:
          break;
      }

      //rebuild the tree
      renderTree(SPARQL.json.toTree(), newNodeId);
    });       
  }

  function updateProperty(selectedNode, parentNode, propertyName, propertyValueName, columnLabel, valueMustBeSet, showInResults){
    var originalPropertyName = selectedNode.attr('id');
    var originalPropertyValue = selectedNode.attr('propertyvaluename');
    //    var originalValueMustBeSet = (selectedNode.attr('valuemustbeset') === 'true');
    var originalShowInResults = (selectedNode.attr('showinresults') === 'true');
    var subjectName = parentNode.attr('id');

    //check if propertyValueName is of type var (starting with ?) and trip it
    var isPropertyValueVar = (propertyValueName.indexOf('?') === 0);
    propertyValueName = propertyValueName.replace(/\?/, '');

    //update triples    
    for(var i = 0; i < SPARQL.json.TreeQuery.triple.length; i++){
      var triple = SPARQL.json.TreeQuery.triple[i];
      if(triple.subject.value === subjectName){
        if(triple.predicate.value === originalPropertyName){
          triple.predicate.value = propertyName;
        }
        if(triple.object.value === originalPropertyValue){
          triple.object.value = propertyValueName;
        }
        triple.optional = valueMustBeSet;
      }
    }
    //update projection_var
    if(showInResults){
      if(originalShowInResults !== showInResults){
        if(isPropertyValueVar)
          SPARQL.json.TreeQuery.projection_var.push(propertyValueName);
      }
      else{
        if(isPropertyValueVar){
          var projection_var = SPARQL.json.TreeQuery.projection_var;
          for(i = 0; i < projection_var.length; i++){
            if(projection_var[i] === originalPropertyValue){
              projection_var[i] = propertyValueName;
              break;
            }
          }
        }
      }
    }
    else{
      if(originalShowInResults !== showInResults){
        projection_var = SPARQL.json.TreeQuery.projection_var;
        for(i = 0; i < projection_var.length; i++){
          if(projection_var[i] === originalPropertyValue){
            projection_var.splice(i, 1);
            break;
          }
        }
      }
    }
    

  }

  function updateCategory(selectedTreeNode, parentNode, categoryName){
    var originalCategoryName = selectedTreeNode.attr('id');
    var subjectId = parentNode.attr('id');
    //if there is a change
    if(originalCategoryName !== categoryName){      
      //update categoryRestriction
      for(i = 0; i < SPARQL.json.TreeQuery.categoryRestriction.length; i++){
        if(SPARQL.json.TreeQuery.categoryRestriction[i].subject.value === subjectId){
          for(var j = 0; j < SPARQL.json.TreeQuery.categoryRestriction[i].category_iri.length; j++){
            if(SPARQL.json.TreeQuery.categoryRestriction[i].category_iri[j] === originalCategoryName){
              SPARQL.json.TreeQuery.categoryRestriction[i].category_iri[j] = categoryName;
            }
          }
        }
      }
    }
  }


  function escapeCssSelector(selector){
    return selector.replace(/([\_\:\/])/g, '\\$1');
  }

  function updateSubject(selectedTreeNode, subjectName, columnLabel, showInResults){
    var originalSubjectName = selectedTreeNode.attr('id');
    var originalShowInResults = (selectedTreeNode.attr('showinresults') === 'true');
    
    //update triples
    if(originalSubjectName !== subjectName){
      //rename each occurence of this var in triples as subject or object
      for(var i = 0; i < SPARQL.json.TreeQuery.triple.length; i++){
        var triple = SPARQL.json.TreeQuery.triple[i];
        if(triple.subject.value === originalSubjectName){
          triple.subject.value = subjectName;
        }
        else if(triple.object.value === originalSubjectName){
          triple.object.value = subjectName;
        }
      }
    }
    //update projection_var array
    if(showInResults){
      if(originalShowInResults !== showInResults){
        SPARQL.json.TreeQuery.projection_var.push(subjectName);
      }
      else if(originalSubjectName !== subjectName){
        for(i = 0; i < SPARQL.json.TreeQuery.projection_var.length; i++){
          if(SPARQL.json.TreeQuery.projection_var[i] === originalSubjectName){
            SPARQL.json.TreeQuery.projection_var[i] = subjectName;
          }
        }
      }
    }
    else{
      if(originalShowInResults !== showInResults){
        var currentSubject = (originalSubjectName !== subjectName) ? originalSubjectName : subjectName;
        for(i = 0; i < SPARQL.json.TreeQuery.projection_var.length; i++){
          if(SPARQL.json.TreeQuery.projection_var[i] === currentSubject){
            SPARQL.json.TreeQuery.projection_var.splice(i, 1);
          }
        }
      }
    }
 

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
      "select_multiple_modifier" : "alt"
    };
   
    var tree = $("#qiTreeDiv").jstree(treeJsonConfig)
    .bind("select_node.jstree", function (NODE, REF_NODE) {
      switch($(REF_NODE.rslt.obj).attr('gui')){
        case 'subject':
          openSubjectDialog(null,
            $(REF_NODE.rslt.obj).attr('id'),
            $(REF_NODE.rslt.obj).attr('columnlabel'),
            $(REF_NODE.rslt.obj).attr('showinresults'));
          break;

        default:
          break;
      }
    })
    .bind("loaded.jstree", function (event, data) {
      //do stuff when tree is loaded
      SPARQL.json.jstree = data.inst;
      
      if(selectedNodeId){
        data.inst.select_node("#" + escapeCssSelector(selectedNodeId) + ':eq(0)');
      }
      activatePropertyNodeLink();
      activateCategoryNodeLink();
      activateUpdateBtn(data.inst);
      activateDeleteLink(data.inst);
      activateCancelLink(data.inst);
      activateAddSubjectBtn(data.inst);
    });

    return tree;

  }

  ///delete selected tree node
  function activateDeleteLink(sparqlTree){
    $('#qiDeleteLink').live('click', function(event){
      sparqlTree.delete_node(sparqlTree.get_selected());
      event.preventDefault();
    });
  }

  //unselect tree nodes and close the dialogs
  function activateCancelLink(sparqlTree){
    $('#qiCancelLink').live('click', function(event){
      sparqlTree.deselect_all();
      sparqlTree.delete_node('[temporary]');
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').hide();
      event.preventDefault();
    });
  }

  function activatePropertyNodeLink(){
    $('#qiTreeDiv li[gui="property"]').each(function(){
      var propertyName = $(this).attr('id');
      var propertyValueName = $(this).attr('propertyvaluename');
      var columnLabel = $(this).attr('columnlabel');
      var showInResults = $(this).attr('showinresults');
      var valueMustBeSet = $(this).attr('valuemustbeset');
      $(this).click(function(){
        openPropertyDialog(null, propertyName, propertyValueName, columnLabel, showInResults, valueMustBeSet);
      });
    });
  }

  function activateCategoryNodeLink(){
    $('#qiTreeDiv li[gui="category"]').each(function(){
      var categoryName = $(this).attr('id');
      $(this).click(function(){
        openCategoryDialog(null, categoryName);
      });
    });
  }



  



  $(document).ready(function(){
    activateSwitchToSparqBtn();
    activateAddCategoryBtn();    
    activateAddPropertyBtn();
    activateAddAndFilterLink();
    activateDeleteFilterImg();
    activateAddOrFilterLink();
    activateAddOrCategoryLink();
    activateDeleteCategoryImg();
    activateQueryViewTabToggle();
    renderTree(SPARQL.json.toTree(SPARQL.json.TreeQuery));

  });

  SPARQL = {
    json : {

      variableIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif',
      instanceIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/instance_icon.gif',
      categoryIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif',
      propertyIcon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif',


      addSubject: function(treeJsonObject, subjectName, showInResults, isVariable){
        var iconFile = this.instanceIcon;
        var subjectAttributes = {
          id: subjectName,
          gui: 'subject',
          columnlabel: subjectName
        };
        if(this.getSubjectIndex(treeJsonObject, subjectName) === -1){
          if(showInResults){
            subjectAttributes.showinresults = 'true';
          }
          if(isVariable){
            iconFile = this.variableIcon;
          }
          treeJsonObject.json_data.data.push(
          {
            data : {
              title : subjectName,
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

      getShortName: function(iri){
        var iriTokens = String.split(iri, '[/:#]');
        return iriTokens ? iriTokens[iriTokens.length - 1] : null;
      },

      addCategoryToSubject: function(treeJsonObject, subjectName, categoryName){
        categoryName = this.getShortName(categoryName);
        var index = this.getSubjectIndex(treeJsonObject, subjectName);
        if(index > -1){
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: categoryName,
              icon: this.categoryIcon
            },
            attr: {
              title: categoryName,
              id: categoryName,
              gui: 'category'
            }
          });
        }
      },

      addPropertyToSubject: function(treeJsonObject, subjectName, propertyName, propertyValueName, valueMustBeSet, showInResutlts){
        var index = this.getSubjectIndex(treeJsonObject, subjectName);
        if(index > -1){
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: propertyName + ' ?' + propertyValueName,
              icon: this.propertyIcon
            },
            attr: {
              id: propertyName,
              propertyvaluename: propertyValueName,
              columnlabel: propertyValueName,
              valuemustbeset: valueMustBeSet,
              showinresults: showInResutlts,
              gui: 'property'
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
        
        var triples = queryJsonObject.triple;
        var categoryRestrictions = queryJsonObject.categoryRestriction;
   
        for(var tripleIndex = 0; tripleIndex < triples.length; tripleIndex++){
          var triple = triples[tripleIndex];
          if(this.isVariable(triple.subject)){
            this.addSubject(treeJsonObject, triple.subject.value, this.isSubjectInProjectionVars(queryJsonObject, triple.subject.value), true);
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

        for (var i = 0; i < categoryRestrictions.length; i++){
          this.addSubject(treeJsonObject, categoryRestrictions[i].subject.value, this.isSubjectInProjectionVars(queryJsonObject, categoryRestrictions[i].subject.value), true);
          this.addCategoryToSubject(treeJsonObject, categoryRestrictions[i].subject.value, categoryRestrictions[i].category_iri[0]);
        }
        return treeJsonObject;
      },
    
      TreeQuery : {
        projection_var: ["a","y"],
        triple: [
        {
          subject: {
            type: "IRI",
            value: "my:girlfriend"
          },
          predicate: {
            type: "IRI",
            value: "my:does"
          },
          object: {
            type: "VAR",
            value: "a"
          },
          optional: true
        },
        {
          subject: {
            type: "VAR",
            value: "y"
          },
          predicate: {
            type: "IRI",
            value: "my:likes"
          },
          object: {
            type: "VAR",
            value: "a"
          },
          optional: false
        }
        ],
        filter: [
        {
          expression: [
          {
            operator: "LT",
            argument: [
            {
              type: "VAR",
              value: "a"
            },
            {
              type: "LITERAL",
              value: "7",
              datatype_iri: "http://www.w3.org/2001/XMLSchema#int"
            }
            ]
          }
          ]
        }
        ],
        order: [{
          ascending: false,
          by_var: ["y"]
        }],
        offset: 10,
        limit: 100,

        categoryRestriction : [
        {
          subject: {
            type: "VAR",
            value: "a"
          },
          category_iri : [
          "http://localhost/mediawiki/category:boyfriend"
          ]
        }
        ]
      }
    }
  };

 
})(jQuery);


