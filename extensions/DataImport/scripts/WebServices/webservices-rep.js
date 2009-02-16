/*  Copyright 2008, ontoprise GmbH
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
 * This file provides some methods for the special page webservice repository
 *
 * @author Ingo Steinbauer
 *
 */
var WebServiceRepositorySpecial = Class.create();

WebServiceRepositorySpecial.prototype = {
	initialize: function() {
		},
		
	/**
	 * this method initializes the update of the cache entriesof a webservice
	 * 
	 * @param string wsId id of the webservice that has to be updated
	 * 
	 */	
	updateCache : function(botId, wsId) {
		this.wsId = wsId.substr(8, wsId.length);
		sajax_do_call('smwf_ga_LaunchGardeningBot', 
				[botId, wsId, null, null], this.updateCacheCallBack.bind(this));
	},

	/**
	 * callback method for the update of cache entries
	 * 
	 * 
	 */
	updateCacheCallBack : function(request) {
		if(request.responseText.substr(0,15) == "ERROR:gardening"){
			alert(request.responseText);
		} else {
			$('update' + this.wsId).style.display = "none";
			$('updating' + this.wsId).style.display = "block";
		}
	},
	
	/**
	 * this method initializes the confirmation of a new webservice
	 * 
	 * @param string wsId id of the webservice that has to be confirmed
	 * 
	 */
	confirmWWSD : function(wsId){
		sajax_do_call("smwf_ws_confirmWWSD", [ wsId ], this.confirmWWSDCallBack.bind(this));
	},

	/**
	 * callback method for the confirmation of a webservice
	 * 
	 * 
	 */
	confirmWWSDCallBack : function(request) {
		var wsId = request.responseText;
		document.getElementById("confirmButton"+wsId).style.display = "none";
		document.getElementById("confirmText"+wsId).childNodes[0].nodeValue = "confirmed";
	}	
}	

webServiceRepSpecial = new WebServiceRepositorySpecial();
