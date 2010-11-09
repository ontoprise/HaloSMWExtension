/*  Copyright 2007, ontoprise GmbH
 *   Author: Kai K�hn
 *   This file is part of the halo-Extension.
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
 * @ingroup SMWHaloSpecials
 * @ingroup SMWHaloOntologyBrowser
 * 
 * TreeView actions 
 *  
 * One listener object for each type entity in each container.
 */

/**
 * Global selection flow arrow states 0 = left to right 1 = right to left
 */
var OB_LEFT_ARROW = 0;
var OB_RIGHT_ARROW = 0;

// Logging on close does not work, because window shuts down. What to do?
// window.onbeforeunload = function() { smwhgLogger.log("", "OB","close"); };
/**
 * 'Abstract' base class for OntologyBrowser trees
 * 
 * Features:
 * 
 * 1. Expansion and collapsing of nodes 2. Reload of tree partitions (i.e. a
 * segment of a tree level) 3. Filtering of nodes on root level. 4. Filtering of
 * nodes showing their place in the hierarchy.
 * 
 */
var OBTreeActionListener = Class.create();
OBTreeActionListener.prototype = {
	initialize : function() {
		this.OB_currentFilter = null;

	},

	/**
	 * @abstract
	 * 
	 * Will be implemented in subclasses.
	 */
	selectionChanged : function(id, title, ns, node) {

	},
	/**
	 * @protected
	 * 
	 * Toggles a tree node expansion.
	 * 
	 * @param event
	 *            Event which triggered expansion (normally onClick).
	 * @param node
	 *            Node on which event was triggered.
	 * @param tree
	 *            Cached tree to update.
	 * @param accessFunc
	 *            Function which returns children needed for expansion. It has
	 *            the following signature: accessFunc(xmlNodeID, xmlNodeName,
	 *            callbackOnExpandForAjax, callBackForCache);
	 * 
	 * @return
	 */
	_toggleExpand : function(event, node, tree, accessFunc) {

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
						dataAccess.OB_currentlyDisplayedTree.firstChild,
						xmlNodeID);
				var parentNodeInCache = GeneralXMLTools.getNodeById(
						tree.firstChild, xmlNodeID);
				if (request.responseText.indexOf('noResult') != -1) {
					// hide expand button if category has no subcategories and
					// mark as leaf
					node.childNodes.item(0).style.visibility = 'hidden';
					parentNode.setAttribute("isLeaf", "true");
					parentNodeInCache.setAttribute("isLeaf", "true");

					return;
				}
				selectionProvider.fireBeforeRefresh();
				var subTree = transformer.transformResultToHTML(request,
						nextDIV);
				selectionProvider.fireRefresh();
				GeneralXMLTools.importSubtree(parentNode, subTree.firstChild);
				GeneralXMLTools.importSubtree(parentNodeInCache,
						subTree.firstChild);
			}

			function callBackForCache(xmlDoc) {
				transformer.transformXMLToHTML(xmlDoc, nextDIV, false);
				Element.show(nextDIV);
			}

			// if category has no child nodes, they will be requested
			if (!nextDIV.hasChildNodes()) {
				// call subtree hook
				OB_tree_pendingIndicator
						.show(globalActionListener.activeTreeName);
				accessFunc(xmlNodeID, xmlNodeName, callbackOnExpandForAjax,
						callBackForCache);

			}

			Element.show(nextDIV);
			var parentNode = GeneralXMLTools.getNodeById(
					dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
			parentNode.setAttribute("expanded", "true");

			var parentNodeInCache = GeneralXMLTools.getNodeById(
					tree.firstChild, xmlNodeID);
			parentNodeInCache.setAttribute("expanded", "true");
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
						dataAccess.OB_currentlyDisplayedTree.firstChild,
						xmlNodeID);
				parentNode.setAttribute("expanded", "false");

				var parentNodeInCache = GeneralXMLTools.getNodeById(
						tree.firstChild, xmlNodeID);
				parentNodeInCache.setAttribute("expanded", "false");
			}

		}
	},

	/**
	 * @protected
	 * 
	 * Requests the next partition of a tree level.
	 * 
	 * @param e
	 *            Event which triggered selection
	 * @param partitionNodeHTML
	 *            Selected partition node in DOM.
	 * @param tree
	 *            XML Tree associated with selection
	 * @param accessFunc
	 *            Function to obtain next partition
	 * @param treeName
	 *            Tree ID to update (categoryTree/propertyTree)
	 * @param calledOnFinish
	 *            Function which is called when tree has been updated.
	 */
	_selectNextPartition : function(e, partitionNodeHTML, tree, accessFunc,
			treeName, calledOnFinish) {

		function selectNextPartitionCallback(request) {
			// TODO: check if empty and do nothing in this case.
			OB_tree_pendingIndicator.hide();
			var xmlFragmentForCache = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			var xmlFragmentForDisplayTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);

			// is it on the root level or not?
			var isRootLevel = parentOfChildrenToReplaceInCache.tagName == 'result';

			// determine HTML node to replace
			var htmlNodeToReplace;
			if (isRootLevel) {
				htmlNodeToReplace = document.getElementById(treeName);
				// adjust xml structure, i.e. replace whole tree
				tree = xmlFragmentForCache;
				dataAccess.OB_currentlyDisplayedTree = xmlFragmentForDisplayTree;
			} else {
				// get element node with children to replace
				// one of nextSiblings is DIV element
				htmlNodeToReplace = GeneralBrowserTools.nextDIV(document
						.getElementById(idOfChildrenToReplace));

				// adjust XML structure
				GeneralXMLTools
						.removeAllChildNodes(parentOfChildrenToReplaceInCache);
				GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache,
						xmlFragmentForCache.firstChild);

				GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
				GeneralXMLTools.importSubtree(parentOfChildrenToReplace,
						xmlFragmentForDisplayTree.firstChild);
			}
			// transform structure to HTML
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(xmlFragmentForDisplayTree,
					htmlNodeToReplace, isRootLevel);
			selectionProvider.fireRefresh();
			calledOnFinish(tree);
		}
		// Identify partition node in XML
		var id = partitionNodeHTML.getAttribute("id");
		var partition = partitionNodeHTML.getAttribute("partitionnum");
		var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
		var partitionNode = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, id);

		// Identify parent of partition node
		var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
		var parentOfChildrenToReplace = partitionNode.parentNode;
		var idOfChildrenToReplace = parentOfChildrenToReplace
				.getAttribute("id");

		// ask for next partition

		partition++;

		var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		accessFunc(isRootLevel, partition, parentOfChildrenToReplace
				.getAttribute("title"), selectNextPartitionCallback);

	},

	/**
	 * @protected
	 * 
	 * Requests the previous partition of a tree level.
	 * 
	 * @param e
	 *            Event which triggered selection
	 * @param partitionNodeHTML
	 *            Selected partition node in DOM.
	 * @param tree
	 *            XML Tree associated with selection
	 * @param accessFunc
	 *            Function to obtain next partition
	 * @param treeName
	 *            Tree ID to update (categoryTree/propertyTree)
	 * @param calledOnFinish
	 *            Function which is called when tree has been updated.
	 */
	_selectPreviousPartition : function(e, partitionNodeHTML, tree, accessFunc,
			treeName, calledOnFinish) {

		function selectPreviousPartitionCallback(request) {
			// TODO: check if empty and do nothing in this case.
			OB_tree_pendingIndicator.hide();
			var xmlFragmentForCache = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			var xmlFragmentForDisplayTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);

			// is it on the root level or not?
			var isRootLevel = parentOfChildrenToReplaceInCache.tagName == 'result';

			// determine HTML node to replace
			var htmlNodeToReplace;
			if (isRootLevel) {
				htmlNodeToReplace = document.getElementById(treeName);
				// adjust xml structure, i.e. replace whole tree
				tree = xmlFragmentForCache;
				dataAccess.OB_currentlyDisplayedTree = xmlFragmentForDisplayTree;
			} else {
				// get element node with children to replace
				// nextSibling is DIV element
				htmlNodeToReplace = GeneralBrowserTools.nextDIV(document
						.getElementById(idOfChildrenToReplace));

				// adjust XML structure
				GeneralXMLTools
						.removeAllChildNodes(parentOfChildrenToReplaceInCache);
				GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache,
						xmlFragmentForCache.firstChild);

				GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
				GeneralXMLTools.importSubtree(parentOfChildrenToReplace,
						xmlFragmentForDisplayTree.firstChild);
			}
			// transform structure to HTML
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(xmlFragmentForDisplayTree,
					htmlNodeToReplace, isRootLevel);
			selectionProvider.fireRefresh();
			calledOnFinish(tree);
		}
		// Identify partition node in XML
		var id = partitionNodeHTML.getAttribute("id");
		var partition = partitionNodeHTML.getAttribute("partitionnum");
		var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
		var partitionNode = GeneralXMLTools.getNodeById(
				dataAccess.OB_currentlyDisplayedTree, id);

		// Identify parent of partition node
		var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
		var parentOfChildrenToReplace = partitionNode.parentNode;
		var idOfChildrenToReplace = parentOfChildrenToReplace
				.getAttribute("id");

		// ask for previous partition, stop if already 0
		if (partition == 0) {
			return;
		}
		partition--;

		var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		accessFunc(isRootLevel, partition, parentOfChildrenToReplace
				.getAttribute("title"), selectPreviousPartitionCallback);

	},

	/**
	 * @protected
	 * 
	 * Filter tree to match given term(s)
	 * 
	 * @param e
	 *            Event
	 * @param tree
	 *            XML Tree to filter
	 * @param treeName
	 *            Tree ID
	 * @param filterStr
	 *            Whitespace separated filter string.
	 */
	_filterTree : function(e, tree, treeName, filterStr) {
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
		this._filterTree_(nodesFound, tree.firstChild, 0, regex);

		for ( var i = 0; i < nodesFound.length; i++) {
			var branch = GeneralXMLTools.getAllParents(nodesFound[i]);
			GeneralXMLTools.addBranch(xmlDoc.firstChild, branch);
		}
		// transform xml and add to category tree DIV
		var rootElement = document.getElementById(treeName);
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(xmlDoc, rootElement, true);
		selectionProvider.fireRefresh();
		if (treeName == 'categoryTree') {
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS,
					null);
		} else if (treeName == 'propertyTree') {
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS,
					null);
		}
		dataAccess.OB_currentlyDisplayedTree = xmlDoc;
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

		var children = node.childNodes;

		if (children) {
			for ( var i = 0; i < children.length; i++) {
				if (children[i].tagName == 'gissues')
					continue;
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

	_filterRootLevel : function(e, tree, treeName) {
		if (OB_bd.isIE && e.type != 'click' && e.keyCode != 13) {
			return;
		}
		if (OB_bd.isGeckoOrOpera && e.type != 'click' && e.which != 13) {
			return;
		}

		xmlDoc = GeneralXMLTools.createTreeViewDocument();

		var inputs = document.getElementsByTagName("input");
		this.OB_currentFilter = inputs[0].value;
		// iterate all root categories identifying those which match user input
		// prefix
		var rootCats = tree.firstChild.childNodes;
		for ( var i = 0; i < rootCats.length; i++) {

			if (rootCats[i].getAttribute("title")) {
				// filter root nodes which have a title
				if (rootCats[i].getAttribute("title").indexOf(inputs[0].value) != -1) {
					if (rootCats[i].childNodes.length > 0)
						rootCats[i].setAttribute("expanded", "true");

					// add matching root category nodes
					if (OB_bd.isGeckoOrOpera) {
						xmlDoc.firstChild.appendChild(document.importNode(
								rootCats[i], true));
					} else if (OB_bd.isIE) {
						xmlDoc.firstChild.appendChild(rootCats[i]
								.cloneNode(true));
					}
				}
			} else {
				// copy all other nodes
				if (OB_bd.isGeckoOrOpera) {
					xmlDoc.firstChild.appendChild(document.importNode(
							rootCats[i], true));
				} else if (OB_bd.isIE) {
					xmlDoc.firstChild.appendChild(rootCats[i].cloneNode(true));
				}
			}
		}

		// transform xml and add to category tree DIV
		var rootElement = document.getElementById(treeName);
		transformer.transformXMLToHTML(xmlDoc, rootElement, true);
		dataAccess.OB_currentlyDisplayedTree = xmlDoc;
	}

}

/**
 * Action Listener for categories
 */
var OBCategoryTreeActionListener = Class.create();
OBCategoryTreeActionListener.prototype = Object
		.extend(
				new OBTreeActionListener(),
				{
					initialize : function() {

						this.selectedCategory = null;
						this.selectedCategoryID = null;
						this.oldSelectedNode = null;
						this.selectedCategoryURI = null;
						this.draggableCategories = [];
						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
						selectionProvider.addListener(this, OB_REFRESHLISTENER);
						selectionProvider.addListener(this,
								OB_BEFOREREFRESHLISTENER);

						this.ignoreNextSelection = false;
						Draggables.addObserver(this);
						Droppables.add('categoryTreeSwitch', {
							accept : 'concept',
							hoverclass : 'dragHover',
							onDrop : this.onDrop.bind(this)
						});
					},

					toggleExpand : function(event, node, folderCode) {
						this._toggleExpand(event, node,
								dataAccess.OB_cachedCategoryTree,
								dataAccess.getCategorySubTree.bind(dataAccess));
					},

					navigateToEntity : function(event, node, categoryName,
							editmode) {
						smwhgLogger.log(categoryName, "OB", "inspect_entity");
						GeneralBrowserTools.navigateToPage(gLanguage
								.getMessage('CATEGORY_NS_WOC'), categoryName,
								editmode);
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_CATEGORY_NS) {

							this.selectedCategory = title;
							this.selectedCategoryID = id;
							this.oldSelectedNode = GeneralBrowserTools
									.toggleHighlighting(this.oldSelectedNode,
											node);

						}
					},

					beforeRefresh : function() {
						if (wgUserGroups == null
								|| (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups
										.indexOf('gardener') == -1)) {

							return;
						}
						if (OB_bd.isIE) {
							return; // no DnD in IE
						}
						this.draggableCategories.each(function(c) {
							c.destroy();

						});
						$$('a.concept').each(function(c) {
							Droppables.remove(c.getAttribute('id'));
						});
						this.draggableCategories = [];
					},

					refresh : function() {
						if (wgUserGroups == null
								|| (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups
										.indexOf('gardener') == -1)) {
							// do not allow dragging, when user is no sysop or
							// gardener
							return;
						}
						if (OB_bd.isIE) {
							return; // do not activate DnD in IE, because
							// scriptaculous is very buggy here
						}
						function addDragAndDrop(c) {
							var d = new Draggable(c.getAttribute('id'), {
								revert : true,
								ghosting : true
							});
							this.draggableCategories.push(d);
							Droppables.add(c.getAttribute('id'), {
								accept : 'concept',
								hoverclass : 'dragHover',
								onDrop : onDrop_bind
							});
						}
						var addDragAndDrop_bind = addDragAndDrop.bind(this);
						var onDrop_bind = this.onDrop.bind(this);
						$$('a.concept').each(addDragAndDrop_bind);

					},

					onStart : function(eventName, draggable, event) {
						if (draggable.element.hasClassName('concept')) {
							this.ignoreNextSelection = true;
						}
					},

					onDrop : function(dragElement, dropElement, event) {
						var draggedCategoryID = dragElement.getAttribute('id');
						var droppedCategoryID = dropElement.getAttribute('id');
						// alert('Dropped on: '+droppedCategoryID+" from:
						// "+draggedCategoryID);
						ontologyTools.moveCategory(draggedCategoryID,
								droppedCategoryID);
					},

					showSubMenu : function(commandID) {
						if (this.selectedCategory == null) {
							alert(gLanguage.getMessage('OB_SELECT_CATEGORY'));
							return;
						}

						obCategoryMenuProvider.showContent(commandID,
								'categoryTree');
					},

					// ---- Selection methods. Called when the entity is
					// selected ---------------------

					/**
					 * @public
					 * 
					 * Called when a category has been selected. Do also expand
					 * the category tree if necessary.
					 * 
					 * @param event
					 *            Event
					 * @param node
					 *            selected HTML node
					 * @param categoryID
					 *            unique ID of category
					 * @param categoryName
					 *            Title of category
					 */
					select : function(event, node, categoryID, categoryName) {

						if (this.ignoreNextSelection && OB_bd.isGecko) {
							this.ignoreNextSelection = false;
							return;
						}
						var e = GeneralTools.getEvent(event);

						// if Ctrl is pressed: navigation mode
						if (e["ctrlKey"]) {
							GeneralBrowserTools.navigateToPage(gLanguage
									.getMessage('CATEGORY_NS_WOC'),
									categoryName);
						} else {

							var nextDIV = node.nextSibling;

							// find the next DIV
							while (nextDIV.nodeName != "DIV") {
								nextDIV = nextDIV.nextSibling;
							}

							// fire selection event
							selectionProvider.fireSelectionChanged(categoryID,
									categoryName, SMW_CATEGORY_NS, node);
							this.selectedCategoryURI = node.getAttribute("uri");
							selectionProvider.fireSelectedTripleChanged(null,
									"rdf:type", this.selectedCategoryURI);

							// check if node is already expanded and expand it
							// if not
							if (!nextDIV.hasChildNodes()
									|| nextDIV.style.display == 'none') {
								this.toggleExpand(event, node, categoryID);
							}

							var instanceDIV = document
									.getElementById("instanceList");
							var relattDIV = document
									.getElementById("relattributes");

							// adjust relatt table headings
							if (!$("relattRangeType").visible()) {
								$("relattRangeType").show();
								$("relattValues").hide();
							}

							smwhgLogger.log(categoryName, "OB", "clicked");

							// callback for instances of a category
							function callbackOnCategorySelect(request) {
								OB_instance_pendingIndicator.hide();
								if (instanceDIV.firstChild) {
									GeneralBrowserTools
											.purge(instanceDIV.firstChild);
									instanceDIV
											.removeChild(instanceDIV.firstChild);
								}

								var xmlFragmentInstanceList = GeneralXMLTools
										.createDocumentFromString(request.responseText);
								dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
								selectionProvider.fireBeforeRefresh();
								transformer.transformResultToHTML(request,
										instanceDIV, true);
								selectionProvider.fireRefresh();
								// de-select instance list
								selectionProvider.fireSelectionChanged(null,
										null, SMW_INSTANCE_NS, null);
							}

							// callback for properties of a category
							function callbackOnCategorySelect2(request) {
								OB_relatt_pendingIndicator.hide();
								if (relattDIV.firstChild) {
									GeneralBrowserTools
											.purge(relattDIV.firstChild);
									relattDIV.removeChild(relattDIV.firstChild);
								}
								var xmlFragmentPropertyList = GeneralXMLTools
										.createDocumentFromString(request.responseText);
								dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
								selectionProvider.fireBeforeRefresh();
								transformer.transformResultToHTML(request,
										relattDIV);
								selectionProvider.fireRefresh();
								selectionProvider.fireSelectionChanged(null,
										null, SMW_PROPERTY_NS, null);
							}

							if (OB_LEFT_ARROW == 0) {
								if ($("hideInstancesButton").getAttribute(
										"hidden") != "true") {
									OB_instance_pendingIndicator.show();
									dataAccess.getInstances(categoryName, 0,
											callbackOnCategorySelect);
								}
							}
							if (OB_RIGHT_ARROW == 0) {
								OB_relatt_pendingIndicator.show();
								var onlyDirect = $('directPropertySwitch').checked;
								var dIndex = $('showForRange').checked ? '_2'
										: '_1';
								dataAccess.getProperties(categoryName,
										onlyDirect, dIndex,
										callbackOnCategorySelect2);
							}

						}
					},

					selectNextPartition : function(e, htmlNode) {

						function calledOnFinish(tree) {
							dataAccess.OB_cachedCategoryTree = tree;
							selectionProvider.fireSelectionChanged(null, null,
									SMW_CATEGORY_NS, null);
						}
						this._selectNextPartition(e, htmlNode,
								dataAccess.OB_cachedCategoryTree,
								dataAccess.getCategoryPartition
										.bind(dataAccess), "categoryTree",
								calledOnFinish);

					},

					selectPreviousPartition : function(e, htmlNode) {

						function calledOnFinish(tree) {
							dataAccess.OB_cachedCategoryTree = tree;
							selectionProvider.fireSelectionChanged(null, null,
									SMW_CATEGORY_NS, null);
						}
						this._selectPreviousPartition(e, htmlNode,
								dataAccess.OB_cachedCategoryTree,
								dataAccess.getCategoryPartition
										.bind(dataAccess), "categoryTree",
								calledOnFinish);

					}

				});

