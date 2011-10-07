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
    $('#qiAddSubjectBtn').live('click', function(){
      $('#qiCategoryDialog').hide();
      $('#qiSubjectDialog').show();
      $('#qiPropertyDialog').hide();
    });
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
      + '<option value="NEQ">' + gLanguage.getMessage('QI_NOT') + ' ' + gLanguage.getMessage('QI_EQUAL') + ' (==)</option>'
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
  });

var TreeQuery = {
  "projection_var": ["x","y"],
  "triple": [
    {
      "subject": {
        "type": "IRI",
        "value": "my:test"
      },
      "predicate": {
        "type": "IRI",
        "value": "my:test"
      },
      "object": {
        "type": "VAR",
        "value": "x"
      },
      "optional": false
    }
  ],
  "filter": [
    {
      "expression": [
        {
          "operator": "LT",
          "argument": [
            {
              "type": "VAR",
              "value": "x"
            },
            {
              "type": "LITERAL",
              "value": "7",
              "datatype_iri": "http://www.w3.org/2001/XMLSchema#int"
            }
          ]
        }
      ]
    }
  ],
  "order": {
    "ascending": false,
    "by_var": ["y"]
  },
  "offset": 10,
  "limit": 100
};
 
})(jQuery);


