/*  Copyright 2010, ontoprise GmbH
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
 * @defgroup SR_OntologyBrowser extensions
 * @ingroup SemanticRules
 * 
 * @author: Kai K�hn / ontoprise / 2010
 * 
 */


var SRRuleWidget = Class.create();
SRRuleWidget.prototype = {
	initialize : function() {
		
	},
	
	renderWidgets: function() {
		var rulelist = "";
		var isfirst = true;
		var pendingIndicators = new Array();
		$$('.ruleWidget').each(function(w) { 
			var ruleID = w.getAttribute("ruleID");
			var ruletext = $(ruleID).textContent;
			var native = $(ruleID).getAttribute("native");
			var width =  (w.getAttribute("width") != null ?  w.getAttribute("width") : 600);
	        var height = (w.getAttribute("height") != null ?  w.getAttribute("height") : 300);
	        //TODO: set size
	        
	        var o = { 'ruleID' : ruleID, 'ruletext' : ruletext, 'native':native };
	      
	        rulelist += isfirst ? Object.toJSON(o) : "##"+Object.toJSON(o);
	        if (isfirst) isfirst = false;
	       		    
		});
		
		var callbackOnRequest = function(request) {
			pendingIndicators.each(function(pi) { 
				pi.hide();
			});
			if (request.status != 200) {
				// ignore
				return;
			}
			var xmlDoc = GeneralXMLTools.createDocumentFromString(request.responseText);
			var ruletextNodes = xmlDoc.getElementsByTagName("ruletext");
			for(var i = 0; i < ruletextNodes.length; i++) {
			
				var id = ruletextNodes[i].getAttribute("id");
				var type = ruletextNodes[i].getAttribute("type");
				var status = ruletextNodes[i].getAttribute("status");
				$$('.ruleWidget').each(function(w) { 
					var ruleID = w.getAttribute("ruleID");
					var wID = w.getAttribute("id");
					if (id == ruleID) {
						
						// escape html
						var html = ruletextNodes[i].textContent;
						html = html.replace(/</g, "&lt;");
						html = html.replace(/>/g, "&gt;");
						
						if (type == "easyreadible") $(wID+"_easyreadible").innerHTML = html;
						else if (type == "stylized") $(wID+"_stylized").innerHTML = html;
						
						if (status == "invalid") { 
							$(wID+"_status").innerHTML = gsrLanguage.getMessage('SR_INVALID_RULE');
							$(wID+"_status").style.color = "orange";
						}
					}
				});
			}
		}
		
		$$('.ruleWidget').each(function(w) {
			var pi = new OBPendingIndicator(w);
			pendingIndicators.push(pi);
			pi.show(w);
		});
		
		if (rulelist != '') {	sajax_do_call('srf_sr_AccessRuleEndpoint', [
					'serializeRules', rulelist ], callbackOnRequest
					.bind(this));
		}
	},
	
	selectMode: function(event) {
		var selectTag = Event.element(event);
		var selectedIndex = selectTag.selectedIndex;
		var ruleContentID = selectTag.parentNode.getAttribute("id");
		
		var mode = selectTag.options[selectedIndex].getAttribute("mode");
		$$('.ruleSerialization').each(function(c) { if (c.getAttribute("id").indexOf(ruleContentID) == 0) c.hide(); });
		$(ruleContentID+"_"+mode).show();
	},
	
	escapeHTML: function(html) {
		html = html.replace(/</g, "&lt;");
		html = html.replace(/>/g, "&gt;");
		return html;
	}

}

var sr_rulewidget = new SRRuleWidget();
Event.observe(window, 'load', sr_rulewidget.renderWidgets.bind(sr_rulewidget));