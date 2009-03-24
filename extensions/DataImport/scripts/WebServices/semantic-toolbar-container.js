/*   The Data Import-Extension is free software; you can redistribute it and/or modify
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
 *   along with this program.  If not, see <http:// www.gnu.org/licenses/>.
 */

/**
 * This file adds a container for inserting a web service into an article
 * to the semantic toolbar
 * 
 * @author Ingo Steinbauer
 * 
 */

var WebServiceToolBar = Class.create();

WebServiceToolBar.prototype = {

	initialize : function() {
		this.genTB = new GenericToolBar();
		this.toolbarContainer = null;
	},

	callme : function(event) {
		if (wgAction == "edit" && stb_control.isToolbarAvailable()) {
			this.wsContainer = stb_control.createDivContainer(WEBSERVICECONTAINER, 0);
			this.showToolbar();
			var params = document.URL.split("&");
			var wsSyn = "";
			for ( var i = 0; i < params.length; i++) {
				if (params[i].indexOf("wsSyn=") == 0) {
					wsSyn = params[i].substr(params[i].indexOf("=") + 1);
				} else if (params[i].indexOf("wsSynS=") == 0) {
					wsSynS = params[i].substr(params[i].indexOf("=") + 1);
				} else if (params[i].indexOf("wsSynE=") == 0) {
					wsSynE = params[i].substr(params[i].indexOf("=") + 1);
				}
			}
			
			if(wsSyn.length > 0){
				$("wpTextbox1").value = $("wpTextbox1").value.substring(0, wsSynS)
				+ unescape(wsSyn) + $("wpTextbox1").value.substring(wsSynE);
			}
			
		}
	},

	showToolbar : function() {
		// todo:use language file
		this.wsContainer.setHeadline("Web services");
		this.fill();
	},
	
	fill: function() {
		//todo:use language file
		this.wsContainer.setContent(this.createLinkToSpecialPage());
		this.wsContainer.contentChanged(true);
	},
	
	createLinkToSpecialPage : function(){
		var response = "<p onclick=\"wsToolBar.openSpecialPage()\" style=\"cursor: pointer\">Add web service call</p>";
		return response;	
	},
	
	openSpecialPage : function(){
		var url = wgArticlePath.replace(/\$1/, "Special:UseWebService");
		var startPos = $("wpTextbox1").selectionStart;
		var endPos = $("wpTextbox1").selectionEnd;
		url += "?url=" + escape(document.URL);
		url += "&wsSyn=" + escape("&ws& test") + "&wsSynS=" + startPos + "&wsSynE=" + endPos;
		window.location.href = url;
		//todo: handle two subsequent ws-adds -> the url contains several parameters
	}
	
	
};

var wsToolBar = new WebServiceToolBar();
Event.observe(window, 'load', wsToolBar.callme.bindAsEventListener(wsToolBar));