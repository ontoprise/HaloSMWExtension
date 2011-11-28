/*
 * Copyright (C) Vulcan Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program.If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @file
 * @defgroup SR_OntologyBrowser extensions
 * @ingroup SemanticRules
 * 
 * @author: Kai K�hn
 * 
 */

var $=$P;
var SRRuleActionListener = Class.create();
SRRuleActionListener.prototype = {
	initialize : function() {
		this.OB_rulesInitialized = false;
		this.OB_cachedRuleTree = null;
		selectionProvider.addListener(this, OB_TREETABCHANGELISTENER);
		selectionProvider.addListener(this, OB_FILTERTREE);
		selectionProvider.addListener(this, OB_RESET);
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);

	},

	registerPendingIndicator : function() {
		this.rulePendingIndicator = new OBPendingIndicator($('ruleList'));
	},

	selectionChanged : function(id, title, ns, node) {
		if (ns == SMW_PROPERTY_NS || ns == SMW_CATEGORY_NS) {
			this.hideRuleContainer();
		}
		if (ns == 300) {
			// 300 means rule, although it is not a namespace
			GeneralBrowserTools.toggleHighlighting(this.oldNode, node);
			this.oldNode = node;
		}
	},

	treeTabChanged : function(tabname) {
		if (tabname == 'ruleTree') {
			this.showRuleContainer();
			this.initializeRootRules();
		} else {
			this.hideRuleContainer();
		}
	},

	showRuleContainer : function() {
		// hide instance and property view
		$('instanceContainer').hide();
		$('rightArrow').hide();
		$('relattributesContainer').hide();
		$('hideInstancesButton').hide();
		$('propertyRangeSpan').hide();

		var ruleContainer = $('ruleContainer');
		if (ruleContainer)
			ruleContainer.show();
	},

	hideRuleContainer : function() {
		// show instance and property view
		if ($("hideInstancesButton").getAttribute("hidden") != "true") {
			$('instanceContainer').show();
		}
		$('rightArrow').show();
		$('relattributesContainer').show();
		$('hideInstancesButton').show();
		$('propertyRangeSpan').show();

		var ruleContainer = $('ruleContainer');
		if (ruleContainer)
			ruleContainer.hide();
	},

	filterBrowsing : function(tabname, filter) {

		var callbackOnSearchRequest = function(request) {
			OB_tree_pendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured
				alert("Error: " + request.status + " " + request.statusText
						+ ": " + request.responseText);
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
				'searchForRulesByFragment', filter + "##true" ],
				callbackOnSearchRequest.bind(this));
	},

	reset : function(treeName) {
		if (treeName == 'ruleTree') {
			this.initializeRootRules(true);
			with($('ruleList')) while(firstChild) removeChild(firstChild);
		}
	},

	treeFilterTree : function(treeName, filter) {
		if (treeName == 'ruleTree') {
			this._filterTree(filter);
		}
	},

	changeRuleState : function(event, node, containingPage, ruleName) {

		var callbackOnChangeState = function(request) {
			OB_tree_pendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured

				return;
			}
			
			if (this.oldNode == null) {
				// this should not happen. If though, tell user to reset manually
				alert("Please reset view. Something is wrong here.");
				return;
			}
			
			if (selectedIndex == 0) {
				// means rule was activated
				var rule_inactive_icon = $(this.oldNode.getAttribute('id')+"_inactive_icon");
				if (rule_inactive_icon) rule_inactive_icon.hide();
			} else {
				// rule was deactivated, so show icon
				var rule_inactive_icon = $(this.oldNode.getAttribute('id')+"_inactive_icon");
				if (rule_inactive_icon) { 
					rule_inactive_icon.show();
					return;
				}
				
				// or if it does not exist create it
				var newNode = document.createElement("img");
				newNode.setAttribute("src", wgScriptPath+"/extensions/SemanticRules/skins/images/rules_inactive.gif")
				newNode.setAttribute("id", this.oldNode.getAttribute('id')+"_inactive_icon");
				newNode.setAttribute("title", gsrLanguage.getMessage('SR_RULE_INACTIVE'));
				this.oldNode.parentNode.insertBefore(newNode, this.oldNode.nextSibling);
				
			}
		}
		var selectTag = Event.element(event);
		var selectedIndex = selectTag.selectedIndex;
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwf_sr_ChangeRuleState', [ containingPage, ruleName,
				(selectedIndex == 0) ], callbackOnChangeState.bind(this));
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
			sajax_do_call('srf_sr_AccessRuleEndpoint', [ 'getRootRules', obAdvancedOptions.getBundle() ],
					this.initializeRootRulesCallback.bind(this));
		}
	},

	initializeRootRulesCallback : function(request) {
		OB_tree_pendingIndicator.hide();
		if (request.status != 200) {
			if (request.status == 404) {
				alert("Could not connect to TSC. Rule endpoint started? Check your selected reasoner at the TSC!");
			} else {
				alert("Error: " + request.status + " " + request.statusText + ": "
						+ request.responseText);
			}
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
			this.rulePendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured

				return;
			}
			selectionProvider.fireBeforeRefresh();
			var subTree = sr_transformer.transformResultToHTML(request,
					$('ruleList'));
			selectionProvider.fireRefresh();
			
			// hack for FF. Its XSLT proc escapes HTML always (not switchable).
			if (OB_bd.isGecko) {
				$$('.ruleSerialization').each(function(s) { 
					var html = s.textContent;
					s.innerHTML = html;
				});
			}

		}
		selectionProvider.fireSelectionChanged(ruleURI, null, 300, node);
		this.rulePendingIndicator.show($('ruleList'));
		sajax_do_call('srf_sr_AccessRuleEndpoint', [ 'getRule', ruleURI ],
				callbackOnRuleRequest.bind(this));
	},

	selectFromExternal : function(node, ruleURI) {
		var callbackOnRuleRequest = function callbackOnRuleRequest(request) {
			
			if ($('ruleContainer') == null) {
				// no right to watch rules
				alert(gsrLanguage.getMessage('SR_RULE_ACCESS_NOT_ALLOWED'));
				return;
			}
			
			this.rulePendingIndicator.hide();

			if (request.responseText.indexOf('error:') != -1) {
				// TODO: some error occured

				return;
			}

			selectionProvider.fireBeforeRefresh();
			var subTree = sr_transformer.transformResultToHTML(request,
					$('ruleList'));
			selectionProvider.fireRefresh();
			
			// hack for FF. Its XSLT proc escapes HTML always (not switchable).
			if (OB_bd.isGecko) {
				$$('.ruleSerialization').each(function(s) { 
					var html = s.textContent;
					s.innerHTML = html;
				});
			}
		}
		this.showRuleContainer();
		this.rulePendingIndicator.show($('ruleList'));
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

window.ruleActionListener = new SRRuleActionListener();
window.sr_transformer = new TreeTransformer(
		"/extensions/SemanticRules/skins/ruleTree.xslt");
sr_transformer.addLanguageProvider(function(id) {
	return gsrLanguage.getMessage(id, "user");
});

Event.observe(window, 'load', ruleActionListener.registerPendingIndicator
		.bind(ruleActionListener));
