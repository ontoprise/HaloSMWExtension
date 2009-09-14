
 
// deactivate combined search if necessary
var csLoadObserver;
if (csLoadObserver != null) Event.stopObserving(window, 'load', csLoadObserver);

 
var UnifiedSearch = Class.create();
UnifiedSearch.prototype = {
    initialize: function() {
    
    },
    
    showDescription: function(title) {
        var div = $(title);
        if (!div.visible()) div.show(); else div.hide();
    },
    
    getCookie: function (name) {
    var value=null;
    if(document.cookie != "") {
      var kk=document.cookie.indexOf(name+"=");
      if(kk >= 0) {
        kk=kk+name.length+1;
        var ll=document.cookie.indexOf(";", kk);
        if(ll < 0)ll=document.cookie.length;
        value=document.cookie.substring(kk, ll);
        value=unescape(value); 
      }
    }
    return value;
  }
 
}


var ToleranceSelector = Class.create();
ToleranceSelector.prototype = {
    
    initialize: function() {
      
    },
    
    activate: function() {
        var initialValue = smwhg_unifiedsearch.getCookie("tolerance-slider");
        if (initialValue == null) initialValue = 0; 
        
        // set tolerance selector
        var toleranceSelector = $('toleranceSelector');   
        if (toleranceSelector) toleranceSelector.options[initialValue].selected = true;
                
        // set tolerance level in search field of skin (if existing)
        if ($('toleranceLevel')) $('toleranceLevel').value = initialValue;
        
        // set search text in extension's search field
        var us_searchfield = $('us_searchfield');
        var queryParams = location.href.toQueryParams();
       
        if (us_searchfield) us_searchfield.value = queryParams['search'];
    },
    
      
    onChange: function(v) {
        
        // read new tolerance selection and stores in a cookie
        var toleranceSelector = $('toleranceSelector');   
        var selectedIndex = toleranceSelector.selectedIndex;
        var cookieText = "tolerance-slider="+selectedIndex+"; path="+wgScript;
        document.cookie = OB_bd.isIE ? "tolerance-slider="+selectedIndex : "tolerance-slider="+selectedIndex+"; path="+wgScript;
       
    }
}

var smwhg_toleranceselector = new ToleranceSelector();
Event.observe(window, 'load', smwhg_toleranceselector.activate.bind(smwhg_toleranceselector));
var smwhg_unifiedsearch = new UnifiedSearch();
