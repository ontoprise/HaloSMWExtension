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


// commandIDs
var SMW_OB_COMMAND_ADDSUBCATEGORY = 1;
var SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL = 2;
var SMW_OB_COMMAND_SUBCATEGORY_RENAME = 3;

var SMW_OB_COMMAND_ADDSUBPROPERTY = 4;
var SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL = 5;
var SMW_OB_COMMAND_SUBPROPERTY_RENAME = 6;

var SMW_OB_COMMAND_INSTANCE_DELETE = 7;
var SMW_OB_COMMAND_INSTANCE_RENAME = 8;

var SMW_OB_COMMAND_ADD_SCHEMAPROPERTY = 9;


// Event types
var OB_SELECTIONLISTENER = 'selectionChanged';
var OB_BEFOREREFRESHLISTENER = 'beforeRefresh';
var OB_REFRESHLISTENER = 'refresh';

/**
 * Event Provider. Supports following events:
 * 
 *  1. selectionChanged
 *  2. refresh
 */
var OBEventProvider = Class.create();
OBEventProvider.prototype = {
	initialize: function() {
		this.listeners = new Array();
	},
	
	/**
	 * @public
	 * 
	 * Adds a listener.
	 * 
	 * @param listener 
	 * @param type
	 */
	addListener: function(listener, type) {
		if (this.listeners[type] == null) {
			this.listeners[type] = new Array();
		} 
		if (typeof(listener[type] == 'function')) { 
			this.listeners[type].push(listener);
		}
	},
	
	/**
	 * @public
	 * 
	 * Removes a listener.
	 * 
	 * @param listener 
	 * @param type
	 */
	removeListener: function(listener, type) {
		if (this.listeners[type] == null) return;
		this.listeners[type] = this.listeners[type].without(listener);
	},
	
	/**
	 * @public
	 * 
	 * Fires selectionChanged event. The listener method 
	 * must have the name 'selectionChanged' with the following
	 * signature:
	 * 
	 * @param id ID of selected element in DOM/XML tree.
	 * @param title Title of selected element
	 * @param ns namespace
	 * @param node in HTML DOM tree.
	 */
	fireSelectionChanged: function(id, title, ns, node) {
		this.listeners[OB_SELECTIONLISTENER].each(function (l) { 
			l.selectionChanged(id, title, ns, node);
		});
	},
	
	/**
	 * @public
	 * 
	 * Fires refresh event. The listener method 
	 * must have the name 'refresh'
	 */
	fireRefresh: function() {
		this.listeners[OB_REFRESHLISTENER].each(function (l) { 
			l.refresh();
		});
	},
	
	fireBeforeRefresh: function() {
		this.listeners[OB_BEFOREREFRESHLISTENER].each(function (l) { 
			l.beforeRefresh();
		});
	}
}	

// create instance of event provider
var selectionProvider = new OBEventProvider();	

/**
 * Class which allows modification of wiki articles
 * via AJAX calls.
 */
var OBArticleCreator = Class.create();
OBArticleCreator.prototype = { 
	initialize: function() {
		this.pendingIndicator = new OBPendingIndicator();
	},
	
	/**
	 * @public
	 * 
	 * Creates an article
	 * 
	 * @param title Title of article
	 * @param content Text which is used when article is created
	 * @param optionalText Text which is appended when article already exists.
	 * @param creationComment comment
	 * @param callback Function called when creation has finished successfully.
	 * @param node HTML node used for displaying a pending indicator.
	 */
	createArticle : function(title, content, optionalText, creationComment,
	                         callback, node) {
		
		function ajaxResponseCreateArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				return;
			}
			
			var answer = request.responseText;
			var regex = /(true|false),(true|false),(.*)/;
			var parts = answer.match(regex);
			
			if (parts == null) {
				alert(gLanguage.getMessage('ERROR_CREATING_ARTICLE'));
				return;
			}
			
			var success = parts[1];
			var created = parts[2];
			var title = parts[3];
			
			if (success == "true") {
				callback(success, created, title);
			} 
		}
		this.pendingIndicator.show(node);
		sajax_do_call('smwfCreateArticle', 
		              [title, content, optionalText, creationComment], 
		              ajaxResponseCreateArticle.bind(this));
		              
	},
	
	/**
	 * @public
	 * 
	 * Deletes an article
	 * 
	 * @param title Title of article
	 * @param reason reason
	 * @param callback Function called when creation has finished successfully.
	 * @param node HTML node used for displaying a pending indicator.
	 */
	deleteArticle: function(title, reason, callback, node) {
		
		function ajaxResponseDeleteArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_DELETING_ARTICLE'));
				return;
			}
					
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfDeleteArticle', 
		              [title, reason], 
		              ajaxResponseDeleteArticle.bind(this));
	},
	
	/**
	 * @public
	 * 
	 * Renames an article
	 * 
	 * @param oldTitle Old title of article
	 * @param newTitle New title of article
	 * @param reason string
	 * @param callback Function called when creation has finished successfully.
	 * @param node HTML node used for displaying a pending indicator.
	 */
	renameArticle: function(oldTitle, newTitle, reason, callback, node) {
		
		function ajaxResponseRenameArticle(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_RENAMING_ARTICLE'));
				return;
			}
					
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfRenameArticle', 
		              [oldTitle, newTitle, reason], 
		              ajaxResponseRenameArticle.bind(this));
	},
	
	moveCategory: function(draggedCategory, oldSuperCategory, newSuperCategory, callback, node) {
		function ajaxResponseMoveCategory(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_MOVING_CATEGORY'));
				return;
			}
			if (request.responseText != 'true') {
				alert('Some error occured on category dragging!');
				return;
			}		
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfMoveCategory', 
		              [draggedCategory, oldSuperCategory, newSuperCategory], 
		              ajaxResponseMoveCategory.bind(this));
	},
	
	moveProperty: function(draggedProperty, oldSuperProperty, newSuperProperty, callback, node) {
		function ajaxResponseMoveProperty(request) {
			this.pendingIndicator.hide();
			if (request.status != 200) {
				alert(gLanguage.getMessage('ERROR_MOVING_PROPERTY'));
				return;
			}
			if (request.responseText != 'true') {
				alert('Some error occured on property dragging!');
				return;
			}		
			callback();
			
		}
		
		this.pendingIndicator.show(node);
		sajax_do_call('smwfMoveProperty', 
		              [draggedProperty, oldSuperProperty, newSuperProperty], 
		              ajaxResponseMoveProperty.bind(this));
	}
	
}
var articleCreator = new OBArticleCreator();

