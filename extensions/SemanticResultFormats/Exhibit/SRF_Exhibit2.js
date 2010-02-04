var smwExhibitJSON = { types: {}, properties: {}, gmaps: [], items: [] };
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
        accuracy = 4;
    }
    
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
            jobs.push({ item: item, address: address });
        }
    });
    
    var results = [];
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
                        results.push("{ id: '" + job.item + "', " + outputProperty + ": '" + lat + "," + lng + "' }");
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

addOnloadHook(EnableExhibit);