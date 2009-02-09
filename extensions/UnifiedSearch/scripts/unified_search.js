
 
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
    }
}


var ToleranceSelector = Class.create();
ToleranceSelector.prototype = {
    
    initialize: function() {
      
    },
    
    activate: function() {
        var initialValue = GeneralBrowserTools.getCookie("tolerance-slider");
        if (initialValue == null) initialValue = 0; 
        
        // set tolerance selector
        var toleranceSelector = $('toleranceSelector');   
        if (toleranceSelector) toleranceSelector.options[initialValue].selected = true;
                
        // set tolerance level in search field of skin (if existing)
        if ($('toleranceLevel')) $('toleranceLevel').value = initialValue;
        
        // set search text in extension's search field
        var us_searchfield = $('us_searchfield');
        var mw_searchfield = $('searchInput');
        if (us_searchfield && mw_searchfield) us_searchfield.value = mw_searchfield.value;
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