/**
 * Modifies the wiki ontology and internal OB model.
 */
var OBOntologyModifier = Class.create();
OBOntologyModifier.prototype = { 
	initialize: function() {
		this.date = new Date();
		this.count = 0;
	},
	
	/**
	 * @public
	 * 
	 * Adds a new subcategory
	 * 
	 * @param subCategoryTitle Title of new subcategory (must not exist!)
	 * @param superCategoryTitle Title of supercategory
	 * @param superCategoryID ID of supercategory in OB data model (XML)
	 */
	addSubcategory: function(subCategoryTitle, superCategoryTitle, superCategoryID) {
		function callback() {
			var subCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(subCategoryTitle));
			this.insertCategoryNode(superCategoryID, subCategoryXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(superCategoryID, superCategoryTitle, SMW_CATEGORY_NS, $(superCategoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY')+subCategoryTitle,  
			                   "[["+gLanguage.getMessage('CATEGORY')+superCategoryTitle+"]]", '',
							   gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(superCategoryID));
	},
	
	/**
	 * @public
	 * 
	 * Adds a new category as sibling of another.
	 * 
	 * @param newCategoryTitle Title of new category (must not exist!)
	 * @param siblingCategoryTitle Title of sibling
	 * @param sibligCategoryID ID of siblig category in OB data model (XML)
	 */
	addSubcategoryOnSameLevel: function(newCategoryTitle, siblingCategoryTitle, sibligCategoryID) {
		function callback() {
			var newCategoryXML = GeneralXMLTools.createDocumentFromString(this.createCategoryNode(newCategoryTitle));
			var superCategoryID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('id');
			this.insertCategoryNode(superCategoryID, newCategoryXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(sibligCategoryID, siblingCategoryTitle, SMW_CATEGORY_NS, $(sibligCategoryID))
			selectionProvider.fireRefresh();
		}
		var superCategoryTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, sibligCategoryID).parentNode.getAttribute('title');
		var content = superCategoryTitle != null ? "[["+gLanguage.getMessage('CATEGORY')+superCategoryTitle+"]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('CATEGORY')+newCategoryTitle, content, '',
							   gLanguage.getMessage('CREATE_SUB_CATEGORY'), callback.bind(this), $(sibligCategoryID));
	},
	
	/**
	 * @public
	 * 
	 * Renames a category.
	 * 
	 * @param newCategoryTitle New category title
	 * @param categoryTitle Old category title
	 * @param categoryID ID of category in OB data model (XML)
	 */
	renameCategory: function(newCategoryTitle, categoryTitle, categoryID) {
		function callback() {
			this.renameCategoryNode(categoryID, newCategoryTitle);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(categoryID, categoryTitle, SMW_CATEGORY_NS, $(categoryID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('CATEGORY')+categoryTitle, gLanguage.getMessage('CATEGORY')+newCategoryTitle, "OB", callback.bind(this), $(categoryID));
	},
	
	/**
	 * Move category so that draggedCategoryID is a new subcategory of droppedCategoryID
	 * 
	 * @param draggedCategoryID ID of category which is moved.
	 * @param droppedCategoryID ID of new supercategory of draggedCategory.
	 */
	moveCategory: function(draggedCategoryID, droppedCategoryID) {
		
		var from_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, draggedCategoryID);
		var to_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, droppedCategoryID);
		
		var from = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, draggedCategoryID);
		var to = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, droppedCategoryID);
		
		var draggedCategory = from_cache.getAttribute('title');
		var oldSuperCategory = from_cache.parentNode.getAttribute('title');
		var newSuperCategory = to_cache.getAttribute('title');
		
		function callback() {
			// only move subtree, if it has already been requested 
			if (to_cache.getAttribute('expanded') == 'true' || GeneralXMLTools.hasChildNodesWithTag(to_cache, 'conceptTreeElement')) { 
				GeneralXMLTools.importNode(to_cache, from_cache, true);
				GeneralXMLTools.importNode(to, from, true);
			}
			
			from.parentNode.removeChild(from);
			from_cache.parentNode.removeChild(from_cache);
			
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedCategoryTree, $('categoryTree'), true);
			
			selectionProvider.fireSelectionChanged(null, null, SMW_CATEGORY_NS, null);
			selectionProvider.fireRefresh();
		}
		articleCreator.moveCategory(draggedCategory, oldSuperCategory, newSuperCategory, callback.bind(this), $('categoryTree'));
	},
	
	/**
	 * Move property so that draggedPropertyID is a new subproperty of droppedPropertyID
	 * 
	 * @param draggedPropertyID ID of property which is moved.
	 * @param droppedPropertyID ID of new superproperty of draggedProperty.
	 */
	moveProperty: function(draggedPropertyID, droppedPropertyID) {
		
		var from_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, draggedPropertyID);
		var to_cache = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, droppedPropertyID);
		
		var from = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, draggedPropertyID);
		var to = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, droppedPropertyID);
		
		var draggedProperty = from_cache.getAttribute('title');
		var oldSuperProperty = from_cache.parentNode.getAttribute('title');
		var newSuperProperty = to_cache.getAttribute('title');
		
		function callback() {
			// only move subtree, if it has already been requested 
			if (to_cache.getAttribute('expanded') == 'true' || GeneralXMLTools.hasChildNodesWithTag(to_cache, 'propertyTreeElement')) { 
				GeneralXMLTools.importNode(to_cache, from_cache, true);
				GeneralXMLTools.importNode(to, from, true);
			}
			
			from.parentNode.removeChild(from);
			from_cache.parentNode.removeChild(from_cache);
			
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
			
			selectionProvider.fireSelectionChanged(null, null, SMW_PROPERTY_NS, null);
			selectionProvider.fireRefresh();
		}
		articleCreator.moveProperty(draggedProperty, oldSuperProperty, newSuperProperty, callback.bind(this), $('propertyTree'));
	},
	
	/**
	 * @public
	 * 
	 * Adds a new subproperty
	 * 
	 * @param subPropertyTitle Title of new subproperty (must not exist!)
	 * @param superPropertyTitle Title of superproperty
	 * @param superPropertyID ID of superproperty in OB data model (XML)
	 */
	addSubproperty: function(subPropertyTitle, superPropertyTitle, superPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(subPropertyTitle));
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
			
			selectionProvider.fireSelectionChanged(superPropertyID, superPropertyTitle, SMW_PROPERTY_NS, $(superPropertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+subPropertyTitle, '',   
			                    "\n[[SMW_SP_SUBPROPERTY_OF::"+gLanguage.getMessage('PROPERTY')+superPropertyTitle+"]]",
							 gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(superPropertyID));
	},
	
	/**
	 * @public
	 * 
	 * Adds a new property as sibling of another.
	 * 
	 * @param newPropertyTitle Title of new property (must not exist!)
	 * @param siblingPropertyTitle Title of sibling
	 * @param sibligPropertyID ID of siblig property in OB data model (XML)
	 */
	addSubpropertyOnSameLevel: function(newPropertyTitle, siblingPropertyTitle, sibligPropertyID) {
		function callback() {
			var subPropertyXML = GeneralXMLTools.createDocumentFromString(this.createPropertyNode(newPropertyTitle));
			var superPropertyID = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('id');
			this.insertPropertyNode(superPropertyID, subPropertyXML);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
		
			selectionProvider.fireSelectionChanged(sibligPropertyID, siblingPropertyTitle, SMW_PROPERTY_NS, $(sibligPropertyID))
			selectionProvider.fireRefresh();
		}
		
		var superPropertyTitle = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, sibligPropertyID).parentNode.getAttribute('title');
		var content = superPropertyTitle != null ? "\n[[SMW_SP_SUBPROPERTY_OF::"+gLanguage.getMessage('PROPERTY')+superPropertyTitle+"]]" : "";
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+superPropertyTitle, '',   
			                   content,
							 gLanguage.getMessage('CREATE_SUB_PROPERTY'), callback.bind(this), $(sibligPropertyID));
	},
	
	/**
	 * @public
	 * 
	 * Renames a property.
	 * 
	 * @param newPropertyTitle New property title
	 * @param oldPropertyTitle Old property title
	 * @param propertyID ID of property in OB data model (XML)
	 */
	renameProperty: function(newPropertyTitle, oldPropertyTitle, propertyID) {
		function callback() {
			this.renamePropertyNode(propertyID, newPropertyTitle);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedPropertyTree, $('propertyTree'), true);
			
			selectionProvider.fireSelectionChanged(propertyID, newPropertyTitle, SMW_PROPERTY_NS, $(propertyID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(gLanguage.getMessage('PROPERTY')+oldPropertyTitle, gLanguage.getMessage('PROPERTY')+newPropertyTitle, "OB", callback.bind(this), $(propertyID));
	},
	
	/**
	 * @public
	 * 
	 * Adds a new property with schema information.
	 * 
	 * @param propertyTitle Title of property
	 * @param minCard Minimum cardinality
	 * @param maxCard Maximum cardinality
	 * @param rangeOrTypes Array of range categories or types.
	 * @param builtinTypes Array of all existing builtin types.
	 * @param domainCategoryTitle Title of domain category
	 * @param domainCategoryID ID of domain category in OB data model (XML)
	 */
	addSchemaProperty: function(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, domainCategoryTitle, domainCategoryID) {
		function callback() {
			var newPropertyXML = GeneralXMLTools.createDocumentFromString(this.createSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, builtinTypes, domainCategoryTitle, domainCategoryID));
			dataAccess.OB_cachedProperties.documentElement.removeAttribute('isEmpty');
			dataAccess.OB_cachedProperties.documentElement.removeAttribute('textToDisplay');
			GeneralXMLTools.importNode(dataAccess.OB_cachedProperties.documentElement, newPropertyXML.documentElement, true);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedProperties, $('relattributes'), true);
					
			selectionProvider.fireRefresh();
		}
		
		var content = maxCard != '' ? "\n[[SMW_SSP_HAS_MAX_CARD::"+maxCard+"]]" : "";
		content += minCard != '' ? "\n[[SMW_SSP_HAS_MIN_CARD::"+minCard+"]]" : "";
		
		var rangeTypeStr = "";
		var rangeCategories = new Array();
		for(var i = 0, n = rangeOrTypes.length; i < n; i++) {
			if (builtinTypes.indexOf(rangeOrTypes[i]) != -1) {
				// is type
				rangeTypeStr += gLanguage.getMessage('TYPE')+rangeOrTypes[i]+(i == n-1 ? "" : ";");
			} else {
				rangeTypeStr += gLanguage.getMessage('TYPE_PAGE')+(i == n-1 ? "" : ";");
				rangeCategories.push(rangeOrTypes[i]);
			}
		}
		content += "\n[[SMW_SP_HAS_TYPE::"+rangeTypeStr+"]]";
		rangeCategories.each(function(c) { content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY')+domainCategoryTitle+"; "+gLanguage.getMessage('CATEGORY')+c+"]]" });
		if (rangeCategories.length == 0) {
			content += "\n[[SMW_SSP_HAS_DOMAIN_AND_RANGE_HINT::"+gLanguage.getMessage('CATEGORY')+domainCategoryTitle+"]]";
		}
		articleCreator.createArticle(gLanguage.getMessage('PROPERTY')+propertyTitle, '',   
			                   content,
							 gLanguage.getMessage('CREATE_PROPERTY'), callback.bind(this), $(domainCategoryID));
	},
	
	/**
	 * @public
	 * 
	 * Renames an instance.
	 * 
	 * @param newInstanceTitle
	 * @param oldInstanceTitle
	 * @param instanceID ID of instance node in OB data model (XML)
	 */
	renameInstance: function(newInstanceTitle, oldInstanceTitle, instanceID) {
		function callback() {
			this.renameInstanceNode(newInstanceTitle, instanceID);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
			
			selectionProvider.fireSelectionChanged(instanceID, newInstanceTitle, SMW_INSTANCE_NS, $(instanceID))
			selectionProvider.fireRefresh();
		}
		articleCreator.renameArticle(oldInstanceTitle, newInstanceTitle, "OB", callback.bind(this), $(instanceID));
	},
	
	/**
	 * @public
	 * 
	 * Deletes an instance.
	 * 
	 * @param instanceTitle
	 * @param instanceID ID of instance node in OB data model (XML)
	 */
	deleteInstance: function(instanceTitle, instanceID) {
		function callback() {
			this.deleteInstanceNode(instanceID);
			selectionProvider.fireBeforeRefresh();
			transformer.transformXMLToHTML(dataAccess.OB_cachedInstances, $('instanceList'), true);
			
			selectionProvider.fireSelectionChanged(null, null, SMW_INSTANCE_NS, null)
			selectionProvider.fireRefresh();
		}
		articleCreator.deleteArticle(instanceTitle, "OB", callback.bind(this), $(instanceID));
	},
	
	/**
	 * @private 
	 * 
	 * Creates a conceptTreeElement for internal OB data model (XML)
	 */
	createCategoryNode: function(subCategoryTitle) {
		this.count++;
		return '<conceptTreeElement title="'+subCategoryTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
	},
	
	/**
	 * @private 
	 * 
	 * Creates a propertyTreeElement for internal OB data model (XML)
	 */
	createPropertyNode: function(subPropertyTitle) {
		this.count++;
		return '<propertyTreeElement title="'+subPropertyTitle+'" id="ID_'+(this.date.getTime()+this.count)+'" isLeaf="true" expanded="true"/>';
	},
	
	/**
	 * @private 
	 * 
	 * Creates a property element for internal OB data model (XML)
	 */
	createSchemaProperty: function(propertyTitle, minCard, maxCard, typeRanges, builtinTypes, selectedTitle, selectedID) {
		this.count++;
		rangeTypes = "";
		for(var i = 0, n = typeRanges.length; i < n; i++) {
			if (builtinTypes.indexOf(typeRanges[i]) != -1) {
				// is type
				rangeTypes += '<rangeType>'+typeRanges[i]+'</rangeType>';
			} else {
				rangeTypes += '<rangeType>'+gLanguage.getMessage('TYPE_PAGE')+'</rangeType>';
			}
		}
		minCard = minCard == '' ? '0' : minCard;
		maxCard = maxCard == '' ? '*' : maxCard;
		return '<property title="'+propertyTitle+'" minCard="'+minCard+'" maxCard="'+maxCard+'">'+rangeTypes+'</property>';
	},
	
	/**
	 * @private 
	 * 
	 * Renames an instance node in internal OB data model (XML)
	 */
	renameInstanceNode: function(newInstanceTitle, instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
		instanceNode.removeAttribute("title");
		instanceNode.setAttribute("title", newInstanceTitle);
	},
	
	
	/**
	 * @private 
	 * 
	 * Deletes an instance node in internal OB data model (XML)
	 */
	deleteInstanceNode: function(instanceID) {
		var instanceNode = GeneralXMLTools.getNodeById(dataAccess.OB_cachedInstances, instanceID);
		instanceNode.parentNode.removeChild(instanceNode);
	},
	
	/**
	 * @private 
	 * 
	 * Inserts a category node in internal OB data model (XML) as subnode of another category node.
	 *
	 */
	insertCategoryNode: function(superCategoryID, subCategoryXML) {
		var superCategoryNodeCached = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, superCategoryID) : dataAccess.OB_cachedCategoryTree.documentElement;
		var superCategoryNodeDisplayed = superCategoryID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superCategoryID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
		
		// make sure that supercategory is no leaf anymore and set it to expanded now.
		superCategoryNodeCached.removeAttribute("isLeaf");
		superCategoryNodeCached.setAttribute("expanded", "true");
		superCategoryNodeDisplayed.removeAttribute("isLeaf");
		superCategoryNodeDisplayed.setAttribute("expanded", "true");
		
		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superCategoryNodeCached, subCategoryXML.documentElement, true);
		GeneralXMLTools.importNode(superCategoryNodeDisplayed, subCategoryXML.documentElement, true);
	},
	
	/**
	 * @private 
	 * 
	 * Inserts a property node in internal OB data model (XML) as subnode of another category node.
	 *
	 */
	insertPropertyNode: function(superpropertyID, subpropertyXML) {
		var superpropertyNodeCached = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, superpropertyID) : dataAccess.OB_cachedPropertyTree.documentElement;
		var superpropertyNodeDisplayed = superpropertyID != null ? GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, superpropertyID) : dataAccess.OB_currentlyDisplayedTree.documentElement;
		
		// make sure that superproperty is no leaf anymore and set it to expanded now.
		superpropertyNodeCached.removeAttribute("isLeaf");
		superpropertyNodeCached.setAttribute("expanded", "true");
		superpropertyNodeDisplayed.removeAttribute("isLeaf");
		superpropertyNodeDisplayed.setAttribute("expanded", "true");
		
		// insert in cache and displayed tree
		GeneralXMLTools.importNode(superpropertyNodeCached, subpropertyXML.documentElement, true);
		GeneralXMLTools.importNode(superpropertyNodeDisplayed, subpropertyXML.documentElement, true);
	},
	
	/**
	 * @private 
	 * 
	 * Renames a category node in internal OB data model (XML)
	 *
	 */
	renameCategoryNode: function(categoryID, newCategoryTitle) {
		var categoryNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedCategoryTree, categoryID);
		var categoryNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, categoryID);
		categoryNodeCached.removeAttribute("title");
		categoryNodeDisplayed.removeAttribute("title");
		categoryNodeCached.setAttribute("title", newCategoryTitle); //TODO: escape
		categoryNodeDisplayed.setAttribute("title", newCategoryTitle);
	
	},
	
	/**
	 * @private 
	 * 
	 * Renames a property node in internal OB data model (XML)
	 *
	 */
	renamePropertyNode: function(propertyID, newPropertyTitle) {
		var propertyNodeCached = GeneralXMLTools.getNodeById(dataAccess.OB_cachedPropertyTree, propertyID);
		var propertyNodeDisplayed = GeneralXMLTools.getNodeById(dataAccess.OB_currentlyDisplayedTree, propertyID);
		propertyNodeCached.removeAttribute("title");
		propertyNodeDisplayed.removeAttribute("title");
		propertyNodeCached.setAttribute("title", newPropertyTitle); //TODO: escape
		propertyNodeDisplayed.setAttribute("title", newPropertyTitle);
	}
}

