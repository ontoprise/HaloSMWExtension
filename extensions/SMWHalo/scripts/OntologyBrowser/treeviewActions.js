/*  Copyright 2007, ontoprise GmbH
*   Author: Kai Kühn
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

/*
 * TreeView actions 
 *  
 * One listener object for each type entity in each container.
 */

/**
 * Global selection (node)
 */
var OB_oldSelectedCategoryNode = null;
var OB_oldSelectedInstanceNode = null;
var OB_oldSelectedAttributeNode = null;
var OB_oldSelectedRelationNode = null;

/** 
 * Global selection flow arrow states
 * 0 = left to right
 * 1 = right to left
 */
var OB_LEFT_ARROW = 0;
var OB_RIGHT_ARROW = 0;

// Logging on close does not work, because window shuts down. What to do?
//window.onbeforeunload = function() { smwhgLogger.log("", "OB","close"); };
/**
 * 'Abstract' base class for OntologyBrowser trees
 * 
 * Features:
 * 
 * 1. Expansion and collapsing of nodes
 * 2. Reload of tree partitions (i.e. a segment of a tree level)
 * 3. Filtering of nodes on root level.
 * 4. Filtering of nodes showing their place in the hierarchy.
 * 
 */
var OBTreeActionListener = Class.create();
OBTreeActionListener.prototype = {
	initialize: function() {
		this.OB_currentFilter = null;
	},
	
	/**
	 * Togges a tree node expansion.
	 * event:
	 * node:
	 * folderCode:
	 * tree: cached tree to update
	 * accessFunc: Function which returns children needed for expansion.
	 */
	_toggleExpand: function (event, node, folderCode, tree, accessFunc) {
	
	// stop event propagation in Gecko and IE
	Event.stop(event);
    // Get the next tag (read the HTML source)
	var nextDIV = node.nextSibling;
	
 	// find the next DIV
	while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}

	// Unfold the branch if it isn't visible
	if (nextDIV.style.display == 'none') {

		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
				node.childNodes.item(0).src = GeneralTools.getImgDirectory(node.childNodes.item(0).src) + "minus.gif";
			}
		}
		
		// get name of category which is about to be expanded
		var xmlNodeName = node.getAttribute("title");
		var xmlNodeID = node.getAttribute("id");
		
 		function callbackOnExpandForAjax(request) {
 			OB_tree_pendingIndicator.hide();
	  		var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
	  		var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
	    	if (request.responseText.indexOf('noResult') != -1) {
	    		// hide expand button if category has no subcategories and mark as leaf
	    		node.childNodes.item(0).style.visibility = 'hidden';
	    		parentNode.setAttribute("isLeaf", "true");
	    		parentNodeInCache.setAttribute("isLeaf", "true");
	    		
	    		return;
	    	}
	  		var subTree = transformer.transformResultToHTML(request,nextDIV);
	  		GeneralXMLTools.importSubtree(parentNode, subTree.firstChild);
	  		GeneralXMLTools.importSubtree(parentNodeInCache, subTree.firstChild);
	  	}
	  	
	  	function callBackForCache(xmlDoc) {
	  		transformer.transformXMLToHTML(xmlDoc, nextDIV, false);
			Element.show(nextDIV);
	  	}
	  
	  // if category has no child nodes, they will be requested
	  if (!nextDIV.hasChildNodes()) {
	  	//call subtree hook
	  	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	  	accessFunc(xmlNodeID, xmlNodeName, callbackOnExpandForAjax, callBackForCache);
	  	
	  }
	
		
		Element.show(nextDIV);
		var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
		parentNode.setAttribute("expanded", "true");
		
		var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
		parentNodeInCache.setAttribute("expanded", "true");
	}

	// Collapse the branch if it IS visible
	else {

		
		Element.hide(nextDIV);
		// Change the image (if there is an image)
		if (node.childNodes.length > 0) {
			if (node.childNodes.item(0).nodeName == "IMG") {
  				node.childNodes.item(0).src = GeneralTools.getImgDirectory(node.childNodes.item(0).src) + "plus.gif";
			}
			var xmlNodeName = node.getAttribute("title");
			var xmlNodeID = node.getAttribute("id");
			var parentNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree.firstChild, xmlNodeID);
			parentNode.setAttribute("expanded","false");
			
			var parentNodeInCache = GeneralXMLTools.getNodeById(tree.firstChild, xmlNodeID);
			parentNodeInCache.setAttribute("expanded", "false");
		}
		
	}
 	},
 	
  _selectNextPartition: function (e, htmlNode, tree, accessFunc, treeName, calledOnFinish) {
	
	function selectNextPartitionCallback (request) {
		//TODO: check if empty and do nothing in this case.
		OB_tree_pendingIndicator.hide();
		var xmlFragmentForCache = GeneralXMLTools.createDocumentFromString(request.responseText);
		var xmlFragmentForDisplayTree = GeneralXMLTools.createDocumentFromString(request.responseText);
		
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
			htmlNodeToReplace = document.getElementById(idOfChildrenToReplace).nextSibling
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		calledOnFinish(tree);
	}		
	// Identify partition node in XML
	var id = htmlNode.getAttribute("id");
	var partition = htmlNode.getAttribute("partitionnum");
	var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
	var partitionNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, id);
	
	// Identify parent of partition node
	var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
	var parentOfChildrenToReplace = partitionNode.parentNode;
	var idOfChildrenToReplace = parentOfChildrenToReplace.getAttribute("id");
	
	// ask for next partition
	
	partition++;

	
	var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	accessFunc(isRootLevel, partition, parentOfChildrenToReplace.getAttribute("title"), selectNextPartitionCallback);
	
	
	},
	
	_selectPreviousPartition: function (e, htmlNode, tree, accessFunc, treeName, calledOnFinish) {
	
	function selectPreviousPartitionCallback (request) {
		//TODO: check if empty and do nothing in this case.
		OB_tree_pendingIndicator.hide();
		var xmlFragmentForCache = GeneralXMLTools.createDocumentFromString(request.responseText);
		var xmlFragmentForDisplayTree = GeneralXMLTools.createDocumentFromString(request.responseText);
		
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
			htmlNodeToReplace = document.getElementById(idOfChildrenToReplace).nextSibling
			
			// adjust XML structure
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplaceInCache);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplaceInCache, xmlFragmentForCache.firstChild);
			
			GeneralXMLTools.removeAllChildNodes(parentOfChildrenToReplace);
			GeneralXMLTools.importSubtree(parentOfChildrenToReplace, xmlFragmentForDisplayTree.firstChild);
		}
		// transform structure to HTML
		transformer.transformXMLToHTML(xmlFragmentForDisplayTree, htmlNodeToReplace, isRootLevel);
		calledOnFinish(tree);
	}	
	// Identify partition node in XML
	var id = htmlNode.getAttribute("id");
	var partition = htmlNode.getAttribute("partitionnum");
	var partitionNodeInCache = GeneralXMLTools.getNodeById(tree, id);
	var partitionNode = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, id);
	
	// Identify parent of partition node
	var parentOfChildrenToReplaceInCache = partitionNodeInCache.parentNode;
	var parentOfChildrenToReplace = partitionNode.parentNode;
	var idOfChildrenToReplace = parentOfChildrenToReplace.getAttribute("id");
	
	// ask for previous partition, stop if already 0
	if (partition == 0) {return;}
	partition--;
	
	
	var isRootLevel = parentOfChildrenToReplace.tagName == 'result';
	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
	accessFunc(isRootLevel, partition, parentOfChildrenToReplace.getAttribute("title"), selectPreviousPartitionCallback);
	
  },
  
  /*
 	* Filter categories which matches a given term
 	*/
  _filterTree: function (e, tree, treeName, catFilter) {
    var xmlDoc = GeneralXMLTools.createTreeViewDocument();
   
   	var nodesFound = new Array();
   	
   	// generate filters
   	var regex = new Array();
    var filterTerms = GeneralTools.splitSearchTerm(catFilter);
    for(var i = 0, n = filterTerms.length; i < n; i++) {
    	try {
	   	 	regex[i] = new RegExp(filterTerms[i],"i");
	   	} catch(e) {
    		// happens when RegExp is invalid. Just do nothing in this case
    		return;
    	}
    }
   	this._filterTree_(nodesFound, tree.firstChild, 0, regex);
   
   	for (var i = 0; i < nodesFound.length; i++) {
   		 var branch = GeneralXMLTools.getAllParents(nodesFound[i]);
   		 GeneralXMLTools.addBranch(xmlDoc.firstChild, branch);
   	}
   	// transform xml and add to category tree DIV 
   	var rootElement = document.getElementById(treeName);
   	transformer.transformXMLToHTML(xmlDoc, rootElement, true);
   	dataAccess.OB_currentlyDisplayedTree = xmlDoc;
},

  
  _filterTree_: function (nodesFound, node, count, regex) {

	var children = node.childNodes;
	
	if (children) {
   	  for (var i = 0; i < children.length; i++) {
   	    	count = this._filterTree_(nodesFound, children[i], count, regex);
    	
      }
	}
	var title = node.getAttribute("title");
    if (title != null && GeneralTools.matchArrayOfRegExp(title, regex)) {
    	nodesFound[count] = node;
		count++;
    	
	}
	
	return count;
  },
  
  _filterRootLevel: function (e, tree, treeName) {
   if (OB_bd.isIE && e.type != 'click' && e.keyCode != 13) {
   	return;
   }
   if (OB_bd.isGeckoOrOpera && e.type != 'click' && e.which != 13) {
   	return;
   }
   
   xmlDoc = GeneralXMLTools.createTreeViewDocument();
   
   var inputs = document.getElementsByTagName("input");
   this.OB_currentFilter = inputs[0].value;
   //iterate all root categories identifying those which match user input prefix  
   var rootCats = tree.firstChild.childNodes;	
   for (var i = 0; i < rootCats.length; i++) {
   	 	
   	 if (rootCats[i].getAttribute("title")) {
   	 	// filter root nodes which have a title
   	 	if (rootCats[i].getAttribute("title").indexOf(inputs[0].value) != -1) {
   	 		if (rootCats[i].childNodes.length > 0) rootCats[i].setAttribute("expanded", "true");
   	 		
   	 		// add matching root category nodes
   	 		if (OB_bd.isGeckoOrOpera) {
   	 			xmlDoc.firstChild.appendChild(document.importNode(rootCats[i], true));
   	 		} else if (OB_bd.isIE) {
   	 			xmlDoc.firstChild.appendChild(rootCats[i].cloneNode(true));
   	 		}
   	 	}
   	 } else {
   	 	// copy all other nodes
   	 	if (OB_bd.isGeckoOrOpera) {
   	 			xmlDoc.firstChild.appendChild(document.importNode(rootCats[i], true));
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
OBCategoryTreeActionListener.prototype = Object.extend(new OBTreeActionListener(), {
	initialize: function() {
		
		this.OB_currentlySelectedCategory = null;
	},
	
	toggleExpand: function (event, node, folderCode) {
		this._toggleExpand(event, node, folderCode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategorySubTree.bind(dataAccess));
	},


	navigateToEntity: function(event, node, categoryName) {
		smwhgLogger.log(categoryName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
	},
// ---- Selection methods. Called when the entity is selected ---------------------

select: function (event, node, categoryID, categoryName) {
	var e = GeneralTools.getEvent(event);
	
	// if Ctrl is pressed: navigation mode
	if (e["ctrlKey"]) {
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
	} else {
	
	
	var nextDIV = node.nextSibling;
	
 	// find the next DIV
	while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
	}
	// check if node is already expanded and expand it if not
	if (!nextDIV.hasChildNodes() || nextDIV.style.display == 'none') {
		this.toggleExpand(event, node, categoryID);
	}
		
	var instanceDIV = document.getElementById("instanceList");
	var relattDIV = document.getElementById("relattributes");
	OB_oldSelectedCategoryNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedCategoryNode, node);
	
	// adjust relatt table headings
	if (!$("relattRangeType").visible()) {
		$("relattRangeType").show();
		$("relattValues").hide();
	}
	
	smwhgLogger.log(categoryName, "OB","clicked");
	
	function callbackOnCategorySelect(request) {
		OB_instance_pendingIndicator.hide();
	  	if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
		}
		
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	 }
	 function callbackOnCategorySelect2(request) {
	 	OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  			GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
		}
		var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
	  	transformer.transformResultToHTML(request,relattDIV);
	 }
	 this.OB_currentlySelectedCategory = categoryName;
	 
	 if (OB_LEFT_ARROW == 0) {
	 	OB_instance_pendingIndicator.show();
	 	dataAccess.getInstances(categoryName, 0, callbackOnCategorySelect);
	 }
	 if (OB_RIGHT_ARROW == 0) {
	 	OB_relatt_pendingIndicator.show();
	 	dataAccess.getProperties(categoryName, callbackOnCategorySelect2);
	 }
	
	}
},


selectNextPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
	}
	this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);
	
},

