(function($){
  function activateSwitchToSparqBtn(){
    var switchToSparqlBtn = $('#switchToSparqlBtn');
    switchToSparqlBtn.live('click', function(){
      $('#askQI').hide();
      $('#sparqlQI').show();
      switchToSparqlBtn.remove();
      switchToSparqlBtn.html('SPARQL');
    //@TODO if ASK query is not empty send it to tsc for convertion to SPARQL
    });
  }

  function activateAddCategoryBtn(){
    $('#qiAddCategoryBtn').live('click', function(){
      $('#qiCategoryDialog').show();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').hide();
    });
  }
  
  function activateAddSubjectBtn(){
    $('#qiAddSubjectBtn').live('click', openSubjectDialog);
  }

  function openSubjectDialog(event, subjectName, columnLabel, showInResults){
    $('#qiCategoryDialog').hide();
    $('#qiSubjectDialog').show();
    $('#qiPropertyDialog').hide();
    $('#qiSubjectDialog #qiSubjectNameInput').val(subjectName);
    $('#qiSubjectDialog #qiSubjectColumnLabel').val(columnLabel);
    if(showInResults){
      $('#qiSubjectDialog #qiSubjectShowInResultsChkBox').attr('checked', 'checked');
    }
    else{
      $('#qiSubjectDialog #qiSubjectShowInResultsChkBox').removeAttr('checked');
    }



  }

  function activateAddPropertyBtn(){
    $('#qiAddPropertyBtn').live('click', function(){
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').hide();
      $('#qiPropertyDialog').show();
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

  function buildSparqlTree(){
    //parse query json string to query jason object
    var queryJson = $.parseJSON(TreeQuery);

    //build jstree json object out of it
    var treeJson = buildTreeJson(queryJson);

    //pass this new object to renderTree function
    renderTree(treeJson);
  }

  function renderTree(treeJsonConfig){

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
    treeJsonConfig.plugins = [ "themes", "json_data", "ui" ];
    //    treeJsonConfig.themes = {
    //	            "theme" : "qi",
    //	            "dots" : true,
    //	            "icons" : true,
    //              "url" : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/qi_tree.css'
    //	        };
   
    $("#qiTreeDiv").jstree(treeJsonConfig).bind("loaded.jstree", function (event, data) {
      activateSubjectNodeLink();
    });
  }

  function activateSubjectNodeLink(){
    $('#qiTreeDiv a[gui="subject"]').each(function(){
      var subjectName = $(this).attr('id');
      var columnLabel = $(this).attr('collabel');
      var showInResults = $(this).attr('showinresults');
      $(this).click(function(){
        openSubjectDialog(null, subjectName, columnLabel, showInResults);
      });
    });
  }

  



  $(document).ready(function(){
    activateSwitchToSparqBtn();
    activateAddCategoryBtn();
    activateAddSubjectBtn();
    activateAddPropertyBtn();
    activateAddAndFilterLink();
    activateDeleteFilterImg();
    activateAddOrFilterLink();
    activateAddOrCategoryLink();
    activateDeleteCategoryImg();
    renderTree(SPARQL.json.toTree(SPARQL.json.TreeQuery));
  });

  SPARQL = {
    json : {

      addSubject: function(treeJsonObject, subjectName, showInResults){
        var subjectAttributes = {
          id: subjectName,
          gui: 'subject',
          collabel: subjectName
        };
        if(this.getSubjectIndex(treeJsonObject, subjectName) === -1){
          if(showInResults){
            subjectAttributes.showinresults = 'true';
          }

          treeJsonObject.json_data.data.push(
          {
            data : {
              title : subjectName,
              attr : subjectAttributes,
              icon : mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/variable_icon.gif'
            },
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

      isVariable: function(name){
        return (name.indexOf('?') === 0);
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
        var index = this.getSubjectIndex(treeJsonObject, subjectName);
        if(index > -1){
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: categoryName,
              icon: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/category_icon.gif'
            }
          });
        }
      },

      addPropertyToSubject: function(treeJsonObject, subjectName, propertyName){
        var index = this.getSubjectIndex(treeJsonObject, subjectName);
        if(index > -1){
          treeJsonObject.json_data.data[index].children.push(
          {
            data: {
              title: propertyName,
              icon: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif'
            }
          });
        }
      },

      toTree: function(queryJsonObject){
        var treeJsonObject = {
          json_data: {
            data: []
          }
        };
        var triples = queryJsonObject.triple;
   
        for(var tripleIndex = 0; tripleIndex < triples.length; tripleIndex++){
          var triple = triples[tripleIndex];
          if(this.isVariable(triple.subject.value)){
            this.addSubject(treeJsonObject, triple.subject.value, this.isSubjectInProjectionVars(queryJsonObject, triple.subject.value));
            
          }
          else if(this.isVariable(triple.object.value)){
            var validName = this.getValidName(triple.subject.value);
            this.addSubject(treeJsonObject, validName, false);
            this.addCategoryToSubject(treeJsonObject, validName, triple.subject.value);
            this.addPropertyToSubject(treeJsonObject, validName, triple.predicate.value + ': ' + triple.object.value);
            
          }
          if(this.isVariable(triple.object.value)){
            this.addSubject(treeJsonObject, triple.object.value, this.isSubjectInProjectionVars(queryJsonObject, triple.object.value));
          }
        }
        return treeJsonObject;
      },

      //        for(var filterIndex = 0; filterIndex < queryJsonObject.filter.length; filterIndex++){
      //          var filter = filters[filterIndex];
      //          for(var expressionIndex = 0; expressionIndex < filter.expression.length; expressionIndex++){
      //            var expression = filter.expression[expressionIndex];
      //            for(var argumentIndex = 0; argumentIndex < expression.argument.length; argumentIndex++){
      //              if(expression.argument[argumentIndex].value.indexOf(subjectName) > -1){
      //                treeJsonObject.json_data.data[subjectIndex].children.push(
      //                {
      //                  data: {
      //                    title: expression.argument[0].value + ' ' + expression.operator + ' ' + expression.argument[1].value,
      //                    icon: mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/extensions/SMWHalo/skins/QueryInterface/images/property_icon.gif'
      //                  }
      //                });
      //              }
      //            }
      //          }
      //        }
      //      }
      //
      //    },
    
      TreeQuery : {
        projection_var: ["?x","?y"],
        triple: [
        {
          subject: {
            type: "IRI",
            value: "my:test"
          },
          predicate: {
            type: "IRI",
            value: "my:test"
          },
          object: {
            type: "VAR",
            value: "?x"
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
              value: "?x"
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
          by_var: ["?y"]
        }],
        offset: 10,
        limit: 100,

        categoryRestriction : {
          subject: {
            type: "IRI",
            value: "my:test"
          },
          category_iri : [
          "http://localhost/mediawiki/category:category"
          ]
        }
      }
    }
  };

 
})(jQuery);