// global object for ontology modification
var ontologyTools = new OBOntologyModifier();

/**
 * Input field validator. Provides an automatic validation 
 * triggering after the user finished typing.
 */
var OBInputFieldValidator = Class.create();
OBInputFieldValidator.prototype = {
	
	/**
	 * @public
	 * Constructor
	 * 
	 * @param id ID of INPUT field
	 * @param isValid Flag if the input field is initially valid.
	 * @param control Control object (derived from OBOntologySubMenu)
	 * @param validate_fnc Validation function
	 */
	initialize: function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;
		
		this.keyListener = null;
		this.blurListener = null;
		this.istyping = false;
		this.timerRegistered = false;
		
		this.isValid = isValid;
		this.lastValidation = null;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	OBInputFieldValidator: function(id, isValid, control, validate_fnc) {
		this.id = id;
		this.validate_fnc = validate_fnc;
		this.control = control;
		
		this.keyListener = null;
		this.blurListener = null;
		this.istyping = false;
		this.timerRegistered = false;
		
		this.isValid = isValid;
		this.lastValidation = null;
		
		if ($(this.id) != null) this.registerListeners();
	},
	
	/**
	 * @private 
	 * Registers some listeners on the INPUT field.
	 */
	registerListeners: function() {
		var e = $(this.id);
		this.keyListener = this.onKeyEvent.bindAsEventListener(this);
		this.blurListener = this.onBlurEvent.bindAsEventListener(this);
		Event.observe(e, "keyup",  this.keyListener);
		Event.observe(e, "keydown",  this.keyListener);
		Event.observe(e, "blur",  this.blurListener);
	},
	
	/**
	 * @private 
	 * De-registers the listeners.
	 */
	deregisterListeners: function() {
		var e = $(this.id);
		if (e == null) return;
		Event.stopObserving(e, "keyup", this.keyListener);
		Event.stopObserving(e, "keydown", this.keyListener);
		Event.stopObserving(e, "blur", this.blurListener);
	},
	
	/**
	 * @private 
	 * 
	 * Triggers a timer which starts validation
	 * when a certain time has elapsed after the last key
	 * was pressed by the user.
	 */
	onKeyEvent: function(event) {
			
		this.istyping = true;
		
		/*if (event.keyCode == 27) {
			// ESCAPE was pressed, so close submenu.
			this.control.cancel(); 
			return;
		}*/
		
		if (event.keyCode == 9 || event.ctrlKey || event.altKey || event.keyCode == 18 || event.keyCode == 17 ) {
			// TAB, CONTROL OR ALT was pressed, do nothing
			return;
		}
		
		if ((event.ctrlKey || event.altKey) && event.keyCode == 32) {
			// autoCompletion request, do nothing
			return;
		}
		
		if (event.keyCode >= 37 && event.keyCode <= 40) {
			// cursor keys, do nothing
			return;
		}
		
		if (!this.timerRegistered) {
			this.control.reset(this.id);
			this.timedCallback(this.validate.bind(this));
			this.timerRegistered = true;
		}
		
	},
	
	/**
	 * Validate on blur if content has changed since last validation
	 */
	onBlurEvent: function(event) {
		if (this.lastValidation != null && this.lastValidation != $F(this.id)) {
			this.validate();
		}
	},
	/**
	 * @private
	 * 
	 * Callback which calls itsself after timeout, if typing continues.
	 */
	timedCallback: function(fnc){
		if(this.istyping){
			this.istyping = false;
			var cb = this.timedCallback.bind(this, fnc);
			setTimeout(cb, 1000);
		} else {	
			fnc(this.id);
			this.timerRegistered = false;
			this.istyping = false;
		}
	},
	
	/**
	 * @private
	 * 
	 * Calls validation function and control.enable,
	 * if validation has a defined value (true/false)
	 */
	validate: function() {
		this.lastValidation = $F(this.id);
		this.isValid = this.validate_fnc(this.id);
		if (this.isValid !== null) {
			this.control.enable(this.isValid, this.id);
		}
	}
	
}