var OBInstanceActionListener = Class.create();
OBInstanceActionListener.prototype = {
	initialize : function() {

		this.selectedInstance = null;
		this.oldSelectedInstance = null;
		this.selectedInstanceURI = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},

	navigateToEntity : function(event, node, instanceName, editmode) {
		smwhgLogger.log(instanceName, "OB", "inspect_entity");
		GeneralBrowserTools.navigateToPage(null, instanceName, editmode);

	},

	selectionChanged : function(id, title, ns, node) {
		if (ns == SMW_INSTANCE_NS) {
			this.selectedInstance = title;
			this.oldSelectedInstance = GeneralBrowserTools.toggleHighlighting(
					this.oldSelectedInstance, node);

		}
	},

	showSubMenu : function(commandID) {
		if (this.selectedInstance == null) {
			alert(gLanguage.getMessage('OB_SELECT_INSTANCE'));
			return;
		}

		obInstanceMenuProvider.showContent(commandID, 'instanceList');
	},
	/**
	 * Called when a supercategory of an instance is selected.
	 */
	showSuperCategory : function(event, node, categoryName) {
		function filterBrowsingCategoryCallback(request) {
			var categoryDIV = $("categoryTree");
			if (categoryDIV.firstChild) {
				GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(
					request.responseText, categoryDIV);
		}
		globalActionListener.switchTreeComponent(null, 'categoryTree', true);
		// TODO: externalize in dataAccess
		sajax_do_call('smwf_ob_OntologyBrowserAccess',
				[ 'filterBrowse', "category##" + categoryName,
						obAdvancedOptions.getDataSource() ],
				filterBrowsingCategoryCallback);

	},

	selectInstance : function(event, node, id, instanceName, instanceNamespace) {

		var e = GeneralTools.getEvent(event);

		// if Ctrl is pressed: navigation mode
		if (e["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(null, instanceName);
		} else {
			// adjust relatt table headings
			if (!$("relattValues").visible()) {
				$("relattValues").show();
				$("relattRangeType").hide();
			}

			var relattDIV = $("relattributes");
			var categoryDIV = $('categoryTree');

			selectionProvider.fireSelectionChanged(id, instanceNamespace + ":"
					+ instanceName, SMW_INSTANCE_NS, node);
			this.selectedInstanceURI = node.getAttribute("uri");
			selectionProvider.fireSelectedTripleChanged(
					this.selectedInstanceURI, "rdf:type",
					categoryActionListener.selectedCategoryURI);

			smwhgLogger.log(instanceName, "OB", "clicked");

			function callbackOnInstanceSelectToRight(request) {
				OB_relatt_pendingIndicator.hide();
				if (relattDIV.firstChild) {
					GeneralBrowserTools.purge(relattDIV.firstChild);
					relattDIV.removeChild(relattDIV.firstChild);
				}
				var xmlFragmentPropertyList = GeneralXMLTools
						.createDocumentFromString(request.responseText);
				dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
				selectionProvider.fireBeforeRefresh();
				transformer.transformResultToHTML(request, relattDIV);
				if (OB_bd.isGecko) {
					// FF needs repasting for chemical formulas and equations
					// because FF's XSLT processor does not know
					// 'disable-output-encoding' switch. IE does.
					// thus, repaste markup on all elements marked with a
					// 'chemFoEq' attribute
					GeneralBrowserTools.repasteMarkup("needRepaste");
				}
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null,
						SMW_PROPERTY_NS, null);
			}

			function callbackOnInstanceSelectToLeft(request) {
				OB_tree_pendingIndicator.hide();
				if (categoryDIV.firstChild) {
					GeneralBrowserTools.purge(categoryDIV.firstChild);
					categoryDIV.removeChild(categoryDIV.firstChild);
				}
				dataAccess.OB_cachedCategoryTree = GeneralXMLTools
						.createDocumentFromString(request.responseText);
				selectionProvider.fireBeforeRefresh();
				dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(
						request.responseText, categoryDIV);
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null,
						SMW_CATEGORY_NS, null);
			}

			if (OB_RIGHT_ARROW == 0) {
				OB_relatt_pendingIndicator.show();
				var instanceParam = node.getAttribute("uri") == null ? instanceNamespace
						+ ":" + instanceName
						: node.getAttribute("uri");

				dataAccess.getAnnotations(instanceParam,
						callbackOnInstanceSelectToRight);

			}
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				// TODO: externalize in dataAccess
				var instanceParam = node.getAttribute("uri") == null ? instanceNamespace
						+ ":" + instanceName
						: node.getAttribute("uri");
				sajax_do_call('smwf_ob_OntologyBrowserAccess', [
						'getCategoryForInstance', instanceParam,
						obAdvancedOptions.getDataSource() ],
						callbackOnInstanceSelectToLeft);
			}

		}
	},

	selectNextPartition : function(e, htmlNode) {

		var partition = htmlNode.getAttribute("partitionnum");
		var dataSrc = htmlNode.getAttribute("dataSrc");
		partition++;
		OB_instance_pendingIndicator.show();
		if (dataSrc) {
			var params = dataSrc.split(",");
			var method = params.shift();
			if (method == 'getInstancesUsingProperty') {
				dataAccess.getInstancesUsingProperty(params[0], partition,
						this.selectPartitionCallback.bind(this));
				return;
			}
		}
		// TODO: refactor this to use dataSrc
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.selectedCategory,
					partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(
					propertyActionListener.selectedProperty, partition,
					this.selectPartitionCallback.bind(this));
		}
	},

	selectPreviousPartition : function(e, htmlNode) {

		var partition = htmlNode.getAttribute("partitionnum");
		partition--;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.selectedCategory,
					partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(
					propertyActionListener.selectedProperty, partition,
					this.selectPartitionCallback.bind(this));
		}
	},

	selectPartitionCallback : function(request) {
		OB_instance_pendingIndicator.hide();
		var instanceListNode = $("instanceList");
		GeneralXMLTools.removeAllChildNodes(instanceListNode);
		var xmlFragmentInstanceList = GeneralXMLTools
				.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(xmlFragmentInstanceList,
				instanceListNode, true);
		selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
				null);
		selectionProvider.fireRefresh();
		instanceListNode.scrollTop = 0;
	},
	/*
	 * Hides/Shows instance box
	 */
	toggleInstanceBox : function(event) {
		if ($("instanceContainer").visible()) {
			$("hideInstancesButton").innerHTML = gLanguage
					.getMessage('SHOW_INSTANCES');
			$("hideInstancesButton").setAttribute("hidden", "true");
			Effect.Fold("instanceContainer");
			Effect.Fold($("leftArrow"));
		} else {
			$("hideInstancesButton").removeAttribute("hidden");
			new Effect.Grow('instanceContainer');
			$("hideInstancesButton").innerHTML = gLanguage
					.getMessage('HIDE_INSTANCES');
			new Effect.Grow($("leftArrow"));
		}
	},

	toggleMetadata : function(event, node, id) {
		var metaContainer = $(id);

		var scrollDiff = $('instanceList').scrollTop;
		var x = Event.pointerX(event) + 16;
		var y = Event.pointerY(event) + 16 - scrollDiff;

		metaContainer.style.top = y + "px";
		metaContainer.style.left = x + "px";
		if (metaContainer.visible()) {
			metaContainer.hide();
			node.removeClassName("metadataContainerSelected");
		} else {
			metaContainer.show();
			node.addClassName("metadataContainerSelected");
		}
	}

}

