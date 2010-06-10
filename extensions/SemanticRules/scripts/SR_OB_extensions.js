/**
 * @file
 * @defgroup SR_OntologyBrowser extensions
 * @ingroup SemanticRules
 * 
 * @author: Kai Kühn / ontoprise / 2010
 * 
 */

var SRRuleActionListener = Class.create();
SRRuleActionListener.prototype = {
	initialize : function() {
		this.OB_rulesInitialized = false;
		this.OB_cachedRuleTree = null;
		selectionProvider.addListener(this, OB_TREETABCHANGELISTENER);
		selectionProvider.addListener(this, OB_FILTERTREE);
		selectionProvider.addListener(this, OB_FILTERBROWSING);
		selectionProvider.addListener(this, OB_RESET);
	},

	treeTabChanged : function(tabname) {
		if (tabname == 'ruleTree') {
			// hide instance and property view
			$('instanceContainer').hide();
			$('rightArrow').hide();
			$('relattributesContainer').hide();
			$('hideInstancesButton').hide();

			$('ruleContainer').show();
			this.initializeRootRules();
		} else {
			// show instance and property view
			$('instanceContainer').show();
			$('rightArrow').show();
			$('relattributesContainer').show();
			$('hideInstancesButton').show();

			$('ruleContainer').hide();
		}
	},

	filterBrowsing : function(tabname, filter) {

		var callbackOnSearchRequest = function(request) {
			OB_tree_pendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured

				return;
			}
			this.OB_cachedRuleTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			selectionProvider.fireBeforeRefresh();
			var subTree = sr_transformer.transformResultToHTML(request,
					$('ruleTree'), true);
			selectionProvider.fireRefresh();

		}
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('srf_sr_AccessRuleEndpoint', [
				'searchForRulesByFragment', filter ], callbackOnSearchRequest
				.bind(this));
	},

	reset : function(treeName) {
		if (treeName == 'ruleTree') {
			this.initializeRootRules(true);
		}
	},

	treeFilterTree : function(treeName, filter) {
		if (treeName == 'ruleTree') {
			this._filterTree(filter);
		}
	},

	changeRuleState : function(node, containingPage, ruleName) {

		var callbackOnChangeState = function(request) {
			OB_tree_pendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured

				return;
			}
			var state = node.getAttribute("state");
			node.setAttribute("state", state == 'active' ? 'inactive' : 'active');
			var img = $('ruleChangeSwitch').getAttribute("src");
			$('ruleChangeSwitch').setAttribute("src", state == 'active' ? img.replace("green-switch", "red-switch") : img.replace("red-switch", "green-switch") );
		}
		var state = node.getAttribute("state");
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwf_sr_ChangeRuleState', [ containingPage, ruleName, !(state == 'active') ], callbackOnChangeState.bind(this));
	},

	/**
	 * @protected
	 * 
	 * Filter tree to match given term(s)
	 * 
	 * @param tree
	 *            XML Tree to filter
	 * @param treeName
	 *            Tree ID
	 * @param filterStr
	 *            Whitespace separated filter string.
	 */
	_filterTree : function(filterStr) {
		var xmlDoc = GeneralXMLTools.createTreeViewDocument();

		var nodesFound = new Array();

		// generate filters
		var regex = new Array();
		var filterTerms = GeneralTools.splitSearchTerm(filterStr);
		for ( var i = 0, n = filterTerms.length; i < n; i++) {
			try {
				regex[i] = new RegExp(filterTerms[i], "i");
			} catch (e) {
				// happens when RegExp is invalid. Just do nothing in this case
				return;
			}
		}
		this._filterTree_(nodesFound, this.OB_cachedRuleTree.firstChild, 0,
				regex);

		for ( var i = 0; i < nodesFound.length; i++) {
			var branch = GeneralXMLTools.getAllParents(nodesFound[i]);
			GeneralXMLTools.addBranch(xmlDoc.firstChild, branch);
		}
		// transform xml and add to category tree DIV
		var rootElement = document.getElementById("ruleTree");
		selectionProvider.fireBeforeRefresh();
		sr_transformer.transformXMLToHTML(xmlDoc, rootElement, true);
		selectionProvider.fireRefresh();

	},

	/**
	 * @private
	 * 
	 * Selects all nodes whose title attribute match the given regex.
	 * 
	 * @param nodesFound
	 *            Empty array which takes the returned nodes
	 * @param node
	 *            Node to start with.
	 * @param count
	 *            internal index for node array (starts with 0)
	 * @param regex
	 *            The regular expression
	 */
	_filterTree_ : function(nodesFound, node, count, regex) {

		if (node.nodeType != 1)
			return count;
		var children = node.childNodes;

		if (children) {
			for ( var i = 0; i < children.length; i++) {

				count = this
						._filterTree_(nodesFound, children[i], count, regex);

			}
		}

		var title = node.getAttribute("title");
		if (title != null && GeneralTools.matchArrayOfRegExp(title, regex)) {
			nodesFound[count] = node;
			count++;

		}

		return count;
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
		// selectionProvider.fireSelectionChanged(null, null, SMW_RULE_NS,
		// null);
	},

	/**
	 * @public
	 * 
	 * Called when a rule has been selected. Do also expand the rule tree if
	 * necessary.
	 * 
	 * @param event
	 *            Event
	 * @param node
	 *            selected HTML node
	 * @param ruleID
	 *            unique ID of rule (in DOM-Tree)
	 * @param ruleURI
	 *            URI of the rule (in wiki)
	 */
	select : function(event, node, ruleID, ruleURI) {
		// alert("Rule-ID:" + ruleID + " Rule URI:" + ruleURI);
	var nextDIV = node.nextSibling;

	// find the next DIV
	while (nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}

	// check if node is already expanded and expand it if not
	if (!nextDIV.hasChildNodes() || nextDIV.style.display == 'none') {
		this.toggleExpand(event, node);
	}

	var callbackOnRuleRequest = function callbackOnRuleRequest(request) {
		OB_tree_pendingIndicator.hide();

		if (request.responseText.indexOf('error:') != -1) {
			// TODO: some error occured

			return;
		}
		selectionProvider.fireBeforeRefresh();
		var subTree = sr_transformer.transformResultToHTML(request,
				$('ruleList'));
		selectionProvider.fireRefresh();

	}

	sajax_do_call('srf_sr_AccessRuleEndpoint', [ 'getRule', ruleURI ],
			callbackOnRuleRequest.bind(this));
},

