
/*  Copyright 2007, ontoprise GmbH
*  This file is part of the halo-Extension.
*
*   The halo-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The halo-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * @file
 * @ingroup SMWHaloSemanticToolbar
 * 
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
		if (this.builtinTypes && this.userTypes) {
// Change request (Bug 7077): Do not update the user types every time they are
// needed.
			for (var i = 0; i < this.callback.length; ++i) {
				this.callback[i]();
			}
			this.callback.clear();
			
			return;
		}
		if (!this.refreshPending) {
			this.refreshPending = true;
			sajax_do_call('smwf_tb_GetUserDatatypes', 
			              [], 
			              this.ajaxResponseGetDatatypes.bind(this));
			if (!this.builtinTypes) {
				this.builtinTypes = GeneralBrowserTools.getCookieObject("smwh_builtinTypes");
				if (this.builtinTypes == null) {
					sajax_do_call('smwf_tb_GetBuiltinDatatypes', 
					              [], 
					              this.ajaxResponseGetDatatypes.bind(this));
				}
			}
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
			// received user defined types
			this.userTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.userTypes[i-1] = types[i];
			}
		} else {
			// received builtin types
			this.builtinTypes = new Array(types.length-1);
			for (var i = 1, len = types.length; i < len; ++i) {
				this.builtinTypes[i-1] = types[i];
			}
			GeneralBrowserTools.setCookieObject("smwh_builtinTypes", this.builtinTypes);
		}
		if (this.userTypes && this.builtinTypes) {
			
			for (var i = 0; i < this.callback.length; ++i) {
				this.callback[i]();
			}
			this.callback.clear();
			this.refreshPending = false;
		}
	}

}

var gDataTypes = new DataTypes();