/**
 * Action Listener for attributes in the attribute tree
 */
var OBPropertyTreeActionListener = Class.create();
OBPropertyTreeActionListener.prototype = Object
		.extend(
				new OBTreeActionListener(),
				{
					initialize : function() {

						this.selectedProperty = null;
						this.selectedPropertyID = null;
						this.oldSelectedProperty = null;
						selectionProvider.addListener(this,
								OB_SELECTIONLISTENER);
						selectionProvider.addListener(this, OB_REFRESHLISTENER);
						selectionProvider.addListener(this,
								OB_BEFOREREFRESHLISTENER);

						Draggables.addObserver(this);
						this.draggableProperties = [];
						Droppables.add('propertyTreeSwitch', {
							accept : 'property',
							hoverclass : 'dragHover',
							onDrop : this.onDrop.bind(this)
						});
					},

					navigateToEntity : function(event, node, propertyName,
							editmode) {
						smwhgLogger.log(propertyName, "OB", "inspect_entity");
						GeneralBrowserTools.navigateToPage(gLanguage
								.getMessage('PROPERTY_NS_WOC'), propertyName,
								editmode);
					},

					selectionChanged : function(id, title, ns, node) {
						if (ns == SMW_PROPERTY_NS) {
							this.selectedProperty = title;
							this.selectedPropertyID = id;
							this.oldSelectedProperty = GeneralBrowserTools
									.toggleHighlighting(
											this.oldSelectedProperty, node);
						}
					},

					beforeRefresh : function() {
						if (wgUserGroups == null
								|| (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups
										.indexOf('gardener') == -1)) {

							return;
						}
						if (OB_bd.isIE) {
							return; // no DnD in IE
						}
						this.draggableProperties.each(function(c) {
							c.destroy();

						});
						$$('a.property').each(function(c) {
							Droppables.remove(c.getAttribute('id'));
						});
						this.draggableProperties = [];
					},

					refresh : function() {
						if (wgUserGroups == null
								|| (wgUserGroups.indexOf('sysop') == -1 && wgUserGroups
										.indexOf('gardener') == -1)) {
							// do not allow dragging, when user is no sysop or
							// gardener
							return;
						}
						if (OB_bd.isIE) {
							return; // do not activate DnD in IE, because
							// scriptaculous is very buggy here
						}
						function addDragAndDrop(c) {
							var d = new Draggable(c.getAttribute('id'), {
								revert : true,
								ghosting : true
							});
							this.draggableProperties.push(d);
							Droppables.add(c.getAttribute('id'), {
								accept : 'property',
								hoverclass : 'dragHover',
								onDrop : onDrop_bind
							});
						}
						var addDragAndDrop_bind = addDragAndDrop.bind(this);
						var onDrop_bind = this.onDrop.bind(this);
						$$('a.property').each(addDragAndDrop_bind);

					},

					onStart : function(eventName, draggable, event) {

					},

					onDrop : function(dragElement, dropElement, event) {
						var draggedPropertyID = dragElement.getAttribute('id');
						var droppedPropertyID = dropElement.getAttribute('id');
						// alert('Dropped on: '+droppedPropertyID+" from:
						// "+draggedPropertyID);
						ontologyTools.moveProperty(draggedPropertyID,
								droppedPropertyID);

					},

					showSubMenu : function(commandID) {
						if (this.selectedProperty == null) {
							alert(gLanguage.getMessage('OB_SELECT_PROPERTY'));
							return;
						}
						obPropertyMenuProvider.showContent(commandID,
								'propertyTree');
					},

					select : function(event, node, propertyID, propertyName) {

						var e = GeneralTools.getEvent(event);

						// if Ctrl is pressed: navigation mode
						if (e["ctrlKey"]) {
							GeneralBrowserTools.navigateToPage(gLanguage
									.getMessage('PROPERTY_NS_WOC'),
									propertyName);
						} else {

							var nextDIV = node.nextSibling;

							// find the next DIV
							while (nextDIV.nodeName != "DIV") {
								nextDIV = nextDIV.nextSibling;
							}
							// check if node is already expanded and expand it
							// if not
							if (!nextDIV.hasChildNodes()
									|| nextDIV.style.display == 'none') {
								this.toggleExpand(event, node, propertyID);
							}

							var instanceDIV = document
									.getElementById("instanceList");
							var relattDIV = $("relattributes");

							// fire selection event
							selectionProvider.fireSelectionChanged(propertyID,
									propertyName, SMW_PROPERTY_NS, node);

							smwhgLogger.log(propertyName, "OB", "clicked");

							function callbackOnPropertySelect(request) {
								OB_instance_pendingIndicator.hide();
								if (instanceDIV.firstChild) {
									GeneralBrowserTools
											.purge(instanceDIV.firstChild);
									instanceDIV
											.removeChild(instanceDIV.firstChild);
								}
								var xmlFragmentInstanceList = GeneralXMLTools
										.createDocumentFromString(request.responseText);
								dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
								selectionProvider.fireBeforeRefresh();
								transformer.transformResultToHTML(request,
										instanceDIV, true);
								selectionProvider.fireRefresh();
							}

							function callbackOnPropertySelect2(request) {
								OB_relatt_pendingIndicator.hide();
								if (relattDIV.firstChild) {
									GeneralBrowserTools
											.purge(relattDIV.firstChild);
									relattDIV.removeChild(relattDIV.firstChild);
								}
								var xmlFragmentPropertyList = GeneralXMLTools
										.createDocumentFromString(request.responseText);
								dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
								selectionProvider.fireBeforeRefresh();
								transformer.transformResultToHTML(request,
										relattDIV);
								selectionProvider.fireRefresh();

							}

							if (OB_LEFT_ARROW == 0) {
								OB_instance_pendingIndicator.show();

								dataAccess.getInstancesUsingProperty(
										propertyName, 0,
										callbackOnPropertySelect);
								selectionProvider.fireSelectionChanged(null,
										null, SMW_INSTANCE_NS, null);
							}
							if (OB_RIGHT_ARROW == 0) {
								OB_relatt_pendingIndicator.show();
								var property = node.getAttribute("uri") == null ? gLanguage
										.getMessage('PROPERTY_NS')
										+ propertyName
										: node.getAttribute("uri");
								dataAccess.getAnnotations(property,
										callbackOnPropertySelect2);

							}
						}
					},

					toggleExpand : function(event, node, folderCode) {
						this._toggleExpand(event, node,
								dataAccess.OB_cachedPropertyTree,
								dataAccess.getPropertySubTree.bind(dataAccess));
					},
					selectNextPartition : function(e, htmlNode) {
						function calledOnFinish(tree) {
							dataAccess.OB_cachedPropertyTree = tree;
							selectionProvider.fireSelectionChanged(null, null,
									SMW_PROPERTY_NS, null);
							$('propertyTree').scrollTop = 0;
						}
						this._selectNextPartition(e, htmlNode,
								dataAccess.OB_cachedPropertyTree,
								dataAccess.getPropertyPartition
										.bind(dataAccess), "propertyTree",
								calledOnFinish);

					},

					selectPreviousPartition : function(e, htmlNode) {
						function calledOnFinish(tree) {
							dataAccess.OB_cachedPropertyTree = tree;
							selectionProvider.fireSelectionChanged(null, null,
									SMW_PROPERTY_NS, null);
							$('propertyTree').scrollTop = 0;
						}
						this._selectPreviousPartition(e, htmlNode,
								dataAccess.OB_cachedPropertyTree,
								dataAccess.getPropertyPartition
										.bind(dataAccess), "propertyTree",
								calledOnFinish);

					}

				});

