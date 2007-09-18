/**
* SMW_DataTypes.js
* 
* Helper functions for retrieving the data types that are currently provided
* by the wiki. The types are retrieved by an ajax call an stored for quick access.
* The list of types can be refreshed.
* 
* There is a singleton instance of this class that is initialized at startup:
* gDataTypes
* 
* @author Thomas Schweitzer
*
*/

var DataTypes = Class.create();

/**
 * Class for retrieving and storing the wiki's data types.
 * 
 */
DataTypes.prototype = {

	/**
	 * @public
	 * 
	 * Constructor.
	 */
	initialize: function() {
		this.builtinTypes = null;
		this.userTypes = null;
		this.callback = new Array();
		this.refresh();
		this.refreshPending = false;
		
	},

	/**
	 * Returns the array of builtin types.
	 * 
	 * @return array<string> 
	 * 			List of builtin types or <null> if there was no answer from the 
	 * 			server yet.
	 *         
	 */
	getBuiltinTypes: function() {
		return this.builtinTypes;
	},
	
	/**
	 * Returns the array of user defined types.
	 * 
	 * @return array<string> 
	 * 			List of user defined types or <null> if there was no answer from
	 * 			the server yet.
	 *         
	 */
	getUserDefinedTypes: function() {
		return this.userTypes;
	},
	
	/**
	 * @public
	 * 
	 * Makes a new request for the current data types. It will take a 
	 * while until they are available.
	 * 
	 */
	refresh: function(callback) {
		if (callback) {
			this.callback.push(callback);
		}
		this.userUpdated    = false;
		this.builtinUpdated = false;
		if (!this.refreshPending) {
			this.refreshPending = true;
			sajax_do_call('smwfGetUserDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
			sajax_do_call('smwfGetBuiltinDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
		}

	},
	
	/**
	 * @private
	 * 
	 * This function is called when the ajax call returns. The data types
	 * are stored in the internal arrays.
	 */
	ajaxResponseGetDatatypes: function(request) {
		if (request.status != 200) {
			// request failed
			return;
		}
		var types = request.responseText.split(",");

		if (types[0].indexOf("User defined types") >= 0) {
			this.userUpdated = true;
			// received user defined types
			this.userTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.userTypes[i-1] = types[i];
			}
		} else {
			// received builtin types
			this.builtinUpdated = true;
			this.builtinTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.builtinTypes[i-1] = types[i];
			}
		}
		if (this.userUpdated && this.builtinUpdated) {
			// If there are articles for builtin types, these types appear as
			// builtin and as user defined types => remove them from the list
			// of user defined types.
			var userTypes = new Array();
			for (var u = 0; u < this.userTypes.length; u++) {
				var found = false;
				for (var b = 0; b < this.builtinTypes.length; b++) {
					if (this.userTypes[u] == this.builtinTypes[b]) {
						found = true;
						break;
					}
				}
				if (!found) {
					userTypes.push(this.userTypes[u]);
				}
			}
			this.userTypes = userTypes;
			
			for (var i = 0; i < this.callback.length; ++i) {
				this.callback[i]();
			}
			this.callback.clear();
			this.refreshPending = false;
		}
	}

}

var gDataTypes = new DataTypes();
