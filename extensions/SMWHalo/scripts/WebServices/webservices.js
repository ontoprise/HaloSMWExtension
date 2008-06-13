
var WebServiceSpecial = Class.create();

WebServiceSpecial.prototype = {
	initialize: function() {
		},
		
	updateCache : function(wsId) {
		sajax_do_call("smwf_ws_updateCache", [ wsId ], this.updateCacheCallBack.bind(this));
	},

	updateCacheCallBack : function(request) {
		alert(request.responseText);
	},
	
	confirmWWSD : function(wsId){
		sajax_do_call("smwf_ws_confirmWWSD", [ wsId ], this.confirmWWSDCallBack.bind(this));
	},

	confirmWWSDCallBack : function(request) {
		alert(request.responseText);
		document.getElementById("confirm").style.visibility = "hidden";
	}	
}	

webServiceSpecial = new WebServiceSpecial();
