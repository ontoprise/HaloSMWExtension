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
		$$('.ruleWidget').each(function(w) { 
		    new Ext.TabPanel({
		        renderTo : "rule_content",
		        activeTab : 0,
		        width :  (w.getAttribute("width") != null ?  w.getAttribute("width") : 600),
		        height : (w.getAttribute("height") != null ?  w.getAttribute("height") : 300),
		        plain : true,
		        defaults : {autoScroll: true},
		        items : [{ title: "Test", contentEl : "testrule"}] 
		    });
		});
	}

}

var sr_rw = new SRRuleWidget();
Event.observe(window, 'load', sr_rw.renderWidgets.bind(sr_rw));