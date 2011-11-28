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

/**
 * @file
 * @ingroup EnhancedRetrieval
 * 
 */
 
// deactivate combined search if necessary
var csLoadObserver;
if (csLoadObserver != null) Event.stopObserving(window, 'load', csLoadObserver);

 
var EnhancedRetrieval = Class.create();
EnhancedRetrieval.prototype = {
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
        var initialValue = smwhg_enhancedretrieval.getCookie("tolerance-slider");
        if (initialValue == null) initialValue = 0; 
        
        // set tolerance selector
        var toleranceSelector = $('toleranceSelector');   
        if (toleranceSelector) toleranceSelector.options[initialValue].selected = true;
                
        // set tolerance level in search field of skin (if existing)
        if ($('toleranceLevel')) $('toleranceLevel').value = initialValue;
        
        // set search text in extension's search field
        var us_searchfield = $('us_searchfield');
        var queryParams = this.getQueryParams();
       
        if (us_searchfield) us_searchfield.value = queryParams['search'];
    },
    
    getQueryParams: function(separator) {
	 var match = location.href.strip().match(/([^?#]*)(#.*)?$/);
	 if (!match) return { };
	
	 return match[1].split(separator || '&').inject({ }, function(hash, pair) {
	 if ((pair = pair.split('='))[0]) {
		 var key = decodeURIComponent(pair.shift());
		 var value = pair.length > 1 ? pair.join('=') : pair[0];
		 if (value != undefined) value = decodeURIComponent(value.replace(/\+/g, ' '));
		
		 if (key in hash) {
		 if (!Object.isArray(hash[key])) hash[key] = [hash[key]];
		 hash[key].push(value);
	 }
	   else hash[key] = value;
	 }
	 return hash;
	 });
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
var smwhg_enhancedretrieval = new EnhancedRetrieval();