toggleExpand : function(event, node, tree) {
	// stop event propagation in Gecko and IE
	Event.stop(event);
	// Get the next tag (read the HTML source)
	var nextDIV = node.nextSibling;

	// find the next DIV
	while (nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}

	// Unfold the branch if it isn't visible
	if (nextDIV.style.display == 'none') {

		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
				node.childNodes.item(0).src = GeneralTools
						.getImgDirectory(node.childNodes.item(0).src)
						+ "minus.gif";
			}
		}

		// get name of category which is about to be expanded
		var xmlNodeName = node.getAttribute("title");
		var xmlNodeID = node.getAttribute("id");

		function callbackOnExpandForAjax(request) {
			OB_tree_pendingIndicator.hide();
			var parentNode = GeneralXMLTools.getNodeById(
					this.OB_cachedRuleTree.firstChild, xmlNodeID);

			if (request.responseText.indexOf('error:') != -1) {
				// hide expand button if category has no subcategories and mark
				// as leaf
				node.childNodes.item(0).style.visibility = 'hidden';
				parentNode.setAttribute("isLeaf", "true");

				return;
			}
			selectionProvider.fireBeforeRefresh();
			var subTree = sr_transformer
					.transformResultToHTML(request, nextDIV);
			selectionProvider.fireRefresh();
			GeneralXMLTools.importSubtree(parentNode, subTree.firstChild);

		}

		// if category has no child nodes, they will be requested
		if (!nextDIV.hasChildNodes()) {
			// call subtree hook
			OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);

			sajax_do_call('srf_sr_AccessRuleEndpoint', [ 'getDependantRules',
					xmlNodeName ], callbackOnExpandForAjax.bind(this));

		}

		Element.show(nextDIV);
		var parentNode = GeneralXMLTools.getNodeById(
				this.OB_cachedRuleTree.firstChild, xmlNodeID);
		parentNode.setAttribute("expanded", "true");

	}

	// Collapse the branch if it IS visible
	else {

		Element.hide(nextDIV);
		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
				node.childNodes.item(0).src = GeneralTools
						.getImgDirectory(node.childNodes.item(0).src)
						+ "plus.gif";
			}
			var xmlNodeName = node.getAttribute("title");
			var xmlNodeID = node.getAttribute("id");

			var parentNode = GeneralXMLTools.getNodeById(
					this.OB_cachedRuleTree.firstChild, xmlNodeID);
			parentNode.setAttribute("expanded", "false");

		}

	}
}
}

var ruleActionListener = new SRRuleActionListener();
var sr_transformer = new TreeTransformer(
		"/extensions/SemanticRules/skins/ruleTree.xslt");
sr_transformer.addLanguageProvider(function(id) {
	return gsrLanguage.getMessage(id, "user");
});