/*  Copyright 2008, ontoprise GmbH
 *  This file is part of the Data Import-Extension.
 *
 *   The Data Import-Extension is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   The Data Import-Extension is distributed in the hope that it will be useful,
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

	updateTermImport : function(termImportName) {
		this.termImportName = termImportName;
		sajax_do_call('smwf_ti_update', 
			[termImportName], this.updateTermImportCallBack.bind(this));
	},
	
	updateTermImportCallBack : function(request) {
		if(request.responseText.substr(0,15) == "ERROR:gardening"){
			alert(request.responseText);
		} else {
			$('update-ti-' + this.termImportName).style.display = "none";
			$('updating-ti-' + this.termImportName).style.display = "block";
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
		var re = /\s*((\S+\s*)*)/;
		wsId = wsId.replace(re, "$1");
		document.getElementById("confirmButton"+wsId).style.display = "none";
		document.getElementById("confirmText"+wsId).childNodes[0].nodeValue = diLanguage.getMessage("smw_wwsr_confirmed");
	},
	
	displayWebServiceTab : function(){
		$('web-service-tab-content').style.display = "";
		$('term-import-tab-content').style.display = "none";
		$('web-service-tab').setAttribute('class', "ActiveTab");
		$('term-import-tab').setAttribute('class', "InactiveTab");
		$('web-service-tab').setAttribute('onmouseover','');
		$('web-service-tab').setAttribute('onmouseout','');
		$('term-import-tab').setAttribute('onmouseover','webServiceRepSpecial.highlightTab(event)');
	},
	
	displayTermImportTab : function(){
		$('web-service-tab-content').style.display = "none";
		$('term-import-tab-content').style.display = "";
		$('web-service-tab').setAttribute('class', "InactiveTab");
		$('term-import-tab').setAttribute('class', "ActiveTab");
		$('term-import-tab').setAttribute('onmouseover','');
		$('term-import-tab').setAttribute('onmouseout','');
		$('web-service-tab').setAttribute('onmouseover','webServiceRepSpecial.highlightTab(event)');
	},
	
	highlightTab : function(event){
		var node = Event.element(event);
		node.setAttribute('class', "InactiveHighlightedTab");
		node.setAttribute('onmouseout','webServiceRepSpecial.deHighlightTab(event)');
	},
	
	deHighlightTab : function(event){
		var node = Event.element(event);
		node.setAttribute('class', "InactiveTab");
	},
	
	/**
	 * this method deletes a WWSD
	 * 
	 * @param string wsId id of the webservice that has to be deleted
	 * 
	 */
	deleteWWSD : function(wsId){
		sajax_do_call("smwf_ws_deleteWWSD", [ wsId ], this.deleteWWSDCallBack.bind(this));
	},

	/**
	 * callback method for the deletion of a webservice
	 * 
	 */
	deleteWWSDCallBack : function(request) {
		var wsId = request.responseText;
		
		$("ws-row-"+wsId).style.display = "none";
	},
	
	/**
	 * this method deletes a term import definition
	 * 
	 * @param string tiName name of the term import that has to be deleted
	 * 
	 */
	deleteTermImport : function(tiName){
		sajax_do_call("smwf_ti_deleteTermImport", [ tiName ], this.deleteTermImportCallBack.bind(this));
	},
	
	/**
	 * callback method for the deletion of a webservice
	 * 
	 */
	deleteTermImportCallBack : function(request) {
		var tiName = request.responseText;
		
		$("ti-row-"+tiName).style.display = "none";
	}
}	

webServiceRepSpecial = new WebServiceRepositorySpecial();