selectPreviousPartition: function (e, htmlNode) {
	
	function calledOnFinish(tree) {
		dataAccess.OB_cachedCategoryTree = tree;
	}
	this._selectPreviousPartition(e, htmlNode, dataAccess.OB_cachedCategoryTree, dataAccess.getCategoryPartition.bind(dataAccess), "categoryTree", calledOnFinish);

}



});


var OBInstanceActionListener = Class.create();
OBInstanceActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToInstance: function(event, node, instanceName) {
		smwhgLogger.log(instanceName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(null, instanceName);
		
	},
	
	showSuperCategory: function(event, node, categoryName) {
		function filterBrowsingCategoryCallback(request) {
	 	var categoryDIV = $("categoryTree");
	 	if (categoryDIV.firstChild) {
	 		GeneralBrowserTools.purge(categoryDIV.firstChild);
			categoryDIV.removeChild(categoryDIV.firstChild);
		}
	  	dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	 }
     globalActionListener.switchTreeComponent(null, 'categoryTree', true);
	 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+categoryName], filterBrowsingCategoryCallback);
   	
	},
	
	selectInstance: function (event, node, instanceName) {
	
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
		OB_oldSelectedInstanceNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedInstanceNode, node);
		
		smwhgLogger.log(instanceName, "OB","clicked");
		
		function callbackOnInstanceSelectToRight(request) {
		OB_relatt_pendingIndicator.hide();
	  	if (relattDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(relattDIV.firstChild);
				relattDIV.removeChild(relattDIV.firstChild);
			}
			var xmlFragmentPropertyList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedProperties = xmlFragmentPropertyList;
	  		transformer.transformResultToHTML(request,relattDIV);
	  		if (OB_bd.isGecko) {
	  			// FF needs repasting for chemical formulas and equations because FF's XSLT processor does not know 'disable-output-encoding' switch. IE does.
	  			// thus, repaste markup on all elements marked with a 'chemFoEq' attribute
	  			GeneralBrowserTools.repasteMarkup("chemFoEq");
	  		}
	  	}
	  	
	  	function callbackOnInstanceSelectToLeft (request) {
	  		OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	
	  	
	  	if (OB_RIGHT_ARROW == 0) {
	  		OB_relatt_pendingIndicator.show();
		 	sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instanceName], callbackOnInstanceSelectToRight);
	  	} 
	  	if (OB_LEFT_ARROW == 1) {
	  		OB_tree_pendingIndicator.show();
	  		sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForInstance',instanceName], callbackOnInstanceSelectToLeft);
	  	}
	
		}
	},
	
	selectNextPartition: function (e, htmlNode) {
			
		var partition = htmlNode.getAttribute("partitionnum");
		partition++;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.OB_currentlySelectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.OB_currentlySelectedAttribute, partition, this.selectPartitionCallback.bind(this));
		} else { // relation tree
			dataAccess.getInstancesUsingProperty(relationActionListener.OB_currentlySelectedRelation, partition, this.selectPartitionCallback.bind(this));
		}
	},
	
	selectPreviousPartition: function (e, htmlNode) {
		
		var partition = htmlNode.getAttribute("partitionnum");
		partition--;
		OB_instance_pendingIndicator.show();
		if (globalActionListener.activeTreeName == 'categoryTree') {
			dataAccess.getInstances(categoryActionListener.OB_currentlySelectedCategory, partition, this.selectPartitionCallback.bind(this));
		} else if (globalActionListener.activeTreeName == 'propertyTree') {
			dataAccess.getInstancesUsingProperty(propertyActionListener.OB_currentlySelectedAttribute, partition, this.selectPartitionCallback.bind(this));
		} else { // relation tree
			dataAccess.getInstancesUsingProperty(relationActionListener.OB_currentlySelectedRelation, partition, this.selectPartitionCallback.bind(this));
		}
	},
	
	selectPartitionCallback: function (request) {
			OB_instance_pendingIndicator.hide();
			var instanceListNode = $("instanceList");			
			GeneralXMLTools.removeAllChildNodes(instanceListNode);
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
			transformer.transformXMLToHTML(xmlFragmentInstanceList, instanceListNode, true);
	},
	/*
 	* Hides/Shows instance box
 	*/
	toggleInstanceBox: function (event) {
		if ($("instanceContainer").visible()) {
			$("hideInstancesButton").innerHTML = gLanguage.getMessage('SHOW_INSTANCES');
			Effect.Fold("instanceContainer");
			Effect.Fold($("leftArrow"));
		} else {
			new Effect.Unfold('instanceContainer');
			$("hideInstancesButton").innerHTML = gLanguage.getMessage('HIDE_INSTANCES');
			new Effect.Unfold($("leftArrow"));
		}
	}
	
}

