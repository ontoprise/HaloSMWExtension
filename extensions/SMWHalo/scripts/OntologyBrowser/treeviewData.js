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
 * Treeview Data
 */


// action listeners are global.
var categoryActionListener = null;
var instanceActionListener = null;
var globalActionListener = null;

// standard partition size
var OB_partitionSize = 40;

// Data for category trees

var OBDataAccess = Class.create();
OBDataAccess.prototype = {
	initialize: function() {
		
		// cached trees
		this.OB_cachedCategoryTree = null;
		this.OB_cachedPropertyTree = null;
	
		this.OB_cachedInstances = null;
		this.OB_cachedProperties = null;
		
		// displayed tree
		this.OB_currentlyDisplayedTree = null;
		
		// initialize flags
		this.OB_categoriesInitialized = false;
		this.OB_attributesInitialized = false;
	
		
		 // initialize action listeners
		 // note: action listeners are global!
   		categoryActionListener = new OBCategoryTreeActionListener();
		instanceActionListener = new OBInstanceActionListener();
		propertyActionListener = new OBPropertyTreeActionListener();
		globalActionListener = new OBGlobalActionListener();
		annotationActionListener = new OBAnnotationActionListener();
		schemaActionPropertyListener = new OBSchemaPropertyActionListener();
		
		// One global instance of OBPendingIndicator for each container. 
		// The tree container has only one for the categoryTree (or any other tree)
		OB_tree_pendingIndicator = new OBPendingIndicator($("categoryTree"));
		OB_instance_pendingIndicator = new OBPendingIndicator($("instanceList"));
		OB_relatt_pendingIndicator = new OBPendingIndicator($("relattributes"));
	
	}, 
	
initializeTree: function (param) {
	// ----- initialize with appropriate data -------
	var title = GeneralBrowserTools.getURLParameter("entitytitle");
	var ns = GeneralBrowserTools.getURLParameter("ns");
	var searchTerm = GeneralBrowserTools.getURLParameter("searchTerm");
	
	// high priority: searchTerm in URL
	if (searchTerm != undefined) {
		var inputs = document.getElementsByTagName("input");
	 	inputs[0].value = searchTerm;
		globalActionListener.filterBrowsing(null, true);
		return;
	}
	
	// if no params: default initialization
	if (title == undefined && ns == undefined) {
  		// default: initialize with root categories
		this.initializeRootCategories();
		return;
   }
   
   // otherwise use namespace and title parameters
   if (ns == gLanguage.getMessage('CATEGORY_NS_WOC')) {
   	this.filterBrowseCategories(title);
   } else if (ns == undefined || ns == '') { // => NS_MAIN
   	this.filterBrowseInstances(title);
   } else if (ns == gLanguage.getMessage('PROPERTY_NS_WOC')) {
    this.filterBrowseProperties(title);
   } 
},

initializeRootCategoriesCallback: function (request) {
  OB_tree_pendingIndicator.hide();
  if ( request.status != 200 ) {
   alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
	return;
  }
  
 	 this.OB_categoriesInitialized = true;
  
	var rootElement = $("categoryTree");
 
  // parse root category xml and transform it to HTML
   	this.OB_cachedCategoryTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	//transformer.transformXMLToHTML(this.OB_cachedCategoryTree, rootElement, true);
  	this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 	selectionProvider.fireRefresh();
  	
},

initializeRootPropertyCallback: function (request) {
  OB_tree_pendingIndicator.hide();
  if ( request.status != 200 ) {
   alert("Error: " + request.status + " " + request.statusText + ": " + request.responseText);
	return;
  }
  
 
  	
  	this.OB_attributesInitialized = true;
  
	var rootElement = $("propertyTree");
 
  // parse root category xml and transform it to HTML
   	this.OB_cachedPropertyTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	//transformer.transformXMLToHTML(this.OB_cachedPropertyTree, rootElement, true);
  	this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString(request.responseText);
  	transformer.transformXMLToHTML(this.OB_currentlyDisplayedTree, rootElement, true);
 	selectionProvider.fireRefresh();
  	
},


updateTree: function(xmlText, rootElement) {
	var tree = GeneralXMLTools.createDocumentFromString(xmlText);
  	transformer.transformXMLToHTML(tree, rootElement, true);
  	selectionProvider.fireRefresh();
  	return tree;
},

initializeRootCategories: function(partition, force) {
	if (!this.OB_categoriesInitialized || force) {
		OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootCategories',OB_partitionSize+","+partition], this.initializeRootCategoriesCallback.bind(this));
	} else {
  		// copy from cache
  		this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString("<result/>");
  		GeneralXMLTools.importSubtree(this.OB_currentlyDisplayedTree.firstChild, this.OB_cachedCategoryTree.firstChild, true);
  } 	
},

initializeRootProperties: function(partition, force) {
	 if (!this.OB_attributesInitialized || force) {
	 	OB_tree_pendingIndicator.show(globalActionListener.activeTreeName);
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootProperties',OB_partitionSize+","+partition], this.initializeRootPropertyCallback.bind(this));
	 } else {
  		// copy from cache
  		this.OB_currentlyDisplayedTree = GeneralXMLTools.createDocumentFromString("<result/>");
  		GeneralXMLTools.importSubtree(this.OB_currentlyDisplayedTree.firstChild, this.OB_cachedPropertyTree.firstChild, true);
	}
},


/*
 * Category Subtree hook
 * param: title of parent node
 * callBack: callBack to be called from AJAX request
 */
getCategorySubTree: function (categoryID, categoryName, callBackOnAjax, callBackOnCache) {
	var nodeToExpand = GeneralXMLTools.getNodeById(this.OB_cachedCategoryTree, categoryID);
	if (nodeToExpand != null && nodeToExpand.getElementsByTagName('conceptTreeElement').length > 0) {
		// copy it from cache to displayed tree.
		var nodeInDisplayedTree = GeneralXMLTools.getNodeById(this.OB_currentlyDisplayedTree, categoryID);
		GeneralXMLTools.importSubtree(nodeInDisplayedTree, nodeToExpand);
		
		// create result dummy document and call 'callBackOnCache' to transform
		var subtree = GeneralXMLTools.createDocumentFromString("<result/>");
		GeneralXMLTools.importSubtree(subtree.firstChild, nodeToExpand);
		callBackOnCache(subtree);
		
		
	} else {
		// download it
		this.getCategoryPartition(false, 0, categoryName, callBackOnAjax);
	}
},

getPropertySubTree: function (attributeID, attributeName, callBackOnAjax, callBackOnCache) {
	var nodeToExpand = GeneralXMLTools.getNodeById(this.OB_cachedPropertyTree, attributeID);
	if (nodeToExpand != null && nodeToExpand.getElementsByTagName('propertyTreeElement').length > 0) {
		// copy it from cache to displayed tree.
		var nodeInDisplayedTree = GeneralXMLTools.getNodeById(this.OB_currentlyDisplayedTree, attributeID);
		GeneralXMLTools.importSubtree(nodeInDisplayedTree, nodeToExpand);
		
		// create result dummy document and call 'callBackOnCache' to transform
		var subtree = GeneralXMLTools.createDocumentFromString("<result/>");
		GeneralXMLTools.importSubtree(subtree.firstChild, nodeToExpand);
		callBackOnCache(subtree);
	} else {
		// download it
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+",0"],  callBackOnAjax);
	}
},



