var UPLanguage = Class.create();

/**
 * This class provides language dependent strings for an identifier.
 * 
 */
UPLanguage.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
	},

	/*
	 * @public
	 * 
	 * Returns a language dependent message for an ID, or the ID, if there is 
	 * no message for it.
	 * 
	 * @param string id
	 * 			ID of the message to be retrieved.
	 * @return string
	 * 			The language dependent message for the given ID.
	 */
	getMessage: function(id, type) {
		switch (type) {
			case "user":
				var msg = wgUPUserLanguageStrings[id];
				if (!msg) {
					msg = id;
				} 
				break;
				
			case "cont":
				var msg = wgUPContLanguageStrings[id];
				if (!msg) {
					msg = id;
				} 
				break;
			default: 
				var msg = wgUPUserLanguageStrings[id];
				if (!msg) {
					var msg = wgUPContLanguageStrings[id];
					if (!msg) {
						msg = id;
					}
				}
		} 
			
		// Replace variables
		msg = msg.replace(/\$n/g,wgCanonicalNamespace); 
		msg = msg.replace(/\$p/g,wgPageName);
		msg = msg.replace(/\$t/g,wgTitle);
		msg = msg.replace(/\$u/g,wgUserName);
		msg = msg.replace(/\$s/g,wgServer);
		return msg;
	}
	
}

// Singleton of this class

var upLanguage = new UPLanguage();