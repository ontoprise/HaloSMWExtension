/**
 * @author: Kai Kühn
 * 
 * Created on: 27.01.2009
 * 
 */
 
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
    	if (initialValue == 0) {
    		$('tolerantsearch').checked = true;
    	} else if (initialValue == 1) {
            $('semitolerantsearch').checked = true;
        } else if (initialValue == 2) {
            $('exactsearch').checked = true;
        }
    },
    
    onClick: function(v) {
    	document.cookie = "tolerance-slider="+v+"; path="+wgScript;
    }
}

var smwhg_toleranceselector = new ToleranceSelector();
Event.observe(window, 'load', smwhg_toleranceselector.activate.bind(smwhg_toleranceselector));
var smwhg_unifiedsearch = new UnifiedSearch();