/**
 * Action Listener for attribute and relation annotations
 */
var OBAnnotationActionListener = Class.create();
OBAnnotationActionListener.prototype = {
	initialize : function() {
		// empty

	},

	navigateToTarget : function(event, node, targetInstance) {
		GeneralBrowserTools.navigateToPage(null, targetInstance);
	},

	selectProperty : function(event, node, propertyName) {
		// delegate to schemaPropertyListener
		var propertyURI = node.getAttribute("uri");
		var valueNode = node.parentNode.nextSibling;
		var valueURI = valueNode.getAttribute("uri");
		var valueTypeURI = valueNode.getAttribute("typeURI");
		var valueString = valueNode.textContent.trim();
		var objectValue = valueURI == null ? '"' + valueString + '"^^'
				+ valueTypeURI : valueURI;
		selectionProvider.fireSelectedTripleChanged(
				instanceActionListener.selectedInstanceURI, propertyURI,
				objectValue);
		schemaActionPropertyListener.selectProperty(event, node, propertyName);
	},

	toggleMetadata : function(event, node, id) {
		var metaContainer = $(id);

		var scrollDiff = $('instanceList').scrollTop;
		var x = Event.pointerX(event) + 16;
		var y = Event.pointerY(event) + 16 - scrollDiff;

		metaContainer.style.top = y + "px";
		metaContainer.style.left = x + "px";
		if (metaContainer.visible()) {
			metaContainer.hide();
			node.removeClassName("metadataContainerSelected");
		} else {
			metaContainer.show();
			node.addClassName("metadataContainerSelected");
		}
	}

}