/**
 * Action Listener for attributes in the attribute tree
 */
var OBPropertyTreeActionListener = Class.create();
OBPropertyTreeActionListener.prototype = Object.extend(new OBTreeActionListener() , {
  initialize: function() {
		
		this.OB_currentlySelectedAttribute = null;
	},
	
	navigateToEntity: function(event, node, attributeName) {
		smwhgLogger.log(attributeName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
	},
	
  select: function (event, node, attributeID, attributeName) {
  			var e = GeneralTools.getEvent(event);
	
		// if Ctrl is pressed: navigation mode
		if (e["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
		
		var nextDIV = node.nextSibling;
	
 		// find the next DIV
		while(nextDIV.nodeName != "DIV") {
		nextDIV = nextDIV.nextSibling;
		}
		// check if node is already expanded and expand it if not
		if (!nextDIV.hasChildNodes()  || nextDIV.style.display == 'none') {
			this.toggleExpand(event, node, attributeID);
		}
		
		var instanceDIV = document.getElementById("instanceList");
		
		OB_oldSelectedAttributeNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedAttributeNode, node);
	
		smwhgLogger.log(attributeName, "OB","clicked");	
	
		function callbackOnPropertySelect(request) {
			OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			 	GeneralBrowserTools.purge(instanceDIV.firstChild);
					instanceDIV.removeChild(instanceDIV.firstChild);
			}
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	 	 	transformer.transformResultToHTML(request,instanceDIV, true);
		}
	     OB_instance_pendingIndicator.show();
	 	 this.OB_currentlySelectedAttribute = attributeName;
	 	 dataAccess.getInstancesUsingProperty(attributeName, 0, callbackOnPropertySelect);
		}
	},
	
  toggleExpand: function (event, node, folderCode) {
  	this._toggleExpand(event, node, folderCode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertySubTree.bind(dataAccess));
  },
  selectNextPartition: function (e, htmlNode) {
	function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
		}
		this._selectNextPartition(e, htmlNode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertyPartition.bind(dataAccess), "propertyTree", calledOnFinish);

	},

	selectPreviousPartition: function (e, htmlNode) {
	 function calledOnFinish(tree) {
			dataAccess.OB_cachedPropertyTree = tree;
		}
		this._selectPreviousPartition(e, htmlNode, dataAccess.OB_cachedPropertyTree, dataAccess.getPropertyPartition.bind(dataAccess), "propertyTree", calledOnFinish);
	
	}
	
});