/**
 * Validates if a title exists (or does not exist).
 * 
 */
var OBInputTitleValidator = Class.create();
OBInputTitleValidator.prototype = Object.extend(new OBInputFieldValidator(), {
	
	/**
	 * @public
	 * Constructor
	 * 
	 * @param id ID of INPUT element
 	 * @param ns namespace for which existance is tested.
 	 * @param mustExist If true, existance is validated. Otherwise non-existance.
 	 * @param control Control object (derived from OBOntologySubMenu)
	 */
	initialize: function(id, ns, mustExist, control) {
		this.OBInputFieldValidator(id, false, control, this._checkIfArticleExists.bind(this));
		this.ns = ns;
		this.mustExist = mustExist;
		this.pendingElement = new OBPendingIndicator();
	},
	
	/**
	 * @private 
	 * 
	 * Checks if article exists and enables/disables command.
	 */
	_checkIfArticleExists: function(id) {
		function ajaxResponseExistsArticle (id, request) {
			this.pendingElement.hide();
			var answer = request.responseText;
			var regex = /(true|false)/;
			var parts = answer.match(regex);
			
			// check if title got empty in the meantime
			if ($F(id) == '') {
				this.control.enable(false, id);
				return;
			}
			if (parts == null) {
				// call fails for some reason. Do nothing!
				this.isValid = false;
				this.control.enable( false, id);
				return;
			} else if (parts[0] == 'true') {
				// article exists -> MUST NOT exist
				this.isValid = this.mustExist;
				this.control.enable(this.mustExist, id);
				return;
			} else {
				this.isValid=!this.mustExist;
				this.control.enable(!this.mustExist, id);
				
			}
		};
		
		var pageName = $F(this.id);
		if (pageName == '') {
			this.control.enable(false, this.id);
			return;
		}
		this.pendingElement.show(this.id)
		var pageNameWithNS = this.ns == '' ? pageName : this.ns+":"+pageName;
		sajax_do_call('smwfExistsArticleIgnoreRedirect', 
		              [pageNameWithNS], 
		              ajaxResponseExistsArticle.bind(this, this.id));
		return null;
	}
	
		
});

