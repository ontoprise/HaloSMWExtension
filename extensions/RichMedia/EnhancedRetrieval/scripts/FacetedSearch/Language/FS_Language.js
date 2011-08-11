/*  Copyright 2011, ontoprise GmbH
*  This file is part of the FacetedSearch-Extension.
*
*   The FacetedSearch-Extension is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 3 of the License, or
*   (at your option) any later version.
*
*   The FacetedSearch-Extension is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*   along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * @file
 * @ingroup FacetedSearchScripts
 * @author: Thomas Schweitzer
 */

if (typeof FacetedSearch == "undefined") {
// Define the FacetedSearch  module	
	var FacetedSearch  = { 
		classes : {}
	};
}

/**
 * @class FSLanguage
 * This class is the base class of all language definition classes for the
 * faceted search.
 * 
 */
FacetedSearch.classes.FSLanguage = function () {
	var $ = jQuery;
	
	// The instance of this object
	var that = {};
	
	//--- Constants ---

	
	//--- Private members ---
	 
	//--- Getters/Setters ---
	
	//--- Public methods ---
	
	/**
	 * Constructor of class FSLanguage.
	 */
	function construct() {
	};
//	that.construct = construct;
	
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
	getMessage = function(id) {
		//that.mMessages must be set by the sub-classes
		var msg = that.mMessages[id] || '&lt;'+id+'&gt;';
		// Replace variables
		msg = msg.replace(/\$n/g,wgCanonicalNamespace); 
		msg = msg.replace(/\$p/g,wgPageName);
		msg = msg.replace(/\$t/g,wgTitle);
		msg = msg.replace(/\$u/g,wgUserName);
		msg = msg.replace(/\$s/g,wgServer);
		
		// Replace additional parameters
		for (var i = 1; i < arguments.length; ++i) {
			var pattern = "\\$" + i;
			var re = new RegExp(pattern,'g');
			msg = msg.replace(re, arguments[i]);
		}
		return msg;
	}
	that.getMessage = getMessage;
	
	//--- Private methods ---
	
	
	//--- Initialization of this object ---
	construct();

	return that;
	
}