/**
 * Action Listener for schema properties, i.e. attributes and relations on
 * schema level
 */
var OBSchemaPropertyActionListener = Class.create();
OBSchemaPropertyActionListener.prototype = {
	initialize : function() {
		this.selectedCategory = null; // initially none is selected
		this.oldSelectedProperty = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},

	selectionChanged : function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedCategory = title;
			var anchor = $('currentSelectedCategory');
			if (anchor != null) {
				if (title == null) {
					anchor.innerHTML = '...';
				} else {
					anchor.innerHTML = "'" + title + "'";
				}
			}
		} else if (ns == SMW_PROPERTY_NS) {
			this.oldSelectedProperty = GeneralBrowserTools.toggleHighlighting(
					this.oldSelectedProperty, node);
		}
	},

	showSubMenu : function(commandID) {
		if (this.selectedCategory == null) {
			alert(gLanguage.getMessage('OB_SELECT_CATEGORY'));
			return;
		}
		obSchemaPropertiesMenuProvider.showContent(commandID, 'relattributes');
	},

	navigateToEntity : function(event, node, attributeName, editmode) {
		smwhgLogger.log(attributeName, "OB", "inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage
				.getMessage('PROPERTY_NS_WOC'), attributeName, editmode);
	},

	selectProperty : function(event, node, attributeName) {
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");

		selectionProvider.fireSelectionChanged(null, attributeName,
				SMW_PROPERTY_NS, node);
		smwhgLogger.log(attributeName, "OB", "clicked");

		function callbackOnPropertySelectForCategory(request) {
			OB_tree_pendingIndicator.hide();
			if (categoryDIV.firstChild) {
				GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(
					request.responseText, categoryDIV);
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS,
					null);
		}

		function callbackOnPropertySelectForInstance(request) {
			OB_instance_pendingIndicator.hide();
			if (instanceDIV.firstChild) {
				GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}

			var xmlFragmentInstanceList = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
			transformer.transformResultToHTML(request, instanceDIV, true);
			selectionProvider.fireRefresh();
			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
					null);
		}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage
					.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				// TODO: externalize in dataAccess
				sajax_do_call('smwf_ob_OntologyBrowserAccess', [
						'getCategoryForProperty', attributeName,
						obAdvancedOptions.getDataSource() ],
						callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				OB_instance_pendingIndicator.show();

				dataAccess.getInstancesUsingProperty(attributeName, 0,
						callbackOnPropertySelectForInstance);
			}
		}
	},

	selectRangeInstance : function(event, node, categoryName) {
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage
					.getMessage('CATEGORY_NS_WOC'), categoryName);
		}
	}
}