/**
 * Base class for OntologyBrowser submenu GUI elements.
 */
var OBOntologySubMenu = Class.create();
OBOntologySubMenu.prototype = { 
	
	/**
	 * @param id ID of DIV element containing the menu
	 * @param objectname Name of JS object (in order to refer in HTML links to it)
	 */
	initialize: function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
	},
	
	OBOntologySubMenu: function(id, objectname) {
		this.id = id;
		this.objectname = objectname;
				
		this.commandID = null;
		
		
		this.envContainerID = null;
		this.oldHeight = 0;
		
		this.menuOpened = false;
		
		
	},
	/**
	 * @public
	 * 
	 * Shows menu subview.
	 * 
	 * @param commandID command to execute.
	 * @param envContainerID ID of container which contains the menu.
	 */
	showContent: function(commandID, envContainerID) {
		if (this.menuOpened) {
			this._cancel();
		}
		this.commandID = commandID;
		this.envContainerID = envContainerID;
		$(this.id).replace(this.getUserDefinedControls());
					  
		// adjust parent container size
		this.oldHeight = $(envContainerID).getHeight();
		this.adjustSize();
		this.setValidators();
		this.setFocus();
		
		this.menuOpened = true;		
	},
	
	/**
	 * @public
	 * 
	 * Adjusts size if menu is modified.
	 */
	adjustSize: function() {
		var menuBarHeight = $(this.id).getHeight();
		var newHeight = (this.oldHeight-menuBarHeight-2)+"px";
		$(this.envContainerID).setStyle({ height: newHeight});
		
	},
	
	
	/**
	 * @public
	 * 
	 * Close subview
	 */
	_cancel: function() {
			
		// reset height
		var newHeight = (this.oldHeight-2)+"px";
		$(this.envContainerID).setStyle({ height: newHeight});
		
		// remove DIV content
		$(this.id).replace('<div id="'+this.id+'">');
		this.menuOpened = false;
	},
	
	/**
	 * @abstract
	 * 
	 * Set validators for input fields.
	 */
	setValidators: function() {
		
	},
	
	/**
	 * @abstract 
	 * 
	 * Returns HTML string with user defined content of th submenu.
	 */
	getUserDefinedControls: function() {
		
	},
	
	/**
	 * @abstract 
	 * 
	 * Executes a command
	 */
	doCommand: function() {
		
	},
	/**
	 * @abstract
	 * 
	 * Enables or disables a INPUT field and (not necessarily) the command button. 
	 */
	enable: function(b, id) {
		// no impl
	},
	
	/**
	 * @abstract
	 * 
	 * Resets a INPUT field and disables (not necessarily) the command button.
	 */
	reset: function(id) {
		// no impl
	}
			
}

