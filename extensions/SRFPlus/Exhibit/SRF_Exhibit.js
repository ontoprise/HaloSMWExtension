SimileAjax.History.enabled = false;

window.smwExhibitJSON = { types: {}, properties: {}, gmaps: [], items: [], latlngs: [], lens: [] };

(function($){
var exhibit_map_items = [];
var exhibit_map_sum = 0;
SMWExhibit_checkFill = function() {
	exhibit_map_sum --;
	if(exhibit_map_sum == 0) {
	    var oItems = eval("({items:[" + exhibit_map_items.join(",") + "]})");
	    window.database.loadData( oItems, Exhibit.Persistence.resolveURL(location.href) );
	    window.exhibit.configureFromDOM();
	    Exhibit.UI.hideBusyIndicator();
	}
}
SMWExhibit_lookupLatLng = function(set, addressExpressionString, outputProperty, database, accuracy) {
    if (accuracy == undefined) {
    	// http://www.google.com/apis/maps/documentation/reference.html#GGeoAddressAccuracy
        // accuracy = 4;
        accuracy = 0;
    }
    
    var results = [];

    var expression = Exhibit.ExpressionParser.parse(addressExpressionString);
    var jobs = [];
    set.visit(function(item) {
        var address = expression.evaluateSingle(
            { "value" : item },
            { "value" : "item" },
            "value",
            database
        ).value
        if (address != null) {
        	var found = false;
        	for(var i=0;i<smwExhibitJSON.latlngs.length;++i){
        		if(smwExhibitJSON.latlngs[i].location == address) {
        			results.push("{ id: '" + item + "', " + outputProperty + ": '" + smwExhibitJSON.latlngs[i].latlng + "' }");
        			found = true;
        			break;
        		}
        	}
        	if(!found) {
        		found = false;
        		for(var i=0;i<jobs.length;++i){
        			if(jobs[i].address == address) {
        				jobs[i].items.push(item);
        				found = true;
        				break;
        			}
        		}
        		if(!found)
        			jobs.push({ items: [item], address: address });
        	}
        }
    });
    
    var geocoder = new GClientGeocoder();
    var cont = function() {
        if (jobs.length > 0) {
            var job = jobs.shift();
            geocoder.getLocations(
                job.address,
                function(json) {
                    if ("Placemark" in json) {
                        json.Placemark.sort(function(p1, p2) {
                            return p2.AddressDetails.Accuracy - p1.AddressDetails.Accuracy;
                        });
                    }
                    
                    if ("Placemark" in json && 
                        json.Placemark.length > 0 && 
                        json.Placemark[0].AddressDetails.Accuracy >= accuracy) {
                        
                        var coords = json.Placemark[0].Point.coordinates;
                        var lat = coords[1];
                        var lng = coords[0];
                        sajax_do_call('srf_AjaxAccess', ["addGeo", job.address + '|' + lat + ',' + lng], function(x){});
                        for(var i=0;i<job.items.length;++i){
                        	results.push("{ id: '" + job.items[i] + "', " + outputProperty + ": '" + lat + "," + lng + "' }");
                        }
                    } else {
                        var segments = job.address.split(",");
                        if (segments.length == 1) {
                            // results.push("{ id: '" + job.item + "' }");
                        } else {
                            job.address = segments.slice(1).join(",").replace(/^\s+/, "");
                            jobs.unshift(job); // do it again
                        }
                    }
                    cont();
                }
            );
        } else {
        	if(results.length>0)
        		exhibit_map_items.push(results.join(","));
	        SMWExhibit_checkFill();
        }
    };
    cont();
};

function createExhibit() {
	for(var i=0;i<smwExhibitJSON.lens.length;++i){
		var exhibitDiv = document.getElementById(smwExhibitJSON.lens[i].id);
		if(exhibitDiv != null) exhibitDiv.innerHTML = smwExhibitJSON.lens[i].content;
	}
  
	window.database = Exhibit.Database.create();
	window.exhibit = Exhibit.create(window.database);

//	var o = eval("(" + smwExhibitJSON + ")");
	var o = smwExhibitJSON;
	window.database.loadData( o, Exhibit.Persistence.resolveURL(location.href) );
	if((o.gmaps == null)||(o.gmaps.length == 0)) {
	    window.exhibit.configureFromDOM();
	    Exhibit.UI.hideBusyIndicator();
	} else {
		for (var i=o.gmaps.length-1; i>=0; --i) {
	        exhibit_map_sum ++;
			SMWExhibit_lookupLatLng(
	               database.getSubjects(o.gmaps[i].types, "type"),
	               "."+o.gmaps[i].label,
	               o.gmaps[i].latlng,
	               database
	        );
		}
	}
}
function EnableExhibit() {
	if(window.ActiveXObject) {
		// wait a while, otherwise, IE may crash, for too many stacks
		setTimeout("Exhibit.UI.showBusyIndicator()", 500);
		setTimeout("createExhibit()", 1500);
	} else {
		Exhibit.UI.showBusyIndicator();
		createExhibit();
	}
}

// addOnloadHook(EnableExhibit);

$(document).ready(function(){
	EnableExhibit();
});

})(jQuery);