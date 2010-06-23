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
		var idlist = "";
		var isfirst = true;
		var pendingIndicators = new Array();
		$$('.ruleWidget').each(function(w) { 
			var ruleID = w.getAttribute("ruleID");
			var width =  (w.getAttribute("width") != null ?  w.getAttribute("width") : 600);
	        var height = (w.getAttribute("height") != null ?  w.getAttribute("height") : 300);
	        //TODO: set size
	        
	        idlist += isfirst ? ruleID : "##"+ruleID;
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
				$$('.ruleWidget').each(function(w) { 
					var ruleID = w.getAttribute("ruleID");
					var wID = w.getAttribute("id");
					if (id == ruleID) {
						if (type == "easyreadible") $(wID+"_easyreadible").innerHTML = this.escapeHTML(ruletextNodes[i].textContent);
						else if (type == "stylized") $(wID+"_stylized").innerHTML = this.escapeHTML(ruletextNodes[i].textContent);
					}
				});
			}
		}
		
		$$('.ruleWidget').each(function(w) {
			var pi = new OBPendingIndicator(w);
			pendingIndicators.push(pi);
			pi.show(w);
		});
		
			sajax_do_call('srf_sr_AccessRuleEndpoint', [
					'serializeRules', idlist ], callbackOnRequest
					.bind(this));
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