/**
 * CategoryTree submenu
 */
var OBCatgeorySubMenu = Class.create();
OBCatgeorySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
	initialize: function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
	
		this.titleInputValidator = null;
		this.selectedTitle = null;
		this.selectedID = null;
	
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
			
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBCATEGORY: {
				ontologyTools.addSubcategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: {
				ontologyTools.addSubcategoryOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_SUBCATEGORY_RENAME: {
				ontologyTools.renameCategory($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_SUBCATEGORY_RENAME: return 'OB_RENAME';
			case SMW_OB_COMMAND_ADDSUBCATEGORY_SAMELEVEL: // fall through
			case SMW_OB_COMMAND_ADDSUBCATEGORY: return 'OB_CREATE';
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		var titlevalue = this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
		return '<div id="'+this.id+'">' +
					'<div style="display: block; height: 22px;">' +
					'<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
					'<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
					'<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
					(this.commandID == SMW_OB_COMMAND_SUBCATEGORY_RENAME ? ' | <a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' : '') +
					'</div>' +
	            '<div id="preview_category_tree"/></div>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), false, this);
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this._cancel();
	},
	
	/**
	 * @public
	 * 
	 * Do preview 
	 */
	preview: function() {
		var pendingElement = new OBPendingIndicator();
		pendingElement.show($('preview_category_tree'));
		sajax_do_call('smwfPreviewRefactoring', [this.selectedTitle, SMW_CATEGORY_NS], this.pastePreview.bind(this, pendingElement));
	},
	
	/**
	 * @private
	 * 
	 * Pastes preview data
	 */
	pastePreview: function(pendingElement, request) {
		pendingElement.hide();
		var table = '<table border="0" class="menuBarConceptTree">'+request.responseText+'</table>';
		$('preview_category_tree').innerHTML = table;
		this.adjustSize();
	},
	
	/**
	 * @private 
	 * 
	 * Replaces the command button with an enabled/disabled version.
	 * 
	 * @param b enable/disable
	 * @param errorMessage message string defined in SMW_LanguageXX.js
	 */
	enableCommand: function(b, errorMessage) {
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" ' +
						'onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">' + 
						gLanguage.getMessage(errorMessage)+'</span>');
		}
	},
	
	/**
	 * @public
	 * 
	 * Enables or disables an INPUT field and enables or disables command button.
	 * 
	 * @param enabled/disable
	 * @param id ID of input field
	 */
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
		this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
	},
	
	/**
	 * Resets an input field and disables the command button. 
	 * 
	 * @param id ID of input field
	 */
	reset: function(id) {
		this.enableCommand(false, 'OB_ENTER_TITLE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	}
});


/**
 * PropertyTree submenu
 */
