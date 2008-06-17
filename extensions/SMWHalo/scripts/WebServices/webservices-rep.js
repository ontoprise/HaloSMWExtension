
var WebServiceRepositorySpecial = Class.create();

WebServiceRepositorySpecial.prototype = {
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
		document.getElementById("confirmButton").style.visibility = "hidden";
		document.getElementById("confirmButton").style.width = "1px";
		document.getElementById("confirmText").childNodes[0].nodeValue = "confirmed";
	}	
}	

webServiceSpecial = new WebServiceRepositorySpecial();
