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

var $=$P;

var SRRuleWidget = Class.create();
SRRuleWidget.prototype = {
	initialize : function() {
		
	},
	
	renderWidgets: function() {
		var rulelist = new Array();
		
		var pendingIndicators = new Array();
		$$('.ruleWidget').each(function(w) { 
			var ruleID = w.getAttribute("ruleID");
			var ruletext = $(ruleID).textContent;
			var natives = $(ruleID).getAttribute("native");
			var width =  (w.getAttribute("width") != null ?  w.getAttribute("width") : 600);
	        var height = (w.getAttribute("height") != null ?  w.getAttribute("height") : 300);
	        //TODO: set size
	        
	        var o = { 'ruleID' : ruleID, 'ruletext' : ruletext, 'native':natives };
	      
	        rulelist.push(o);
	      
	       		    
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
				var active = ruletextNodes[i].getAttribute("active");
				var status = ruletextNodes[i].getAttribute("status");
				$$('.ruleWidget').each(function(w) { 
					var ruleID = w.getAttribute("ruleID");
					var wID = w.getAttribute("id");
					if (id == ruleID) {
						
						
						var html = ruletextNodes[i].textContent;
											
						if (type == null || type == "easyreadible") $(wID+"_easyreadible").innerHTML = html;
						else if (type == "stylized") $(wID+"_stylized").innerHTML = html;
						
						if (status == "invalid") { 
							$(wID+"_switch").innerHTML = '<option>'+gsrLanguage.getMessage('SR_INVALID_RULE')+'</option>';
							$(wID+"_switch").style.backgroundColor = "orange";
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
					'serializeRules', Object.toJSON(rulelist) ], callbackOnRequest
					.bind(this));
		}
	},
	
	selectMode: function(event) {
		var selectTag = Event.element(event);
		var selectedIndex = selectTag.selectedIndex;
		var ruleContentID = selectTag.parentNode.parentNode.getAttribute("id");
		
		var mode = selectTag.options[selectedIndex].getAttribute("mode");
		$$('.ruleSerialization').each(function(c) { if (c.getAttribute("id").indexOf(ruleContentID) == 0) c.hide(); });
		$(ruleContentID+"_"+mode).show();
	},
			
	changeRuleState : function(event, node, containingPage, ruleName, index) {

		var callbackOnChangeState = function(request) {
			pi.hide();
			var r = request.responseText;
			if (r == "true") {
				
				// toggle switch color
				if (selectedIndex == 0) {
					selectTag.style.backgroundColor = "lightgreen";
				} else {
					selectTag.style.backgroundColor = "red";
				}
				
			} else {
				alert(gsrLanguage.getMessage('SR_COULD_NOT_CHANGE_RULESTATE'));
				return;
			}
			
		}
		var selectTag = Event.element(event);
		var selectedIndex = selectTag.selectedIndex;
		var w = $('rule_content_'+index)
		var pi = new OBPendingIndicator(w);
		pi.show(w);
		sajax_do_call('smwf_sr_ChangeRuleState', [ containingPage, ruleName, (selectedIndex == 0) ], callbackOnChangeState.bind(this));
	}

}

window.sr_rulewidget = new SRRuleWidget();
Event.observe(window, 'load', sr_rulewidget.renderWidgets.bind(sr_rulewidget));