var OBPropertySubMenu = Class.create();
OBPropertySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
	initialize: function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
		this.selectedTitle = null;
		this.selectedID = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_PROPERTY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_ADDSUBPROPERTY: {
				ontologyTools.addSubproperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: {
				ontologyTools.addSubpropertyOnSameLevel($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: {
				ontologyTools.renameProperty($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			case SMW_OB_COMMAND_SUBPROPERTY_RENAME: return 'OB_RENAME';
			case SMW_OB_COMMAND_ADDSUBPROPERTY_SAMELEVEL: // fall through
			case SMW_OB_COMMAND_ADDSUBPROPERTY: return 'OB_CREATE';
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		var titlevalue = this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
		return '<div id="'+this.id+'">' +
					'<div style="display: block; height: 22px;">' +
					'<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
					'<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
					'<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
					(this.commandID == SMW_OB_COMMAND_SUBPROPERTY_RENAME ? ' | <a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' : '') +
	            '</div>' +  '<div id="preview_property_tree"/></div>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		
		this._cancel();
	},
	
	preview: function() {
		sajax_do_call('smwfPreviewRefactoring', [this.selectedTitle, SMW_PROPERTY_NS], this.pastePreview.bind(this));
	},
	
	pastePreview: function(request) {
		var table = '<table border="0" class="menuBarPropertyTree">'+request.responseText+'</table>';
		$('preview_property_tree').innerHTML = table;
		this.adjustSize();
	},
	
	/**
	 * @private 
	 * 
	 * Replaces the command button with an enabled/disabled version.
	 * 
	 * @param b enable/disable
	 * @param errorMessage message string defined in SMW_LanguageXX.js
	 */
	enableCommand: function(b, errorMessage) {
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
		}
	},
	
	/**
	 * @public
	 * 
	 * Enables or disables an INPUT field and enables or disables command button.
	 * 
	 * @param enabled/disable
	 * @param id ID of input field
	 */
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
		this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
	},
	
	/**
	 * Resets the input field and disables the command button. 
	 * 
	 * @param id ID of input field
	 */
	reset: function(id) {
		this.enableCommand(false, 'OB_ENTER_TITLE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	}
});

/**
 * Instance list submenu
 */
var OBInstanceSubMenu = Class.create();
OBInstanceSubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
	initialize: function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
			
		this.selectedTitle = null;
		this.selectedID = null;
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_INSTANCE_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function(directCommandID) {
		var commandID = directCommandID ? directCommandID : this.commandID
		switch(commandID) {
			
			case SMW_OB_COMMAND_INSTANCE_RENAME: {
				ontologyTools.renameInstance($F(this.id+'_input_ontologytools'), this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			case SMW_OB_COMMAND_INSTANCE_DELETE: {
				ontologyTools.deleteInstance(this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_INSTANCE_RENAME: return 'OB_RENAME';
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		var titlevalue = this.commandID == SMW_OB_COMMAND_INSTANCE_RENAME ? this.selectedTitle.replace(/_/g, " ") : '';
		return '<div id="'+this.id+'">' +
					'<div style="display: block; height: 22px;">' +
					'<input style="display:block; width:45%; float:left" id="'+this.id+'_input_ontologytools" type="text" value="'+titlevalue+'"/>' +
					'<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
					'<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a> | ' +
					'<a onclick="'+this.objectname+'.preview()" id="'+this.id+'_preview_ontologytools">'+gLanguage.getMessage('OB_PREVIEW')+'</a>' +
	            '</div>' +  '<div id="preview_instance_list"/></div>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_input_ontologytools', '', false, this);
			
	},
	
	setFocus: function() {
		$(this.id+'_input_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		
		this._cancel();
	},
	
	preview: function() {
		sajax_do_call('smwfPreviewRefactoring', [this.selectedTitle, SMW_INSTANCE_NS], this.pastePreview.bind(this));
	},
	
	pastePreview: function(request) {
		var table = '<table border="0" class="menuBarInstance">'+request.responseText+'</table>';
		$('preview_instance_list').innerHTML = table;
		this.adjustSize();
	},
	
	/**
	 * @private 
	 * 
	 * Replaces the command button with an enabled/disabled version.
	 * 
	 * @param b enable/disable
	 * @param errorMessage message string defined in SMW_LanguageXX.js
	 */
	enableCommand: function(b, errorMessage) {
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
		}
	},
	
	/**
	 * @public
	 * 
	 * Enables or disables an INPUT field and enables or disables command button.
	 * 
	 * @param enabled/disable
	 * @param id ID of input field
	 */
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
		this.enableCommand(b, b ?  this.getCommandText() : $F(id) == '' ? 'OB_ENTER_TITLE': 'OB_TITLE_EXISTS');
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
	},
	
	/**
	 * Resets the input field and disables the command button. 
	 * 
	 * @param id ID of input field
	 */
	reset: function(id) {
		this.enableCommand(false, 'OB_ENTER_TITLE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	}
});

/**
 * Schema Property submenu
 */
var OBSchemaPropertySubMenu = Class.create();
OBSchemaPropertySubMenu.prototype = Object.extend(new OBOntologySubMenu(), {
	initialize: function(id, objectname) {
		this.OBOntologySubMenu(id, objectname);
	
		
		this.selectedTitle = null;
		this.selectedID = null;
		
		this.maxCardValidator = null;
		this.minCardValidator = null;
		this.rangeValidators = [];
		
		this.builtinTypes = [];
		this.count = 0;
		
		selectionProvider.addListener(this, OB_SELECTIONLISTENER);
		this.requestTypes();
	},
	
	selectionChanged: function(id, title, ns, node) {
		if (ns == SMW_CATEGORY_NS) {
			this.selectedTitle = title;
			this.selectedID = id;
		}
	},
	
	doCommand: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: {
				var propertyTitle = $F(this.id+'_propertytitle_ontologytools');
				var minCard = $F(this.id+'_minCard_ontologytools');
				var maxCard = $F(this.id+'_maxCard_ontologytools');
				var rangeOrTypes = [];
				for (var i = 0; i < this.count; i++) {
					if ($('typeRange'+i+'_ontologytools') != null) {
						rangeOrTypes.push($F('typeRange'+i+'_ontologytools'));
					}
				}
				ontologyTools.addSchemaProperty(propertyTitle, minCard, maxCard, rangeOrTypes, this.builtinTypes, this.selectedTitle, this.selectedID);
				this.cancel();
				break;
			}
			
			default: alert('Unknown command!');
		}
	},
	
	getCommandText: function() {
		switch(this.commandID) {
			
			case SMW_OB_COMMAND_ADD_SCHEMAPROPERTY: return 'OB_CREATE';
			
			
			default: return 'Unknown command';
		}
		
	},
	
	getUserDefinedControls: function() {
		return '<div id="'+this.id+'">' +
					 '<table class="menuBarProperties"><tr>' +
						'<td width="60px;">'+gLanguage.getMessage('NAME')+'</td>' +
						'<td><input id="'+this.id+'_propertytitle_ontologytools" type="text" tabIndex="101"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="60px;">'+gLanguage.getMessage('MIN_CARD')+'</td>' +
						'<td><input id="'+this.id+'_minCard_ontologytools" type="text" size="5" tabIndex="102"/></td>' +
					'</tr>' +
					'<tr>' +
						'<td width="60px;">'+gLanguage.getMessage('MAX_CARD')+'</td>' +
						'<td><input id="'+this.id+'_maxCard_ontologytools" type="text" size="5" tabIndex="103"/></td>' +
					'</tr>' +
				'</table>' +
				'<table class="menuBarProperties" id="typesAndRanges"></table>' +
				'<table class="menuBarProperties">' +
					'<tr>' +
						'<td><a onclick="'+this.objectname+'.addType()">'+gLanguage.getMessage('ADD_TYPE')+'</a></td>' +
						'<td><a onclick="'+this.objectname+'.addRange()">'+gLanguage.getMessage('ADD_RANGE')+'</a></td>' +
					'</tr>' +
				'</table>' + '<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage('OB_ENTER_TITLE')+'</span> | ' +
					'<a onclick="'+this.objectname+'.cancel()">'+gLanguage.getMessage('CANCEL')+'</a>' +
	            '</div>';
	},
	
	setValidators: function() {
		this.titleInputValidator = new OBInputTitleValidator(this.id+'_propertytitle_ontologytools', gLanguage.getMessage('PROPERTY_NS_WOC'), false, this);
		this.maxCardValidator = new OBInputFieldValidator(this.id+'_maxCard_ontologytools', true, this, this.checkMaxCard.bind(this));
		this.minCardValidator = new OBInputFieldValidator(this.id+'_minCard_ontologytools', true, this, this.checkMinCard.bind(this));
		this.rangeValidators = [];
	},
	
	/**
	 * @private
	 * 
	 * Check if max cardinality is an integer > 0
	 */
	checkMaxCard: function() {
		var maxCard = $F(this.id+'_maxCard_ontologytools');
		var valid = maxCard == '' || (maxCard.match(/^\d+$/) != null && parseInt(maxCard) > 0) ;
		return valid;
	},
	
	/**
	 * @private
	 * 
	 * Check if min cardinality is an integer >= 0
	 */
	checkMinCard: function() {
		var minCard = $F(this.id+'_minCard_ontologytools');
		var valid = minCard == '' || (minCard.match(/^\d+$/) != null && parseInt(minCard) >= 0) ;
		return valid;
	},
	
	
	
	setFocus: function() {
		$(this.id+'_propertytitle_ontologytools').focus();	
	},
	
	cancel: function() {
		this.titleInputValidator.deregisterListeners();
		this.maxCardValidator.deregisterListeners();
		this.minCardValidator.deregisterListeners();
		this.rangeValidators.each(function(e) { if (e!=null) e.deregisterListeners() });
		
		this._cancel();
	},
	
	enable: function(b, id) {
		var bg_color = b ? '#0F0' : $F(id) == '' ? '#FFF' : '#F00';
	
		$(id).setStyle({
			backgroundColor: bg_color
		});
		
		this.enableCommand(this.allIsValid(), this.getCommandText());
		
	},
	
	reset: function(id) {
		this.enableCommand(false, 'OB_CREATE');
		$(id).setStyle({
				backgroundColor: '#FFF'
		});
	},
	
	/**
	 * @private 
	 * 
	 * Replaces the command button with an enabled/disabled version.
	 * 
	 * @param b enable/disable
	 * @param errorMessage message string defined in SMW_LanguageXX.js
	 */
	enableCommand: function(b, errorMessage) {
		if (b) {
			$(this.id+'_apply_ontologytools').replace('<a style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools" onclick="'+this.objectname+'.doCommand()">'+gLanguage.getMessage(this.getCommandText())+'</a>');
		} else {
			$(this.id+'_apply_ontologytools').replace('<span style="margin-left: 10px;" id="'+this.id+'_apply_ontologytools">'+gLanguage.getMessage(errorMessage)+'</span>');
		}
	},
	
	/**
	 * @abstract
	 * 
	 * Checks if a INPUTs are valid
	 * 
	 * @return true/false
	 */
	allIsValid: function() {
		var valid =  this.titleInputValidator.isValid && this.maxCardValidator.isValid &&  this.minCardValidator.isValid;
		this.rangeValidators.each(function(e) { if (e!=null) valid &= e.isValid });
		return valid;
	},
	
	/**
	 * @private
	 * 
	 * Requests builtin types from wiki via AJAX call.
	 */
	requestTypes: function() {
				
		function fillBuiltinTypesCallback(request) {
			this.builtinTypes = this.builtinTypes.concat(request.responseText.split(","));
			
		}
		
		function fillUserTypesCallback(request) {
			var userTypes = request.responseText.split(",");
			// remove first element
			userTypes.shift();
			this.builtinTypes = this.builtinTypes.concat(userTypes);
		}
		
		sajax_do_call('smwfGetBuiltinDatatypes', 
		              [], 
		              fillBuiltinTypesCallback.bind(this));	
		sajax_do_call('smwfGetUserDatatypes', 
		              [], 
		              fillUserTypesCallback.bind(this));	
	},
	
	/**
	 * @private
	 * 
	 * Creates new type selection box
	 * 
	 * @return HTML
	 */
	newTypeInputBox: function() {
		var toReplace = '<select id="typeRange'+this.count+'_ontologytools" name="types'+this.count+'">';
		for(var i = 1; i < this.builtinTypes.length; i++) {
			toReplace += '<option>'+this.builtinTypes[i]+'</option>';
		}
		toReplace += '</select><img style="cursor: pointer; cursor: hand;" src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', false)"/>';
	
		return toReplace;
	},
	
	/**
	 * @private
	 * 
	 * Creates new range category selection box with auto-completion.
	 * 
	 * @return HTML
	 */
	newRangeInputBox: function() {
		var toReplace = '<input class="wickEnabled" typeHint="14" type="text" id="typeRange'+this.count+'_ontologytools" tabIndex="'+(this.count+104)+'"/>';
		toReplace += '<img style="cursor: pointer; cursor: hand;" src="'+wgServer+wgScriptPath+'/extensions/SMWHalo/skins/redcross.gif" onclick="'+this.objectname+'.removeTypeOrRange(\'typeRange'+this.count+'_ontologytools\', true)"/>';
		return toReplace;
	},
	
	/**
	 * @private 
	 * 
	 * Adds additional type selection box in typesRange container.
	 */
	addType: function() {
		if (this.builtinTypes == null) {
			return;
		}
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="60px;">Type </td><td>'+this.newTypeInputBox()+'</td></tr>');
		
		this.count++;
		this.adjustSize();
	},
	
	/**
	 * @private 
	 * 
	 * Adds additional range category selection box in typesRange container.
	 */
	addRange: function() {
		// tbody already in DOM?
		var addTo = $('typesAndRanges').firstChild == null ? $('typesAndRanges') : $('typesAndRanges').firstChild;
		
		autoCompleter.deregisterAllInputs();
		// create dummy element and replace afterwards
		var toReplace = $(addTo.appendChild(document.createElement("tr")));
		toReplace.replace('<tr><td width="60px;">Range </td><td>'+this.newRangeInputBox()+'</td></tr>');
		autoCompleter.registerAllInputs();
		
		this.rangeValidators[this.count] = (new OBInputTitleValidator('typeRange'+this.count+'_ontologytools', gLanguage.getMessage('CATEGORY_NS_WOC'), true, this));
		this.enable(false, 'typeRange'+this.count+'_ontologytools');
		
		this.count++;
		this.adjustSize();
	},
	
	/**
	 * @private 
	 * 
	 * Removes type or range category selection box from typesRange container.
	 */
	removeTypeOrRange: function(id, isRange) {
		
		if (isRange) {		
			// deregisterValidator
			var match = /typeRange(\d+)/;
			var num = match.exec(id)[1];
			this.rangeValidators[num].deregisterListeners();
			this.rangeValidators[num] = null;
		}
		
		var row = $(id);
		while(row.parentNode.getAttribute('id') != 'typesAndRanges') row = row.parentNode;
		// row is tbody element
		row.removeChild($(id).parentNode.parentNode);
		
		this.enableCommand(this.allIsValid(), this.getCommandText());
		this.adjustSize();
	}
});

var obCategoryMenuProvider = new OBCatgeorySubMenu('categoryTreeMenu', 'obCategoryMenuProvider');
var obPropertyMenuProvider = new OBPropertySubMenu('propertyTreeMenu', 'obPropertyMenuProvider');
var obInstanceMenuProvider = new OBInstanceSubMenu('instanceListMenu', 'obInstanceMenuProvider');
var obSchemaPropertiesMenuProvider = new OBSchemaPropertySubMenu('schemaPropertiesMenu', 'obSchemaPropertiesMenuProvider');