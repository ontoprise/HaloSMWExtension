/**
 * @file
 * @defgroup SR_OntologyBrowser extensions
 * @ingroup SemanticRules
 * 
 * @author: Kai Kühn / ontoprise / 2010
 * 
 */

var SROntologyBrowserExtensions = Class.create();
SROntologyBrowserExtensions.prototype = {

	initialize : function() {
		this.OB_rulesInitialized = false;
		this.OB_cachedRuleTree = null;
	},

	switchTreeComponent : function(event, showWhichTree, noInitialize) {
		globalActionListener.switchTreeComponent(event, showWhichTree,
				noInitialize);

		if (!noInitialize) {
			if (showWhichTree == 'ruleTree') {
				this.initializeRootRules();
			}
		}
	},

	initializeRootRules : function(force) {
		if (!this.OB_rulesInitialized || force) {
			OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
			sajax_do_call('srf_sr_AccessRuleEndpoint', [ 'getRootRules', '' ],
					this.initializeRootRulesCallback.bind(this));
		}
	},

	initializeRootRulesCallback : function(request) {
		OB_tree_pendingIndicator.hide();
		if (request.status != 200) {
			alert("Error: " + request.status + " " + request.statusText + ": "
					+ request.responseText);
			return;
		}

		this.OB_rulesInitialized = true;

		var rootElement = $("ruleTree");

		// parse root category xml and transform it to HTML
		this.OB_cachedRuleTree = GeneralXMLTools
				.createDocumentFromString(request.responseText);

		selectionProvider.fireBeforeRefresh();
		sr_transformer.transformXMLToHTML(this.OB_cachedRuleTree, rootElement,
				true);
		selectionProvider.fireRefresh();
		//selectionProvider.fireSelectionChanged(null, null, SMW_RULE_NS, null);
	}
}

var srDataAcess = new SROntologyBrowserExtensions();
var sr_transformer = new TreeTransformer("/extensions/SemanticRules/skins/ruleTree.xslt");