/**
 * Action Listener for attribute and relation annotations
 */
var OBAnnotationActionListener = Class.create();
OBAnnotationActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToTarget: function(event, node, targetInstance) {
		GeneralBrowserTools.navigateToPage(null, targetInstance);
	},
	
	selectAttribute: function(event, node, attributeName) {
		// delegate to schemaPropertyListener
		schemaActionPropertyListener.selectAttribute(event, node, attributeName);
	},
	selectRelation: function(event, node, relationName) {
		// delegate to schemaPropertyListener
		schemaActionPropertyListener.selectRelation(event, node, relationName);
	}
}

/**
 * Action Listener for schema properties, i.e. attributes and relations
 * on schema level
 */
var OBSchemaPropertyActionListener = Class.create();
OBSchemaPropertyActionListener.prototype = {
	initialize: function() {
		//empty
		
	},
	
	navigateToAttribute: function(event, node, attributeName) {
		smwhgLogger.log(attributeName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
	},
	
	navigateToRelation: function(event, node, relationName) {
		smwhgLogger.log(relationName, "OB","inspect_entity");
		GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), relationName);
	},
	
	selectAttribute: function(event, node, attributeName) {
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		OB_oldSelectedAttributeNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedAttributeNode, node);
		
		smwhgLogger.log(attributeName, "OB","clicked");	
		
		function callbackOnPropertySelectForCategory (request) {
			OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	function callbackOnPropertySelectForInstance (request) {
	  		OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
		
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  		transformer.transformResultToHTML(request,instanceDIV, true);
	  	}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('PROPERTY_NS_WOC'), attributeName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForProperty',attributeName], callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				 OB_instance_pendingIndicator.show();
				 this.OB_currentlySelectedAttribute = attributeName;
				 dataAccess.getInstancesUsingProperty(attributeName, 0, callbackOnPropertySelectForInstance);
			}
		}
	},
	selectRelation: function(event, node, relationName) {
	
		var categoryDIV = $("categoryTree");
		var instanceDIV = $("instanceList");
		
		OB_oldSelectedRelationNode = GeneralBrowserTools.toggleHighlighting(OB_oldSelectedRelationNode, node);
		
		smwhgLogger.log(relationName, "OB","clicked");	
		
		function callbackOnPropertySelectForCategory (request) {
			OB_tree_pendingIndicator.hide();
	  		if (categoryDIV.firstChild) {
	  		  	GeneralBrowserTools.purge(categoryDIV.firstChild);
				categoryDIV.removeChild(categoryDIV.firstChild);
			}
			dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	  	}
	  	
	  	function callbackOnPropertySelectForInstance (request) {
	  		OB_instance_pendingIndicator.hide();
	  		if (instanceDIV.firstChild) {
	  			GeneralBrowserTools.purge(instanceDIV.firstChild);
				instanceDIV.removeChild(instanceDIV.firstChild);
			}
		
			var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
			dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  		transformer.transformResultToHTML(request,instanceDIV, true);
	  	}
		// if Ctrl is pressed: navigation mode
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('RELATION_NS_WOC'), relationName);
		} else {
			if (OB_LEFT_ARROW == 1) {
				OB_tree_pendingIndicator.show();
				sajax_do_call('smwfOntologyBrowserAccess', ['getCategoryForProperty',relationName, gLanguage.getMessage('RELATION_NS_WOC')], callbackOnPropertySelectForCategory);
			}
			if (OB_RIGHT_ARROW == 1) {
				OB_instance_pendingIndicator.show();
				this.OB_currentlySelectedRelation = relationName;
			 	dataAccess.getInstancesUsingProperty(relationName, 0, callbackOnPropertySelectForInstance);
			}
		}
	},
	
	selectRangeInstance: function(event, node, categoryName) {
		if (event["ctrlKey"]) {
			GeneralBrowserTools.navigateToPage(gLanguage.getMessage('CATEGORY_NS_WOC'), categoryName);
		}
	}
}