getInstances: function(categoryName, partition, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getInstance',categoryName+","+OB_partitionSize+","+partition], callback);
},

getProperties: function(categoryName, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getProperties',categoryName], callback);
},

getAnnotations: function(instanceName, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instanceName], callback);
},

getCategoryPartition: function(isRootLevel, partition, categoryName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootCategories',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubCategory',categoryName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},

getPropertyPartition: function(isRootLevel, partition, attributeName, selectPartitionCallback) {
	if (isRootLevel) {
		// root level
		sajax_do_call('smwfOntologyBrowserAccess', ['getRootProperties',OB_partitionSize+','+partition],  selectPartitionCallback);
	} else {
		// every other level
		sajax_do_call('smwfOntologyBrowserAccess', ['getSubProperties',attributeName+","+OB_partitionSize+","+partition],  selectPartitionCallback);
	}
},



getInstancesUsingProperty: function(propertyName, partition, callback) {
	sajax_do_call('smwfOntologyBrowserAccess', ['getInstancesUsingProperty',propertyName+","+OB_partitionSize+","+partition], callback);
},

filterBrowseCategories: function(title) {
	// initialize with given category
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
	OB_tree_pendingIndicator.show(); 
   	globalActionListener.switchTreeComponent(null, 'categoryTree', true);
	sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"category,"+title], filterBrowsingCategoryCallback);
   	
},

filterBrowseInstances: function(title) {
	// initialize with given instance
   	 function filterBrowsingInstanceCallback(request) {
   	 	OB_instance_pendingIndicator.hide();
	 	var instanceDIV = $("instanceList");
	 	if (instanceDIV.firstChild) {
	 		GeneralBrowserTools.purge(instanceDIV.firstChild);
			instanceDIV.removeChild(instanceDIV.firstChild);
		}
		var xmlFragmentInstanceList = GeneralXMLTools.createDocumentFromString(request.responseText);
		
		// if only one instance found -> fetch annotations too
		if (xmlFragmentInstanceList.firstChild.childNodes.length == 1) {
			var instance = xmlFragmentInstanceList.firstChild.firstChild;
			OB_relatt_pendingIndicator.show();
			sajax_do_call('smwfOntologyBrowserAccess', ['getAnnotations',instance.getAttribute("title")], getAnnotationsCallback);
		}
		dataAccess.OB_cachedInstances = xmlFragmentInstanceList;
	  	transformer.transformResultToHTML(request,instanceDIV, true);
	 }
	 
	 function getAnnotationsCallback(request) {
	 	OB_relatt_pendingIndicator.hide();
	 	var relattDIV = $("relattributes");
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
	 
	 OB_instance_pendingIndicator.show();
	
   	 sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"instance,"+title], filterBrowsingInstanceCallback);	
   	
},

filterBrowseProperties: function(title) {
		// initialize with given attribute
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
	 OB_tree_pendingIndicator.show(); 
	globalActionListener.switchTreeComponent(null, 'propertyTree', true);
   	sajax_do_call('smwfOntologyBrowserAccess', ['filterBrowse',"propertyTree,"+title], filterBrowsingAttributeCallback);
}


};