/**
 * Action Listener for global Ontology Browser events, e.g. switch tree
 */
var OBGlobalActionListener = Class.create();
OBGlobalActionListener.prototype = {
	initialize : function() {
		this.activeTreeName = 'categoryTree';

		new Form.Element.Observer($("treeFilter"), 0.5, this.filterTree
				.bindAsEventListener(this));
		new Form.Element.Observer($("instanceFilter"), 0.5,
				this.filterInstances.bindAsEventListener(this));
		new Form.Element.Observer($("propertyFilter"), 0.5,
				this.filterProperties.bindAsEventListener(this));

		// make sure that OntologyBrowser Filter search gets focus if a key is
		// pressed
		var keyDownListener = function(event) {
			if (event.target && event.target.localName == 'HTML') { // that
				// means, no
				// other
				// element
				// has the
				// focus
				$('FilterBrowserInput').focus()
			}

			if (event.keyCode == 27) { // escape pressed
				this.closeAllMetadataviews();
			}
		};

		Event.observe(document, 'keydown', keyDownListener.bind(this));

		selectionProvider.addListener(this, OB_REFRESHLISTENER);
		selectionProvider.addListener(this, OB_SELECTEDTRIPLELISTENER);
	},

	selectedTripleChanged : function(s, p, o) {
		// do nothing
	},

	refresh : function() {
		_smw_hideAllTooltips();
		// re-initialize tooltips when content has changed.
		smw_tooltipInit();

		// re-initialize LOD tooltips

		// register the tool-tips for metadata switch
		jQuery(".metadataContainerSwitch")
				.each(
						function() {
							// get subject this metdata is about
							var subjectID = this.getAttribute("subjectid");
							var subjectType = this.getAttribute("subjecttype");
							var data = this.getAttribute("data");
							
							// content for menu tooltip
							var html = jQuery('<a onclick="globalActionListener.selectedMetadataSwitch(event, \''
									+ subjectID
									+ '\', '
									+ '\''
									+ subjectType
									+ '\''
									+ ', 0, \''
									+ data
									+ '\')">'+gLanguage.getMessage('SMW_OB_META_COMMAND_SHOW')+'</a>'
									+ '<br><a onclick="globalActionListener.selectedMetadataSwitch(event, \''
									+ subjectID
									+ '\', '
									+ '\''
									+ subjectType
									+ '\''
									+ ', 1, \''
									+ data
									+ '\')">'+gLanguage.getMessage('SMW_OB_META_COMMAND_RATE')+</a>');
									
							// install the tool-tip on the current DOM element
							jQuery(this).qtip( {
								content : html,
								show : {
									effect : {
										length : 500
									},
									when : {
										event : 'mouseover'
									}
								},
								hide : {
									effect : {
										length : 500
									},
									when : {
										event : 'mouseout'
									},
									fixed : true
								},
								position : {
									corner : {
										target : 'topLeft',
										tooltip : 'bottomLeft'
									}
								},
								style : {
									tip : 'bottomLeft',
									width : {
										max : 500
									}
								}
							});
						});

	},

	/**
	 * Called when the user selects a command from the metadata
	 * toolbox.
	 * 
	 * @param event
	 *            JS Event object
	 * @param subjectID
	 *            The subject which this metadata item is about (DOM node ID)
	 * @param subjectType
	 *            The type of the subject (instance or annotation)
	 * @param commandID
	 *            Type of the selected command.
	 * @param data Command specific data
	 */
	selectedMetadataSwitch : function(event, subjectID, subjectType, commandID,
			data) {
		if (commandID == 0) {
			// show metadata
			var metadataShowContainerID = data;
			switch (subjectType) {

			case 'instance':
				instanceActionListener.toggleMetadata(event, $(subjectID),
						metadataShowContainerID);
				break;
			case 'annotation':
				annotationActionListener.toggleMetadata(event, $(subjectID),
						metadataShowContainerID);
				break;
			}
		} else if (commandID == 1) {
			// rate metadata
			switch (subjectType) {

			case 'instance':
				var s = $(subjectID).getAttribute("uri");
				var p = "rdf:type"
				var o = categoryActionListener.selectedCategoryURI;
				var value = $(subjectID).innerHTML;
				break;
			case 'annotation':
				var s = instanceActionListener.selectedInstanceURI;
				var p = $(subjectID).getAttribute("uri");

				// select object value
				var node = $(subjectID);
				var propertyURI = node.getAttribute("uri");
				var valueNodeURI = node.parentNode.nextSibling.firstChild;
				var valueNodeLiteral = node.parentNode.nextSibling;
				if (valueNodeURI.nodeType == 3) { // textnode, means literal
					var valueTypeURI = valueNodeLiteral.getAttribute("typeURI");
					var valueString = valueNodeLiteral.textContent.trim();
				} else {
					var valueURI = valueNodeURI.getAttribute("uri");

				}

				var objectValue = valueURI == null ? '"' + valueString + '"^^'
						+ valueTypeURI : valueURI;
				var o = objectValue;
				var value = valueString;
				break;
			}
			
			if (typeof LOD !== "undefined") {
				LOD.ratingEditor.selectedTripleInOB(s, p, o, value);
			}
		}
	},

	/*
	 * Switches to the given tree.
	 */
	switchTreeComponent : function(event, showWhichTree, noInitialize) {
		$$('.treeContainer').each(function(e) {
			e.hide()
		});
		$(showWhichTree).show();
		$(showWhichTree + "Switch").addClassName("selectedSwitch");
		$$('.treeSwitch').each(function(e) {
			if (e.id != showWhichTree + "Switch")
				e.removeClassName("selectedSwitch")
		});
		$$('.menuBarTree').each(function(e) {
			e.hide()
		});
		var menuBarToShow = $("menuBar" + showWhichTree);
		if (menuBarToShow)
			menuBarToShow.show(); // menubar is optional

		this.activeTreeName = showWhichTree;

		if (!noInitialize) {
			if (showWhichTree == 'categoryTree') {
				dataAccess.initializeRootCategories(0);
				$('instanceContainer').show();
				$('rightArrow').show();
				$('relattributesContainer').show();

			} else if (showWhichTree == 'propertyTree') {
				dataAccess.initializeRootProperties(0);
				$('instanceContainer').show();
				$('rightArrow').show();
				$('relattributesContainer').show();

			}
		}
		selectionProvider.fireTreeTabChanged(showWhichTree);

	},

	/**
	 * Global filter event listener. Filters the currently visible tree.
	 * 
	 * @param event
	 */
	filterTree : function(event) {

		// reads filter string

		var filter = $F('treeFilter');
		var tree;
		var actionListener;

		// decide which tree is active and
		// set actionListener for that tree
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;
			if (filter == "") { // special case empty filter, just copy
				dataAccess.initializeRootCategories(0);
				selectionProvider.fireBeforeRefresh();
				transformer.transformXMLToHTML(
						dataAccess.OB_currentlyDisplayedTree,
						$(this.activeTreeName), true);
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null,
						SMW_CATEGORY_NS, null);
				return;
			}
			// filter tree
			actionListener
					._filterTree(event, tree, this.activeTreeName, filter);
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;
			tree = dataAccess.OB_cachedPropertyTree;
			if (filter == "") {
				dataAccess.initializeRootProperties(0);
				selectionProvider.fireBeforeRefresh();
				transformer.transformXMLToHTML(
						dataAccess.OB_currentlyDisplayedTree,
						$(this.activeTreeName), true);
				selectionProvider.fireRefresh();
				selectionProvider.fireSelectionChanged(null, null,
						SMW_PROPERTY_NS, null);
				return;
			}
			// filter tree
			actionListener
					._filterTree(event, tree, this.activeTreeName, filter);
		} else {
			// may be another tree tab (provided by other extensions)
			selectionProvider.fireFilterTree(this.activeTreeName, filter);
		}

	},

	/**
	 * Filters instances currently visible.
	 */
	filterInstances : function(event) {
		if (dataAccess.OB_cachedInstances == null) {
			return;
		}

		var filter = $F('instanceFilter');

		var regex = new Array();
		var filterTerms = GeneralTools.splitSearchTerm(filter);
		for ( var i = 0, n = filterTerms.length; i < n; i++) {
			try {
				regex[i] = new RegExp(filterTerms[i], "i");
			} catch (e) {
				return;
			}
		}

		var nodesFound = GeneralXMLTools
				.createDocumentFromString("<instanceList/>");
		var instanceList = dataAccess.OB_cachedInstances.firstChild;
		for ( var i = 0, n = instanceList.childNodes.length; i < n; i++) {
			var inst = instanceList.childNodes[i];
			var title = inst.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
			if (inst.tagName == 'instancePartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
		}
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(nodesFound, $("instanceList"), true);
		selectionProvider.fireRefresh();
		selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
				null);
	},

	/**
	 * Filters properties currently visible.
	 */
	filterProperties : function(event) {
		if (dataAccess.OB_cachedProperties == null) {
			return;
		}

		var filter = $F('propertyFilter');

		var regex = new Array();
		var filterTerms = GeneralTools.splitSearchTerm(filter);
		for ( var i = 0, n = filterTerms.length; i < n; i++) {
			try {
				regex[i] = new RegExp(filterTerms[i], "i");
			} catch (e) {
				return;
			}
		}

		var tagName = dataAccess.OB_cachedProperties.firstChild.tagName;
		var nodesFound = GeneralXMLTools.createDocumentFromString("<" + tagName
				+ "/>");
		var propertyList = dataAccess.OB_cachedProperties.firstChild;
		for ( var i = 0, n = propertyList.childNodes.length; i < n; i++) {
			var property = propertyList.childNodes[i];
			var title = property.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, property,
						true);
			}
			if (property.tagName == 'propertyPartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, property,
						true);
			}
		}
		selectionProvider.fireBeforeRefresh();
		transformer.transformXMLToHTML(nodesFound, $("relattributes"), true);
		selectionProvider.fireRefresh();
		selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS,
				null);
		GeneralBrowserTools.repasteMarkup("needRepaste");
	},

	/**
	 * @deprecated not used any more
	 */
	filterRoot : function(event) {
		var actionListener;
		var tree;
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;
			tree = dataAccess.OB_cachedPropertyTree;
		}
		actionListener._filterRootLevel(event, tree, this.activeTreeName);
	},

	/**
	 * Filters database wide. Categories, instances, properties
	 * 
	 * @param event
	 * @param force
	 *            Filters in any case, otherwise only if enter is pressed in
	 *            given event.
	 */
	filterBrowsing : function(event, force) {

		function filterBrowsingCategoryCallback(request) {
			OB_tree_pendingIndicator.hide();
			var categoryDIV = $("categoryTree");
			if (categoryDIV.firstChild) {
				GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(
					request.responseText, categoryDIV);
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS,
					null);
		}

		function filterBrowsingAttributeCallback(request) {
			OB_tree_pendingIndicator.hide();
			var attributeDIV = $("propertyTree");
			if (attributeDIV.firstChild) {
				GeneralBrowserTools.purge(attributeDIV.firstChild);
				attributeDIV.removeChild(attributeDIV.firstChild);
			}
			dataAccess.OB_cachedPropertyTree = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(
					request.responseText, attributeDIV);
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS,
					null);
		}

		function filterBrowsingInstanceCallback(request) {
			OB_instance_pendingIndicator.hide();
			var instanceDIV = $("instanceList");
			if (instanceDIV.firstChild) {
				GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
			var xmlFragmentInstanceList = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
			transformer.transformResultToHTML(request, instanceDIV, true);
			selectionProvider.fireRefresh();
			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS,
					null);
		}

		function filterBrowsingPropertyCallback(request) {
			OB_relatt_pendingIndicator.hide();
			var propertyDIV = $("relattributes");
			if (propertyDIV.firstChild) {
				GeneralBrowserTools.purge(propertyDIV.firstChild);
				propertyDIV.removeChild(propertyDIV.firstChild);
			}
			var xmlFragmentInstanceList = GeneralXMLTools
					.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedProperties = xmlFragmentInstanceList;
			selectionProvider.fireBeforeRefresh();
			transformer.transformResultToHTML(request, propertyDIV, true);
			selectionProvider.fireRefresh();
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS,
					null);
		}

		if (!force && event["keyCode"] != 13) {
			return;
		}
		var filterBrowserInput = $("FilterBrowserInput");
		var hint = filterBrowserInput.value;

		if (hint.length <= 1) {
			alert(gLanguage.getMessage('ENTER_MORE_LETTERS'));
			return;
		}
		if (this.activeTreeName == 'categoryTree') {
			OB_tree_pendingIndicator.show(this.activeTreeName);
			// TODO: externalize in dataAccess
			sajax_do_call('smwf_ob_OntologyBrowserAccess', [ 'filterBrowse',
					"category##" + hint, obAdvancedOptions.getDataSource() ],
					filterBrowsingCategoryCallback);
		} else if (this.activeTreeName == 'propertyTree') {
			OB_tree_pendingIndicator.show(this.activeTreeName);
			// TODO: externalize in dataAccess
			sajax_do_call('smwf_ob_OntologyBrowserAccess',
					[ 'filterBrowse', "propertyTree##" + hint,
							obAdvancedOptions.getDataSource() ],
					filterBrowsingAttributeCallback);
		} else {
			selectionProvider.fireFilterBrowsing(this.activeTreeName, hint);
		}

		if (this.activeTreeName == 'categoryTree'
				|| this.activeTreeName == 'propertyTree') {
			OB_instance_pendingIndicator.show();
			OB_relatt_pendingIndicator.show();
			// TODO: externalize in dataAccess
			sajax_do_call('smwf_ob_OntologyBrowserAccess', [ 'filterBrowse',
					"instance##" + hint, obAdvancedOptions.getDataSource() ],
					filterBrowsingInstanceCallback);
			sajax_do_call('smwf_ob_OntologyBrowserAccess', [ 'filterBrowse',
					"property##" + hint, obAdvancedOptions.getDataSource() ],
					filterBrowsingPropertyCallback);
		}
	},

	/**
	 * Sets back tree view and clear search field.
	 */
	reset : function(event) {
		if (this.activeTreeName == 'categoryTree') {
			dataAccess.initializeRootCategories(0, true);
		} else if (this.activeTreeName == 'propertyTree') {
			dataAccess.initializeRootProperties(0, true);

		}
		selectionProvider.fireReset(this.activeTreeName);

		// clear input fields except search field in skin
		$$('input').each(function(e) {
			if (e.getAttribute("id") != 'searchInput')
				e.value = "";
		});

	},

	/**
	 * Toggles left arrow
	 */
	toogleCatInstArrow : function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB", "flipflow_left");
		if (OB_LEFT_ARROW == 0) {
			OB_LEFT_ARROW = 1;
			img
					.setAttribute(
							"src",
							wgScriptPath
									+ "/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_LEFT_ARROW = 0;
			img
					.setAttribute(
							"src",
							wgScriptPath
									+ "/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	},

	/**
	 * Toggles right arrow
	 */
	toogleInstPropArrow : function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB", "flipflow_right");
		if (OB_RIGHT_ARROW == 0) {
			OB_RIGHT_ARROW = 1;
			img
					.setAttribute(
							"src",
							wgScriptPath
									+ "/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_RIGHT_ARROW = 0;
			img
					.setAttribute(
							"src",
							wgScriptPath
									+ "/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	},

	closeMetadataview : function(event, id) {
		$(id).hide();

	},

	closeAllMetadataviews : function() {
		$$(".metadataContainer").each(function(s) {
			s.hide();
		});
	}

}