/**
 * Action Listener for global Ontology Browser events, e.g. switch tree
 */
var OBGlobalActionListener = Class.create();
OBGlobalActionListener.prototype = {
	initialize: function() {
		this.activeTreeName = 'categoryTree';
		var inputs = document.getElementsByTagName("input");
		new Form.Element.Observer(inputs[1], 0.5, this.filterTree.bindAsEventListener(this));
		new Form.Element.Observer(inputs[2], 0.5, this.filterInstances.bindAsEventListener(this));
		new Form.Element.Observer(inputs[3], 0.5, this.filterProperties.bindAsEventListener(this));
		
		// make sure that OntologyBrowser Filter search gets focus if a key is pressed
		Event.observe(document, 'keydown', function(event) { 
			if (event.target.id == 'searchInput') return;
			if (event.target.parentNode != document && $(event.target.parentNode).hasClassName('OB-filters')) return;
			$('FilterBrowserInput').focus() 
		});
	},
	
	/*
	 * Switches to the given tree.
 	*/
	switchTreeComponent: function (event, showWhichTree, noInitialize) {
		if ($("categoryTree").visible() && showWhichTree != 'categoryTree') {
			$("categoryTree").hide();
			$(showWhichTree).show();
			$(showWhichTree+"Switch").addClassName("selectedSwitch");
			$("categoryTreeSwitch").removeClassName("selectedSwitch");
			
		} else if ($("propertyTree").visible() && showWhichTree != 'propertyTree') {
			$("propertyTree").hide();
			$(showWhichTree).show();
			$(showWhichTree+"Switch").addClassName("selectedSwitch");
			$("propertyTreeSwitch").removeClassName("selectedSwitch");
		}
		
		this.activeTreeName = showWhichTree;
		
		if (!noInitialize) {
			if (showWhichTree == 'categoryTree') {
				dataAccess.initializeRootCategories(0);
				
			} else if (showWhichTree == 'propertyTree') {
				dataAccess.initializeRootProperties(0);
				
			} 
		}
		
		
	},
	
	/**
	 * Global filter event listener
	 */
	filterTree: function(event) {
		
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[1].value;
		var tree;
		var actionListener;
		if (this.activeTreeName == 'categoryTree') {
			actionListener = categoryActionListener;
			tree = dataAccess.OB_cachedCategoryTree;
			if (filter == "") { //special case empty filter, just copy
				dataAccess.initializeRootCategories(0);
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				return;
			}	
		} else if (this.activeTreeName == 'propertyTree') {
			actionListener = propertyActionListener;	
			tree = dataAccess.OB_cachedPropertyTree;
			if (filter == "") {
				dataAccess.initializeRootProperties(0);
				transformer.transformXMLToHTML(dataAccess.OB_currentlyDisplayedTree, $(this.activeTreeName), true);
				return;
			}
		}  
		
		actionListener._filterTree(event, tree, this.activeTreeName, filter);
		
		
	},
	
	filterInstances: function(event) {
		if (dataAccess.OB_cachedInstances == null) {
			return;
		}
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[2].value;
		
		var regex = new Array();
    	var filterTerms = GeneralTools.splitSearchTerm(filter);
    	for(var i = 0, n = filterTerms.length; i < n; i++) {
    		try{
	   		 regex[i] = new RegExp(filterTerms[i],"i");
    		} catch(e) {
    			return;
    		}
    	}
    		
		var nodesFound = GeneralXMLTools.createDocumentFromString("<instanceList/>");
		var instanceList = dataAccess.OB_cachedInstances.firstChild;
		for (var i = 0, n = instanceList.childNodes.length; i < n; i++) {
			var inst = instanceList.childNodes[i];
			var	title = inst.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
			if (inst.tagName == 'instancePartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, inst, true);
			}
		}
		transformer.transformXMLToHTML(nodesFound, $("instanceList"), true); 
	},
	
	filterProperties: function(event) {
		if (dataAccess.OB_cachedProperties == null) {
			return;
		}
		var inputs = document.getElementsByTagName("input");
		var filter = inputs[3].value;
		
		var regex = new Array();
    	var filterTerms = GeneralTools.splitSearchTerm(filter);
    	for(var i = 0, n = filterTerms.length; i < n; i++) {
    		try {
	   		 regex[i] = new RegExp(filterTerms[i],"i");
    		} catch(e) {
    			return;
    		}
    	}
    	
		var tagName = dataAccess.OB_cachedProperties.firstChild.tagName;
		var nodesFound = GeneralXMLTools.createDocumentFromString("<"+tagName+"/>");
		var propertyList = dataAccess.OB_cachedProperties.firstChild;
		for (var i = 0, n = propertyList.childNodes.length; i < n; i++) {
			var property = propertyList.childNodes[i];
			var	title = property.getAttribute("title");
			if (title && GeneralTools.matchArrayOfRegExp(title, regex)) {
				GeneralXMLTools.importNode(nodesFound.firstChild, property, true);
			}
			if (property.tagName == 'propertyPartition') {
				GeneralXMLTools.importNode(nodesFound.firstChild, property, true);
			}
		}
		transformer.transformXMLToHTML(nodesFound, $("relattributes"), true); 
		GeneralBrowserTools.repasteMarkup("chemFoEq");
	},
	
	/**
	 * Global filter event listener
	 */
	filterRoot: function(event) {
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
	
	filterBrowsing: function(event, force) {
		
	 function filterBrowsingCategoryCallback(request) {
	 	OB_tree_pendingIndicator.hide();
	 	var categoryDIV = $("categoryTree");
	 	if (categoryDIV.firstChild) {
	 		GeneralBrowserTools.purge(categoryDIV.firstChild);
			categoryDIV.removeChild(categoryDIV.firstChild);
		}
	  	dataAccess.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, categoryDIV);
	 }
	 
	  function filterBrowsingAttributeCallback(request) {
	 	OB_tree_pendingIndicator.hide();
	 	var attributeDIV = $("propertyTree");
	 	if (attributeDIV.firstChild) {
	 		GeneralBrowserTools.purge(attributeDIV.firstChild);
			attributeDIV.removeChild(attributeDIV.firstChild);
		}
	  	dataAccess.OB_cachedPropertyTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  		dataAccess.OB_currentlyDisplayedTree = dataAccess.updateTree(request.responseText, attributeDIV);
	 }
	 
	 
	 
	 function filterBrowsingInstanceCallback(request) {
	 	OB_instance_pendingIndicator.hide();
	 	var instanceDIV = $("instanceList");
	 	if (instanceDIV.firstChild) {
	 		GeneralBrowserTools.purge(instanceDIV.firstChild);
			instanceDIV.removeChild(instanceDIV.firstChild);
		}
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	 }
	 
	 function filterBrowsingPropertyCallback(request) {
	 	OB_relatt_pendingIndicator.hide();
	 	var propertyDIV = $("relattributes");
	 	if (propertyDIV.firstChild) {
	 		GeneralBrowserTools.purge(propertyDIV.firstChild);
			propertyDIV.removeChild(propertyDIV.firstChild);
		}
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		dataAccess.OB_cachedProperties = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,propertyDIV, true);
	 }
	 
	 if (!force && event["keyCode"] != 13 ) {
	 	return;
	 }
	 var inputs = document.getElementsByTagName("input");
	 var hint = inputs[0].value;
	 
	 if (hint.length <= 1) {
	 	alert(gLanguage.getMessage('ENTER_MORE_LETTERS'));
	 	return;
	 }
	 if (this.activeTreeName == 'categoryTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
		 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+hint], filterBrowsingCategoryCallback);
	 }  else if (this.activeTreeName == 'propertyTree') {
	 	 OB_tree_pendingIndicator.show(this.activeTreeName);
         sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"propertyTree,"+hint], filterBrowsingAttributeCallback);
	 } 
	  OB_instance_pendingIndicator.show();
	  OB_relatt_pendingIndicator.show();
	  sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"instance,"+hint], filterBrowsingInstanceCallback);	
	  sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"property,"+hint], filterBrowsingPropertyCallback);
	 
	},
	
	reset: function(event) {
		if (this.activeTreeName == 'categoryTree') {
			dataAccess.initializeRootCategories(0, true);
		} else if (this.activeTreeName == 'propertyTree') {
			dataAccess.initializeRootProperties(0, true);

		} 
		// clear input field
		var inputs = document.getElementsByTagName("input");
		inputs[0].value = "";
	},
	
	toogleCatInstArrow: function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB","flipflow_left");
		if (OB_LEFT_ARROW == 0) {
			OB_LEFT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_LEFT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	},
	
	toogleInstPropArrow: function(event) {
		var img = Event.element(event);
		smwhgLogger.log("", "OB","flipflow_right");
		if (OB_RIGHT_ARROW == 0) {
			OB_RIGHT_ARROW = 1;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow_left.gif");
		} else {
			OB_RIGHT_ARROW = 0;
			img.setAttribute("src",wgScriptPath+"/extensions/SMWHalo/skins/OntologyBrowser/images/bigarrow.gif");
		}
	}
}


	



